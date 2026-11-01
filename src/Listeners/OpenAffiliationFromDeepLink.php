<?php

namespace TelegramBotEssentials\Affiliates\Listeners;

use TelegramBotEssentials\Affiliates\Telegram\Features\Member\AffiliationFeature;
use TelegramBotEssentials\Essence\Events\BotDeepLinkReceived;

/** Opens the affiliate menu in the private chat, from the link /affiliation sends in a group. */
class OpenAffiliationFromDeepLink
{
    public const PAYLOAD = 'open_affiliation';

    public function handle(BotDeepLinkReceived $event): void
    {
        if ($event->payload !== self::PAYLOAD || ! settings()->get('affiliates.affiliates_status')) {
            return;
        }

        AffiliationFeature::menu()->send();
    }
}
