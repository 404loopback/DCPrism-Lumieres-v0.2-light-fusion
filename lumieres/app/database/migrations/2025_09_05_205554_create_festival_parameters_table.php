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
        Schema::create('festival_parameters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('festival_id')->constrained('festivals')->onDelete('cascade');
            $table->foreignId('parameter_id')->constrained('parameters')->onDelete('cascade');
            $table->boolean('is_enabled')->default(true)->comment('Le paramètre est-il activé pour ce festival');
            // Colonnes pour la nouvelle logique système
            $table->boolean('is_required')->default(false)->comment('Paramètre requis pour la logique métier future');
            $table->boolean('is_visible_in_nomenclature')->default(true)->comment('Visible dans la nomenclature (actif/inactif)');
            $table->boolean('is_system')->default(false)->comment('Paramètre système (auto-ajouté, non supprimable)');
            // Autres colonnes
            $table->text('custom_default_value')->nullable()->comment('Valeur par défaut personnalisée pour ce festival');
            $table->json('custom_formatting_rules')->nullable()->comment('Règles de formatage spécifiques au festival');
            $table->integer('display_order')->nullable()->comment('Ordre d\'affichage spécifique au festival');
            $table->text('festival_specific_notes')->nullable()->comment('Notes spécifiques au festival pour ce paramètre');
            $table->timestamps();

            // Clé unique pour éviter les doublons festival/paramètre
            $table->unique(['festival_id', 'parameter_id']);

            // Index pour les requêtes fréquentes
            $table->index(['festival_id', 'is_enabled']);
            $table->index(['parameter_id']);
            // Index pour les nouvelles colonnes système
            $table->index(['festival_id', 'is_system']);
            $table->index(['festival_id', 'is_visible_in_nomenclature']);
            $table->index(['is_system', 'is_enabled']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('festival_parameters');
    }
};
