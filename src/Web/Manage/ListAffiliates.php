<?php

namespace TelegramBotEssentials\Affiliates\Web\Manage;

use Filament\Resources\Pages\ListRecords;
use TelegramBotEssentials\Affiliates\Web\Widgets\AffiliateStats;
use TelegramBotEssentials\Essence\Web\Filament\Concerns\GuardsResourcePage;

class ListAffiliates extends ListRecords
{
    use GuardsResourcePage;

    protected static string $resource = AffiliateResource::class;

    protected function getHeaderWidgets(): array
    {
        return [AffiliateStats::class];
    }
}
