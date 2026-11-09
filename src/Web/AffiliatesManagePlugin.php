<?php

namespace TelegramBotEssentials\Affiliates\Web;

use Filament\Contracts\Plugin;
use Filament\Panel;
use TelegramBotEssentials\Affiliates\Web\Manage\AffiliateResource;

class AffiliatesManagePlugin implements Plugin
{
    public function getId(): string
    {
        return 'tbe-affiliates-manage';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([AffiliateResource::class]);
    }

    public function boot(Panel $panel): void {}
}
