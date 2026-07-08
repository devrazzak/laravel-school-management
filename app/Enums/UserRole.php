<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Teacher = 'teacher';
    case Student = 'student';

    public function relation(): ?string
    {
        return match ($this) {
            self::Student => 'student',
            self::Teacher => 'teacher',
            default => null,
        };
    }
}
