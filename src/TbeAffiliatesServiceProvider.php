<?php

namespace TelegramBotEssentials\Affiliates;

use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use TelegramBotEssentials\Affiliates\Listeners\HandleAffiliateReferral;
use TelegramBotEssentials\Affiliates\Listeners\HandleInvoicePaid;
use TelegramBotEssentials\Affiliates\Listeners\HandleInvoiceRevoked;
use TelegramBotEssentials\Affiliates\Listeners\OpenAffiliationFromDeepLink;
use TelegramBotEssentials\Affiliates\Models\Affiliate;
use TelegramBotEssentials\Affiliates\Models\AffiliateTransaction;
use TelegramBotEssentials\Affiliates\Models\Referral;
use TelegramBotEssentials\Affiliates\Telegram\CallbackQueries\Admin\AffiliatesQuery;
use TelegramBotEssentials\Affiliates\Telegram\CallbackQueries\Member\AffiliationQuery;
use TelegramBotEssentials\Affiliates\Telegram\Commands\Member\AffiliationCommand;
use TelegramBotEssentials\Affiliates\Telegram\Features\Admin\AffiliatesFeature;
use TelegramBotEssentials\Affiliates\Telegram\ReplyKeys\Admin\AffiliatesKey;
use TelegramBotEssentials\Affiliates\Telegram\ReplyKeys\Member\AffiliationKey;
use TelegramBotEssentials\Affiliates\Telegram\StateAnswers\Admin\AffiliatesAnswer;
use TelegramBotEssentials\Affiliates\Telegram\StateAnswers\Member\AffiliationAnswer;
use TelegramBotEssentials\Affiliates\Web\AffiliatesManagePlugin;
use TelegramBotEssentials\Affiliates\Web\Manage\AffiliateResource;
use TelegramBotEssentials\Announcements\DTOs\Placeholder;
use TelegramBotEssentials\Billing\Events\InvoicePaid;
use TelegramBotEssentials\Billing\Events\InvoiceRevoked;
use TelegramBotEssentials\Essence\Events\BotDeepLinkReceived;
use TelegramBotEssentials\Essence\Exceptions\LogicException;
use TelegramBotEssentials\Essence\Models\BotUser;
use TelegramBotEssentials\Settings\DTOs\Setting;
use TelegramBotEssentials\Settings\Enums\SettingType;
use TelegramBotEssentials\UserManagement\DTOs\BotUserSort;
use TelegramBotEssentials\UserManagement\DTOs\SectionSummary;
use TelegramBotEssentials\UserManagement\DTOs\UserSection;
use TelegramBotEssentials\UserManagement\Enums\SectionMode;
use TelegramBotEssentials\UserManagement\Services\BotUserSorts;
use TelegramBotEssentials\UserManagement\Services\UserManagementSections;

class TbeAffiliatesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Class names only: Filament is loaded only where the app installed it.
        webPanels()->manage(AffiliatesManagePlugin::class);
    }

    /**
     * @throws LogicException
     * @throws BindingResolutionException
     */
    public function boot(): void
    {
        $this->registerPublishing();

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'tbe-affiliates');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'tbe-affiliates');

        callbackQueryBus()->addCallbackQueries([
            AffiliationQuery::class,
            AffiliatesQuery::class,
        ]);

        replyKeyBus()->addReplyKeys([
            AffiliationKey::class,
            AffiliatesKey::class,
        ]);

        commandBus()->addCommands([
            AffiliationCommand::class,
        ]);

        stateAnswerBus()->addStateAnswers([
            AffiliationAnswer::class,
            AffiliatesAnswer::class,
        ]);

        Event::listen(InvoicePaid::class, HandleInvoicePaid::class);
        Event::listen(InvoiceRevoked::class, HandleInvoiceRevoked::class);
        Event::listen(BotDeepLinkReceived::class, HandleAffiliateReferral::class);
        Event::listen(BotDeepLinkReceived::class, OpenAffiliationFromDeepLink::class);

        $this->addSettings();
        $this->registerUserSection();
        $this->registerAnnouncementPlaceholders();

        BotUser::resolveRelationUsing('affiliate', function (BotUser $user) {
            return $user->hasOne(
                Affiliate::class,
                'bot_user_id',
                'id'
            );
        });

        BotUser::resolveRelationUsing('referredBy', function (BotUser $user) {
            return $user->hasOne(
                Referral::class,
                'bot_user_id',
                'id'
            );
        });
    }

    /**
     * Optional: user-management is not a dependency of this package. With it
     * installed, the profile of a member who refers or was referred links to
     * their affiliation.
     */
    /** The member's affiliate screen in the web panel, when they refer anyone. */
    private static function affiliateWebUrl(BotUser $user): ?string
    {
        $affiliate = Affiliate::query()->where('bot_user_id', $user->id)->first();

        return $affiliate === null ? null : AffiliateResource::getUrl('view', ['record' => $affiliate]);
    }

    /**
     * `{$affiliates.link}` and `{$affiliates.earnings}` in announcement texts.
     * Registered only when announcements is installed, which this package
     * does not require.
     */
    private function registerAnnouncementPlaceholders(): void
    {
        if (! class_exists(Placeholder::class)) {
            return;
        }

        $active = fn () => (bool) settings()->get('affiliates.affiliates_status');

        announcementPlaceholders()
            ->add(new Placeholder(
                key: 'affiliates.link',
                label: fn () => __('tbe-affiliates::affiliation.placeholders.link'),
                // A member who never opened the affiliation menu has no code
                // yet: they get one here, as the menu would give them.
                resolve: function (BotUser $user): ?string {
                    $username = wHook()->bot()->username();
                    if ($username === null) {
                        return null;
                    }

                    $code = Affiliate::query()
                        ->firstOrCreate(
                            ['bot_user_id' => $user->id],
                            // Named, not left to the tenant scope: this runs on the queue.
                            ['bot_id' => $user->getAttribute('bot_id'), 'referral_code' => uniqid()],
                        )
                        ->getAttribute('referral_code');

                    return is_string($code) ? "https://t.me/{$username}?start={$code}" : null;
                },
                example: 'https://t.me/your_bot?start=64f1c2a9e1b3d',
                active: $active,
            ))
            ->add(new Placeholder(
                key: 'affiliates.earnings',
                label: fn () => __('tbe-affiliates::affiliation.placeholders.earnings'),
                resolve: fn (BotUser $user) => currency()->priceFormat(
                    (string) AffiliateTransaction::query()
                        ->where('recipient_bot_user_id', $user->id)
                        ->where('status', AffiliateTransaction::STATUS_CREDITED)
                        ->sum('amount')
                ),
                example: fn () => currency()->priceFormat('120000'),
                active: $active,
            ));
    }

    private function registerUserSection(): void
    {
        if (! class_exists(UserSection::class)) {
            return;
        }

        $referrals = fn (BotUser $user): int => Referral::query()
            ->whereIn('affiliate_id', Affiliate::query()->where('bot_user_id', $user->id)->select('id'))
            ->count();

        app(UserManagementSections::class)->addSection(new UserSection(
            key: 'affiliation',
            order: 25,
            mode: SectionMode::BUTTON,
            target: fn (BotUser $user) => encodeCallback(AffiliatesFeature::$type, 'user', [$user->id]),
            active: fn (BotUser $user) => Affiliate::query()->where('bot_user_id', $user->id)->exists()
                || Referral::query()->where('bot_user_id', $user->id)->exists(),
            summary: fn (BotUser $user): SectionSummary => new SectionSummary(
                title: __('tbe-affiliates::admin.section.title'),
                value: $referrals($user),
                emoji: '🤝',
                icon: Heroicon::OutlinedUserGroup,
            ),
            webUrl: fn (BotUser $user): ?string => self::affiliateWebUrl($user),
        ));

        $sorts = app(BotUserSorts::class);

        $sorts->addSort(new BotUserSort(
            key: 'referrals',
            label: fn () => __('tbe-affiliates::admin.sorts.referrals'),
            apply: fn (Builder $query, string $direction) => $direction === 'asc'
                ? $query->orderBy(self::referralCount())
                : $query->orderByDesc(self::referralCount()),
            display: fn (BotUser $user) => (string) Referral::query()
                ->whereIn('affiliate_id', Affiliate::query()->where('bot_user_id', $user->id)->select('id'))
                ->count(),
        ));

        $sorts->addSort(new BotUserSort(
            key: 'affiliate_earnings',
            label: fn () => __('tbe-affiliates::admin.sorts.earnings'),
            apply: fn (Builder $query, string $direction) => $direction === 'asc'
                ? $query->orderBy(self::earnings())
                : $query->orderByDesc(self::earnings()),
            display: fn (BotUser $user) => currency()->priceFormat((string) AffiliatesFeature::credited()
                ->where('recipient_bot_user_id', $user->id)
                ->sum('amount')),
        ));
    }

    /**
     * How many members the outer bot_users row referred, as a subquery.
     *
     * @return Builder<Referral>
     */
    private static function referralCount(): Builder
    {
        return Referral::query()
            ->selectRaw('COUNT(*)')
            ->whereIn('affiliate_id', Affiliate::query()->select('id')->whereColumn('bot_user_id', 'bot_users.id'));
    }

    /**
     * What the outer bot_users row was credited, as a subquery.
     *
     * @return Builder<AffiliateTransaction>
     */
    private static function earnings(): Builder
    {
        return AffiliatesFeature::credited()
            ->selectRaw('COALESCE(SUM(amount), 0)')
            ->whereColumn('recipient_bot_user_id', 'bot_users.id');
    }

    protected function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../lang' => resource_path('lang/vendor/tbe-affiliates'),
            ], 'tbe-affiliates-translations');
        }
    }

    private function addSettings(): void
    {
        settings()->addSetting(new Setting(
            key: 'affiliates',
            label: fn () => __('tbe-affiliates::settings.labels.affiliates'),
            type: SettingType::DIRECTORY,
            description: fn () => __('tbe-affiliates::settings.descriptions.affiliates'),
        ));

        settings()->addSetting(new Setting(
            key: 'affiliates.affiliates_status',
            label: fn () => __('tbe-affiliates::settings.labels.status'),
            type: SettingType::CHECKBOX,
            default: false,
            description: fn () => __('tbe-affiliates::settings.descriptions.status'),
        ));

        settings()->addSetting(new Setting(
            key: 'affiliates.allow_existing_users',
            label: fn () => __('tbe-affiliates::settings.labels.allow_existing_users'),
            type: SettingType::CHECKBOX,
            default: false,
            description: fn () => __('tbe-affiliates::settings.descriptions.allow_existing_users'),
        ));

        settings()->addSetting(new Setting(
            key: 'affiliates.share_percentage',
            label: fn () => __('tbe-affiliates::settings.labels.share_percentage'),
            type: SettingType::NUMBER,
            default: 10,
            description: fn () => __('tbe-affiliates::settings.descriptions.share_percentage'),
        ));

        settings()->addSetting(new Setting(
            key: 'affiliates.referrer_signup_bonus',
            label: fn () => __('tbe-affiliates::settings.labels.referrer_signup_bonus'),
            type: SettingType::NUMBER,
            default: 0,
            description: fn () => __('tbe-affiliates::settings.descriptions.referrer_signup_bonus'),
        ));

        settings()->addSetting(new Setting(
            key: 'affiliates.referred_signup_bonus',
            label: fn () => __('tbe-affiliates::settings.labels.referred_signup_bonus'),
            type: SettingType::NUMBER,
            default: 0,
            description: fn () => __('tbe-affiliates::settings.descriptions.referred_signup_bonus'),
        ));

        settings()->addSetting(new Setting(
            key: 'affiliates.share_tagline',
            label: fn () => __('tbe-affiliates::settings.labels.share_tagline'),
            type: SettingType::TEXT,
            description: fn () => __('tbe-affiliates::settings.descriptions.share_tagline'),
        ));
    }
}
