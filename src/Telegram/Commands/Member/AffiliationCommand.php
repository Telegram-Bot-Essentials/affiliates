<?php

namespace TelegramBotEssentials\Affiliates\Telegram\Commands\Member;

use TelegramBotEssentials\Affiliates\Telegram\Features\Member\AffiliationFeature;
use TelegramBotEssentials\Essence\Enums\Roles;
use TelegramBotEssentials\Essence\Telegram\Commands\Command;

class AffiliationCommand extends Command
{
    protected string $name = 'affiliation';

    protected int $perm = Roles::MEMBER->value;

    protected function description(): string
    {
        return __('tbe-affiliates::affiliation.command_description');
    }

    public function handle(): ?bool
    {
        dependsOn(settings()->get('affiliates.affiliates_status'));

        AffiliationFeature::menu()->send();

        return true;
    }
}
