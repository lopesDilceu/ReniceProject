<?php

namespace App\Support;

/**
 * Formats integer cents for text the server writes (e-mails). The browser
 * formats with Intl; this avoids requiring the intl extension.
 */
final class Money
{
    public static function format(int $cents): string
    {
        return 'R$ '.number_format($cents / 100, 2, ',', '.');
    }
}
