<?php

namespace App\Http\Controllers;

use App\Models\Depot;
use App\Models\Paiement;
use App\Models\Depense;
use App\Models\Achat;
use App\Models\Produit;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RapportController extends Controller
{
    public function index(Request $request): View
    {
        $mois  = $request->input('mois', now()->format('Y-m'));
        $debut = \Carbon\Carbon::parse($mois . '-01')->startOfMonth();
        $fin   = $debut->copy()->endOfMonth();

        // Chiffre d'affaires
        $chiffreAffaire = Depot::where('statut', '!=', 'annule')->whereBetween('date_depot', [$debut, $fin])->sum('montant_total');
        $encaisse = Paiement::whereHas('depot')->whereBetween('date_paiement', [$debut, $fin])->sum('montant');
        $nonEncaisse = Depot::where('statut', '!=', 'annule')->whereBetween('date_depot', [$debut, $fin])->sum('reste_a_payer');

        // Dépenses
        $totalDepenses = Depense::whereBetween('date_depense', [$debut, $fin])->sum('montant');

        // Achats
        $totalAchats = Achat::whereBetween('date_achat', [$debut, $fin])->sum('montant_total');

        // Bénéfice net estimé
        // Les achats sont déjà inclus dans les dépenses : ne pas les soustraire deux fois
        $beneficeNet = $encaisse - $totalDepenses;

        // Dépôts par statut
        $depotsParStatut = Depot::whereBetween('date_depot', [$debut, $fin])
            ->selectRaw('statut, COUNT(*) as count, SUM(montant_total) as total')
            ->groupBy('statut')
            ->get()
            ->keyBy('statut');

        // Meilleurs clients
        $topClients = Client::withSum(['depots as total_paye' => fn ($q) => $q->whereBetween('date_depot', [$debut, $fin])], 'montant_paye')
            ->having('total_paye', '>', 0)
            ->orderByDesc('total_paye')
            ->take(5)
            ->get();

        // Produits en alerte
        $produitsEnAlerte = Produit::whereColumn('stock_actuel', '<=', 'seuil_alerte')
            ->orderBy('stock_actuel')
            ->get();

        // Evolution journalière du CA
        $evolutionJournaliere = collect();
        $cursor = $debut->copy();
        while ($cursor->lte($fin) && $cursor->lte(now())) {
            $evolutionJournaliere->push([
                'jour'    => $cursor->format('d'),
                'recette' => Paiement::whereHas('depot')->whereDate('date_paiement', $cursor)->sum('montant'),
            ]);
            $cursor->addDay();
        }

        return view('rapports.index', compact(
            'mois', 'chiffreAffaire', 'encaisse', 'nonEncaisse',
            'totalDepenses', 'totalAchats', 'beneficeNet',
            'depotsParStatut', 'topClients', 'produitsEnAlerte',
            'evolutionJournaliere'
        ));
    }
}
