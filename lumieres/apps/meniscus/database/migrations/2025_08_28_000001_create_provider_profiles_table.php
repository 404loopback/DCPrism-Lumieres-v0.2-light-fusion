<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('provider_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index(); // ex: "Vultr-Production", "AWS-EU-Dev"
            $table->string('provider')->index(); // vultr, aws, gcp, local
            $table->json('credentials'); // Clés API chiffrées
            $table->json('default_config')->nullable(); // Configuration par défaut (region, etc.)
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false)->index();
            $table->timestamps();
            
            // Index composite pour recherche rapide
            $table->index(['provider', 'is_active']);
            $table->index(['is_default', 'provider']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('provider_profiles');
    }
};
