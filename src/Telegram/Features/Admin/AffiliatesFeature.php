<?php

namespace TelegramBotEssentials\Affiliates\Telegram\Features\Admin;

use Telegram\Bot\Keyboard\Button;
use Telegram\Bot\Keyboard\Keyboard;
use TelegramBotEssentials\Affiliates\Models\Affiliate;
use TelegramBotEssentials\Affiliates\Models\AffiliateTransaction;
use TelegramBotEssentials\Affiliates\Models\Referral;
use TelegramBotEssentials\Essence\Models\BotUser;
use TelegramBotEssentials\Essence\Telegram\TelegramResponse;

/**
 * A member's affiliation as an admin sees it from the member's profile in user
 * management: who referred them, and whom they referred and what it earned.
 */
class AffiliatesFeature
{
    public static string $type = 'AFFILIATES';

    /** How many referred members are listed as buttons; the count above covers the rest. */
    public const LISTED_REFERRALS = 10;

    public static function show(BotUser $botUser): TelegramResponse
    {
        $affiliate = Affiliate::query()->where('bot_user_id', $botUser->id)->first();
        $referredBy = Referral::query()->where('bot_user_id', $botUser->id)->with('affiliate.botUser.telegramUser')->first();
        $referrer = $referredBy?->affiliate?->botUser;

        $lines = [
            __('tbe-affiliates::admin.show.text.header', ['user' => e(self::label($botUser))]),
            __('tbe-affiliates::admin.show.text.referredBy', ['referrer' => $referrer ? e(self::label($referrer)) : '—']),
        ];

        $referrals = $affiliate
            ? $affiliate->referrals()->with('botUser.telegramUser')->latest('id')->get()
            : collect();

        if ($affiliate) {
            $lines[] = __('tbe-affiliates::admin.show.text.code', ['code' => e($affiliate->referral_code)]);
            $lines[] = __('tbe-affiliates::admin.show.text.referrals', ['count' => $referrals->count()]);
            $lines[] = __('tbe-affiliates::admin.show.text.earned', [
                'amount' => currency()->priceFormat(
                    AffiliateTransaction::query()
                        ->where('recipient_bot_user_id', $botUser->id)
                        ->where('status', AffiliateTransaction::STATUS_CREDITED)
                        ->sum('amount')
                ),
            ]);
        } else {
            $lines[] = __('tbe-affiliates::admin.show.text.noAffiliate');
        }

        $replyMarkup = Keyboard::make()->inline();

        if ($referrer) {
            $replyMarkup->row([self::profileButton(__('tbe-affiliates::admin.show.keys.referrer', ['user' => self::label($referrer)]), $referrer)]);
        }

        foreach ($referrals->take(self::LISTED_REFERRALS) as $referral) {
            if ($referral->botUser) {
                $replyMarkup->row([self::profileButton(self::label($referral->botUser), $referral->botUser)]);
            }
        }

        $replyMarkup->row([self::profileButton(__('tbe-affiliates::admin.show.keys.back'), $botUser)]);

        return new TelegramResponse(
            text: implode("\r\n", $lines),
            replyMarkup: $replyMarkup,
            parseMode: 'HTML',
        );
    }

    public static function label(BotUser $botUser): string
    {
        $telegramUser = $botUser->telegramUser;

        return match (true) {
            filled($telegramUser->username) => '@'.$telegramUser->username,
            filled($telegramUser->full_name) => $telegramUser->full_name,
            default => (string) $telegramUser->peer_id,
        };
    }

    private static function profileButton(string $text, BotUser $botUser): array|string|Button
    {
        return Keyboard::inlineButton([
            'text' => $text,
            'callback_data' => encodeCallback('BOTUSERS', 'show', [$botUser->id]),
        ]);
    }
}
