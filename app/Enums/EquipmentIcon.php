<?php

namespace App\Enums;

/**
 * Whitelist of icons an equipment type may use. Values are lucide-react icon names,
 * mapped to components on the frontend (resources/js/components/equipment/equipment-icon.tsx).
 */
enum EquipmentIcon: string
{
    case Smartphone = 'smartphone';
    case Tablet = 'tablet';
    case Laptop = 'laptop';
    case Monitor = 'monitor';
    case Printer = 'printer';
    case Tv = 'tv';
    case Gamepad = 'gamepad';
    case Watch = 'watch';
    case Headphones = 'headphones';
    case Camera = 'camera';
    case Car = 'car';
    case Motorcycle = 'motorcycle';
    case Truck = 'truck';
    case Refrigerator = 'refrigerator';
    case WashingMachine = 'washing-machine';
    case AirVent = 'air-vent';
    case Wrench = 'wrench';

    public function label(): string
    {
        return match ($this) {
            self::Smartphone => 'Celular',
            self::Tablet => 'Tablet',
            self::Laptop => 'Notebook',
            self::Monitor => 'Monitor / computador',
            self::Printer => 'Impressora',
            self::Tv => 'TV',
            self::Gamepad => 'Videogame',
            self::Watch => 'Relógio',
            self::Headphones => 'Fone de ouvido',
            self::Camera => 'Câmera',
            self::Car => 'Carro',
            self::Motorcycle => 'Moto',
            self::Truck => 'Caminhão',
            self::Refrigerator => 'Geladeira',
            self::WashingMachine => 'Máquina de lavar',
            self::AirVent => 'Ar-condicionado',
            self::Wrench => 'Ferramenta (genérico)',
        };
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(fn (self $icon) => ['value' => $icon->value, 'label' => $icon->label()], self::cases());
    }
}
