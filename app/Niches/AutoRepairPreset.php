<?php

namespace App\Niches;

use App\Enums\FieldType;

class AutoRepairPreset extends BasePreset
{
    public function equipmentTypes(): array
    {
        $vehicleFields = [
            self::field('ano', 'Ano', FieldType::Number),
            self::field('cor', 'Cor'),
            self::field('km', 'Quilometragem', FieldType::Number, required: true, help: 'Como está no painel na entrada'),
            self::field('combustivel', 'Combustível', FieldType::Select, required: true, options: ['Reserva', '1/4', '1/2', '3/4', 'Cheio']),
            self::field('chassi', 'Chassi'),
        ];

        $vehicleChecklist = [
            self::item('lataria', 'Lataria'),
            self::item('pneus', 'Pneus'),
            self::item('farois_lanternas', 'Faróis e lanternas'),
            self::item('painel_alertas', 'Painel e luzes de alerta'),
            self::item('documentos', 'Documentos no veículo'),
            self::item('objetos_pessoais', 'Objetos pessoais'),
        ];

        return [
            [
                'name' => 'Carro',
                'icon' => 'car',
                'identifier_label' => 'Placa',
                'fields' => $vehicleFields,
                'checklist' => [
                    ...$vehicleChecklist,
                    self::item('estepe', 'Estepe'),
                    self::item('macaco_chave_roda', 'Macaco e chave de roda'),
                ],
            ],
            [
                'name' => 'Moto',
                'icon' => 'motorcycle',
                'identifier_label' => 'Placa',
                'fields' => $vehicleFields,
                'checklist' => $vehicleChecklist,
            ],
        ];
    }
}
