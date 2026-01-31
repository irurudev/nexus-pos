<?php

namespace App\Enums;

enum JenisKelamin: string
{
    case PRIA = 'PRIA';
    case WANITA = 'WANITA';

    public function label(): string
    {
        return match ($this) {
            self::PRIA => 'Pria',
            self::WANITA => 'Wanita',
        };
    }
}
