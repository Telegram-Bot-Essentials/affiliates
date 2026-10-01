<?php

declare(strict_types=1);

use TelegramBotEssentials\Affiliates\Models\Affiliate;
use TelegramBotEssentials\Affiliates\Models\Referral;
use TelegramBotEssentials\Affiliates\Telegram\Features\Admin\AffiliatesFeature;
use TelegramBotEssentials\Essence\Models\BotUser;

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

it('lists every affiliate with the totals on top', function () {
    $response = AffiliatesFeature::menu();

    expect($response->text)->toContain('1')
        ->and(($this->callbacks)($response))->toContain(encodeCallback('AFFILIATES', 'user', [$this->referrer->id, 1, 'earned', 'desc']));
});

it('sorts the overview by referrals and flips the direction', function () {
    $other = $this->makeBotUser($this->bot, 1004);
    $quiet = Affiliate::create(['bot_id' => $this->bot->id, 'bot_user_id' => $other->id, 'referral_code' => 'QUIET']);

    $ids = fn (string $direction) => AffiliatesFeature::listQuery()->orderBy('referrals_count', $direction)->pluck('bot_user_id')->all();

    expect($ids('desc'))->toBe([$this->referrer->id, $other->id])
        ->and($ids('asc'))->toBe([$other->id, $this->referrer->id])
        ->and($quiet->id)->not->toBeNull();
});

it('goes back to the overview page the screen was opened from', function () {
    $callbacks = ($this->callbacks)(AffiliatesFeature::show($this->referrer, 2, 'referrals', 'asc'));

    expect($callbacks)->toContain(encodeCallback('AFFILIATES', 'menu', [2, 0, 'referrals', 'asc']));
});

it('sorts the user list by referrals and earnings', function () {
    $sorts = botUserSorts();
    $ordered = fn (string $key, string $direction) => $sorts->apply($key, BotUser::query(), $direction)->pluck('id')->all();

    expect($ordered('referrals', 'desc')[0])->toBe($this->referrer->id)
        ->and($sorts->getSort('referrals')->displayValue($this->referrer))->toBe('1')
        ->and($sorts->getSort('affiliate_earnings'))->not->toBeNull()
        ->and($ordered('affiliate_earnings', 'desc'))->toHaveCount(3);
});
