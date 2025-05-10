<?php

namespace App\Enum;

enum RoleEnum: string
{
    case ADMIN = 'admin';
    case USER = 'user';
    case MODERATOR = 'moderator';

    public function toSymfonyRole(): string
    {
        return match ($this) {
            self::ADMIN => 'ROLE_ADMIN',
            self::USER => 'ROLE_USER',
            self::MODERATOR => 'ROLE_MODERATOR',
        };
    }
}
