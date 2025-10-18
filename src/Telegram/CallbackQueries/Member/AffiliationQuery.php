<?php

namespace TelegramBotEssentials\Affiliates\Telegram\CallbackQueries\Member;

use TelegramBotEssentials\Essence\Enums\Roles;
use TelegramBotEssentials\Essence\Telegram\CallbackQueries\CallbackQuery;

class AffiliationQuery extends CallbackQuery
{
    protected string $type = 'AFFILIATION';
    protected int $perm = Roles::MEMBER->value;

    public function start(): void
    {
        // Logic to execute
    }
}
