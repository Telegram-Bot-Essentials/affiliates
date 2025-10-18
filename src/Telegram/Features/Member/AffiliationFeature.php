<?php

namespace TelegramBotEssentials\Affiliates\Telegram\Features\Member;

use Telegram\Bot\Keyboard\Keyboard;
use TelegramBotEssentials\Essence\Telegram\TelegramResponse;

class AffiliationFeature
{
    static string $type = 'AFFILIATION';

    // TODO: Implement static functions for generating bot messages
    public static function menu(): TelegramResponse
    {
        $affiliate = wHook()->user()->refresh()->affiliate;
        if (!$affiliate) {
            $replyMarkup = Keyboard::make()
                ->inline();

            $replyMarkup->row([
                Keyboard::inlineButton([
                    'text' => 'Activate Affiliation',
                    'callback_data' => encodeCallback(AffiliationFeature::$type, 'activate')
                ])
            ]);

            return new TelegramResponse(
                text: 'You did not enabled affiliation program yet.',
                replyMarkup: $replyMarkup,
                parseMode: 'HTML'
            );
        }
        $text = 'Affiliation Program';

        $replyMarkup = Keyboard::make()
            ->inline();

        $replyMarkup->row([
            Keyboard::inlineButton([
                'text' => 'Get Invite Link',
                'callback_data' => encodeCallback(AffiliationFeature::$type, 'getInviteLink')
            ])
        ]);

        return new TelegramResponse(
            text: $text,
            replyMarkup: $replyMarkup,
            parseMode: 'HTML'
        );
    }
}
