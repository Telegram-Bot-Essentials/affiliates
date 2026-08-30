<?php

declare(strict_types=1);

use TelegramBotEssentials\Affiliates\Listeners\HandleAffiliateReferral;
use TelegramBotEssentials\Affiliates\Models\Affiliate;
use TelegramBotEssentials\Affiliates\Models\Referral;
use TelegramBotEssentials\Essence\Events\BotDeepLinkReceived;
use TelegramBotEssentials\Essence\Support\WebhookContext;
use TelegramBotEssentials\Settings\Services\Settings;

// HandleAffiliateReferral works off the current webhook user arriving with
// a deep-link payload. Signup bonuses stay at their 0 default here, so the
// listener only writes the Referral row - no wallet or Telegram calls.
beforeEach(function () {
    $this->bot = $this->makeBot();
    wHook()->setBot($this->bot);

    app(Settings::class)->set('affiliates.affiliates_status', true);

    $owner = $this->makeBotUser($this->bot, 1001);
    $this->affiliate = Affiliate::create([
        'bot_id' => $this->bot->id,
        'bot_user_id' => $owner->id,
        'referral_code' => 'CODE123',
    ]);
});

function arriveWith(string $payload, int $peerId): void
{
    $newUser = test()->makeBotUser(test()->bot, $peerId);
    wHook()->setUser($newUser);

    app(HandleAffiliateReferral::class)->handle(new BotDeepLinkReceived(
        new WebhookContext(botId: test()->bot->id, botUserId: $newUser->id),
        $payload,
    ));
}

it('attributes a new user to the affiliate whose code they arrived with', function () {
    arriveWith('CODE123', 2002);

    $referral = Referral::sole();
    expect($referral->affiliate_id)->toBe($this->affiliate->id)
        ->and((int) $referral->bot_id)->toBe($this->bot->id);
});

it('does nothing while affiliates are disabled', function () {
    app(Settings::class)->set('affiliates.affiliates_status', false);

    arriveWith('CODE123', 2003);

    expect(Referral::count())->toBe(0);
});

it('ignores an unknown referral code', function () {
    arriveWith('NOPE', 2004);

    expect(Referral::count())->toBe(0);
});

it('ignores a self-referral', function () {
    $owner = $this->affiliate->botUser;
    wHook()->setUser($owner);

    app(HandleAffiliateReferral::class)->handle(new BotDeepLinkReceived(
        new WebhookContext(botId: $this->bot->id, botUserId: $owner->id),
        'CODE123',
    ));

    expect(Referral::count())->toBe(0);
});

it('keeps the first attribution when a second code arrives', function () {
    $second = Affiliate::create([
        'bot_id' => $this->bot->id,
        'bot_user_id' => $this->makeBotUser($this->bot, 1002)->id,
        'referral_code' => 'CODE999',
    ]);

    $newUser = $this->makeBotUser($this->bot, 2005);
    wHook()->setUser($newUser);
    $context = new WebhookContext(botId: $this->bot->id, botUserId: $newUser->id);

    app(HandleAffiliateReferral::class)->handle(new BotDeepLinkReceived($context, 'CODE123'));
    app(HandleAffiliateReferral::class)->handle(new BotDeepLinkReceived($context, 'CODE999'));

    expect(Referral::count())->toBe(1)
        ->and(Referral::sole()->affiliate_id)->toBe($this->affiliate->id)
        ->and($second->refresh()->referrals()->count())->toBe(0);
});
