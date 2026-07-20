<?php

return [
    'reply_key' => 'Affiliation 🤝',

    'menu' => [
        'text' => "🤝 Affiliate Program"
            . "\r\n"
            . "\r\nInvite friends and earn <b>:percentage%</b> commission on every purchase they make."
            . "\r\n"
            . "\r\n🔗 Your link: :link"
            . "\r\n"
            . "\r\n👥 Referrals: :referralsCount"
            . "\r\n💰 Total earned: :totalEarned",
        'referrer_bonus_line' => "\r\n🎁 Bonus per referral: :amount",
        'keys' => [
            'share' => '📤 Share my link',
        ],
    ],

    'share' => [
        'text' => "🚀 Join me on :botName!"
            . "\r\n"
            . "\r\nIt's a shop right inside Telegram — browse and buy in a couple of taps."
            . "\r\n"
            . "\r\n👇 Use my link to get started:"
            . "\r\n:link",
        'referred_bonus_line' => "\r\n"
            . "\r\n🎁 Sign up through this link and get :amount credited to your wallet, free.",
    ],

    'notifications' => [
        'referrer_signup_bonus' => '🎉 Someone joined using your affiliate link! :amount was added to your wallet.',
        'referred_signup_bonus' => '🎁 Welcome bonus! :amount was added to your wallet for joining through an affiliate link.',
        'purchase_commission' => '💰 A user you referred made a purchase! :amount was added to your wallet.',
        'purchase_commission_reversed' => '⚠️ A referred purchase was reversed, so :amount was deducted from your wallet.',
    ],
];
