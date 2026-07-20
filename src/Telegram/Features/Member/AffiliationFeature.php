<?php

namespace TelegramBotEssentials\Affiliates\Telegram\Features\Member;

use Brick\Math\BigDecimal;
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
            'percentage' => (string) settings()->get('affiliates.share_percentage'),
            'link' => $link,
            'referralsCount' => $referralsCount,
            'totalEarned' => currency()->priceFormat($totalEarned),
        ]);

        $referrerBonus = BigDecimal::of((string) settings()->get('affiliates.referrer_signup_bonus'));
        if ($referrerBonus->isPositive()) {
            $text .= __('tbe-affiliates::affiliation.menu.referrer_bonus_line', [
                'amount' => currency()->priceFormat($referrerBonus),
            ]);
        }

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

    public static function shareText(string $referralCode): string
    {
        $text = __('tbe-affiliates::affiliation.share.text', [
            'botName' => wHook()->api()->getMe()->first_name,
            'link' => self::referralLink($referralCode),
        ]);

        $referredBonus = BigDecimal::of((string) settings()->get('affiliates.referred_signup_bonus'));
        if ($referredBonus->isPositive()) {
            $text .= __('tbe-affiliates::affiliation.share.referred_bonus_line', [
                'amount' => currency()->priceFormat($referredBonus),
            ]);
        }

        return $text;
    }
}
