<?php

return [
    'reply_key' => 'Affiliates 🤝',
    'menu' => [
        'text' => [
            'header' => "🤝 <b>Affiliate program</b>\r\n👥 Affiliates: :affiliates\r\n🔗 Referrals: :referrals\r\n💰 Paid out: :paid",
            'empty' => 'Nobody has joined the program yet.',
            'waitingPage' => '⌛ Waiting for the page number.',
            'enterPage' => '🔢 Enter the page number:',
            'pageLoaded' => '📄 Page :page loaded.',
        ],
        'keys' => [
            'sort' => [
                'earned' => '💰 Earned',
                'referrals' => '👥 Referrals',
            ],
            'row' => ':user · 👥 :referrals · 💰 :earned',
        ],
    ],
    'sorts' => [
        'referrals' => 'Referrals',
        'earnings' => 'Affiliate earnings',
    ],
    'section' => [
        'label' => '🤝 Affiliation (:count)',
    ],
    'show' => [
        'text' => [
            'header' => '🤝 Affiliation of <b>:user</b>',
            'referredBy' => '↩️ Referred by: :referrer',
            'code' => '🔗 Referral code: <code>:code</code>',
            'referrals' => '👥 Referrals: :count',
            'earned' => '💰 Total earned: :amount',
            'noAffiliate' => '👥 Has not referred anyone.',
        ],
        'keys' => [
            'referrer' => '↩️ Referrer: :user',
            'backToList' => '🔙 Back to the affiliates',
            'back' => '🔙 Back to the profile',
        ],
    ],
];
