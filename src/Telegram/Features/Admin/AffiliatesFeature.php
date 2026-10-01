<?php

namespace TelegramBotEssentials\Affiliates\Telegram\Features\Admin;

use Illuminate\Database\Eloquent\Builder;
use Telegram\Bot\Keyboard\Keyboard;
use TelegramBotEssentials\Affiliates\Models\Affiliate;
use TelegramBotEssentials\Affiliates\Models\AffiliateTransaction;
use TelegramBotEssentials\Affiliates\Models\Referral;
use TelegramBotEssentials\Essence\Exceptions\InvalidPageNumber;
use TelegramBotEssentials\Essence\Models\BotUser;
use TelegramBotEssentials\Essence\Services\TelegramPaginator;
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

    public const PER_PAGE = 10;

    /** The columns the overview can be sorted by, mapped to the select alias they order on. */
    public const SORTS = ['earned' => 'earned', 'referrals' => 'referrals_count'];

    /**
     * Every affiliate with what they brought in, the shop's totals above the list.
     *
     * @throws InvalidPageNumber
     */
    public static function menu(int $page = 1, int $currentPage = 0, string $sort = 'earned', string $direction = 'desc'): TelegramResponse
    {
        $sort = array_key_exists($sort, self::SORTS) ? $sort : 'earned';
        $direction = $direction === 'asc' ? 'asc' : 'desc';

        $affiliates = self::listQuery()
            ->orderBy(self::SORTS[$sort], $direction)
            ->orderByDesc('affiliates.id')
            ->paginate(perPage: self::PER_PAGE, page: $page);
        TelegramPaginator::validatePageNumber($page, $currentPage, $affiliates);

        $text = __('tbe-affiliates::admin.menu.text.header', [
            'affiliates' => Affiliate::query()->count(),
            'referrals' => Referral::query()->count(),
            'paid' => currency()->priceFormat(self::credited()->sum('amount')),
        ]);

        if ($affiliates->isEmpty()) {
            return new TelegramResponse(text: $text."\r\n\r\n".__('tbe-affiliates::admin.menu.text.empty'), parseMode: 'HTML');
        }

        $replyMarkup = Keyboard::make()->inline();
        $replyMarkup->row(array_map(
            fn (string $key) => Keyboard::inlineButton([
                'text' => __("tbe-affiliates::admin.menu.keys.sort.{$key}").($sort === $key ? ($direction === 'desc' ? ' ↓' : ' ↑') : ''),
                'callback_data' => encodeCallback(self::$type, 'menu', [1, 0, $key, $sort === $key && $direction === 'desc' ? 'asc' : 'desc']),
            ]),
            array_keys(self::SORTS),
        ));

        foreach ($affiliates as $affiliate) {
            $replyMarkup->row([Keyboard::inlineButton([
                'text' => __('tbe-affiliates::admin.menu.keys.row', [
                    'user' => self::label($affiliate->botUser),
                    'referrals' => $affiliate->referrals_count,
                    'earned' => currency()->priceFormat((string) $affiliate->earned),
                ]),
                'callback_data' => encodeCallback(self::$type, 'user', [$affiliate->bot_user_id, $page, $sort, $direction]),
            ])]);
        }

        TelegramPaginator::addNavigationRow($replyMarkup, self::$type, $page, $affiliates->lastPage(), 'menu', 'set_menu_page', [$sort, $direction]);

        return new TelegramResponse(text: $text, replyMarkup: $replyMarkup, parseMode: 'HTML');
    }

    /**
     * @param  int  $listPage  the overview page the admin came from, 0 when they came from a profile
     */
    public static function show(BotUser $botUser, int $listPage = 0, string $sort = 'earned', string $direction = 'desc'): TelegramResponse
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

        $replyMarkup->row([$listPage
            ? ['text' => __('tbe-affiliates::admin.show.keys.backToList'), 'callback_data' => encodeCallback(self::$type, 'menu', [$listPage, 0, $sort, $direction])]
            : self::profileButton(__('tbe-affiliates::admin.show.keys.back'), $botUser)]);

        return new TelegramResponse(
            text: implode("\r\n", $lines),
            replyMarkup: $replyMarkup,
            parseMode: 'HTML',
        );
    }

    /** Affiliates with their referral count and what they were credited, as columns to sort on. */
    public static function listQuery(): Builder
    {
        return Affiliate::query()
            ->with('botUser.telegramUser')
            ->select('affiliates.*')
            ->withCount('referrals')
            ->selectSub(
                self::credited()->selectRaw('COALESCE(SUM(amount), 0)')->whereColumn('recipient_bot_user_id', 'affiliates.bot_user_id'),
                'earned',
            );
    }

    /** Commissions and bonuses that were paid out and not taken back. */
    public static function credited(): Builder
    {
        return AffiliateTransaction::query()->where('status', AffiliateTransaction::STATUS_CREDITED);
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

    /** @return array<string, string> */
    private static function profileButton(string $text, BotUser $botUser): array
    {
        return [
            'text' => $text,
            'callback_data' => encodeCallback('BOTUSERS', 'show', [$botUser->id]),
        ];
    }
}
