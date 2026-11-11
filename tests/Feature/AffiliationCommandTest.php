<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use TelegramBotEssentials\Affiliates\Models\Affiliate;
use TelegramBotEssentials\Affiliates\Models\Referral;
use TelegramBotEssentials\Affiliates\Telegram\Features\Member\AffiliationFeature;
use TelegramBotEssentials\Essence\Telegram\Commands\Command;

const AFFILIATION_GROUP = -100333;

class AffiliationTestStartCommand extends Command
{
    protected string $name = 'start';

    public function handle(): ?bool
    {
        return true;
    }
}

beforeEach(function () {
    config(['tbe-essence.groups.enabled' => true]);

    $this->bot = $this->makeBot();
    $this->user = $this->makeBotUser($this->bot, 4001);
    Cache::put('tbe:bot-username:'.$this->bot->getKey().':'.md5((string) $this->bot->bot_token), 'shop_bot', 3600);
    settings()->set('affiliates.affiliates_status', true, $this->bot);
});

it('sends a group member to their private chat, showing no earnings', function () {
    $this->postWebhookUpdate($this->bot, $this->inGroupChat($this->makeMessageUpdate('/affiliation', 4001), AFFILIATION_GROUP))->assertOk();

    $this->assertTelegramSent(function ($request) {
        if (! str_ends_with($request->url(), '/sendMessage') || (int) $request['chat_id'] !== AFFILIATION_GROUP) {
            return false;
        }

        $markup = json_decode((string) $request['reply_markup'], true);

        return $request['text'] === __('tbe::general.continue_in_private.text')
            && $markup['inline_keyboard'][0][0]['url'] === 'https://t.me/shop_bot?start=open_affiliation';
    });
    Http::assertNotSent(fn ($request) => str_ends_with($request->url(), '/sendMessage') && (int) $request['chat_id'] === 4001);
});

it('opens the menu from the deep link and ignores others', function () {
    commandBus()->addCommand(AffiliationTestStartCommand::class);

    $this->postWebhookUpdate($this->bot, $this->makeMessageUpdate('/start CODE123', 4001))->assertOk();
    Http::assertNotSent(fn ($request) => str_ends_with($request->url(), '/sendMessage'));

    $this->postWebhookUpdate($this->bot, $this->makeMessageUpdate('/start open_affiliation', 4001))->assertOk();
    $this->assertTelegramSent(fn ($request) => str_ends_with($request->url(), '/sendMessage') && (int) $request['chat_id'] === 4001);
});

it('attributes someone first met in a group when they arrive by referral link', function () {
    commandBus()->addCommand(AffiliationTestStartCommand::class);
    $referrer = $this->makeBotUser($this->bot, 4100);
    $affiliate = Affiliate::create(['bot_id' => $this->bot->id, 'bot_user_id' => $referrer->id, 'referral_code' => 'FRIEND']);

    $this->postWebhookUpdate($this->bot, $this->inGroupChat($this->makeMessageUpdate('/affiliation', 4200), AFFILIATION_GROUP))->assertOk();
    $this->postWebhookUpdate($this->bot, $this->makeMessageUpdate('/start FRIEND', 4200))->assertOk();

    expect(Referral::where('affiliate_id', $affiliate->id)->count())->toBe(1);
});

it('still ignores a returning user arriving by referral link', function () {
    commandBus()->addCommand(AffiliationTestStartCommand::class);
    $referrer = $this->makeBotUser($this->bot, 4100);
    $affiliate = Affiliate::create(['bot_id' => $this->bot->id, 'bot_user_id' => $referrer->id, 'referral_code' => 'FRIEND']);
    $this->makeBotUser($this->bot, 4300);

    $this->postWebhookUpdate($this->bot, $this->makeMessageUpdate('/start FRIEND', 4300))->assertOk();

    expect(Referral::where('affiliate_id', $affiliate->id)->count())->toBe(0);
});

it('opens the menu for a user carrying a cached relation that is not a real one', function () {
    wHook()->setBot($this->bot);
    wHook()->setApi(telegramApi((string) $this->bot->bot_token));
    wHook()->setUser($this->user);
    Affiliate::create(['bot_id' => $this->bot->id, 'bot_user_id' => $this->user->id, 'referral_code' => 'CODE4001']);
    Http::fake(['*/getMe' => Http::response(['ok' => true, 'result' => ['id' => 1, 'is_bot' => true, 'first_name' => 'Shop', 'username' => 'shop_bot']])]);
    // Another package may cache data on the user with setRelation() alone.
    $this->user->setRelation('accessRoles', collect());

    expect(AffiliationFeature::menu()->text)->toContain('CODE4001');
});
