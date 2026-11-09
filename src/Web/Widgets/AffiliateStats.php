<?php

namespace TelegramBotEssentials\Affiliates\Web\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use TelegramBotEssentials\Affiliates\Models\Affiliate;
use TelegramBotEssentials\Affiliates\Models\AffiliateTransaction;
use TelegramBotEssentials\Affiliates\Models\Referral;
use TelegramBotEssentials\Affiliates\Telegram\Features\Admin\AffiliatesFeature;
use TelegramBotEssentials\Essence\Web\Filament\Concerns\GuardsWidget;
use TelegramBotEssentials\Essence\Web\Filament\DailySeries;

/** The program at a glance: who joins through it, and what it costs. */
class AffiliateStats extends StatsOverviewWidget
{
    use GuardsWidget;

    protected static ?string $ability = 'affiliates.manage';

    protected static ?int $sort = 6;

    // Drawn with the page above the affiliate list.
    protected static bool $isLazy = false;

    /** @return array<Stat> */
    protected function getStats(): array
    {
        $referrals = DailySeries::of(Referral::query(), 'created_at', 30);
        $paid = DailySeries::of(AffiliateTransaction::query()->where('status', AffiliateTransaction::STATUS_CREDITED), 'created_at', 30, 'amount');
        $allTime = AffiliatesFeature::credited()->sum('amount');

        return [
            Stat::make((string) __('tbe-affiliates::web.stats.referrals_30'), number_format(array_sum($referrals)))
                ->description((string) __('tbe-affiliates::web.stats.all_time', ['count' => number_format(Referral::query()->count())]))
                ->chart(array_values($referrals))
                ->color('primary'),
            Stat::make((string) __('tbe-affiliates::web.stats.paid_30'), currency()->priceFormat((string) array_sum($paid)))
                ->description((string) __('tbe-affiliates::web.stats.paid_all_time', ['amount' => currency()->priceFormat((string) $allTime)]))
                ->chart(array_values($paid))
                ->color('warning'),
            Stat::make((string) __('tbe-affiliates::web.nav'), number_format(Affiliate::query()->count())),
        ];
    }
}
