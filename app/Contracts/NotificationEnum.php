<?php

namespace App\Contracts;

enum NotificationEnum: string {
    case PERSONICON = 'mat:account_circle';
    case PERSONICONCOLORCLASSPRIMARY = 'text-primary-600';
    case PERSONICONCOLORCLASSORANGE = 'text-orange-600';
    case PERSONICONCOLORCLASSRED = 'text-red-600';
    case PERSONICONCOLORCLASSGREEN = 'text-green-600';

    /**
     * Get all values as array for validation or DB usage.
     */
    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}
