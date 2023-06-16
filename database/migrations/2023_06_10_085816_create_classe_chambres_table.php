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
        Schema::create('classe_chambres', function (Blueprint $table) {
            $table->id();
            $table->string('nom')->unique()->nullable(false);
            $table->string('description')->unique()->nullable(false);
            $table->float('prix')->unique()->nullable(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classe_chambres');
    }
};
