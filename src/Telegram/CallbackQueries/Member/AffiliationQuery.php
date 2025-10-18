<?php

namespace TelegramBotEssentials\Affiliates\Telegram\CallbackQueries\Member;

use TelegramBotEssentials\Affiliates\Telegram\Features\Member\AffiliationFeature;
use TelegramBotEssentials\Essence\Enums\Roles;
use TelegramBotEssentials\Essence\Telegram\CallbackQueries\CallbackQuery;

class AffiliationQuery extends CallbackQuery
{
    protected string $type = 'AFFILIATION';
    protected int $perm = Roles::MEMBER->value;

    public function activate(): void
    {
        if (wHook()->user()->affiliate) {
            return;
        }
        wHook()->user()->affiliate()->create([
            'referral_code' => uniqid()
        ]);
        AffiliationFeature::menu()->update();
    }

    public function getInviteLink(): void
    {
        wHook()->api()->sendMessage([
            'chat_id' => wHook()->user()->telegramUser->peer_id,
            'text' => wHook()->user()->affiliate->referral_code,
            'reply_markup' => wHook()->user()->getKeyboard()
        ]);
        $this->answer();
    }
}
