<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('legal_name')->nullable();
            $table->string('document', 14)->nullable(); // CPF (11) ou CNPJ (14, pode ser alfanumérico), sem máscara
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();

            $table->string('zip_code', 8)->nullable(); // somente dígitos
            $table->string('street')->nullable();
            $table->string('number', 20)->nullable();
            $table->string('complement')->nullable();
            $table->string('district')->nullable();
            $table->string('city')->nullable();
            $table->char('state', 2)->nullable();

            $table->string('niche', 50)->default('generic');
            $table->jsonb('settings')->default('{}');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
