<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pressings', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 100);
            $table->text('adresse')->nullable();
            $table->string('telephone', 30)->nullable();
            $table->string('logo', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pressings');
    }
};