<?php

namespace TelegramBotEssentials\Affiliates\Telegram\ReplyKeys\Member;

use TelegramBotEssentials\Affiliates\Telegram\Features\Member\AffiliationFeature;
use TelegramBotEssentials\Essence\Enums\Roles;
use TelegramBotEssentials\Essence\Telegram\ReplyKeys\ReplyKey;

class AffiliationKey extends ReplyKey
{
    protected int $perm = Roles::MEMBER->value;

    protected function text(): string
    {
        return __('tbe-affiliates::affiliation.reply_key');
    }

    public function handle(): void
    {
        dependsOn(settings()->get('affiliates.affiliates_status'));

        AffiliationFeature::menu()->send();
    }

    public function isEnabled(): bool
    {
        return (bool) settings()->get('affiliates.affiliates_status');
    }
}
