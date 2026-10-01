<?php

namespace TelegramBotEssentials\Affiliates\Telegram\StateAnswers\Admin;

use TelegramBotEssentials\Affiliates\Models\Affiliate;
use TelegramBotEssentials\Affiliates\Telegram\Features\Admin\AffiliatesFeature;
use TelegramBotEssentials\Essence\Enums\AllowableFields;
use TelegramBotEssentials\Essence\Enums\Roles;
use TelegramBotEssentials\Essence\Services\TelegramPaginator;
use TelegramBotEssentials\Essence\Telegram\StateAnswers\StateAnswer;

class AffiliatesAnswer extends StateAnswer
{
    protected string $type = 'AFFILIATES';

    protected int $perm = Roles::ADMIN->value;

    protected array $allowedFields = [
        AllowableFields::TEXT->value,
    ];

    public function setMenuPage(string $sort = 'earned', string $direction = 'desc'): void
    {
        $page = wHook()->update()->message->text;

        TelegramPaginator::validatePageInput($page, Affiliate::query()->paginate(perPage: AffiliatesFeature::PER_PAGE)->lastPage());

        $data = AffiliatesFeature::menu(intval($page), sort: $sort, direction: $direction);

        wHook()->user()->changeState();
        wHook()->api()->sendMessage([
            'chat_id' => wHook()->user()->telegramUser->peer_id,
            'text' => __('tbe-affiliates::admin.menu.text.pageLoaded', ['page' => $page]),
            'reply_markup' => wHook()->user()->getKeyboard(),
        ]);

        $this->requireMessageMeta()->updateAndContinueAction($data);
    }
}
