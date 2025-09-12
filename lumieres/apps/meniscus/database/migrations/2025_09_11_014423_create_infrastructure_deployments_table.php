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
        Schema::create('infrastructure_deployments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('scenario');
            $table->string('environment');
            $table->string('project_name');
            $table->string('status')->default('draft');
            $table->json('terraform_config')->nullable();
            $table->json('terraform_state')->nullable();
            $table->json('terraform_outputs')->nullable();
            $table->json('provider_config')->nullable();
            $table->json('deployment_logs')->nullable();
            $table->json('resource_details')->nullable();
            $table->decimal('estimated_cost', 10, 2)->nullable();
            $table->timestamp('deployed_at')->nullable();
            $table->timestamp('destroyed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['user_id', 'status']);
            $table->index('environment');
            $table->index('scenario');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('infrastructure_deployments');
    }
};
