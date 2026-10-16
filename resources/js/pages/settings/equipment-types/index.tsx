import { Head, Link } from '@inertiajs/react';
import { Pencil, Plus, Power, PowerOff } from 'lucide-react';
import { EquipmentIcon } from '@/components/equipment/equipment-icon';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { create, edit, index, toggle } from '@/routes/equipment-types';
import type { EquipmentTypeSummary } from '@/types';

type Props = {
    equipmentTypes: EquipmentTypeSummary[];
};

const plural = (count: number, one: string, many: string) =>
    `${count} ${count === 1 ? one : many}`;

export default function EquipmentTypesIndex({ equipmentTypes }: Props) {
    return (
        <>
            <Head title="Tipos de equipamento" />

            <div className="space-y-6">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <Heading
                        variant="small"
                        title="Tipos de equipamento"
                        description="Os aparelhos que sua empresa atende, com os campos e o checklist de cada um."
                    />
                    <Button asChild size="sm">
                        <Link href={create()}>
                            <Plus />
                            Novo tipo
                        </Link>
                    </Button>
                </div>

                {equipmentTypes.length === 0 ? (
                    <p className="rounded-lg border border-dashed p-6 text-center text-sm text-muted-foreground">
                        Nenhum tipo cadastrado ainda.
                    </p>
                ) : (
                    <ul className="divide-y rounded-lg border">
                        {equipmentTypes.map((type) => (
                            <li
                                key={type.id}
                                className={`flex items-center gap-3 p-3 ${type.isActive ? '' : 'opacity-60'}`}
                            >
                                <div className="flex size-9 shrink-0 items-center justify-center rounded-md border">
                                    <EquipmentIcon
                                        name={type.icon}
                                        className="size-5"
                                    />
                                </div>

                                <div className="min-w-0 flex-1">
                                    <div className="flex items-center gap-2">
                                        <span className="truncate font-medium">
                                            {type.name}
                                        </span>
                                        {!type.isActive && (
                                            <Badge variant="secondary">
                                                Desativado
                                            </Badge>
                                        )}
                                    </div>
                                    <p className="truncate text-xs text-muted-foreground">
                                        {type.identifierLabel} ·{' '}
                                        {plural(
                                            type.fieldsCount,
                                            'campo',
                                            'campos',
                                        )}{' '}
                                        ·{' '}
                                        {plural(
                                            type.checklistCount,
                                            'item',
                                            'itens',
                                        )}{' '}
                                        no checklist
                                    </p>
                                </div>

                                <Button variant="ghost" size="icon" asChild>
                                    <Link
                                        href={edit(type.id)}
                                        aria-label={`Editar ${type.name}`}
                                    >
                                        <Pencil />
                                    </Link>
                                </Button>
                                <Button variant="ghost" size="icon" asChild>
                                    <Link
                                        href={toggle(type.id)}
                                        as="button"
                                        preserveScroll
                                        aria-label={
                                            type.isActive
                                                ? `Desativar ${type.name}`
                                                : `Reativar ${type.name}`
                                        }
                                        title={
                                            type.isActive
                                                ? 'Desativar'
                                                : 'Reativar'
                                        }
                                    >
                                        {type.isActive ? (
                                            <PowerOff />
                                        ) : (
                                            <Power />
                                        )}
                                    </Link>
                                </Button>
                            </li>
                        ))}
                    </ul>
                )}

                <p className="text-xs text-muted-foreground">
                    Tipos desativados não aparecem em novos cadastros, mas os
                    equipamentos já cadastrados continuam com eles.
                </p>
            </div>
        </>
    );
}

EquipmentTypesIndex.layout = {
    breadcrumbs: [{ title: 'Tipos de equipamento', href: index() }],
};
