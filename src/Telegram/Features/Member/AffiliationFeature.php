<?php

namespace TelegramBotEssentials\Affiliates\Telegram\Features\Member;

use Telegram\Bot\Keyboard\Keyboard;
use TelegramBotEssentials\Affiliates\Models\AffiliateTransaction;
use TelegramBotEssentials\Essence\Telegram\TelegramResponse;

class AffiliationFeature
{
    static string $type = 'AFFILIATION';

    public static function menu(): TelegramResponse
    {
        $affiliate = wHook()->user()->refresh()->affiliate;
        if (!$affiliate) {
            $affiliate = wHook()->user()->affiliate()->create([
                'referral_code' => uniqid(),
            ]);
        }

        $link = self::referralLink($affiliate->referral_code);

        $referralsCount = $affiliate->referrals()->count();
        $totalEarned = AffiliateTransaction::where('recipient_bot_user_id', wHook()->user()->id)
            ->where('status', AffiliateTransaction::STATUS_CREDITED)
            ->sum('amount');

        $text = __('tbe-affiliates::affiliation.menu.text', [
            'link' => $link,
            'referralsCount' => $referralsCount,
            'totalEarned' => currency()->priceFormat($totalEarned),
        ]);

        $replyMarkup = Keyboard::make()->inline();
        $replyMarkup->row([
            Keyboard::inlineButton([
                'text' => __('tbe-affiliates::affiliation.menu.keys.share'),
                'callback_data' => encodeCallback(self::$type, 'getInviteLink'),
            ]),
        ]);

        return new TelegramResponse(
            text: $text,
            replyMarkup: $replyMarkup,
            parseMode: 'HTML'
        );
    }

    public static function referralLink(string $referralCode): string
    {
        $botUsername = wHook()->api()->getMe()->username;

        return "https://t.me/{$botUsername}?start={$referralCode}";
    }
}
