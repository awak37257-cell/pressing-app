<?php

namespace App\Services;

use App\Models\MouvementStock;
use App\Models\Produit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockService
{
    /**
     * entree : ajoute | sortie, perte : retire | ajustement : $quantite = stock réel compté.
     */
    public function mouvement(
        Produit $produit,
        string $type,
        float $quantite,
        string $motif,
        User $user,
        ?int $achatId = null
    ): MouvementStock {
        return DB::transaction(function () use ($produit, $type, $quantite, $motif, $user, $achatId) {
            $produit = Produit::lockForUpdate()->findOrFail($produit->id);
            $stock   = (float) $produit->stock_actuel;

            $apres = match ($type) {
                'entree'          => $stock + $quantite,
                'sortie', 'perte' => $stock - $quantite,
                'ajustement'      => $quantite,
            };

            if ($apres < 0) {
                throw ValidationException::withMessages([
                    'quantite' => "Stock insuffisant : il reste {$stock} {$produit->unite}.",
                ]);
            }

            $apres = round($apres, 3);

            $mouvement = MouvementStock::create([
                'pressing_id'    => $produit->pressing_id,
                'produit_id'     => $produit->id,
                'type'           => $type,
                'quantite'       => $quantite,
                'stock_apres'    => $apres,
                'motif'          => $motif,
                'achat_id'       => $achatId,
                'user_id'        => $user->id,
                'date_mouvement' => now(),
            ]);

            $produit->update(['stock_actuel' => $apres]);

            return $mouvement;
        });
    }
}
