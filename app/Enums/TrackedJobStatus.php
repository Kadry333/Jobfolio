<?php

namespace App\Enums;

enum TrackedJobStatus: string
{
    case WillApply = 'will apply';
    case Applied = 'applied';
    case Interview = 'interview';
    case NoResponse = 'no response';
    case Offer = 'offer';
    case Rejected = 'rejected';

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
