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
        Schema::create('nomenclatures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('festival_id');
            $table->unsignedBigInteger('parameter_id');
            $table->integer('order_position'); // Ordre dans la nomenclature
            $table->string('separator', 10)->default('_');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_required')->default(false);
            $table->string('prefix', 50)->nullable();
            $table->string('suffix', 50)->nullable();
            $table->string('default_value')->nullable();
            $table->json('formatting_rules')->nullable(); // Règles formatage complexes
            $table->json('conditional_rules')->nullable(); // Règles conditionnelles
            $table->timestamps();
            
            $table->unique(['festival_id', 'parameter_id']);
            $table->unique(['festival_id', 'order_position']);
            $table->index(['is_active']);
            
            $table->foreign('festival_id')->references('id')->on('festivals')->onDelete('cascade');
            $table->foreign('parameter_id')->references('id')->on('parameters')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nomenclatures');
    }
};
