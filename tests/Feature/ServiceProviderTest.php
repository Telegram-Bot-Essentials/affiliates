<?php

declare(strict_types=1);

use TelegramBotEssentials\Affiliates\Telegram\Commands\Member\AffiliationCommand;
use TelegramBotEssentials\Settings\Services\Settings;

it('registers the affiliates settings tree', function () {
    $keys = app(Settings::class)->getSettings()->keys();

    expect($keys)->toContain(
        'affiliates',
        'affiliates.affiliates_status',
        'affiliates.allow_existing_users',
        'affiliates.share_percentage',
        'affiliates.referrer_signup_bonus',
        'affiliates.referred_signup_bonus',
        'affiliates.share_tagline',
    );
});

it('registers the affiliation command with essence', function () {
    expect(config('tbe-essence.commands'))
        ->toContain(AffiliationCommand::class);
});
