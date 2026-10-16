import {
    AirVent,
    Bike,
    Camera,
    Car,
    Gamepad2,
    Headphones,
    Laptop,
    Monitor,
    Printer,
    Refrigerator,
    Smartphone,
    Tablet,
    Truck,
    Tv,
    WashingMachine,
    Watch,
    Wrench,
} from 'lucide-react';
import type { LucideIcon, LucideProps } from 'lucide-react';

/** Mirrors App\Enums\EquipmentIcon. "motorcycle" uses Bike until lucide ships a motorbike icon. */
export const equipmentIcons: Record<string, LucideIcon> = {
    smartphone: Smartphone,
    tablet: Tablet,
    laptop: Laptop,
    monitor: Monitor,
    printer: Printer,
    tv: Tv,
    gamepad: Gamepad2,
    watch: Watch,
    headphones: Headphones,
    camera: Camera,
    car: Car,
    motorcycle: Bike,
    truck: Truck,
    refrigerator: Refrigerator,
    'washing-machine': WashingMachine,
    'air-vent': AirVent,
    wrench: Wrench,
};

export function EquipmentIcon({
    name,
    ...props
}: { name: string } & LucideProps) {
    const Icon = equipmentIcons[name] ?? Wrench;

    return <Icon {...props} />;
}
