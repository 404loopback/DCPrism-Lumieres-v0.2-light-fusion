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
        Schema::table('langs', function (Blueprint $table) {
            $table->string('french_name')->nullable()->after('local_name')
                ->comment('Nom de la langue en français');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('langs', function (Blueprint $table) {
            $table->dropColumn('french_name');
        });
    }
};
