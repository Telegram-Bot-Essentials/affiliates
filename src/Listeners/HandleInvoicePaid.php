<?php

namespace TelegramBotEssentials\Affiliates\Listeners;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use TelegramBotEssentials\Affiliates\Models\AffiliateTransaction;
use TelegramBotEssentials\Affiliates\Models\Referral;
use TelegramBotEssentials\Billing\Events\InvoicePaid;
use TelegramBotEssentials\UserWallet\Models\CreditOrder;

class HandleInvoicePaid implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'billing';

    public function handle(InvoicePaid $event): void
    {
        $event->context->apply();

        if (!settings()->get('affiliates.affiliates_status')) {
            return;
        }

        // Wallet top-ups aren't a sale — paying commission on money a user
        // is simply moving into their own wallet would let a referred user
        // top up and instantly hand their referrer a cut of nothing bought.
        if ($event->invoice->payable instanceof CreditOrder) {
            return;
        }

        $referral = Referral::where('bot_user_id', wHook()->user()->id)->first();

        if (!$referral) {
            return;
        }

        $sharePercentage = BigDecimal::of((string) settings()->get('affiliates.share_percentage'));

        if (!$sharePercentage->isPositive()) {
            return;
        }

        // Round down — never over-pay a commission due to rounding.
        $commission = BigDecimal::of((string) $event->invoice->price)
            ->multipliedBy($sharePercentage)
            ->dividedBy(100, 10, RoundingMode::DOWN);

        if (!$commission->isPositive()) {
            return;
        }

        $referrer = $referral->affiliate->botUser;

        wHook()->runForUser($referrer, function () use ($referral, $commission, $event) {
            wallet()->adjustBalance($commission);

            AffiliateTransaction::create([
                'bot_id' => wHook()->bot()->id,
                'referral_id' => $referral->id,
                'recipient_bot_user_id' => wHook()->user()->id,
                'type' => AffiliateTransaction::TYPE_PURCHASE_COMMISSION,
                'invoice_id' => $event->invoice->id,
                'amount' => $commission,
                'status' => AffiliateTransaction::STATUS_CREDITED,
            ]);

            wHook()->api()->sendMessage([
                'chat_id' => wHook()->user()->telegramUser->peer_id,
                'text' => __('tbe-affiliates::affiliation.notifications.purchase_commission', [
                    'amount' => currency()->priceFormat($commission),
                ]),
                'reply_markup' => wHook()->user()->getKeyboard(),
            ]);
        });
    }
}
