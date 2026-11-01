<?php

namespace TelegramBotEssentials\Affiliates\Telegram\Commands\Member;

use TelegramBotEssentials\Affiliates\Listeners\OpenAffiliationFromDeepLink;
use TelegramBotEssentials\Affiliates\Telegram\Features\Member\AffiliationFeature;
use TelegramBotEssentials\Essence\Enums\ChatScope;
use TelegramBotEssentials\Essence\Telegram\Commands\Command;
use TelegramBotEssentials\Essence\Telegram\TelegramResponse;

class AffiliationCommand extends Command
{
    protected string $name = 'affiliation';

    protected ChatScope $chatScope = ChatScope::Any;

    protected function description(): string
    {
        return __('tbe-affiliates::affiliation.command_description');
    }

    public function handle(): ?bool
    {
        dependsOn(settings()->get('affiliates.affiliates_status'));

        // Earnings and the referral code are the member's own.
        if (wHook()->inGroup()) {
            TelegramResponse::continueInPrivate(OpenAffiliationFromDeepLink::PAYLOAD)->send();

            return true;
        }

        AffiliationFeature::menu()->send();

        return true;
    }
}
