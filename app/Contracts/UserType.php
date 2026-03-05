<?php

namespace App\Contracts;

enum UserType: string {
    case Admin = 'Admin';
    case Staff = 'Staff';
    case Member = 'Member';
    case Guest = 'Guest';

    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}
