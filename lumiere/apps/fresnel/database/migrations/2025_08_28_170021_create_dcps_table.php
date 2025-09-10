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
        Schema::create('dcps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movie_id')->constrained('movies')->onDelete('cascade');
            $table->foreignId('version_id')->nullable()->constrained('versions')->onDelete('cascade');
            $table->boolean('is_ov')->default(false); // Est-ce la version originale ?
            $table->string('backblaze_file_id'); // ID du fichier sur Backblaze B2
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->boolean('is_valid')->nullable()->default(null); // DCP techniquement valide (null = en attente)
            $table->timestamp('validated_at')->nullable();
            $table->timestamp('uploaded_at'); // Date d'upload effective
            $table->string('audio_lang', 3); // Langue audio du DCP
            $table->string('subtitle_lang', 3); // Langue des sous-titres
            $table->string('file_path')->nullable(); // Chemin local si stocké localement
            $table->bigInteger('file_size')->nullable(); // Taille en bytes
            $table->json('technical_metadata')->nullable(); // Métadonnées techniques (résolution, format, etc.)
            $table->text('validation_notes')->nullable(); // Notes de validation
            $table->enum('status', ['uploaded', 'processing', 'valid', 'invalid', 'error'])->default('uploaded');
            $table->timestamps();
            
            $table->index(['movie_id']);
            $table->index(['version_id']);
            $table->index(['uploaded_by']);
            $table->index(['is_valid']);
            $table->index(['uploaded_at']);
            $table->index(['status']);
            $table->index(['is_valid', 'uploaded_at']);
            $table->index(['audio_lang', 'subtitle_lang']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dcps');
    }
};
