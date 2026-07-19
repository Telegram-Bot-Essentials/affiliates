<?php

return [
    'reply_key' => 'Affiliation',

    'menu' => [
        'text' => "🤝 <b>Affiliation Program</b>"
            . "\r\n"
            . "\r\nShare your link with friends. When someone joins through it, and every time they buy something, you earn credit in your wallet."
            . "\r\n"
            . "\r\n🔗 :link"
            . "\r\n"
            . "\r\n👥 Referrals: :referralsCount"
            . "\r\n💰 Total earned: :totalEarned",
        'keys' => [
            'share' => '📤 Share my link',
        ],
    ],

    'share' => [
        'text' => "Join me on this bot! 👇"
            . "\r\n:link",
    ],

    'notifications' => [
        'referrer_signup_bonus' => '🎉 Someone joined using your affiliate link! :amount was added to your wallet.',
        'referred_signup_bonus' => '🎁 Welcome bonus! :amount was added to your wallet for joining through an affiliate link.',
        'purchase_commission' => '💰 A user you referred made a purchase! :amount was added to your wallet.',
        'purchase_commission_reversed' => '⚠️ A referred purchase was reversed, so :amount was deducted from your wallet.',
    ],
];
