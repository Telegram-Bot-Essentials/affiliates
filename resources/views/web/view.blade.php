<x-filament-panels::page>
    @php($affiliate = $this->affiliate())
    <div class="grid gap-6">
        <x-filament::section>
            <dl style="display:grid;grid-template-columns:repeat(auto-fit,minmax(10rem,1fr));gap:1rem;">
                <div><dt style="font-size:.875rem;opacity:.7;">{{ __('tbe-affiliates::web.code') }}</dt><dd style="font-weight:600;"><code>{{ $affiliate->referral_code }}</code></dd></div>
                <div><dt style="font-size:.875rem;opacity:.7;">{{ __('tbe-affiliates::web.referred_by') }}</dt><dd style="font-weight:600;">{{ $this->referrer() }}</dd></div>
                <div><dt style="font-size:.875rem;opacity:.7;">{{ __('tbe-affiliates::web.referrals') }}</dt><dd style="font-weight:600;">{{ $this->referrals()->count() }}</dd></div>
                <div><dt style="font-size:.875rem;opacity:.7;">{{ __('tbe-affiliates::web.earned') }}</dt><dd style="font-weight:600;">{{ $this->earned() }}</dd></div>
            </dl>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">{{ __('tbe-affiliates::web.referrals') }}</x-slot>
            @forelse ($this->referrals() as $referral)
                <div style="display:flex;justify-content:space-between;padding:.375rem 0;border-bottom:1px solid rgb(127 127 127 / .15);">
                    <span>{{ \TelegramBotEssentials\Affiliates\Web\Manage\AffiliateResource::memberLabel($referral->botUser) }}</span>
                    <span style="opacity:.7;">{{ $referral->created_at?->diffForHumans() }}</span>
                </div>
            @empty
                <p style="opacity:.7;">{{ __('tbe-affiliates::web.no_referrals') }}</p>
            @endforelse
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">{{ __('tbe-affiliates::web.transactions') }}</x-slot>
            @forelse ($this->transactions() as $transaction)
                <div style="display:flex;gap:1rem;justify-content:space-between;padding:.375rem 0;border-bottom:1px solid rgb(127 127 127 / .15);">
                    <span>{{ __('tbe-affiliates::web.types.'.$transaction->type) }} · {{ \TelegramBotEssentials\Affiliates\Web\Manage\AffiliateResource::memberLabel($transaction->referral?->botUser) }}</span>
                    <span @class(['line-through opacity-60' => $transaction->status === \TelegramBotEssentials\Affiliates\Models\AffiliateTransaction::STATUS_REVERSED])>{{ $this->money($transaction->amount) }}</span>
                    <span style="opacity:.7;">{{ $transaction->created_at?->diffForHumans() }}</span>
                </div>
            @empty
                <p style="opacity:.7;">{{ __('tbe-affiliates::web.no_transactions') }}</p>
            @endforelse
        </x-filament::section>
    </div>
</x-filament-panels::page>
