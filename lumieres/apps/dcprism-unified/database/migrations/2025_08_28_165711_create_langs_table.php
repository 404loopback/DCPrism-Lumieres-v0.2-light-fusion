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
        Schema::create('langs', function (Blueprint $table) {
            $table->id();
            $table->string('iso_639_1', 2)->unique(); // Code ISO 2 lettres (ex: fr, en)
            $table->string('iso_639_3', 3)->nullable(); // Code ISO 3 lettres (ex: fra, eng)
            $table->string('name'); // Nom en anglais
            $table->string('local_name')->nullable(); // Nom dans la langue locale
            $table->timestamps();
            
            $table->index(['iso_639_1']);
            $table->index(['name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('langs');
    }
};
