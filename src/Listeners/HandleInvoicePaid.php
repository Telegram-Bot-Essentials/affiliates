<?php

namespace TelegramBotEssentials\Affiliates\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use TelegramBotEssentials\Billing\Events\InvoicePaid;

class HandleInvoicePaid implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'affiliates';

    public function handle(InvoicePaid $event): void
    {
        if (!$event->bot || !$event->botUser) {
            debugMessage("InvoicePaid event received without bot context, invoice #{$event->invoice->id}");
            return;
        }

        wHook()->setBot($event->bot);
        wHook()->setUser($event->botUser);

        debugMessage('InvoicePaid event received by affiliates package');
        debugMessage(json_encode($event->invoice, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        debugMessage(json_encode($event->bot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        debugMessage(json_encode($event->botUser, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
