<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('categorie_produit_fournisseur')) {
            Schema::create('categorie_produit_fournisseur', function (Blueprint $table) {
                $table->foreignId('fournisseur_id')->constrained('fournisseurs')->cascadeOnDelete();
                $table->foreignId('categorie_produit_id')->constrained('categories_produits')->cascadeOnDelete();

                $table->unique(
                    ['fournisseur_id', 'categorie_produit_id'],
                    'cpf_fournisseur_categorie_unique'
                );
            });
        } elseif (! Schema::hasIndex(
            'categorie_produit_fournisseur',
            ['fournisseur_id', 'categorie_produit_id'],
            'unique'
        )) {
            Schema::table('categorie_produit_fournisseur', function (Blueprint $table) {
                $table->unique(
                    ['fournisseur_id', 'categorie_produit_id'],
                    'cpf_fournisseur_categorie_unique'
                );
            });
        }

        DB::table('categorie_produit_fournisseur')->insertUsing(
            ['fournisseur_id', 'categorie_produit_id'],
            DB::table('achats')
                ->join('lignes_achat', 'achats.id', '=', 'lignes_achat.achat_id')
                ->join('produits', 'lignes_achat.produit_id', '=', 'produits.id')
                ->select('achats.fournisseur_id', 'produits.categorie_produit_id')
                ->distinct()
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('categorie_produit_fournisseur');
    }
};
