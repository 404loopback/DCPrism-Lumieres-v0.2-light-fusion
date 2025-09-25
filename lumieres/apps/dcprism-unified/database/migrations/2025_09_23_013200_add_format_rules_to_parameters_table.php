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
        Schema::table('parameters', function (Blueprint $table) {
            $table->string('format_rules')->nullable()->after('default_value')
                  ->comment('Règles de formatage: no_spacing,caps_lock,camel_case,etc');
        });
        
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_partner')->default(false)->after('is_active')
                  ->comment('Partenaire permanent - compte non désactivé sans festival');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parameters', function (Blueprint $table) {
            $table->dropColumn('format_rules');
        });
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_partner');
        });
    }
};
