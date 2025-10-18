<?php

namespace TelegramBotEssentials\Affiliates\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use TelegramBotEssentials\Billing\Events\InvoiceRevoked;

class HandleInvoiceRevoked implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'billing';

    public function handle(InvoiceRevoked $event): void
    {
        $event->context->apply();

        debugMessage(wHook()->user()->telegramUser->full_name . ' revoked a payment for amount of: ' . currency()->priceFormat($event->invoice->price));
    }
}
