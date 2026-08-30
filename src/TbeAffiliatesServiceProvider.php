<?php

namespace TelegramBotEssentials\Affiliates;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use TelegramBotEssentials\Affiliates\Listeners\HandleAffiliateReferral;
use TelegramBotEssentials\Affiliates\Listeners\HandleInvoicePaid;
use TelegramBotEssentials\Affiliates\Listeners\HandleInvoiceRevoked;
use TelegramBotEssentials\Affiliates\Models\Affiliate;
use TelegramBotEssentials\Affiliates\Models\Referral;
use TelegramBotEssentials\Affiliates\Telegram\CallbackQueries\Member\AffiliationQuery;
use TelegramBotEssentials\Affiliates\Telegram\Commands\Member\AffiliationCommand;
use TelegramBotEssentials\Affiliates\Telegram\ReplyKeys\Member\AffiliationKey;
use TelegramBotEssentials\Affiliates\Telegram\StateAnswers\Member\AffiliationAnswer;
use TelegramBotEssentials\Billing\Events\InvoicePaid;
use TelegramBotEssentials\Billing\Events\InvoiceRevoked;
use TelegramBotEssentials\Essence\Events\BotDeepLinkReceived;
use TelegramBotEssentials\Essence\Exceptions\LogicException;
use TelegramBotEssentials\Essence\Models\BotUser;
use TelegramBotEssentials\Settings\DTOs\Setting;
use TelegramBotEssentials\Settings\Enums\SettingType;

class TbeAffiliatesServiceProvider extends ServiceProvider
{
    public function register(): void {}

    /**
     * @throws LogicException
     * @throws BindingResolutionException
     */
    public function boot(): void
    {
        $this->registerPublishing();

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'tbe-affiliates');

        callbackQueryBus()->addCallbackQueries([
            AffiliationQuery::class,
        ]);

        replyKeyBus()->addReplyKeys([
            AffiliationKey::class,
        ]);

        config([
            'tbe-essence.commands' => [
                ...config('tbe-essence.commands', []),
                AffiliationCommand::class,
            ],
        ]);

        commandBus()->addCommands([
            AffiliationCommand::class,
        ]);

        stateAnswerBus()->addStateAnswers([
            AffiliationAnswer::class,
        ]);

        Event::listen(InvoicePaid::class, HandleInvoicePaid::class);
        Event::listen(InvoiceRevoked::class, HandleInvoiceRevoked::class);
        Event::listen(BotDeepLinkReceived::class, HandleAffiliateReferral::class);

        $this->addSettings();

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
