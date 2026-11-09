<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Auth;
use TelegramBotEssentials\Affiliates\Models\Affiliate;
use TelegramBotEssentials\Affiliates\Models\AffiliateTransaction;
use TelegramBotEssentials\Affiliates\Models\Referral;
use TelegramBotEssentials\Essence\Models\BotUser;
use TelegramBotEssentials\Essence\Web\WebAccounts;
use TelegramBotEssentials\UserManagement\DTOs\UserSection;

function affiliateOf(BotUser $user, string $code): Affiliate
{
    return Affiliate::query()->create(['bot_id' => $user->bot_id, 'bot_user_id' => $user->id, 'referral_code' => $code]);
}

function referredBy(Affiliate $affiliate, BotUser $user, ?string $credit = null): Referral
{
    $referral = Referral::query()->create(['bot_id' => $user->bot_id, 'bot_user_id' => $user->id, 'affiliate_id' => $affiliate->id]);
    if ($credit !== null) {
        AffiliateTransaction::query()->create([
            'bot_id' => $user->bot_id, 'referral_id' => $referral->id, 'recipient_bot_user_id' => $affiliate->bot_user_id,
            'type' => AffiliateTransaction::TYPE_PURCHASE_COMMISSION, 'amount' => $credit, 'status' => AffiliateTransaction::STATUS_CREDITED,
        ]);
    }

    return $referral;
}

beforeEach(function () {
    $this->bot = $this->makeBot(['bot_owner_peer_id' => 9201]);
    $this->admin = $this->grant($this->makeBotUser($this->bot, 9201), 'affiliates.manage', 'web.manage');
    $this->account = $this->panelAccount($this->admin);
    Auth::guard(WebAccounts::guard())->login($this->account);
    $this->url = '/manage/'.$this->bot->unique_id.'/affiliates';
});

it('lists this bot\'s affiliates with their referrals and earnings', function () {
    $affiliate = affiliateOf($this->makeBotUser($this->bot, 9202), 'OURCODE');
    referredBy($affiliate, $this->makeBotUser($this->bot, 9203), '1500');
    $otherBot = $this->makeBot(['unique_id' => 'other-bot']);
    affiliateOf($this->makeBotUser($otherBot, 9300), 'THEIRCODE');

    $this->get($this->url)
        ->assertOk()
        ->assertSee('OURCODE')
        ->assertDontSee('THEIRCODE')
        ->assertSee(currency()->priceFormat('1500'));
});

it('shows one affiliate\'s referrals and credits', function () {
    $affiliate = affiliateOf($this->makeBotUser($this->bot, 9204), 'SHOWME');
    $referred = $this->makeBotUser($this->bot, 9205);
    referredBy($affiliate, $referred, '700');

    $this->get($this->url.'/'.$affiliate->id)
        ->assertOk()
        ->assertSee('SHOWME')
        ->assertSee(__('tbe-affiliates::web.types.purchase_commission'))
        ->assertSee(currency()->priceFormat('700'));
});

it('keeps the screens from anyone without affiliates.manage', function () {
    $limited = $this->grant($this->makeBotUser($this->bot, 9206), 'web.manage');
    Auth::guard(WebAccounts::guard())->login($this->panelAccount($limited));

    $this->get($this->url)->assertForbidden();
});

it('does not open another bot\'s affiliate', function () {
    $otherBot = $this->makeBot(['unique_id' => 'other-bot']);
    $theirs = affiliateOf($this->makeBotUser($otherBot, 9301), 'NOPE');

    $this->get($this->url.'/'.$theirs->id)->assertNotFound();
});

// One Livewire update per test: Livewire skips persistent middleware it already ran for the route.
it('sorts the list over a real request', function () {
    affiliateOf($this->makeBotUser($this->bot, 9207), 'SORTED');

    $this->livewireRequest($this->url, 'ListAffiliates', 'sortTable', ['referrals_count'])->assertOk();
});

it('refuses list updates once signed out', function () {
    $this->livewireRequest($this->url, 'ListAffiliates', 'sortTable', ['referrals_count'],
        fn () => Auth::guard(WebAccounts::guard())->logout())->assertUnauthorized();
});

it('links the member profile\'s affiliation to the affiliate screen', function () {
    $member = $this->makeBotUser($this->bot, 9208);
    $affiliate = affiliateOf($member, 'LINKED');
    referredBy($affiliate, $this->makeBotUser($this->bot, 9209));
    // The profile renders inside the panel, which gives the URL its bot.
    $this->enterPanel('manage', $this->bot, $this->account);

    $section = userManagementSections()->getSectionsFor($member)->firstWhere('key', 'affiliation');

    expect($section?->webUrlFor($member))->toEndWith('/affiliates/'.$affiliate->id);
})->skip(fn () => ! property_exists(UserSection::class, 'webUrl'), 'needs user-management with webUrl');
