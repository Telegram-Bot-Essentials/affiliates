<?php

namespace TelegramBotEssentials\Affiliates\Web\Manage;

use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use TelegramBotEssentials\Affiliates\Models\Affiliate;
use TelegramBotEssentials\Affiliates\Models\Referral;
use TelegramBotEssentials\Affiliates\Telegram\Features\Admin\AffiliatesFeature;
use TelegramBotEssentials\Essence\Web\Filament\Concerns\GuardsResourcePage;

class ListAffiliates extends ListRecords
{
    use GuardsResourcePage;

    protected static string $resource = AffiliateResource::class;

    /** The bot's totals, as the overview's header in Telegram. */
    public function getSubheading(): string|Htmlable|null
    {
        $paid = AffiliatesFeature::credited()->sum('amount');

        return (string) __('tbe-affiliates::web.totals', [
            'affiliates' => Affiliate::query()->count(),
            'referrals' => Referral::query()->count(),
            'paid' => currency()->priceFormat((string) $paid),
        ]);
    }
}
