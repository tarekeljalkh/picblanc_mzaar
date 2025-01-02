<?php

namespace App\Enums;

enum ProductType: string
{
    case STANDARD = 'standard';
    case FIXED = 'fixed';

    /**
     * Get all enum values as an array.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
