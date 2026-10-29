<?php

namespace App\Http\Controllers;

use App\Models\Depot;
use App\Services\ConsommationService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** File de travail de l'opérateur : dépôts à commencer ou en cours de traitement (sans montants). */
class TraitementController extends Controller
{
    public function index(Request $request, ConsommationService $consommations): View
    {
        $filtre = $request->query('statut');

        $query = Depot::with(['client', 'articles.typeArticle', 'articles.service'])
            ->whereIn('statut', ['depose', 'en_traitement', 'pret'])
            ->orderByDesc('express')
            ->orderBy('date_retrait_prevue')
            ->orderBy('id');

        if (in_array($filtre, ['depose', 'en_traitement'], true)) {
            $query->where('statut', $filtre);
        }

        $depots = $query->paginate(20)->withQueryString();

        $compteurs = Depot::whereIn('statut', ['depose', 'en_traitement', 'pret'])
            ->selectRaw('statut, COUNT(*) as n')
            ->groupBy('statut')
            ->pluck('n', 'statut');

        $enRetard = Depot::whereIn('statut', ['depose', 'en_traitement'])
            ->whereDate('date_retrait_prevue', '<', today())
            ->count();

        // Pour chaque dépôt : faut-il déclarer des produits avant de le marquer prêt ?
        $produitsRequis = $depots->getCollection()
            ->mapWithKeys(fn ($d) => [$d->id => $consommations->requise($d)]);

        return view('traitement.index', compact('depots', 'compteurs', 'enRetard', 'filtre', 'produitsRequis'));
    }
}
