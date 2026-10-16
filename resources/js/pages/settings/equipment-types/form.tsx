import { Head, Link, router, useForm } from '@inertiajs/react';
import { ArrowDown, ArrowUp, Plus, Trash2 } from 'lucide-react';
import { useEffect, useState } from 'react';
import { ChecklistInput } from '@/components/equipment/checklist-input';
import { DynamicFields } from '@/components/equipment/dynamic-fields';
import { EquipmentIcon } from '@/components/equipment/equipment-icon';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import { index, store, update } from '@/routes/equipment-types';
import type {
    ChecklistAnswers,
    ChecklistItem,
    EquipmentTypeForm,
    FieldDefinition,
    FieldType,
    FieldValues,
} from '@/types';

type Props = {
    equipmentType: (EquipmentTypeForm & { id: number }) | null;
    fieldTypes: { value: FieldType; label: string }[];
    icons: { value: string; label: string }[];
    limits: { fields: number; checklist: number };
};

// Client-only id so React can track rows while they are added, removed and reordered.
// (Not the array index: reordering would make inputs "jump" between rows.)
type Row<T> = T & { _uid: string };

let uidCounter = 0;
const withUid = <T,>(item: T): Row<T> => ({
    ...item,
    _uid: `row-${++uidCounter}`,
});

const emptyField = (): Row<FieldDefinition> =>
    withUid({
        key: null,
        label: '',
        type: 'text',
        required: false,
        options: null,
        help: null,
    });

const emptyItem = (): Row<ChecklistItem> => withUid({ key: null, label: '' });

function move<T>(list: T[], from: number, to: number): T[] {
    const copy = [...list];
    const [item] = copy.splice(from, 1);
    copy.splice(to, 0, item);

    return copy;
}

