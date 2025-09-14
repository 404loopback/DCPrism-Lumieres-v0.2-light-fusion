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
        Schema::table('nomenclatures', function (Blueprint $table) {
            $table->foreignId('festival_parameter_id')->nullable()->constrained('festival_parameters')->onDelete('cascade');
            $table->index(['festival_parameter_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nomenclatures', function (Blueprint $table) {
            $table->dropForeign(['festival_parameter_id']);
            $table->dropColumn('festival_parameter_id');
        });
    }
};
