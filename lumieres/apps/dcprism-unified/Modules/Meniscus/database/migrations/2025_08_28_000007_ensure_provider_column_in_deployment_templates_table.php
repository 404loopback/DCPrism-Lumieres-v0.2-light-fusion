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
        Schema::table('deployment_templates', function (Blueprint $table) {
            // Check if the 'provider' column exists, add it if it doesn't
            if (! Schema::hasColumn('deployment_templates', 'provider')) {
                $table->string('provider')->default('unknown')->index()->after('scenario');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deployment_templates', function (Blueprint $table) {
            if (Schema::hasColumn('deployment_templates', 'provider')) {
                $table->dropColumn('provider');
            }
        });
    }
};
