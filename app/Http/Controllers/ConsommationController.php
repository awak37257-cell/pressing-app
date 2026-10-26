<?php

namespace App\Http\Controllers;

use App\Models\Depot;
use App\Models\Produit;
use App\Services\ConsommationService;
use App\Services\DepotService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Page « produits utilisés » affichée quand on marque prêt un dépôt qui consomme des produits. */
class ConsommationController extends Controller
{
    public function __construct(private ConsommationService $consommations) {}

    public function create(Depot $depot): View|RedirectResponse
    {
        if ($depot->statut !== 'en_traitement' || $depot->consommation_validee_at !== null) {
            return redirect()->route('depots.show', $depot)
                ->with('error', 'Les produits de ce dépôt ne peuvent pas (ou plus) être déclarés.');
        }

        $propositions = $this->consommations->propositions($depot);

        if ($propositions->isEmpty()) {
            return redirect()->route('depots.show', $depot)
                ->with('error', 'Aucune quantité standard n\'est définie pour les services de ce dépôt : vous pouvez le marquer prêt directement.');
        }

        $depot->load(['client', 'articles.typeArticle', 'articles.service']);
        
        // Transformation propre des produits pour éviter les erreurs de parsing dans Blade
        $produits = Produit::orderBy('nom')->get()->map(function ($p) {
            return [
                'id' => $p->id,
                'nom' => $p->nom,
                'unite' => $p->unite,
                'stock' => $p->stock_actuel ?? 0,
            ];
        });

        // Définition de la variable initial pour éviter l'erreur Undefined variable dans la vue
        $initial = $propositions->map(function ($prop) {
            return [
                'produit_id' => $prop['produit_id'] ?? null,
                'quantite' => $prop['quantite'] ?? 0,
            ];
        })->values();

        return view('consommations.create', compact('depot', 'propositions', 'produits', 'initial'));
    }

    public function store(Request $request, Depot $depot, DepotService $depots): RedirectResponse
    {
        $data = $request->validate([
            'lignes'              => 'required|array|min:1',
            'lignes.*.produit_id' => ['required', Rule::exists('produits', 'id')->where('pressing_id', $this->pressingId())->withoutTrashed()],
            'lignes.*.quantite'   => 'required|numeric|min:0|max:100000',
        ]);

        $this->consommations->valider($depot, $data['lignes'], $request->user(), $depots);

        $route = $request->user()->role === 'operateur'
            ? route('traitement.index')
            : route('depots.show', $depot);

        return redirect($route)->with('success', "Produits enregistrés : le dépôt {$depot->numero_ticket} est prêt.");
    }
}