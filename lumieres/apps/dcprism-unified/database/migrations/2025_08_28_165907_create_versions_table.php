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
        Schema::create('versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movie_id')->constrained('movies')->onDelete('cascade');
            $table->enum('type', ['VO', 'VOST', 'DUB', 'VOSTF', 'VF']); // Type de version
            $table->string('audio_lang', 3); // Code ISO langue audio (référence vers langs.iso_639_1)
            $table->string('sub_lang', 3)->nullable(); // Code ISO langue sous-titres
            $table->string('accessibility')->nullable(); // Malentendants, malvoyants, etc.
            $table->unsignedBigInteger('ov_id')->nullable(); // Référence vers la version originale si c'est un doublage
            $table->json('vf_ids')->nullable(); // IDs des versions doublées dérivées de cette VO
            $table->string('generated_nomenclature')->nullable(); // Nomenclature générée automatiquement
            $table->enum('format', ['FTR', 'SHR', 'EPS', 'TST', 'TRL', 'RTG', 'POL', 'PSA', 'ADV'])->default('FTR');
            $table->timestamps();

            $table->index(['movie_id']);
            $table->index(['type', 'audio_lang']);
            $table->index(['ov_id']);
            $table->index(['generated_nomenclature']);

            $table->foreign('ov_id')->references('id')->on('versions')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('versions');
    }
};
