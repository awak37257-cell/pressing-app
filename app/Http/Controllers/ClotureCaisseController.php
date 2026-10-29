<?php
namespace App\Http\Controllers;

use App\Models\ClotureCaisse;
use App\Models\Paiement;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ClotureCaisseController extends Controller
{
    // Affirmer la liste des clôtures et le formulaire du jour
    public function index(Request $request)
    {
        $pressingId = auth()->user()->pressing_id;
        $dateCible = $request->input('date', Carbon::today()->toDateString());

        // Récupérer les paiements de la date choisie pour le pressing
        $paiementsJour = Paiement::whereHas('depot', function($query) use ($pressingId) {
                $query->where('pressing_id', $pressingId);
            })
            ->whereDate('date_paiement', $dateCible)
            ->get();

        // Calculer les totaux par mode de paiement
        $totauxParMode = [
            'especes' => $paiementsJour->where('mode', 'especes')->sum('montant'),
            'wave' => $paiementsJour->where('mode', 'wave')->sum('montant'),
            'orange_money' => $paiementsJour->where('mode', 'orange_money')->sum('montant'),
            'mtn_money' => $paiementsJour->where('mode', 'mtn_money')->sum('montant'),
            'carte' => $paiementsJour->where('mode', 'carte')->sum('montant'),
        ];

        $totalGeneral = array_sum($totauxParMode);

        // Vérifier si une clôture a déjà été faite pour cette date
        $clotureExistante = ClotureCaisse::where('pressing_id', $pressingId)
            ->whereDate('date_cloture', $dateCible)
            ->first();

        // Historique des clôtures précédentes
        $historique = ClotureCaisse::where('pressing_id', $pressingId)
            ->latest('date_cloture')
            ->paginate(10);

        return view('caisse.cloture', compact(
            'totauxParMode',
            'totalGeneral',
            'dateCible',
            'clotureExistante',
            'historique',
            'paiementsJour'
        ));
    }

    // Enregistrer la clôture de caisse
    public function store(Request $request)
    {
        $request->validate([
            'date_cloture' => 'required|date',
            'montant_physique_declare' => 'required|numeric|min:0',
            'commentaire' => 'nullable|string|max:500',
        ]);

        $pressingId = auth()->user()->pressing_id;
        $dateCible = $request->date_cloture;

        // Recalculer les totaux par sécurité
        $paiementsJour = Paiement::whereHas('depot', function($query) use ($pressingId) {
                $query->where('pressing_id', $pressingId);
            })
            ->whereDate('date_paiement', $dateCible)
            ->get();

        $totalEspeces = $paiementsJour->where('mode', 'especes')->sum('montant');
        $totalWave = $paiementsJour->where('mode', 'wave')->sum('montant');
        $totalOrange = $paiementsJour->where('mode', 'orange_money')->sum('montant');
        $totalMtn = $paiementsJour->where('mode', 'mtn_money')->sum('montant');
        $totalCarte = $paiementsJour->where('mode', 'carte')->sum('montant');
        $totalGeneral = $totalEspeces + $totalWave + $totalOrange + $totalMtn + $totalCarte;

        // Calcul de l'écart sur les espèces (Montant physique compté en caisse - Total espèces théorique)
        $montantPhysique = $request->montant_physique_declare;
        $ecart = $montantPhysique - $totalEspeces;

        ClotureCaisse::updateOrCreate(
            [
                'pressing_id' => $pressingId,
                'date_cloture' => $dateCible,
            ],
            [
                'user_id' => auth()->id(),
                'total_especes' => $totalEspeces,
                'total_wave' => $totalWave,
                'total_orange' => $totalOrange,
                'total_mtn' => $totalMtn,
                'total_carte' => $totalCarte,
                'total_general' => $totalGeneral,
                'montant_physique_declare' => $montantPhysique,
                'ecart' => $ecart,
                'commentaire' => $request->commentaire,
            ]
        );

        return redirect()->route('caisse.cloture', ['date' => $dateCible])
            ->with('success', 'La clôture de caisse a été enregistrée avec succès.');
    }
}