<?php

namespace App\Enums;

enum UserStatus
{
    case Active;
    case Inactive;

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Inactive => 'Inactive',
        };
    }
}
