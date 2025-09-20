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
        Schema::create('dcp_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->enum('status', ['pending', 'running', 'completed', 'failed', 'cancelled']);
            $table->json('payload')->nullable();
            $table->integer('progress')->default(0);
            $table->json('result')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->integer('estimated_duration')->nullable(); // en secondes
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->index(['status']);
            $table->index(['type']);
            $table->index(['user_id']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dcp_jobs');
    }
};
