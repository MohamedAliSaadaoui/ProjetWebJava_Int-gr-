<?php

namespace App\Doctrine\Type;

use App\Enum\StatutEnum;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\ConversionException;

class StatutEnumType extends Type
{
    public const NAME = 'statut_enum';

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        return "ENUM('actif', 'inactif')";
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?StatutEnum
    {
        if ($value === null) {
            return null;
        }

        return StatutEnum::from($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if (!$value instanceof StatutEnum) {
            throw ConversionException::conversionFailedInvalidType($value, self::NAME, ['null', StatutEnum::class]);
        }

        return $value->value;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
