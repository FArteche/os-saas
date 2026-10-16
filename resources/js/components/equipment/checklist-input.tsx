import { Input } from '@/components/ui/input';
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';
import type {
    ChecklistAnswer,
    ChecklistAnswers,
    ChecklistItem,
    ChecklistStatus,
} from '@/types';

const statuses: { value: ChecklistStatus; label: string; className: string }[] =
    [
        {
            value: 'ok',
            label: 'OK',
            className:
                'data-[state=on]:bg-emerald-600 data-[state=on]:text-white',
        },
        {
            value: 'defect',
            label: 'Com defeito',
            className: 'data-[state=on]:bg-red-600 data-[state=on]:text-white',
        },
        {
            value: 'untested',
            label: 'Não testado',
            className:
                'data-[state=on]:bg-muted-foreground data-[state=on]:text-background',
        },
    ];

const emptyAnswer: ChecklistAnswer = { status: null, note: '' };

const notePlaceholders: Record<ChecklistStatus, string> = {
    ok: 'Observação (opcional)',
    defect: 'Descreva o defeito (ex.: tela trincada no canto)',
    untested: 'Por que não foi testado? (ex.: aparelho sem bateria)',
};

type Props = {
    items: ChecklistItem[];
    answers: ChecklistAnswers;
    onChange: (key: string, answer: ChecklistAnswer) => void;
    disabled?: boolean;
};

/**
 * Entry checklist: each item is answered OK / Com defeito / Não testado, with an optional note.
 * Controlled like DynamicFields; reused when opening a service order.
 */
export function ChecklistInput({
    items,
    answers,
    onChange,
    disabled = false,
}: Props) {
    if (items.length === 0) {
        return (
            <p className="text-sm text-muted-foreground">
                Este tipo não tem checklist.
            </p>
        );
    }

    return (
        <ul className="divide-y rounded-lg border">
            {items.map((item, index) => {
                const key = item.key ?? `novo_${index}`;
                const answer = answers[key] ?? emptyAnswer;

                return (
                    <li key={key} className="grid gap-2 p-3">
                        {/* Phones: buttons always under the label (no zig-zag); wider screens: same row. */}
                        <div className="flex flex-col items-start gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <span className="text-sm font-medium">
                                {item.label || 'Item sem nome'}
                            </span>
                            <ToggleGroup
                                type="single"
                                variant="outline"
                                size="sm"
                                disabled={disabled}
                                value={answer.status ?? ''}
                                onValueChange={(status) =>
                                    onChange(key, {
                                        ...answer,
                                        status: (status ||
                                            null) as ChecklistStatus | null,
                                    })
                                }
                                aria-label={`Estado de ${item.label}`}
                            >
                                {statuses.map((status) => (
                                    <ToggleGroupItem
                                        key={status.value}
                                        value={status.value}
                                        className={`px-2 text-xs ${status.className}`}
                                    >
                                        {status.label}
                                    </ToggleGroupItem>
                                ))}
                            </ToggleGroup>
                        </div>

                        {answer.status !== null && (
                            <Input
                                placeholder={notePlaceholders[answer.status]}
                                value={answer.note}
                                disabled={disabled}
                                onChange={(e) =>
                                    onChange(key, {
                                        ...answer,
                                        note: e.target.value,
                                    })
                                }
                            />
                        )}
                    </li>
                );
            })}
        </ul>
    );
}
