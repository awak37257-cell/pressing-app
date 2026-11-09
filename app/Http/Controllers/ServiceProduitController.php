<?php

namespace App\Http\Controllers;

use App\Models\DepotConsommation;
use Illuminate\View\View;

/** Historique des consommations réelles déclarées pour les dépôts du pressing. */
class ServiceProduitController extends Controller
{
    public function index(): View
    {
        $consommationsQuery = DepotConsommation::query()
            ->whereHas('depot', fn ($query) => $query->where('pressing_id', $this->pressingId()));

        $statistiques = (clone $consommationsQuery)
            ->selectRaw('COUNT(*) as nombre_lignes, COUNT(DISTINCT produit_id) as nombre_produits, COALESCE(SUM(quantite * cout_unitaire), 0) as cout_total')
            ->first();

        $consommations = $consommationsQuery
            ->with(['produit', 'depot.client', 'user'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(20);

        return view('quantites-standard.index', compact('consommations', 'statistiques'));
    }
}
