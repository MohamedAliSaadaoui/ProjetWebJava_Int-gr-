<?php

namespace App\Enum;

enum RoleEnum: string {
    case ROLE_ADMIN = 'ROLE_ADMIN';
    case ROLE_USER = 'ROLE_USER';
    case ROLE_MODERATOR = 'ROLE_MODERATOR';
    case EMPTY = '';
    
    /**
     * Convertit l'enum en label lisible
     */
    public function toLabel(): string
    {
        return match($this) {
            self::ROLE_ADMIN => 'Administrateur',
            self::ROLE_USER => 'Utilisateur',
            self::ROLE_MODERATOR => 'Modérateur',
            self::EMPTY => 'Utilisateur',
            default => 'Utilisateur'
        };
    }
    
    /**
     * Pour la rétrocompatibilité si nécessaire
     */
    public static function fromLegacyValue(string $value): self
    {
        return match($value) {
            'admin' => self::ROLE_ADMIN,
            'user' => self::ROLE_USER,
            'moderator' => self::ROLE_MODERATOR,
            '' => self::EMPTY,
            default => self::ROLE_USER
        };
    }
}