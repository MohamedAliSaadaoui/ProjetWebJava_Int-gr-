<?php

namespace App\Doctrine\Type;

use App\Enum\RoleEnum;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class RoleEnumType extends Type
{
    const ROLE_ENUM = 'role_enum';

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        // Explicitement créer un type ENUM avec les valeurs exactes
        return "ENUM('ROLE_ADMIN', 'ROLE_USER', 'ROLE_MODERATOR', '')";
    }
    
    public function convertToPHPValue($value, AbstractPlatform $platform): ?RoleEnum
    {
        if ($value === null) {
            return null;
        }
        
        // Convertir de la valeur DB vers l'enum PHP
        return RoleEnum::from($value);
    }
    
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }
        
        // Si c'est déjà un enum, retourne sa valeur
        if ($value instanceof RoleEnum) {
            return $value->value;
        }
        
        // Si c'est une chaîne, vérifie qu'elle est valide
        return RoleEnum::from($value)->value;
    }
    
    public function getName(): string
    {
        return self::ROLE_ENUM;
    }
    
    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}