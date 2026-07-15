<?php

namespace TelegramBotEssentials\Affiliates;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use TelegramBotEssentials\Affiliates\Listeners\HandleInvoicePaid;
use TelegramBotEssentials\Affiliates\Listeners\HandleInvoiceRevoked;
use TelegramBotEssentials\Affiliates\Models\Affiliate;
use TelegramBotEssentials\Affiliates\Telegram\CallbackQueries\Member\AffiliationQuery;
use TelegramBotEssentials\Affiliates\Telegram\StateAnswers\Member\AffiliationAnswer;
use TelegramBotEssentials\Billing\Events\InvoicePaid;
use TelegramBotEssentials\Billing\Events\InvoiceRevoked;
use TelegramBotEssentials\Essence\Exceptions\LogicException;
use TelegramBotEssentials\Essence\Models\BotUser;
use TelegramBotEssentials\Settings\DTOs\Setting;
use TelegramBotEssentials\Settings\Enums\SettingType;

class TbeAffiliatesServiceProvider extends ServiceProvider
{
    public function register(): void
    {

    }

    /**
     * @throws LogicException
     * @throws BindingResolutionException
     */
    public function boot(): void
    {
        $this->registerPublishing();

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'tbe-affiliates');

        callbackQueryBus()->addCallbackQueries([
            AffiliationQuery::class
        ]);

        stateAnswerBus()->addStateAnswers([
            AffiliationAnswer::class
        ]);

        Event::listen(InvoicePaid::class, HandleInvoicePaid::class);
        Event::listen(InvoiceRevoked::class, HandleInvoiceRevoked::class);

        $this->addSettings();

        BotUser::resolveRelationUsing('affiliate', function (BotUser $user) {
            return $user->hasOne(
                Affiliate::class,
                'bot_user_id',
                'id'
            );
        });
    }

    protected function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../lang' => resource_path('lang/vendor/tbe-affiliates'),
            ], 'tbe-affiliates-translations');
        }
    }

    private function addSettings(): void
    {
        settings()->addSetting(new Setting(
            key: 'affiliates',
            label: fn () => __('tbe-affiliates::settings.labels.affiliates'),
            type: SettingType::DIRECTORY,
        ));

        settings()->addSetting(new Setting(
            key: 'affiliates.affiliates_status',
            label: fn () => __('tbe-affiliates::settings.labels.status'),
            type: SettingType::DIRECTORY,
        ));
    }
}
