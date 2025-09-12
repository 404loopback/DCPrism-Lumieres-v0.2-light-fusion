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
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('worker_id')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('source_file_path');
            $table->bigInteger('source_file_size')->nullable();
            $table->string('source_file_hash', 64)->nullable();
            $table->string('output_path')->nullable();
            $table->enum('status', ['pending', 'queued', 'processing', 'completed', 'failed', 'cancelled', 'retrying'])->default('pending');
            $table->tinyInteger('priority')->default(2); // 1=low, 2=normal, 3=high, 4=urgent
            $table->tinyInteger('progress')->default(0);
            $table->json('settings')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('estimated_duration')->nullable(); // seconds
            $table->integer('actual_duration')->nullable(); // seconds
            $table->tinyInteger('retry_count')->default(0);
            $table->tinyInteger('max_retries')->default(3);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['status', 'priority']);
            $table->index(['user_id', 'status']);
            $table->index(['worker_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};
