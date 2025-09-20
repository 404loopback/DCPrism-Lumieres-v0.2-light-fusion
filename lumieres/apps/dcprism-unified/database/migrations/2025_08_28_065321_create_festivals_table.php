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
        Schema::create('festivals', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('subdomain')->unique();
            $table->text('description')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('contact_phone')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamp('submission_deadline')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_public')->default(false);
            $table->boolean('accept_submissions')->default(true);
            $table->string('nomenclature_separator', 5)->default('_');
            $table->text('nomenclature_template')->nullable();
            $table->json('technical_requirements')->nullable();
            $table->json('accepted_formats')->nullable();
            $table->bigInteger('max_storage')->nullable(); // en bytes
            $table->bigInteger('max_file_size')->nullable(); // en bytes
            $table->string('backblaze_folder')->nullable();
            $table->enum('storage_status', ['active', 'full', 'error', 'maintenance'])->default('active');
            $table->json('storage_info')->nullable();
            $table->timestamp('storage_last_tested_at')->nullable();
            $table->timestamps();

            $table->index(['is_active']);
            $table->index(['start_date', 'end_date']);
            $table->index(['submission_deadline']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('festivals');
    }
};
