<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Supprime la colonne 'role' legacy car nous utilisons maintenant
     * exclusivement Shield/Spatie avec le trait HasRoles.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Supprimer la colonne role legacy
            $table->dropColumn('role');
        });
    }

    /**
     * Reverse the migrations.
     *
     * Recrée la colonne role en cas de rollback.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Recréer la colonne role (nullable pour éviter les erreurs)
            $table->string('role')->nullable()->after('password');
        });
    }
};
