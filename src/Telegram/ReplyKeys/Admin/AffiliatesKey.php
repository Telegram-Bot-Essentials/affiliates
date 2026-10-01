<?php

namespace TelegramBotEssentials\Affiliates\Telegram\ReplyKeys\Admin;

use TelegramBotEssentials\Affiliates\Telegram\Features\Admin\AffiliatesFeature;
use TelegramBotEssentials\Essence\Enums\Roles;
use TelegramBotEssentials\Essence\Telegram\ReplyKeys\ReplyKey;

class AffiliatesKey extends ReplyKey
{
    protected int $perm = Roles::ADMIN->value;

    protected function text(): string
    {
        return __('tbe-affiliates::admin.reply_key');
    }

    public function handle(): void
    {
        AffiliatesFeature::menu()->send();
    }
}
