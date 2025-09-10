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
        Schema::create('validation_results', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('movie_id');
            $table->unsignedBigInteger('parameter_id')->nullable(); // Null for general validations
            $table->string('validation_type', 50); // nomenclature, technical, conformity, etc.
            $table->string('status', 20); // passed, failed, warning, pending
            $table->string('severity', 20)->default('info'); // error, warning, info
            $table->string('rule_name', 100); // Name of the validation rule
            $table->text('description')->nullable(); // Human-readable description
            $table->text('expected_value')->nullable();
            $table->text('actual_value')->nullable();
            $table->json('details')->nullable(); // Additional validation details
            $table->text('suggestion')->nullable(); // How to fix the issue
            $table->boolean('can_auto_fix')->default(false);
            $table->timestamp('validated_at');
            $table->string('validator_version', 20)->nullable();
            $table->timestamps();
            
            $table->index(['movie_id', 'status']);
            $table->index(['validation_type']);
            $table->index(['status']);
            $table->index(['severity']);
            $table->index(['validated_at']);
            
            $table->foreign('movie_id')->references('id')->on('movies')->onDelete('cascade');
            $table->foreign('parameter_id')->references('id')->on('parameters')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('validation_results');
    }
};
