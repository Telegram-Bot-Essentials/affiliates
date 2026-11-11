<?php

namespace TelegramBotEssentials\Affiliates\Web\Manage;

use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use TelegramBotEssentials\Affiliates\Models\Affiliate;
use TelegramBotEssentials\Affiliates\Telegram\Features\Admin\AffiliatesFeature;
use TelegramBotEssentials\Affiliates\Web\Widgets\AffiliateStats;
use TelegramBotEssentials\Essence\Models\BotUser;
use TelegramBotEssentials\Essence\Web\Filament\PanelDates;
use TelegramBotEssentials\Essence\Web\Filament\PanelGroup;
use TelegramBotEssentials\Essence\Web\Filament\PanelResource;
use UnitEnum;

/** The affiliate program as the bot's admin overview shows it: who brings whom, and what it earned. */
class AffiliateResource extends PanelResource
{
    protected static ?string $model = Affiliate::class;

    protected static ?string $ability = 'affiliates.manage';

    protected static ?string $slug = 'affiliates';

    protected static ?string $tenantOwnershipRelationshipName = 'bot';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|UnitEnum|null $navigationGroup = PanelGroup::Members;

    public static function getNavigationLabel(): string
    {
        return (string) __('tbe-affiliates::web.nav');
    }

    public static function getModelLabel(): string
    {
        return (string) __('tbe-affiliates::web.affiliate');
    }

    public static function getPluralModelLabel(): string
    {
        return (string) __('tbe-affiliates::web.nav');
    }

    public static function table(Table $table): Table
    {
        return $table
            // The bot overview's columns, on Filament's tenant-scoped query.
            ->modifyQueryUsing(fn (Builder $query): Builder => AffiliatesFeature::withTotals($query))
            ->columns([
                TextColumn::make('member')
                    ->label((string) __('tbe-affiliates::web.member'))
                    ->state(fn (Affiliate $record): string => self::memberLabel($record->botUser)),
                TextColumn::make('referral_code')->label((string) __('tbe-affiliates::web.code'))->searchable()->copyable(),
                TextColumn::make('referrals_count')->label((string) __('tbe-affiliates::web.referrals'))->numeric()->sortable(),
                TextColumn::make('earned')
                    ->label((string) __('tbe-affiliates::web.earned'))
                    ->formatStateUsing(fn (mixed $state): string => currency()->priceFormat(is_numeric($state) ? (string) $state : '0'))
                    ->sortable(),
                PanelDates::since(TextColumn::make('created_at'))->label((string) __('tbe-affiliates::web.joined'))->sortable(),
            ])
            ->defaultSort('earned', 'desc')
            ->persistFiltersInSession()
            ->persistSearchInSession()
            ->emptyStateHeading((string) __('tbe-affiliates::web.empty.affiliates'))
            ->recordUrl(fn (Affiliate $record): string => ViewAffiliate::getUrl(['record' => $record]));
    }

    public static function memberLabel(mixed $botUser): string
    {
        return $botUser instanceof BotUser ? AffiliatesFeature::label($botUser) : '—';
    }

    public static function getWidgets(): array
    {
        return [AffiliateStats::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAffiliates::route('/'),
            'view' => ViewAffiliate::route('/{record}'),
        ];
    }
}
