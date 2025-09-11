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
        Schema::create('open_tofu_configs', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('scenario');
            $table->string('provider');
            $table->json('variables')->nullable();
            $table->string('status')->default('draft');
            $table->text('description')->nullable();
            $table->string('region')->nullable();
            $table->integer('instance_count')->default(1);
            $table->json('tags')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('open_tofu_configs');
    }
};
