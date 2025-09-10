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
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->integer('progress')->default(0);
            
            // File information
            $table->string('source_file_path')->nullable();
            $table->string('source_file_size')->nullable();
            $table->string('output_path')->nullable();
            $table->enum('format', ['2K', '4K', 'HD'])->default('2K');
            
            // Processing info
            $table->unsignedBigInteger('worker_id')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('estimated_completion')->nullable();
            
            $table->timestamps();
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
