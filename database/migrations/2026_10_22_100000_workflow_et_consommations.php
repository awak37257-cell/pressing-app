<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Précision des quantités (petites doses : 0,05 L) et des prix au litre
        Schema::table('produits', function (Blueprint $table) {
            $table->decimal('stock_actuel', 12, 3)->default(0)->change();
            $table->decimal('seuil_alerte', 12, 3)->default(0)->change();
            $table->decimal('prix_achat_moyen', 12, 2)->default(0)->change();
        });

        Schema::table('mouvements_stock', function (Blueprint $table) {
            $table->decimal('quantite', 12, 3)->change();
            $table->decimal('stock_apres', 12, 3)->nullable()->change();
        });

        Schema::table('lignes_achat', function (Blueprint $table) {
            $table->decimal('quantite', 12, 3)->change();
            $table->decimal('prix_unitaire', 12, 2)->change();
        });

        // 2. Conditionnement (ex. bidon de 12 L)
        Schema::table('produits', function (Blueprint $table) {
            $table->decimal('contenance', 12, 3)->nullable()->after('unite');
            $table->string('libelle_conditionnement', 50)->nullable()->after('contenance');
        });

        // 3. Dépôts : date de validation des produits utilisés
        Schema::table('depots', function (Blueprint $table) {
            $table->timestamp('consommation_validee_at')->nullable()->after('date_retrait_reelle');
        });

        // 4. Quantités standard consommées par service (et éventuellement par type d'article)
        Schema::create('service_produit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->foreignId('type_article_id')->nullable()->constrained('types_articles')->cascadeOnDelete();
            $table->foreignId('produit_id')->constrained('produits')->restrictOnDelete();
            $table->decimal('quantite_par_article', 12, 3);
            $table->timestamps();
            $table->unique(['service_id', 'type_article_id', 'produit_id'], 'uq_service_produit');
        });

        // 5. Produits réellement utilisés pour un dépôt
        Schema::create('depot_consommations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('depot_id')->constrained('depots')->restrictOnDelete();
            $table->foreignId('produit_id')->constrained('produits')->restrictOnDelete();
            $table->foreignId('mouvement_stock_id')->nullable()->constrained('mouvements_stock')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->decimal('quantite', 12, 3);
            $table->decimal('cout_unitaire', 12, 2)->default(0);
            $table->timestamps();
        });

        // 6. Historique des changements de statut
        Schema::create('depot_historique', function (Blueprint $table) {
            $table->id();
            $table->foreignId('depot_id')->constrained('depots')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('statut', 30);
            $table->string('note', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // Reprise de l'existant : une ligne d'historique par dépôt déjà enregistré
        DB::table('depots')->orderBy('id')->select('id', 'user_id', 'statut', 'date_depot')->chunkById(200, function ($depots) {
            DB::table('depot_historique')->insert(
                $depots->map(fn ($d) => [
                    'depot_id'   => $d->id,
                    'user_id'    => $d->user_id,
                    'statut'     => $d->statut,
                    'note'       => 'Situation à la mise à jour du logiciel',
                    'created_at' => $d->date_depot ?? now(),
                ])->all()
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('depot_historique');
        Schema::dropIfExists('depot_consommations');
        Schema::dropIfExists('service_produit');

        Schema::table('depots', fn (Blueprint $table) => $table->dropColumn('consommation_validee_at'));
        Schema::table('produits', fn (Blueprint $table) => $table->dropColumn(['contenance', 'libelle_conditionnement']));
    }
};
