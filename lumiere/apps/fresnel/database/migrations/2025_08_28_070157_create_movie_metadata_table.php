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
        Schema::create('movie_metadata', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('movie_id');
            $table->string('metadata_key', 100); // Technical parameter name
            $table->text('metadata_value'); // Parameter value (can be long)
            $table->string('data_type', 20)->default('string'); // string, number, boolean, date, file
            $table->string('source', 50)->nullable(); // dcp_analyzer, manual, import, etc.
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_critical')->default(false); // Critical for validation
            $table->json('validation_rules')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('extracted_at')->nullable();
            $table->timestamps();
            
            $table->unique(['movie_id', 'metadata_key']);
            $table->index(['metadata_key']);
            $table->index(['data_type']);
            $table->index(['source']);
            $table->index(['is_verified']);
            $table->index(['is_critical']);
            
            $table->foreign('movie_id')->references('id')->on('movies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movie_metadata');
    }
};
