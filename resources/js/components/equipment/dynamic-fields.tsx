import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';
import type { FieldDefinition, FieldValues } from '@/types';

type Props = {
    fields: FieldDefinition[];
    values: FieldValues;
    onChange: (key: string, value: string | boolean | null) => void;
    /** Validation errors keyed by field key. */
    errors?: Record<string, string | undefined>;
    disabled?: boolean;
    /** Prefix for input ids, so two forms on one page don't clash. */
    idPrefix?: string;
};

/**
 * Renders the inputs of an equipment type's custom fields from its definition.
 * Controlled: values live in the parent (e.g. a useForm) and changes go up through onChange.
 */
export function DynamicFields({
    fields,
    values,
    onChange,
    errors = {},
    disabled = false,
    idPrefix = 'field',
}: Props) {
    if (fields.length === 0) {
        return (
            <p className="text-sm text-muted-foreground">
                Este tipo não tem campos extras.
            </p>
        );
    }

    return (
        <div className="grid gap-4">
            {fields.map((field, index) => {
                // New fields (still without a server key) are identified by position.
                const key = field.key ?? `novo_${index}`;
                const id = `${idPrefix}-${key}`;
                const value = values[key] ?? null;

                return (
                    <div key={key} className="grid gap-2">
                        <Label htmlFor={id}>
                            {field.label || 'Campo sem nome'}
                            {field.required && (
                                <span className="text-destructive"> *</span>
                            )}
                        </Label>

                        <FieldInput
                            id={id}
                            field={field}
                            value={value}
                            disabled={disabled}
                            onChange={(newValue) => onChange(key, newValue)}
                        />

                        {field.help && (
                            <p className="text-xs text-muted-foreground">
                                {field.help}
                            </p>
                        )}
                        <InputError message={errors[key]} />
                    </div>
                );
            })}
        </div>
    );
}

function FieldInput({
    id,
    field,
    value,
    disabled,
    onChange,
}: {
    id: string;
    field: FieldDefinition;
    value: string | boolean | null;
    disabled: boolean;
    onChange: (value: string | boolean | null) => void;
}) {
    const text = typeof value === 'string' ? value : '';

    switch (field.type) {
        case 'textarea':
            return (
                <Textarea
                    id={id}
                    value={text}
                    disabled={disabled}
                    onChange={(e) => onChange(e.target.value)}
                />
            );

        case 'number':
            return (
                <Input
                    id={id}
                    type="number"
                    inputMode="decimal"
                    value={text}
                    disabled={disabled}
                    onChange={(e) => onChange(e.target.value)}
                />
            );

        case 'date':
            return (
                <Input
                    id={id}
                    type="date"
                    value={text}
                    disabled={disabled}
                    onChange={(e) => onChange(e.target.value)}
                />
            );

        case 'secret':
            // Masked by default; the eye button reveals it. Stored encrypted on the server.
            return (
                <PasswordInput
                    id={id}
                    value={text}
                    disabled={disabled}
                    autoComplete="off"
                    onChange={(e) => onChange(e.target.value)}
                />
            );

        case 'select':
            return (
                <Select
                    value={text || undefined}
                    disabled={disabled}
                    onValueChange={(selected) => onChange(selected)}
                >
                    <SelectTrigger id={id} className="w-full">
                        <SelectValue placeholder="Selecione" />
                    </SelectTrigger>
                    <SelectContent>
                        {(field.options ?? [])
                            .filter((option) => option !== '')
                            .map((option) => (
                                <SelectItem key={option} value={option}>
                                    {option}
                                </SelectItem>
                            ))}
                    </SelectContent>
                </Select>
            );

        case 'boolean':
            return (
                <ToggleGroup
                    id={id}
                    type="single"
                    variant="outline"
                    className="w-fit"
                    disabled={disabled}
                    value={value === true ? 'yes' : value === false ? 'no' : ''}
                    onValueChange={(selected) =>
                        onChange(selected === '' ? null : selected === 'yes')
                    }
                >
                    <ToggleGroupItem value="yes" className="px-4">
                        Sim
                    </ToggleGroupItem>
                    <ToggleGroupItem value="no" className="px-4">
                        Não
                    </ToggleGroupItem>
                </ToggleGroup>
            );

        default:
            return (
                <Input
                    id={id}
                    value={text}
                    disabled={disabled}
                    onChange={(e) => onChange(e.target.value)}
                />
            );
    }
}
