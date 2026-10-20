<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories_depenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pressing_id')->constrained('pressings')->onDelete('cascade');
            $table->string('nom', 50);
            $table->timestamps();

            $table->unique(['pressing_id', 'nom'], 'uq_cat_depense');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories_depenses');
    }
};