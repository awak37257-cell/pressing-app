<?php

namespace App\Services;

use App\Models\Depot;
use App\Models\DepotConsommation;
use App\Models\Produit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ConsommationService
{
    public function __construct(private StockService $stock) {}

    public function requise(Depot $depot): bool
    {
        return $depot->statut === 'en_traitement' && $depot->consommation_validee_at === null;
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

                $produit = Produit::findOrFail($ligne['produit_id']);
                $mouvement = $this->stock->mouvement($produit, 'sortie', $quantite, 'Dépôt '.$depot->numero_ticket, $user);

                DepotConsommation::create([
                    'depot_id' => $depot->id,
                    'produit_id' => $produit->id,
                    'mouvement_stock_id' => $mouvement->id,
                    'user_id' => $user->id,
                    'quantite' => $quantite,
                    'cout_unitaire' => (float) $produit->prix_achat_moyen,
                ]);

                $nb++;
            }

            $depot->update(['consommation_validee_at' => now()]);

            return $depots->changerStatut(
                $depot,
                'pret',
                $user,
                false,
                $nb > 0 ? "Produits utilisés déclarés ($nb ligne".($nb > 1 ? 's' : '').')' : 'Aucun produit déclaré'
            );
        });
    }
}
