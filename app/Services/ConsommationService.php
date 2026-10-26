<?php

namespace App\Services;

use App\Models\Depot;
use App\Models\DepotConsommation;
use App\Models\Produit;
use App\Models\ServiceProduit;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ConsommationService
{
    public function __construct(private StockService $stock) {}

    /**
     * Produits proposés pour ce dépôt, d'après les quantités standard de chaque service.
     * Retourne : [produit_id => ['produit' => Produit, 'quantite' => float]]
     */
    public function propositions(Depot $depot): Collection
    {
        $depot->loadMissing('articles');

        $standards = ServiceProduit::with('produit')
            ->whereIn('service_id', $depot->articles->pluck('service_id')->unique()->all())
            ->get();

        $lignes = [];

        foreach ($depot->articles as $article) {
            if ($article->statut === 'perdu') {
                continue;
            }

            foreach ($standards as $std) {
                if ((int) $std->service_id !== (int) $article->service_id) {
                    continue;
                }
                if ($std->type_article_id !== null && (int) $std->type_article_id !== (int) $article->type_article_id) {
                    continue;
                }
                if (! $std->produit || $std->produit->trashed()) {
                    continue;
                }

                $id = (int) $std->produit_id;
                $lignes[$id]['produit']  = $std->produit;
                $lignes[$id]['quantite'] = ($lignes[$id]['quantite'] ?? 0) + (float) $std->quantite_par_article * (int) $article->quantite;
            }
        }

        return collect($lignes)->map(fn ($l) => [
            'produit'  => $l['produit'],
            'quantite' => round($l['quantite'], 3),
        ]);
    }

    /** Les produits utilisés doivent-ils encore être déclarés avant de passer « prêt » ? */
    public function requise(Depot $depot): bool
    {
        return $depot->consommation_validee_at === null
            && $this->propositions($depot)->isNotEmpty();
    }

    /**
     * Enregistre les produits réellement utilisés (sorties de stock), puis passe le dépôt à « prêt ».
     * $lignes : [['produit_id' => int, 'quantite' => float], ...]
     */
    public function valider(Depot $depot, array $lignes, User $user, DepotService $depots): Depot
    {
        return DB::transaction(function () use ($depot, $lignes, $user, $depots) {
            $depot = Depot::lockForUpdate()->findOrFail($depot->id);

            if ($depot->consommation_validee_at !== null) {
                throw ValidationException::withMessages(['consommation' => 'Les produits de ce dépôt ont déjà été déclarés.']);
            }

            if ($depot->statut !== 'en_traitement') {
                throw ValidationException::withMessages(['consommation' => 'Le dépôt doit être « en traitement » pour déclarer les produits utilisés.']);
            }

            $nb = 0;

            foreach ($lignes as $ligne) {
                $quantite = round((float) ($ligne['quantite'] ?? 0), 3);

                if ($quantite <= 0) {
                    continue;
                }

                $produit  = Produit::findOrFail($ligne['produit_id']);
                $mouvement = $this->stock->mouvement($produit, 'sortie', $quantite, 'Dépôt ' . $depot->numero_ticket, $user);

                DepotConsommation::create([
                    'depot_id'           => $depot->id,
                    'produit_id'         => $produit->id,
                    'mouvement_stock_id' => $mouvement->id,
                    'user_id'            => $user->id,
                    'quantite'           => $quantite,
                    'cout_unitaire'      => (float) $produit->prix_achat_moyen,
                ]);

                $nb++;
            }

            $depot->update(['consommation_validee_at' => now()]);

            return $depots->changerStatut(
                $depot,
                'pret',
                $user,
                false,
                $nb > 0 ? "Produits utilisés déclarés ($nb ligne" . ($nb > 1 ? 's' : '') . ')' : 'Aucun produit déclaré'
            );
        });
    }
}
