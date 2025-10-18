<?php

namespace TelegramBotEssentials\Affiliates\Telegram\StateAnswers\Member;

use TelegramBotEssentials\Essence\Enums\AllowableFields;
use TelegramBotEssentials\Essence\Enums\Roles;
use TelegramBotEssentials\Essence\Telegram\StateAnswers\StateAnswer;

class AffiliationAnswer extends StateAnswer
{
    protected string $type = 'AFFILIATION';
    protected int $perm = Roles::MEMBER->value;
    protected array $allowedFields = [
        AllowableFields::TEXT->value
    ];

    // TODO: Implement cancel() method for custom cancellation logic
    // function cancel(): void
    // {
    // }
}
