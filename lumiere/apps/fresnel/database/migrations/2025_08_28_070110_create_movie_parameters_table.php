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
        Schema::create('movie_parameters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movie_id')->constrained()->onDelete('cascade');
            $table->foreignId('parameter_id')->constrained()->onDelete('cascade');
            $table->text('value')->nullable(); // Valeur du paramètre pour ce film
            $table->enum('status', ['pending', 'extracted', 'validated', 'error'])->default('pending');
            $table->enum('extraction_method', ['manual', 'auto', 'computed'])->default('manual');
            $table->json('metadata')->nullable(); // Métadonnées additionnelles
            $table->timestamps();
            
            // Index pour optimiser les requêtes
            $table->unique(['movie_id', 'parameter_id']);
            $table->index(['status']);
            $table->index(['extraction_method']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movie_parameters');
    }
};
