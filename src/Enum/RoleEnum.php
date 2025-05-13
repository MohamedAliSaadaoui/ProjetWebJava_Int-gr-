<?php

namespace App\Enum;

class RoleEnum
{
    const ADMIN = 'ROLE_ADMIN';
    const VISITEUR = 'ROLE_VISITEUR';
    const LIVREUR = 'ROLE_LIVREUR';

    public static function getRoles(): array
    {
        return [
            self::ADMIN,
            self::VISITEUR,
            self::LIVREUR,
        ];
    }
}
