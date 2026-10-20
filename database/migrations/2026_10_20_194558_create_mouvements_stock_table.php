<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mouvements_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pressing_id')->constrained('pressings')->onDelete('cascade');
            $table->foreignId('produit_id')->constrained('produits')->onDelete('restrict');
            $table->enum('type', ['entree', 'sortie', 'ajustement', 'perte']);
            $table->decimal('quantite', 12, 2);
            $table->decimal('stock_apres', 12, 2)->nullable();
            $table->text('motif')->nullable();
            $table->foreignId('achat_id')->nullable()->constrained('achats')->onDelete('set null');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->dateTime('date_mouvement');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['produit_id', 'date_mouvement'], 'idx_mvt_produit');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mouvements_stock');
    }
};