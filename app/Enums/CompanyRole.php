<?php

namespace App\Enums;

enum CompanyRole: string
{
    case Owner = 'owner';
    case Attendant = 'attendant';
    case Technician = 'technician';

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Proprietário',
            self::Attendant => 'Atendente',
            self::Technician => 'Técnico',
        };
    }
}
