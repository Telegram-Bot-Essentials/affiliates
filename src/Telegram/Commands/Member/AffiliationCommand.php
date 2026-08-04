<?php

namespace TelegramBotEssentials\Affiliates\Telegram\Commands\Member;

use TelegramBotEssentials\Affiliates\Telegram\Features\Member\AffiliationFeature;
use TelegramBotEssentials\Essence\Enums\Roles;
use TelegramBotEssentials\Essence\Telegram\Commands\Command;

class AffiliationCommand extends Command
{
    protected string $name = 'affiliation';
    protected string $description;
    protected int $perm = Roles::MEMBER->value;

    public function __construct()
    {
        $this->description = __('tbe-affiliates::affiliation.command_description');
    }

    public function handle(): void
    {
        dependsOn(settings()->get('affiliates.affiliates_status'));

        AffiliationFeature::menu()->send();
    }
}
