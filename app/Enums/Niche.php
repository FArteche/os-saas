<?php

namespace App\Enums;

use App\Niches\AutoRepairPreset;
use App\Niches\CellphonePreset;
use App\Niches\GenericPreset;
use App\Niches\NichePreset;

enum Niche: string
{
    case Generic = 'generic';
    case Cellphone = 'cellphone';
    case AutoRepair = 'auto_repair';

    /**
     * The starting configuration (equipment types, settings) for this niche.
     */
    public function preset(): NichePreset
    {
        return match ($this) {
            self::Generic => new GenericPreset,
            self::Cellphone => new CellphonePreset,
            self::AutoRepair => new AutoRepairPreset,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Generic => 'Assistência técnica em geral',
            self::Cellphone => 'Celulares e eletrônicos',
            self::AutoRepair => 'Oficina mecânica',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Generic => 'Qualquer tipo de equipamento. Você configura os campos depois.',
            self::Cellphone => 'Já vem com IMEI, senha de desbloqueio e checklist de aparelho.',
            self::AutoRepair => 'Já vem com placa, quilometragem e checklist de veículo.',
        };
    }

    /**
     * Options for select/radio inputs on the frontend.
     *
     * @return list<array{value: string, label: string, description: string}>
     */
    public static function options(): array
    {
        return array_map(fn (self $niche) => [
            'value' => $niche->value,
            'label' => $niche->label(),
            'description' => $niche->description(),
        ], self::cases());
    }
}
