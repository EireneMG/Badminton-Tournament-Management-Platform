<?php

namespace App\Enums;

enum CategoryType: string
{
    case MENS_SINGLES = 'MS';
    case WOMENS_SINGLES = 'WS';
    case MENS_DOUBLES = 'MD';
    case WOMENS_DOUBLES = 'WD';
    case MIXED_DOUBLES = 'XD';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function isValid(string $value): bool
    {
        return in_array($value, self::values());
    }

    public function isDoubles(): bool
    {
        return in_array($this, [self::MENS_DOUBLES, self::WOMENS_DOUBLES, self::MIXED_DOUBLES]);
    }
}

