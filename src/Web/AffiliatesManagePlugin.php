<?php

namespace TelegramBotEssentials\Affiliates\Web;

use Filament\Contracts\Plugin;
use Filament\Panel;
use TelegramBotEssentials\Affiliates\Web\Manage\AffiliateResource;
use TelegramBotEssentials\Affiliates\Web\Widgets\AffiliateStats;

class AffiliatesManagePlugin implements Plugin
{
    public function getId(): string
    {
        return 'tbe-affiliates-manage';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([AffiliateResource::class]);
        $panel->widgets([AffiliateStats::class]);
    }

    public function boot(Panel $panel): void {}
}
