<?php

namespace App\Http\Controllers\Settings;

use App\Actions\EquipmentTypes\SaveEquipmentType;
use App\Enums\EquipmentIcon;
use App\Enums\FieldType;
use App\Http\Controllers\Controller;
use App\Http\Requests\SaveEquipmentTypeRequest;
use App\Models\EquipmentType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

/**
 * @phpstan-import-type TypeInput from SaveEquipmentType
 */
class EquipmentTypeController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('viewAny', EquipmentType::class);

        return Inertia::render('settings/equipment-types/index', [
            'equipmentTypes' => EquipmentType::ordered()->get()->map(fn (EquipmentType $type) => [
                'id' => $type->id,
                'name' => $type->name,
                'icon' => $type->icon->value,
                'identifierLabel' => $type->identifier_label,
                'fieldsCount' => count($type->fields),
                'checklistCount' => count($type->checklist),
                'isActive' => $type->is_active,
            ]),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', EquipmentType::class);

        return $this->form(null);
    }

    public function store(SaveEquipmentTypeRequest $request, SaveEquipmentType $save): RedirectResponse
    {
        Gate::authorize('create', EquipmentType::class);

        /** @var TypeInput $data */
        $data = $request->validated();
        $type = $save->handle(null, $data);

        Inertia::flash('toast', ['type' => 'success', 'message' => "Tipo \"{$type->name}\" criado."]);

        return to_route('equipment-types.index');
    }

    public function edit(EquipmentType $equipmentType): Response
    {
        Gate::authorize('update', $equipmentType);

        return $this->form($equipmentType);
    }

    public function update(SaveEquipmentTypeRequest $request, EquipmentType $equipmentType, SaveEquipmentType $save): RedirectResponse
    {
        Gate::authorize('update', $equipmentType);

        /** @var TypeInput $data */
        $data = $request->validated();
        $save->handle($equipmentType, $data);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Alterações salvas.']);

        return to_route('equipment-types.index');
    }

    /**
     * Types are never deleted (equipment keeps pointing to them); they are deactivated.
     */
    public function toggle(EquipmentType $equipmentType): RedirectResponse
    {
        Gate::authorize('update', $equipmentType);

        // Without any active type, no equipment could be registered at all.
        if ($equipmentType->is_active && ! EquipmentType::active()->whereKeyNot($equipmentType->id)->exists()) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Mantenha pelo menos um tipo de equipamento ativo.',
            ]);

            return back();
        }

        $equipmentType->update(['is_active' => ! $equipmentType->is_active]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $equipmentType->is_active ? 'Tipo reativado.' : 'Tipo desativado.',
        ]);

        return back();
    }

    private function form(?EquipmentType $type): Response
    {
        return Inertia::render('settings/equipment-types/form', [
            'equipmentType' => $type ? [
                'id' => $type->id,
                'name' => $type->name,
                'icon' => $type->icon->value,
                'identifier_label' => $type->identifier_label,
                'fields' => $type->fields,
                'checklist' => $type->checklist,
            ] : null,
            'fieldTypes' => FieldType::options(),
            'icons' => EquipmentIcon::options(),
            'limits' => [
                'fields' => SaveEquipmentTypeRequest::MAX_FIELDS,
                'checklist' => SaveEquipmentTypeRequest::MAX_CHECKLIST_ITEMS,
            ],
        ]);
    }
}