export default function EquipmentTypeFormPage({
    equipmentType,
    fieldTypes,
    icons,
    limits,
}: Props) {
    const isEditing = equipmentType !== null;

    const form = useForm({
        name: equipmentType?.name ?? '',
        icon: equipmentType?.icon ?? 'wrench',
        identifier_label: equipmentType?.identifier_label ?? 'Nº de série',
        fields: (equipmentType?.fields ?? []).map(withUid),
        checklist: (equipmentType?.checklist ?? []).map(withUid),
    });

    const errors = form.errors as Record<string, string | undefined>;

    // Warn before throwing away unsaved changes (in-app links or closing the tab).
    useEffect(() => {
        if (!form.isDirty) {
            return;
        }

        const removeListener = router.on('before', (event) => {
            if (event.detail.visit.method !== 'get') {
                return; // our own save
            }

            if (
                !window.confirm('Há alterações não salvas. Sair mesmo assim?')
            ) {
                event.preventDefault();
            }
        });

        const onBeforeUnload = (event: BeforeUnloadEvent) =>
            event.preventDefault();
        window.addEventListener('beforeunload', onBeforeUnload);

        return () => {
            removeListener();
            window.removeEventListener('beforeunload', onBeforeUnload);
        };
    }, [form.isDirty]);

    // Preview state is local: it only shows how the form will look, nothing is saved.
    const [previewValues, setPreviewValues] = useState<FieldValues>({});
    const [previewAnswers, setPreviewAnswers] = useState<ChecklistAnswers>({});

    const updateField = (i: number, changes: Partial<FieldDefinition>) =>
        form.setData(
            'fields',
            form.data.fields.map((f, idx) =>
                idx === i ? { ...f, ...changes } : f,
            ),
        );

    const updateItem = (i: number, label: string) =>
        form.setData(
            'checklist',
            form.data.checklist.map((item, idx) =>
                idx === i ? { ...item, label } : item,
            ),
        );

    const submit = (e: React.FormEvent) => {
        e.preventDefault();

        // Strip the client-only ids and empty select options before sending.
        form.transform((data) => ({
            ...data,
            fields: data.fields.map(({ _uid, ...field }) => ({
                ...field,
                options:
                    field.type === 'select'
                        ? (field.options ?? [])
                              .map((o) => o.trim())
                              .filter(Boolean)
                        : null,
            })),
            checklist: data.checklist.map(({ _uid, ...item }) => item),
        }));

        if (isEditing) {
            form.put(update.url(equipmentType.id), { preserveScroll: true });
        } else {
            form.post(store.url(), { preserveScroll: true });
        }
    };

    return (
        <>
            <Head
                title={
                    isEditing
                        ? `Editar ${equipmentType.name}`
                        : 'Novo tipo de equipamento'
                }
            />

            <form onSubmit={submit} className="space-y-8">
                <Heading
                    variant="small"
                    title={
                        isEditing
                            ? `Editar "${equipmentType.name}"`
                            : 'Novo tipo de equipamento'
                    }
                    description="Defina os campos preenchidos no cadastro e o checklist conferido na entrada."
                />

                {/* ------------------------------------------------ basics */}
                <div className="grid gap-4 sm:grid-cols-2">
                    <div className="grid gap-2">
                        <Label htmlFor="name">Nome do tipo</Label>
                        <Input
                            id="name"
                            value={form.data.name}
                            onChange={(e) =>
                                form.setData('name', e.target.value)
                            }
                            placeholder="Ex.: Celular"
                            maxLength={100}
                            required
                        />
                        <InputError message={errors.name} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="identifier_label">
                            Identificador principal
                        </Label>
                        <Input
                            id="identifier_label"
                            value={form.data.identifier_label}
                            onChange={(e) =>
                                form.setData('identifier_label', e.target.value)
                            }
                            placeholder="Ex.: IMEI, Placa, Nº de série"
                            maxLength={50}
                            required
                        />
                        <InputError message={errors.identifier_label} />
                    </div>
                </div>

                <fieldset className="grid gap-2">
                    <legend className="mb-2 text-sm font-medium">Ícone</legend>
                    <div className="flex flex-wrap gap-2">
                        {icons.map(({ value: icon, label }) => (
                            <button
                                key={icon}
                                type="button"
                                onClick={() => form.setData('icon', icon)}
                                aria-pressed={form.data.icon === icon}
                                aria-label={label}
                                title={label}
                                className="flex size-10 items-center justify-center rounded-md border transition-colors hover:bg-accent aria-pressed:border-primary aria-pressed:bg-accent"
                            >
                                <EquipmentIcon name={icon} className="size-5" />
                            </button>
                        ))}
                    </div>
                    <InputError message={errors.icon} />
                </fieldset>

                <Separator />

                {/* ------------------------------------------------ fields */}
                <section className="space-y-4">
                    <div>
                        <h3 className="font-medium">Campos extras</h3>
                        <p className="text-sm text-muted-foreground">
                            Além de marca, modelo e identificador, que todo
                            equipamento já tem.
                        </p>
                    </div>

                    {form.data.fields.map((field, i) => (
                        <div
                            key={field._uid}
                            className="grid gap-3 rounded-lg border p-3"
                        >
                            <div className="grid gap-3 sm:grid-cols-[1fr_12rem]">
                                <div className="grid gap-1">
                                    <Label
                                        htmlFor={`field-${field._uid}-label`}
                                    >
                                        Nome do campo
                                    </Label>
                                    <Input
                                        id={`field-${field._uid}-label`}
                                        value={field.label}
                                        onChange={(e) =>
                                            updateField(i, {
                                                label: e.target.value,
                                            })
                                        }
                                        placeholder="Ex.: Cor"
                                        maxLength={60}
                                    />
                                    <InputError
                                        message={errors[`fields.${i}.label`]}
                                    />
                                </div>
                                <div className="grid gap-1">
                                    <Label htmlFor={`field-${field._uid}-type`}>
                                        Tipo
                                    </Label>
                                    <Select
                                        value={field.type}
                                        onValueChange={(type) =>
                                            updateField(i, {
                                                type: type as FieldType,
                                                // Keep typed options while switching types back and
                                                // forth; they are only sent for "select" fields.
                                                options:
                                                    field.options ??
                                                    (type === 'select'
                                                        ? ['']
                                                        : null),
                                            })
                                        }
                                    >
                                        <SelectTrigger
                                            id={`field-${field._uid}-type`}
                                            className="w-full"
                                        >
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {fieldTypes.map((type) => (
                                                <SelectItem
                                                    key={type.value}
                                                    value={type.value}
                                                >
                                                    {type.label}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    <InputError
                                        message={errors[`fields.${i}.type`]}
                                    />
                                </div>
                            </div>

                            {field.type === 'select' && (
                                <div className="grid gap-1">
                                    <Label
                                        htmlFor={`field-${field._uid}-options`}
                                    >
                                        Opções (uma por linha)
                                    </Label>
                                    <Textarea
                                        id={`field-${field._uid}-options`}
                                        value={(field.options ?? []).join('\n')}
                                        onChange={(e) =>
                                            updateField(i, {
                                                options:
                                                    e.target.value.split('\n'),
                                            })
                                        }
                                        placeholder={'Reserva\n1/4\n1/2'}
                                        rows={3}
                                    />
                                    <InputError
                                        message={
                                            errors[`fields.${i}.options`] ??
                                            Object.entries(errors).find(([k]) =>
                                                k.startsWith(
                                                    `fields.${i}.options.`,
                                                ),
                                            )?.[1]
                                        }
                                    />
                                </div>
                            )}

                            {field.type === 'secret' && (
                                <p className="text-xs text-muted-foreground">
                                    Fica criptografado e oculto na tela; ideal
                                    para senhas de desbloqueio.
                                </p>
                            )}

                            <div className="grid gap-1">
                                <Label htmlFor={`field-${field._uid}-help`}>
                                    Texto de ajuda (opcional)
                                </Label>
                                <Input
                                    id={`field-${field._uid}-help`}
                                    value={field.help ?? ''}
                                    onChange={(e) =>
                                        updateField(i, {
                                            help: e.target.value || null,
                                        })
                                    }
                                    placeholder="Ex.: Como está no painel"
                                    maxLength={120}
                                />
                            </div>

                            <div className="flex flex-wrap items-center justify-between gap-2">
                                <label className="flex items-center gap-2 text-sm">
                                    <Checkbox
                                        checked={field.required}
                                        onCheckedChange={(checked) =>
                                            updateField(i, {
                                                required: checked === true,
                                            })
                                        }
                                    />
                                    Obrigatório
                                </label>

                                <div className="flex gap-1">
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="icon"
                                        disabled={i === 0}
                                        onClick={() =>
                                            form.setData(
                                                'fields',
                                                move(
                                                    form.data.fields,
                                                    i,
                                                    i - 1,
                                                ),
                                            )
                                        }
                                        aria-label="Mover para cima"
                                    >
                                        <ArrowUp />
                                    </Button>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="icon"
                                        disabled={
                                            i === form.data.fields.length - 1
                                        }
                                        onClick={() =>
                                            form.setData(
                                                'fields',
                                                move(
                                                    form.data.fields,
                                                    i,
                                                    i + 1,
                                                ),
                                            )
                                        }
                                        aria-label="Mover para baixo"
                                    >
                                        <ArrowDown />
                                    </Button>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="icon"
                                        onClick={() =>
                                            form.setData(
                                                'fields',
                                                form.data.fields.filter(
                                                    (_, idx) => idx !== i,
                                                ),
                                            )
                                        }
                                        aria-label="Remover campo"
                                    >
                                        <Trash2 />
                                    </Button>
                                </div>
                            </div>
                        </div>
                    ))}

                    <InputError message={errors.fields} />

                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        disabled={form.data.fields.length >= limits.fields}
                        onClick={() =>
                            form.setData('fields', [
                                ...form.data.fields,
                                emptyField(),
                            ])
                        }
                    >
                        <Plus />
                        Adicionar campo
                    </Button>

                    {isEditing && (
                        <p className="text-xs text-muted-foreground">
                            Renomear um campo mantém os dados já preenchidos. Ao
                            remover um campo, os equipamentos já cadastrados
                            guardam o valor antigo apenas para consulta.
                        </p>
                    )}
                </section>

                <Separator />

                {/* ------------------------------------------------ checklist */}
                <section className="space-y-4">
                    <div>
                        <h3 className="font-medium">Checklist de entrada</h3>
                        <p className="text-sm text-muted-foreground">
                            Itens conferidos quando o equipamento chega: OK, com
                            defeito ou não testado.
                        </p>
                    </div>

                    {form.data.checklist.map((item, i) => (
                        <div key={item._uid} className="grid gap-1">
                            <div className="flex items-center gap-1">
                                <Input
                                    value={item.label}
                                    onChange={(e) =>
                                        updateItem(i, e.target.value)
                                    }
                                    placeholder="Ex.: Tela"
                                    aria-label={`Item ${i + 1} do checklist`}
                                    maxLength={60}
                                />
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="icon"
                                    disabled={i === 0}
                                    onClick={() =>
                                        form.setData(
                                            'checklist',
                                            move(form.data.checklist, i, i - 1),
                                        )
                                    }
                                    aria-label="Mover para cima"
                                >
                                    <ArrowUp />
                                </Button>
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="icon"
                                    disabled={
                                        i === form.data.checklist.length - 1
                                    }
                                    onClick={() =>
                                        form.setData(
                                            'checklist',
                                            move(form.data.checklist, i, i + 1),
                                        )
                                    }
                                    aria-label="Mover para baixo"
                                >
                                    <ArrowDown />
                                </Button>
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="icon"
                                    onClick={() =>
                                        form.setData(
                                            'checklist',
                                            form.data.checklist.filter(
                                                (_, idx) => idx !== i,
                                            ),
                                        )
                                    }
                                    aria-label="Remover item"
                                >
                                    <Trash2 />
                                </Button>
                            </div>
                            <InputError
                                message={errors[`checklist.${i}.label`]}
                            />
                        </div>
                    ))}

                    <InputError message={errors.checklist} />

                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        disabled={
                            form.data.checklist.length >= limits.checklist
                        }
                        onClick={() =>
                            form.setData('checklist', [
                                ...form.data.checklist,
                                emptyItem(),
                            ])
                        }
                    >
                        <Plus />
                        Adicionar item
                    </Button>
                </section>

                <Separator />

                {/* ------------------------------------------------ preview */}
                <section className="space-y-4 rounded-lg bg-muted/40 p-4">
                    <div>
                        <h3 className="font-medium">Pré-visualização</h3>
                        <p className="text-sm text-muted-foreground">
                            Como o cadastro de um equipamento deste tipo vai
                            ficar. Nada aqui é salvo.
                        </p>
                    </div>

                    <div className="grid gap-4 sm:grid-cols-2">
                        <div className="grid gap-2">
                            <Label>Marca</Label>
                            <Input
                                readOnly
                                tabIndex={-1}
                                placeholder="Ex.: Samsung"
                            />
                        </div>
                        <div className="grid gap-2">
                            <Label>Modelo</Label>
                            <Input
                                readOnly
                                tabIndex={-1}
                                placeholder="Ex.: Galaxy A54"
                            />
                        </div>
                        <div className="grid gap-2 sm:col-span-2">
                            <Label>
                                {form.data.identifier_label || 'Identificador'}
                            </Label>
                            <Input
                                readOnly
                                tabIndex={-1}
                                placeholder="Identificador do equipamento"
                            />
                        </div>
                    </div>

                    <DynamicFields
                        fields={form.data.fields}
                        values={previewValues}
                        onChange={(key, value) =>
                            setPreviewValues((v) => ({ ...v, [key]: value }))
                        }
                        idPrefix="preview"
                    />

                    <ChecklistInput
                        items={form.data.checklist}
                        answers={previewAnswers}
                        onChange={(key, answer) =>
                            setPreviewAnswers((a) => ({ ...a, [key]: answer }))
                        }
                    />
                </section>

                <div className="flex items-center gap-3">
                    <Button
                        type="submit"
                        disabled={form.processing}
                        data-test="save-equipment-type"
                    >
                        {form.processing && <Spinner />}
                        {isEditing ? 'Salvar alterações' : 'Criar tipo'}
                    </Button>
                    <Button variant="ghost" asChild>
                        <Link href={index()}>Cancelar</Link>
                    </Button>
                </div>
            </form>
        </>
    );
}

EquipmentTypeFormPage.layout = (props: Props) => ({
    breadcrumbs: [
        { title: 'Tipos de equipamento', href: index() },
        {
            title: props.equipmentType ? props.equipmentType.name : 'Novo tipo',
            href: '#',
        },
    ],
});
