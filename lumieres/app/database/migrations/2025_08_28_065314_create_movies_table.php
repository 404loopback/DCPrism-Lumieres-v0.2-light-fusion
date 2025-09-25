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
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('source_email');
            $table->string('status', 50)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->text('description')->nullable();
            $table->integer('duration')->nullable(); // en minutes
            $table->string('genre')->nullable();
            $table->integer('year')->nullable();
            $table->string('country')->nullable();
            $table->string('language')->nullable();
            $table->string('backblaze_folder')->nullable();
            $table->string('backblaze_file_id')->nullable();
            $table->integer('upload_progress')->default(0);
            $table->json('DCP_metadata')->nullable();
            $table->text('technical_notes')->nullable();
            $table->unsignedBigInteger('validated_by')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->string('file_path')->nullable();
            $table->bigInteger('file_size')->nullable();
            $table->string('original_filename')->nullable();
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamps();

            $table->index(['status']);
            $table->index(['source_email']);
            $table->index(['created_at']);
            $table->index(['validated_by']);

            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('validated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};
