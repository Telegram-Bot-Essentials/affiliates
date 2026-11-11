<x-filament-panels::page>
    @php($affiliate = $this->affiliate())
    @php($R = \TelegramBotEssentials\Affiliates\Web\Manage\AffiliateResource::class)
    <div class="grid gap-6">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(13rem,1fr));gap:1rem;">
            <x-tbe::stat-card :label="__('tbe-affiliates::web.referrals')" :value="$this->referrals()->count()" icon="heroicon-o-user-group" />
            <x-tbe::stat-card :label="__('tbe-affiliates::web.earned')" :value="$this->earned()" icon="heroicon-o-banknotes" />
        </div>

        <x-filament::section>
            <x-tbe::facts>
                <x-tbe::fact :label="__('tbe-affiliates::web.code')"><code style="user-select:all;">{{ $affiliate->referral_code }}</code></x-tbe::fact>
                <x-tbe::fact :label="__('tbe-affiliates::web.referred_by')" :value="$this->referrer()" />
            </x-tbe::facts>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">{{ __('tbe-affiliates::web.referrals') }}</x-slot>
            @forelse ($this->referrals() as $referral)
                <x-tbe::row>
                    <span>{{ $R::memberLabel($referral->botUser) }}</span>
                    <x-tbe::when :at="$referral->created_at" />
                </x-tbe::row>
            @empty
                <x-tbe::empty>{{ __('tbe-affiliates::web.no_referrals') }}</x-tbe::empty>
            @endforelse
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">{{ __('tbe-affiliates::web.transactions') }}</x-slot>
            @forelse ($this->transactions() as $transaction)
                @php($reversed = $transaction->status === \TelegramBotEssentials\Affiliates\Models\AffiliateTransaction::STATUS_REVERSED)
                <x-tbe::row>
                    <span>{{ __('tbe-affiliates::web.types.'.$transaction->type) }} · {{ $R::memberLabel($transaction->referral?->botUser) }}</span>
                    <span @style(['text-decoration:line-through;opacity:.6;' => $reversed])>{{ $this->money($transaction->amount) }}</span>
                    <x-tbe::when :at="$transaction->created_at" />
                </x-tbe::row>
            @empty
                <x-tbe::empty>{{ __('tbe-affiliates::web.no_transactions') }}</x-tbe::empty>
            @endforelse
        </x-filament::section>
    </div>
</x-filament-panels::page>
