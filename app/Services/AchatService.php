<?php

namespace App\Services;

use App\Models\Achat;
use App\Models\CategorieDepense;
use App\Models\Depense;
use App\Models\LigneAchat;
use App\Models\Produit;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AchatService
{
    public function __construct(private StockService $stock) {}

    public function creer(array $data, User $user): Achat
    {
        return DB::transaction(function () use ($data, $user) {
            $total = (int) round(collect($data['lignes'])->sum(fn ($l) => (float) $l['quantite'] * (float) $l['prix_unitaire']));

            $achat = Achat::create([
                'pressing_id'    => $user->pressing_id,
                'fournisseur_id' => $data['fournisseur_id'],
                'user_id'        => $user->id,
                'date_achat'     => $data['date_achat'],
                'montant_total'  => $total,
            ]);

            foreach ($data['lignes'] as $l) {
                $produit = Produit::lockForUpdate()->findOrFail($l['produit_id']);
                $ancien  = (float) $produit->stock_actuel;
                $qte     = (float) $l['quantite'];
                $pu      = (float) $l['prix_unitaire'];

                LigneAchat::create([
                    'achat_id'      => $achat->id,
                    'produit_id'    => $produit->id,
                    'quantite'      => $qte,
                    'prix_unitaire' => $pu,
                ]);

                $this->stock->mouvement($produit, 'entree', $qte, 'Achat #' . $achat->id, $user, $achat->id);

                // Prix d'achat moyen pondéré
                $moyen = round(($ancien * (float) $produit->prix_achat_moyen + $qte * $pu) / ($ancien + $qte), 2);
                Produit::whereKey($produit->id)->update(['prix_achat_moyen' => $moyen]);
            }

            // Dépense liée (évite le double comptage dans les rapports)
            $categorie = CategorieDepense::firstOrCreate([
                'pressing_id' => $user->pressing_id,
                'nom'         => 'Achats de produits',
            ]);

            Depense::create([
                'pressing_id'          => $user->pressing_id,
                'categorie_depense_id' => $categorie->id,
                'user_id'              => $user->id,
                'achat_id'             => $achat->id,
                'libelle'              => 'Achat fournisseur #' . $achat->id,
                'montant'              => $total,
                'mode_paiement'        => $data['mode_paiement'],
                'date_depense'         => $data['date_achat'],
            ]);

            return $achat;
        });
    }
}
