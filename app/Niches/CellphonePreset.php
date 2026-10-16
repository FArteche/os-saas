<?php

namespace App\Niches;

use App\Enums\FieldType;

class CellphonePreset extends BasePreset
{
    public function equipmentTypes(): array
    {
        $mobileFields = [
            self::field('cor', 'Cor'),
            self::field('senha_desbloqueio', 'Senha de desbloqueio', FieldType::Secret, help: 'Senha, PIN ou desenho do padrão'),
            self::field('acessorios', 'Acessórios entregues', help: 'Ex.: capa, chip, carregador'),
            self::field('liga', 'Liga?', FieldType::Boolean),
        ];

        $mobileChecklist = [
            self::item('tela', 'Tela'),
            self::item('touch', 'Touch'),
            self::item('cameras', 'Câmeras'),
            self::item('alto_falante', 'Alto-falante'),
            self::item('microfone', 'Microfone'),
            self::item('conector_carga', 'Conector de carga'),
            self::item('botoes', 'Botões'),
            self::item('biometria', 'Biometria / Face ID'),
            self::item('wifi_bluetooth', 'Wi-Fi / Bluetooth'),
            self::item('carcaca', 'Carcaça'),
            self::item('chip_cartao', 'Chip / cartão de memória'),
        ];

        return [
            [
                'name' => 'Celular',
                'icon' => 'smartphone',
                'identifier_label' => 'IMEI',
                'fields' => $mobileFields,
                'checklist' => $mobileChecklist,
            ],
            [
                'name' => 'Tablet',
                'icon' => 'tablet',
                'identifier_label' => 'IMEI ou nº de série',
                'fields' => $mobileFields,
                'checklist' => $mobileChecklist,
            ],
            [
                'name' => 'Notebook',
                'icon' => 'laptop',
                'identifier_label' => 'Nº de série',
                'fields' => [
                    self::field('senha', 'Senha', FieldType::Secret),
                    self::field('carregador', 'Carregador entregue?', FieldType::Boolean),
                    self::field('liga', 'Liga?', FieldType::Boolean),
                ],
                'checklist' => [
                    self::item('tela', 'Tela'),
                    self::item('teclado', 'Teclado'),
                    self::item('touchpad', 'Touchpad'),
                    self::item('carcaca_dobradicas', 'Carcaça e dobradiças'),
                    self::item('portas_usb', 'Portas USB'),
                    self::item('bateria', 'Bateria'),
                    self::item('wifi', 'Wi-Fi'),
                ],
            ],
        ];
    }
}
