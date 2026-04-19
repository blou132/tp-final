<?php

namespace App\Enums;

enum TicketCategory: string
{
    case GENERAL = 'general';
    case TECHNICAL = 'technical';
    case BILLING = 'billing';
    case ACCOUNT = 'account';

    public static function values(): array
    {
        return array_map(static fn (self $category) => $category->value, self::cases());
    }
}
