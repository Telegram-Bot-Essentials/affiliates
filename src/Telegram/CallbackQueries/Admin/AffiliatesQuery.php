<?php

namespace TelegramBotEssentials\Affiliates\Telegram\CallbackQueries\Admin;

use TelegramBotEssentials\Affiliates\Telegram\Features\Admin\AffiliatesFeature;
use TelegramBotEssentials\Essence\Exceptions\InvalidPageNumber;
use TelegramBotEssentials\Essence\Models\BotUser;
use TelegramBotEssentials\Essence\Models\MessageMeta;
use TelegramBotEssentials\Essence\Telegram\CallbackQueries\CallbackQuery;

class AffiliatesQuery extends CallbackQuery
{
    protected string $type = 'AFFILIATES';

    protected ?string $permission = 'affiliates.manage';

    /**
     * @throws InvalidPageNumber
     */
    public function menu(int $page = 1, int $currentPage = 0, string $sort = 'earned', string $direction = 'desc'): void
    {
        AffiliatesFeature::menu($page, $currentPage, $sort, $direction)->update();
    }

    public function setMenuPage(string $sort = 'earned', string $direction = 'desc'): void
    {
        $messageMeta = MessageMeta::makeWithCurrentMessage();
        $messageMeta->lockAction(__('tbe-affiliates::admin.menu.text.waitingPage'));
        wHook()->user()->changeState(encodeAnswerState($this->type, 'setMenuPage', [
            'message_meta_id' => $messageMeta->id,
            'sort' => $sort,
            'direction' => $direction,
        ]));
        wHook()->api()->sendMessage([
            'chat_id' => wHook()->user()->telegramUser->peer_id,
            'text' => __('tbe-affiliates::admin.menu.text.enterPage'),
            'reply_markup' => replyKeyboard(),
        ]);
    }

    /**
     * A member's affiliation, from their profile in user management or from a row of the overview.
     */
    public function user(BotUser $botUser, int $listPage = 0, string $sort = 'earned', string $direction = 'desc'): void
    {
        AffiliatesFeature::show($botUser, $listPage, $sort, $direction)->update();
    }
}
