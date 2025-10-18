<?php

namespace TelegramBotEssentials\Affiliates\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use TelegramBotEssentials\Billing\Events\InvoicePaid;

class HandleInvoicePaid implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'billing';

    public function handle(InvoicePaid $event): void
    {
        $event->context->apply();

        debugMessage(wHook()->user()->telegramUser->full_name . ' made a payment for amount of: ' . currency()->priceFormat($event->invoice->price));
    }
}
