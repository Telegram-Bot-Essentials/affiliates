<?php

declare(strict_types=1);

use TelegramBotEssentials\Affiliates\Models\Affiliate;
use TelegramBotEssentials\Affiliates\Models\Referral;
use TelegramBotEssentials\Affiliates\Telegram\Features\Admin\AffiliatesFeature;

beforeEach(function () {
    $this->bot = $this->makeBot();
    wHook()->setBot($this->bot);

    $this->referrer = $this->makeBotUser($this->bot, 1001);
    $this->friend = $this->makeBotUser($this->bot, 1002);
    $this->loner = $this->makeBotUser($this->bot, 1003);

    $this->affiliate = Affiliate::create([
        'bot_id' => $this->bot->id,
        'bot_user_id' => $this->referrer->id,
        'referral_code' => 'CODE123',
    ]);
    Referral::create([
        'bot_id' => $this->bot->id,
        'affiliate_id' => $this->affiliate->id,
        'bot_user_id' => $this->friend->id,
    ]);

    $this->callbacks = fn ($response) => collect($response->replyMarkup->toArray()['inline_keyboard'])->flatten(1)->pluck('callback_data');
});

it('shows a referrer their code, referral count and the members they brought in', function () {
    $response = AffiliatesFeature::show($this->referrer);

    expect($response->text)->toContain('CODE123')->toContain('1')
        ->and(($this->callbacks)($response))->toContain(
            encodeCallback('BOTUSERS', 'show', [$this->friend->id]),
            encodeCallback('BOTUSERS', 'show', [$this->referrer->id]),
        );
});

it('shows a referred member who referred them', function () {
    $response = AffiliatesFeature::show($this->friend);

    expect($response->text)->not->toContain('—')
        ->and(($this->callbacks)($response))->toContain(encodeCallback('BOTUSERS', 'show', [$this->referrer->id]));
});

it('says so when a member has no affiliation at all', function () {
    expect(AffiliatesFeature::show($this->loner)->text)->toContain(__('tbe-affiliates::admin.show.text.noAffiliate'));
});
