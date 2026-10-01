<?php

namespace TelegramBotEssentials\Affiliates\Telegram\CallbackQueries\Admin;

use TelegramBotEssentials\Affiliates\Telegram\Features\Admin\AffiliatesFeature;
use TelegramBotEssentials\Essence\Enums\Roles;
use TelegramBotEssentials\Essence\Models\BotUser;
use TelegramBotEssentials\Essence\Telegram\CallbackQueries\CallbackQuery;

class AffiliatesQuery extends CallbackQuery
{
    protected string $type = 'AFFILIATES';

    protected int $perm = Roles::ADMIN->value;

    /**
     * The "affiliation" section of a member's profile in user management.
     */
    public function user(BotUser $botUser): void
    {
        AffiliatesFeature::show($botUser)->update();
    }
}
