<?php

namespace App\Contracts;

enum UserStatus: string
{
    case Active = 'Active';
    case Inactive = 'Inactive';
    case Prohibit = 'Prohibit';

    /**
     * Get all values as array for validation or DB usage.
     */
    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}
