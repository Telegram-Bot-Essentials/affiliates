<?php

namespace TelegramBotEssentials\Affiliates\Telegram\Commands\Member;

use Telegram\Bot\Commands\Command;
use TelegramBotEssentials\Affiliates\Telegram\Features\Member\AffiliationFeature;

class AffiliationCommand extends Command
{
    protected string $name = 'affiliation';
    protected string $description;

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
