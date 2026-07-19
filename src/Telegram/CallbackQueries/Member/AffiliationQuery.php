<?php

namespace TelegramBotEssentials\Affiliates\Telegram\CallbackQueries\Member;

use TelegramBotEssentials\Affiliates\Telegram\Features\Member\AffiliationFeature;
use TelegramBotEssentials\Essence\Enums\Roles;
use TelegramBotEssentials\Essence\Telegram\CallbackQueries\CallbackQuery;

class AffiliationQuery extends CallbackQuery
{
    protected string $type = 'AFFILIATION';
    protected int $perm = Roles::MEMBER->value;

    public function getInviteLink(): void
    {
        dependsOn(settings()->get('affiliates.affiliates_status'));

        $affiliate = wHook()->user()->affiliate;

        wHook()->api()->sendMessage([
            'chat_id' => wHook()->user()->telegramUser->peer_id,
            'text' => __('tbe-affiliates::affiliation.share.text', [
                'link' => AffiliationFeature::referralLink($affiliate->referral_code),
            ]),
        ]);
        $this->answer();
    }
}
