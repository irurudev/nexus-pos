<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case KASIR = 'kasir';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrator',
            self::KASIR => 'Kasir',
        };
    }
}
