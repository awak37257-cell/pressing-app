<?php

namespace App\Http\Controllers;

use App\Models\Depot;
use App\Models\Paiement;
use App\Models\Produit;
use App\Models\Client;
use App\Models\Depense;
use App\Models\Achat;
use App\Models\MouvementStock;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        $today = now()->startOfDay();
        $monthStart = now()->startOfMonth();

        // KPIs du jour
        $depotsDuJour = Depot::whereDate('date_depot', today())->count();
        $chiffreAffaireJour = Paiement::whereHas('depot')->whereDate('date_paiement', today())->sum('montant');
        $depensesJour = Depense::whereDate('date_depense', today())->sum('montant');
        $clientsActifs = Client::whereHas('depots', fn ($q) => $q->whereDate('date_depot', today()))->count();

        // KPIs du mois
        $depotsMois = Depot::where('date_depot', '>=', $monthStart)->count();
        $chiffreAffaireMois = Paiement::whereHas('depot')->where('date_paiement', '>=', $monthStart)->sum('montant');
        $depensesMois = Depense::where('date_depense', '>=', $monthStart)->sum('montant');
        $resteAPayer = Depot::where('statut', '!=', 'annule')->where('reste_a_payer', '>', 0)->sum('reste_a_payer');

        // Produits en alerte
        $produitsEnAlerte = Produit::whereColumn('stock_actuel', '<=', 'seuil_alerte')
            ->with('categorie')
            ->orderBy('stock_actuel')
            ->take(5)
            ->get();

        // Dépôts récents
        $depotsRecents = Depot::with(['client', 'articles.typeArticle'])
            ->orderByDesc('date_depot')
            ->take(8)
            ->get();

        // Achats récents
        $achatsRecents = Achat::with('fournisseur')
            ->orderByDesc('date_achat')
            ->take(5)
            ->get();

        // Stats par statut de dépôt (mois en cours)
        $statsByStatut = Depot::where('date_depot', '>=', $monthStart)
            ->selectRaw('statut, COUNT(*) as count, SUM(montant_total) as total')
            ->groupBy('statut')
            ->get()
            ->keyBy('statut');

        // Evolution des 7 derniers jours
        $derniers7Jours = collect(range(6, 0))->map(function (int $daysAgo) {
            $date = now()->subDays($daysAgo);
            return [
                'date'    => $date->format('d/m'),
                'depots'  => Depot::whereDate('date_depot', $date)->count(),
                'recette' => Paiement::whereHas('depot')->whereDate('date_paiement', $date)->sum('montant'),
            ];
        });

        // File de travail (utile surtout à l'opérateur)
        $nbADemarrer = Depot::where('statut', 'depose')->count();
        $nbEnCours   = Depot::where('statut', 'en_traitement')->count();
        $nbPrets     = Depot::where('statut', 'pret')->count();
        $nbEnRetard  = Depot::whereIn('statut', ['depose', 'en_traitement'])->whereDate('date_retrait_prevue', '<', today())->count();

        return view('dashboard.index', compact(
            'nbADemarrer', 'nbEnCours', 'nbPrets', 'nbEnRetard',
            'depotsDuJour', 'chiffreAffaireJour', 'depensesJour', 'clientsActifs',
            'depotsMois', 'chiffreAffaireMois', 'depensesMois', 'resteAPayer',
            'produitsEnAlerte', 'depotsRecents', 'achatsRecents',
            'statsByStatut', 'derniers7Jours'
        ));
    }
}
