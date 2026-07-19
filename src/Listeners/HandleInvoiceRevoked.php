<?php

namespace TelegramBotEssentials\Affiliates\Listeners;

use Brick\Math\BigDecimal;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use TelegramBotEssentials\Affiliates\Models\AffiliateTransaction;
use TelegramBotEssentials\Billing\Events\InvoiceRevoked;

class HandleInvoiceRevoked implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'billing';

    public function handle(InvoiceRevoked $event): void
    {
        $event->context->apply();

        // Not gated on affiliates.affiliates_status: if a commission was
        // already paid out, revoking the invoice must claw it back
        // regardless of whether the program has since been turned off.
        $transaction = AffiliateTransaction::where('invoice_id', $event->invoice->id)
            ->where('type', AffiliateTransaction::TYPE_PURCHASE_COMMISSION)
            ->where('status', AffiliateTransaction::STATUS_CREDITED)
            ->first();

        if (!$transaction) {
            return;
        }

        $referrer = $transaction->recipient;

        wHook()->runForUser($referrer, function () use ($transaction) {
            wallet()->adjustBalance(BigDecimal::of($transaction->amount)->negated(), allowNegative: true);

            $transaction->update(['status' => AffiliateTransaction::STATUS_REVERSED]);

            wHook()->api()->sendMessage([
                'chat_id' => wHook()->user()->telegramUser->peer_id,
                'text' => __('tbe-affiliates::affiliation.notifications.purchase_commission_reversed', [
                    'amount' => currency()->priceFormat($transaction->amount),
                ]),
                'reply_markup' => wHook()->user()->getKeyboard(),
            ]);
        });
    }
}
