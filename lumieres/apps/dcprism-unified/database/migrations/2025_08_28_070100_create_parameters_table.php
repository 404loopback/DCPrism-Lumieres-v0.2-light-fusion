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
        Schema::create('parameters', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('code', 50)->unique();
            $table->enum('type', ['string', 'int', 'bool', 'float', 'date', 'json'])->default('string');
            $table->json('possible_values')->nullable(); // Valeurs autorisées
            $table->text('description')->nullable();
            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false);
            $table->enum('extraction_source', ['DCP', 'metadata', 'manual', 'auto'])->nullable();
            $table->string('extraction_pattern')->nullable(); // Pattern pour extraction auto
            $table->json('validation_rules')->nullable(); // Règles de validation
            $table->text('default_value')->nullable();
            $table->enum('category', ['video', 'audio', 'accessibility', 'format', 'technical', 'metadata', 'management', 'content'])->default('technical');
            $table->string('dcp_specification')->nullable()->comment('Spécification DCP (Interop/SMPTE) pour ce paramètre');
            $table->json('standard_compatibility')->nullable()->comment('Compatibilité avec les standards (Interop, SMPTE, etc.)');
            $table->json('technical_range')->nullable()->comment('Plage technique (min/max, unités, etc.)');
            $table->timestamps();
            
            $table->index(['is_active']);
            $table->index(['category']);
            $table->index(['is_system', 'is_active']);
            $table->index(['is_active'], 'parameters_global_active_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parameters');
    }
};
