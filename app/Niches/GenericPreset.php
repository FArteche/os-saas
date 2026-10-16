<?php

namespace App\Niches;

use App\Enums\FieldType;

class GenericPreset extends BasePreset
{
    public function equipmentTypes(): array
    {
        return [
            [
                'name' => 'Equipamento',
                'icon' => 'wrench',
                'identifier_label' => 'Nº de série',
                'fields' => [
                    self::field('cor', 'Cor'),
                    self::field('liga', 'Liga?', FieldType::Boolean),
                    self::field('acessorios', 'Acessórios entregues'),
                ],
                'checklist' => [
                    self::item('liga_normalmente', 'Liga normalmente'),
                    self::item('partes_externas', 'Partes externas'),
                    self::item('cabos_acessorios', 'Cabos e acessórios'),
                ],
            ],
        ];
    }
}
