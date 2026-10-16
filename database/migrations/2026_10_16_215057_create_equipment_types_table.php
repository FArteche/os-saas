<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('equipment_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('icon', 30)->default('wrench');
            $table->string('identifier_label', 50);
            $table->jsonb('fields')->default('[]');    // list of field definitions
            $table->jsonb('checklist')->default('[]'); // list of checklist items
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();

            $table->index(['company_id', 'is_active', 'position']);
        });

        // "Celular" and "celular" are the same type: unique per company, ignoring case.
        DB::statement('CREATE UNIQUE INDEX equipment_types_company_name_unique ON equipment_types (company_id, lower(name))');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_types');
    }
};
