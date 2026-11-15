<?php

declare(strict_types=1);

use Illuminate\Http\Client\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use TelegramBotEssentials\Affiliates\Models\Affiliate;
use TelegramBotEssentials\Affiliates\Models\Referral;
use TelegramBotEssentials\Announcements\Services\AlertSweep;

beforeEach(function () {
    Cache::flush();
    // The base test case answers every request with an empty body, and the first fake wins.
    Http::swap(new Factory);
    Http::fake([
        '*/getMe' => Http::response(['ok' => true, 'result' => ['id' => 1, 'is_bot' => true, 'first_name' => 'Shop', 'username' => 'shop_bot']]),
        '*' => Http::response(['ok' => true, 'result' => ['message_id' => 1, 'date' => 0, 'chat' => ['id' => 1, 'type' => 'private']]]),
    ]);
    config(['queue.default' => 'sync']);
    Carbon::setTestNow('2026-11-15 12:00:00');

    $this->bot = $this->makeBot();
    wHook()->setBot($this->bot);
    settings()->set('affiliates.affiliates_status', true, $this->bot);
    settings()->set('affiliates.share_percentage', 15, $this->bot);

    $this->alert = announcementAlerts()->get('affiliates.intro');

    // A member in a private chat with the bot, who joined $daysAgo days ago.
    $this->member = function (int $peerId, int $daysAgo) {
        $user = $this->makeBotUser($this->bot, $peerId, ['has_private_chat' => true]);
        $user->forceFill(['created_at' => now()->subDays($daysAgo)])->save();

        return $user;
    };

    $this->sweep = fn (): int => wHook()->runInContext($this->bot, null, null, null, fn () => app(AlertSweep::class)->run($this->bot));
    $this->texts = fn (): array => collect(Http::recorded())
        ->map(fn (array $pair) => $pair[0])
        ->filter(fn ($request) => str_contains((string) $request->url(), '/sendMessage'))
        ->map(fn ($request) => [$request['chat_id'], $request['text']])
        ->values()->all();
});

it('offers the alert only while affiliates is switched on, and off until an admin says so', function () {
    expect($this->alert)->not->toBeNull()
        ->and(announcementAlerts()->enabled($this->alert))->toBeFalse();

    settings()->set('affiliates.affiliates_status', false, $this->bot);

    expect(announcementAlerts()->get('affiliates.intro'))->toBeNull();
});

it('invites members who brought nobody in yet, with the share and their own link', function () {
    ($this->member)(4001, 5);
    ($this->member)(4002, 1);

    $referrer = ($this->member)(4003, 5);
    $affiliate = Affiliate::create(['bot_id' => $this->bot->id, 'bot_user_id' => $referrer->id, 'referral_code' => 'CODE123']);
    $referred = ($this->member)(4004, 0);
    Referral::create(['bot_id' => $this->bot->id, 'affiliate_id' => $affiliate->id, 'bot_user_id' => $referred->id]);

    settings()->set(announcementAlerts()->settingKey($this->alert, 'status'), true, $this->bot);

    expect(($this->sweep)())->toBe(1);

    [[$chat, $text]] = ($this->texts)();
    $code = Affiliate::query()->whereHas('botUser', fn ($user) => $user->where('telegram_user_peer_id', 4001))->value('referral_code');

    expect($chat)->toBe(4001)
        ->and($text)->toContain('15%')
        ->and($text)->toContain("https://t.me/shop_bot?start={$code}");
});

it('invites a member once only', function () {
    ($this->member)(4001, 5);
    settings()->set(announcementAlerts()->settingKey($this->alert, 'status'), true, $this->bot);

    ($this->sweep)();
    Carbon::setTestNow(now()->addDays(40));

    expect(($this->sweep)())->toBe(0);
});
