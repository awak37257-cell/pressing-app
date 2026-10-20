<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pressing_id')->constrained('pressings')->onDelete('cascade');
            $table->foreignId('categorie_produit_id')->constrained('categories_produits')->onDelete('restrict');
            $table->string('nom', 100);
            $table->string('unite', 20);
            $table->decimal('stock_actuel', 12, 2)->default(0);
            $table->decimal('seuil_alerte', 12, 2)->default(0);
            $table->decimal('prix_achat_moyen', 12, 0)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['pressing_id', 'nom'], 'uq_produit');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produits');
    }
};