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
        Schema::create('uploads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('movie_id');
            $table->foreignId('festival_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('original_filename', 255);
            $table->string('file_path', 500); // S3/B2 key path
            $table->string('bucket_name', 100);
            $table->unsignedBigInteger('file_size');
            $table->string('file_type', 50);
            $table->string('mime_type', 100);
            $table->string('status', 20)->default('pending'); // pending, uploading, completed, failed, cancelled
            $table->json('metadata')->nullable(); // Upload metadata, progress info
            $table->string('upload_id')->nullable(); // B2 Large File ID
            $table->string('b2_file_id')->nullable();
            $table->string('b2_file_name')->nullable();
            $table->string('storage_path')->nullable();
            $table->integer('total_parts')->nullable(); // For multipart uploads
            $table->integer('completed_parts')->default(0);
            $table->json('part_sha1_array')->nullable(); // SHA1 hashes of completed parts
            $table->decimal('progress_percentage', 5, 2)->default(0);
            $table->unsignedBigInteger('uploaded_bytes')->default(0);
            $table->decimal('upload_speed_mbps', 8, 2)->nullable(); // MB/s
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('expires_at')->nullable(); // TTL for resumable uploads
            $table->text('error_message')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->index(['movie_id']);
            $table->index(['festival_id']);
            $table->index(['user_id']);
            $table->index(['status']);
            $table->index(['upload_id']);
            $table->index(['b2_file_id']);
            $table->index(['expires_at']);

            $table->foreign('movie_id')->references('id')->on('movies')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uploads');
    }
};
