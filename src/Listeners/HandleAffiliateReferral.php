<?php

namespace TelegramBotEssentials\Affiliates\Listeners;

use Brick\Math\BigDecimal;
use Illuminate\Database\QueryException;
use TelegramBotEssentials\Affiliates\Models\Affiliate;
use TelegramBotEssentials\Affiliates\Models\AffiliateTransaction;
use TelegramBotEssentials\Affiliates\Models\Referral;
use TelegramBotEssentials\Essence\Events\BotDeepLinkReceived;

class HandleAffiliateReferral
{
    public function handle(BotDeepLinkReceived $event): void
    {
        if (! settings()->get('affiliates.affiliates_status')) {
            return;
        }

        // By default only a brand-new BotUser can be attributed. Admin can
        // opt in to letting existing/returning users join via the link too
        // (affiliates.allow_existing_users) — first-touch-wins either way,
        // enforced by the unique(bot_id, bot_user_id) constraint below.
        if (! wHook()->user()->wasRecentlyCreated && ! settings()->get('affiliates.allow_existing_users')) {
            return;
        }

        $affiliate = Affiliate::where('referral_code', $event->payload)->first();

        if (! $affiliate || (int) $affiliate->bot_user_id === (int) wHook()->user()->id) {
            return;
        }

        $referral = $this->createReferral($affiliate);

        if (! $referral) {
            return;
        }

        tbeLog('affiliates')->info('Referral attributed', [
            'referral_id' => $referral->id,
            'affiliate_id' => $affiliate->id,
        ]);

        $this->payReferrerBonus($affiliate, $referral);
        $this->payReferredBonus($referral);
    }

    private function createReferral(Affiliate $affiliate): ?Referral
    {
        try {
            return Referral::create([
                'bot_id' => wHook()->bot()->id,
                'bot_user_id' => wHook()->user()->id,
                'affiliate_id' => $affiliate->id,
            ]);
        } catch (QueryException) {
            // unique(bot_id, bot_user_id) already exists — user was already
            // referred by someone; first-touch wins, silently ignore.
            return null;
        }
    }

    private function payReferrerBonus(Affiliate $affiliate, Referral $referral): void
    {
        $amount = BigDecimal::of((string) settings()->get('affiliates.referrer_signup_bonus'));

        if (! $amount->isPositive()) {
            return;
        }

        $referrer = $affiliate->botUser;

        wHook()->runForUser($referrer, function () use ($referral, $amount) {
            wallet()->adjustBalance($amount);

            AffiliateTransaction::create([
                'bot_id' => wHook()->bot()->id,
                'referral_id' => $referral->id,
                'recipient_bot_user_id' => wHook()->user()->id,
                'type' => AffiliateTransaction::TYPE_REFERRER_SIGNUP_BONUS,
                'amount' => $amount,
                'status' => AffiliateTransaction::STATUS_CREDITED,
            ]);

            tbeLog('affiliates')->info('Referrer signup bonus credited', [
                'referral_id' => $referral->id,
                'amount' => (string) $amount,
            ]);

            wHook()->api()->sendMessage([
                'chat_id' => wHook()->user()->telegramUser->peer_id,
                'text' => __('tbe-affiliates::affiliation.notifications.referrer_signup_bonus', [
                    'amount' => currency()->priceFormat($amount),
                ]),
                'reply_markup' => wHook()->user()->getKeyboard(),
            ]);
        });
    }

    private function payReferredBonus(Referral $referral): void
    {
        $amount = BigDecimal::of((string) settings()->get('affiliates.referred_signup_bonus'));

        if (! $amount->isPositive()) {
            return;
        }

        wallet()->adjustBalance($amount);

        AffiliateTransaction::create([
            'bot_id' => wHook()->bot()->id,
            'referral_id' => $referral->id,
            'recipient_bot_user_id' => wHook()->user()->id,
            'type' => AffiliateTransaction::TYPE_REFERRED_SIGNUP_BONUS,
            'amount' => $amount,
            'status' => AffiliateTransaction::STATUS_CREDITED,
        ]);

        tbeLog('affiliates')->info('Referred signup bonus credited', [
            'referral_id' => $referral->id,
            'amount' => (string) $amount,
        ]);

        wHook()->api()->sendMessage([
            'chat_id' => wHook()->user()->telegramUser->peer_id,
            'text' => __('tbe-affiliates::affiliation.notifications.referred_signup_bonus', [
                'amount' => currency()->priceFormat($amount),
            ]),
            'reply_markup' => wHook()->user()->getKeyboard(),
        ]);
    }
}
