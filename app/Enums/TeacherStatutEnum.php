<?php

namespace App\Enums;

enum TeacherStatutEnum: string
{
    case Permanent = 'permanent';
    case Temporary = 'temporary';
    case Contractual = 'contractual';

    /**
     * Retourne un tableau des valeurs de l'enum.
     *
     * @return array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
