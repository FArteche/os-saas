export type FieldType =
    | 'text'
    | 'textarea'
    | 'number'
    | 'select'
    | 'boolean'
    | 'date'
    | 'secret';

/** A custom field of an equipment type (mirrors EquipmentType::FieldDefinition in PHP). */
export type FieldDefinition = {
    key: string | null; // null while the field is new; the server generates it
    label: string;
    type: FieldType;
    required: boolean;
    options: string[] | null;
    help: string | null;
};

export type ChecklistItem = {
    key: string | null;
    label: string;
};

/** Values typed into dynamic fields, by field key. */
export type FieldValues = Record<string, string | boolean | null>;

export type ChecklistStatus = 'ok' | 'defect' | 'untested';

/** How one checklist item was answered when the equipment arrived. */
export type ChecklistAnswer = {
    status: ChecklistStatus | null;
    note: string;
};

export type ChecklistAnswers = Record<string, ChecklistAnswer>;

export type EquipmentTypeSummary = {
    id: number;
    name: string;
    icon: string;
    identifierLabel: string;
    fieldsCount: number;
    checklistCount: number;
    isActive: boolean;
};

export type EquipmentTypeForm = {
    id?: number;
    name: string;
    icon: string;
    identifier_label: string;
    fields: FieldDefinition[];
    checklist: ChecklistItem[];
};
