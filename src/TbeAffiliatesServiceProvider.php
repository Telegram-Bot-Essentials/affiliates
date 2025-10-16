<?php

namespace TelegramBotEssentials\Affiliates;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\ServiceProvider;
use TelegramBotEssentials\Essence\Exceptions\LogicException;
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
        ]);

        stateAnswerBus()->addStateAnswers([
        ]);

        $this->addSettings();
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
            label: 'Affiliates',
            type: SettingType::DIRECTORY,
        ));

        settings()->addSetting(new Setting(
            key: 'affiliates.affiliates_status',
            label: 'Status',
            type: SettingType::DIRECTORY,
        ));
    }
}
