<?php

declare(strict_types=1);

use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use TelegramBotEssentials\Affiliates\Models\Affiliate;
use TelegramBotEssentials\Affiliates\Models\AffiliateTransaction;
use TelegramBotEssentials\Affiliates\Models\Referral;

beforeEach(function () {
    Cache::flush();
    // The base test case answers every request with an empty body, and the first fake wins.
    Http::swap(new Factory);
    Http::fake(['*/getMe' => Http::response(['ok' => true, 'result' => ['id' => 1, 'is_bot' => true, 'first_name' => 'Shop', 'username' => 'shop_bot']])]);

    $this->bot = $this->makeBot();
    $this->user = $this->makeBotUser($this->bot, 4242);
    wHook()->setBot($this->bot);

    settings()->set('affiliates.affiliates_status', true, $this->bot);
});

it('fills in the recipient\'s own referral link', function () {
    Affiliate::create(['bot_id' => $this->bot->id, 'bot_user_id' => $this->user->id, 'referral_code' => 'CODE123']);

    expect(announcementPlaceholders()->render('{$affiliates.link}', $this->user))
        ->toBe('https://t.me/shop_bot?start=CODE123');
});

it('gives a recipient with no referral code one, in their own bot', function () {
    $text = announcementPlaceholders()->render('{$affiliates.link}', $this->user);
    $affiliate = Affiliate::query()->where('bot_user_id', $this->user->id)->sole();

    expect($text)->toBe('https://t.me/shop_bot?start='.$affiliate->referral_code)
        ->and($affiliate->bot_id)->toBe($this->bot->id);

    announcementPlaceholders()->render('{$affiliates.link}', $this->user);

    expect(Affiliate::query()->where('bot_user_id', $this->user->id)->count())->toBe(1);
});

it('fills in what the recipient earned, counting credited commissions only', function () {
    $affiliate = Affiliate::create(['bot_id' => $this->bot->id, 'bot_user_id' => $this->user->id, 'referral_code' => 'CODE123']);
    $referral = Referral::create(['bot_id' => $this->bot->id, 'affiliate_id' => $affiliate->id, 'bot_user_id' => $this->makeBotUser($this->bot, 4243)->id]);

    foreach ([[3000, AffiliateTransaction::STATUS_CREDITED], [2000, AffiliateTransaction::STATUS_CREDITED], [9000, 'reversed']] as [$amount, $status]) {
        AffiliateTransaction::query()->create([
            'bot_id' => $this->bot->id, 'referral_id' => $referral->id, 'recipient_bot_user_id' => $this->user->id,
            'type' => AffiliateTransaction::TYPE_PURCHASE_COMMISSION, 'amount' => $amount, 'status' => $status,
        ]);
    }

    expect(announcementPlaceholders()->render('{$affiliates.earnings}', $this->user))
        ->toBe(currency()->priceFormat('5000'));
});

it('offers its placeholders only while affiliates is switched on', function () {
    expect(announcementPlaceholders()->all())->toHaveKeys(['affiliates.link', 'affiliates.earnings']);

    settings()->set('affiliates.affiliates_status', false, $this->bot);

    expect(announcementPlaceholders()->unknownIn('{$affiliates.link}'))->toBe(['affiliates.link']);
});
