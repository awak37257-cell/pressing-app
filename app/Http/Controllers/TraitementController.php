<?php

namespace App\Http\Controllers;

use App\Models\Depot;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** File de travail de l'opérateur : dépôts à commencer ou en cours de traitement (sans montants). */
class TraitementController extends Controller
{
    public function index(Request $request): View
    {
        $filtre = $request->query('statut');

        $query = Depot::with(['client', 'articles.typeArticle', 'articles.service'])
            ->whereIn('statut', ['depose', 'en_traitement'])
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

        return view('traitement.index', compact('depots', 'compteurs', 'enRetard', 'filtre'));
    }
}
