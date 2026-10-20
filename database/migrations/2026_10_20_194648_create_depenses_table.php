<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('depenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pressing_id')->constrained('pressings')->onDelete('cascade');
            $table->foreignId('categorie_depense_id')->constrained('categories_depenses')->onDelete('restrict');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('achat_id')->nullable()->constrained('achats')->onDelete('set null');
            $table->string('libelle', 150);
            $table->decimal('montant', 12, 0);
            $table->dateTime('date_depense');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('depenses');
    }
};