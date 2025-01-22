<?php

namespace App\Enums;

enum StatutStudentEnum :string
{
    case Active = 'Active';
    case Inactive = 'Inactive';
    case Graduated = 'Graduated';
    case DroppedOut = 'DroppedOut';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
