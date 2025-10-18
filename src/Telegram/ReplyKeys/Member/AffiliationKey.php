<?php

namespace TelegramBotEssentials\Affiliates\Telegram\ReplyKeys\Member;

use TelegramBotEssentials\Affiliates\Telegram\Features\Member\AffiliationFeature;
use TelegramBotEssentials\Essence\Enums\Roles;
use TelegramBotEssentials\Essence\Telegram\ReplyKeys\ReplyKey;

class AffiliationKey extends ReplyKey
{
    protected string $text = 'Affiliation';
    protected int $perm = Roles::MEMBER->value;
    protected string $response = 'Affiliation executed successfully.';

    public function __construct()
    {
        // Multilingual translations
         $this->text = __('Affiliation Program');
        // $this->response = __('');
    }

    public function handle(): void
    {
        AffiliationFeature::menu()->send();
        // Logic to execute
    }
}
