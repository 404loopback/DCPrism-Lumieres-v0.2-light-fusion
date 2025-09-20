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

            // Basic deployment info
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('scenario', [
                'backend-automation',
                'manual-testing',
            ])->default('backend-automation');
            $table->enum('environment', [
                'development',
                'staging',
                'production',
            ])->default('development');
            $table->string('project_name')->default('dcparty');

            // Status and lifecycle
            $table->enum('status', [
                'draft',
                'planning',
                'deploying',
                'deployed',
                'failed',
                'destroying',
                'destroyed',
            ])->default('draft');

            // Terraform data (JSON columns)
            $table->json('terraform_config')->nullable();
            $table->json('terraform_state')->nullable();
            $table->json('terraform_outputs')->nullable();

            // Provider configuration
            $table->json('provider_config')->nullable();

            // Logs and monitoring
            $table->json('deployment_logs')->nullable();
            $table->json('resource_details')->nullable();

            // Cost estimation
            $table->decimal('estimated_cost', 10, 2)->nullable();

            // Timestamps
            $table->timestamp('deployed_at')->nullable();
            $table->timestamp('destroyed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['scenario', 'environment']);
            $table->index('status');
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
