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
        Schema::create('deployment_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->text('description')->nullable();
            $table->string('scenario')->index(); // backend-automation, etc.
            $table->string('provider')->index(); // aws, vultr, gcp, local
            $table->json('configuration'); // Configuration template
            $table->json('ansible_template')->nullable(); // Ansible template
            $table->json('terraform_template')->nullable(); // Terraform template
            $table->boolean('is_active')->default(true)->index();
            $table->json('tags')->nullable(); // Tags for organization
            $table->json('metadata')->nullable(); // Additional metadata
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            
            // Indexes for performance
            $table->index(['scenario', 'provider']);
            $table->index(['is_active', 'scenario']);
            $table->index(['created_by', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deployment_templates');
    }
};
