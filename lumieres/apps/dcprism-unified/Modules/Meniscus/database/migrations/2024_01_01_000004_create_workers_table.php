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
        Schema::create('meniscus_workers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['auto', 'manual', 'dedicated'])->default('auto');
            $table->enum('status', ['registering', 'idle', 'busy', 'offline', 'error', 'terminating'])->default('registering');
            $table->string('ip_address', 45);
            $table->string('hostname')->nullable();
            $table->string('version')->nullable();
            $table->json('capabilities')->nullable();
            $table->integer('cpu_cores')->nullable();
            $table->bigInteger('memory_total')->nullable(); // bytes
            $table->bigInteger('memory_available')->nullable(); // bytes
            $table->bigInteger('storage_total')->nullable(); // bytes
            $table->bigInteger('storage_available')->nullable(); // bytes
            $table->unsignedBigInteger('current_job_id')->nullable();
            $table->integer('jobs_completed')->default(0);
            $table->integer('jobs_failed')->default(0);
            $table->timestamp('last_heartbeat')->nullable();
            $table->timestamp('registered_at');
            $table->timestamp('terminated_at')->nullable();
            $table->json('metadata')->nullable();
            $table->string('api_token', 64)->unique();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['status', 'type']);
            $table->index(['last_heartbeat']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meniscus_workers');
    }
};
