<?php

return [
    'reply_key' => 'زیر مجموعه گیری 👥',
    'menu' => [
        'text' => [
            'header' => "🤝 <b>برنامه زیرمجموعه‌گیری</b>\r\n👥 اعضای برنامه: :affiliates\r\n🔗 دعوت‌شده‌ها: :referrals\r\n💰 مجموع پرداخت‌شده: :paid",
            'empty' => 'هنوز کسی عضو برنامه نشده است.',
            'waitingPage' => '⌛ در انتظار شماره صفحه.',
            'enterPage' => '🔢 شماره صفحه را وارد کنید:',
            'pageLoaded' => '📄 صفحه :page بارگذاری شد.',
        ],
        'keys' => [
            'sort' => [
                'earned' => '💰 درآمد',
                'referrals' => '👥 دعوت‌شده‌ها',
            ],
            'row' => ':user · 👥 :referrals · 💰 :earned',
        ],
    ],
    'sorts' => [
        'referrals' => 'تعداد دعوت‌شده‌ها',
        'earnings' => 'درآمد زیرمجموعه‌گیری',
    ],
    'section' => [
        'label' => '🤝 زیرمجموعه‌گیری (:count)',
    ],
    'show' => [
        'text' => [
            'header' => '🤝 زیرمجموعه‌گیری <b>:user</b>',
            'referredBy' => '↩️ معرف: :referrer',
            'code' => '🔗 کد معرفی: <code>:code</code>',
            'referrals' => '👥 دعوت‌شده‌ها: :count',
            'earned' => '💰 مجموع درآمد: :amount',
            'noAffiliate' => '👥 کسی را دعوت نکرده است.',
        ],
        'keys' => [
            'referrer' => '↩️ معرف: :user',
            'backToList' => '🔙 بازگشت به زیرمجموعه‌گیری',
            'back' => '🔙 بازگشت به نمایه کاربر',
        ],
    ],
];
