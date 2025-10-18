<?php

namespace TelegramBotEssentials\Affiliates\Telegram\Features\Member;
use TelegramBotEssentials\Essence\Telegram\TelegramResponse;
use Telegram\Bot\Keyboard\Keyboard;

class AffiliationFeature
{
    static string $type = 'AFFILIATION';

    // TODO: Implement static functions for generating bot messages
    public static function menu(): TelegramResponse
    {
        $text = 'menu';

        $replyMarkup = Keyboard::make()
            ->inline();

        return new TelegramResponse(
            text: $text,
            replyMarkup: $replyMarkup,
            parseMode: 'HTML'
        );
    }
}
