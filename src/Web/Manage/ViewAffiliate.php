<?php

namespace TelegramBotEssentials\Affiliates\Web\Manage;

use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Database\Eloquent\Collection;
use TelegramBotEssentials\Affiliates\Models\Affiliate;
use TelegramBotEssentials\Affiliates\Models\AffiliateTransaction;
use TelegramBotEssentials\Affiliates\Models\Referral;
use TelegramBotEssentials\Affiliates\Telegram\Features\Admin\AffiliatesFeature;
use TelegramBotEssentials\Essence\Web\Filament\Concerns\GuardsResourcePage;

/** One affiliate: who referred them, whom they referred, and every credit and reversal. */
class ViewAffiliate extends Page
{
    use GuardsResourcePage;
    use InteractsWithRecord;

    protected static string $resource = AffiliateResource::class;

    protected string $view = 'tbe-affiliates::web.view';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function affiliate(): Affiliate
    {
        /** @var Affiliate $affiliate */
        $affiliate = $this->getRecord();

        return $affiliate;
    }

    public function getTitle(): string
    {
        return AffiliateResource::memberLabel($this->affiliate()->botUser);
    }

    public function referrer(): string
    {
        $referral = Referral::query()->where('bot_user_id', $this->affiliate()->bot_user_id)->with('affiliate.botUser.telegramUser')->first();

        return AffiliateResource::memberLabel($referral?->affiliate?->botUser);
    }

    public function earned(): string
    {
        $sum = AffiliatesFeature::credited()->where('recipient_bot_user_id', $this->affiliate()->bot_user_id)->sum('amount');

        return currency()->priceFormat((string) $sum);
    }

    /** @return Collection<int, Referral> */
    public function referrals(): Collection
    {
        return $this->affiliate()->referrals()->with('botUser.telegramUser')->latest('id')->get();
    }

    /** @return Collection<int, AffiliateTransaction> */
    public function transactions(): Collection
    {
        return AffiliateTransaction::query()
            ->where('recipient_bot_user_id', $this->affiliate()->bot_user_id)
            ->with('referral.botUser.telegramUser')
            ->latest('id')
            ->limit(100)
            ->get();
    }

    public function money(mixed $amount): string
    {
        return currency()->priceFormat(is_numeric($amount) ? (string) $amount : '0');
    }
}
