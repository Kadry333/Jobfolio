<?php

namespace App\Enums;

enum JobContactStatus: string
{
    case WillContact = 'will contact';
    case MessageSent = 'message sent';
    case Replied = 'replied';
    case NoResponse = 'no response';

    /**
     * Get all enum values.
     *
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
