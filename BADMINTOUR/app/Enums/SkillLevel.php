<?php

namespace App\Enums;

enum SkillLevel: string
{
    case OPEN = 'Open';
    case A = 'A';
    case B = 'B';
    case C = 'C';
    case D = 'D';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function isValid(string $value): bool
    {
        return in_array($value, self::values());
    }
}

