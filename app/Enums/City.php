<?php

declare(strict_types=1);

namespace App\Enums;

enum City: string
{
    case Prague = 'Prague';
    case Paris = 'Paris';
    case Berlin = 'Berlin';

    public function country(): Country
    {
        return match ($this) {
            self::Prague => Country::CzechRepublic,
            self::Paris => Country::France,
            self::Berlin => Country::Germany,
        };
    }
}
