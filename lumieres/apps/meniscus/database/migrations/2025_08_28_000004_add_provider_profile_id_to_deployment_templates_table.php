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
            // Add provider_profile_id column after scenario
            $table->unsignedBigInteger('provider_profile_id')->nullable()->after('scenario');
            
            // Add foreign key constraint
            $table->foreign('provider_profile_id')
                  ->references('id')
                  ->on('provider_profiles')
                  ->onDelete('set null');
                  
            // Add index for performance
            $table->index('provider_profile_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deployment_templates', function (Blueprint $table) {
            // Drop foreign key and index
            $table->dropForeign(['provider_profile_id']);
            $table->dropIndex(['provider_profile_id']);
            
            // Drop column
            $table->dropColumn('provider_profile_id');
        });
    }
};
