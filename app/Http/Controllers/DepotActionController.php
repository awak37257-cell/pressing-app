<?php

namespace App\Http\Controllers;

use App\Models\ArticleDepot;
use App\Models\Depot;
use App\Services\DepotService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Boutons du workflow : commencer, marquer prêt, remettre au client, rouvrir, signaler un article. */
class DepotActionController extends Controller
{
    public function __construct(private DepotService $depots) {}

    public function commencer(Request $request, Depot $depot): RedirectResponse
    {
        $role = strtolower(trim(auth()->user()->role ?? ''));

        // Interdire l'action aux secrétaires / caissières
        if (in_array($role, ['secretaire', 'caissiere'], true)) {
            abort(403, 'Seul un opérateur ou la gérance peut démarrer le traitement.');
        }
        $this->depots->changerStatut($depot, 'en_traitement', $request->user());

        return redirect()->route('traitement.index', ['statut' => 'en_traitement'])
            ->with('success', "Traitement commencé : {$depot->numero_ticket}.");
    }

    public function pret(Request $request, Depot $depot): RedirectResponse
    {
        $role = strtolower(trim(auth()->user()->role ?? ''));

        // Interdire l'action aux secrétaires / caissières
        if (in_array($role, ['secretaire', 'caissiere'], true)) {
            abort(403, 'Seul un opérateur ou la gérance peut marquer un dépôt comme prêt.');
        }
        if ($depot->statut === 'en_traitement' && $depot->consommation_validee_at === null) {
            return redirect()->route('consommations.create', $depot);
        }

        $this->depots->changerStatut($depot, 'pret', $request->user());

        return back()->with('success', "Dépôt {$depot->numero_ticket} prêt à être retiré.");
    }

    public function retirer(Request $request, Depot $depot): RedirectResponse
    {
        $request->validate(['derogation' => 'sometimes|boolean']);

        $this->depots->changerStatut($depot, 'retire', $request->user(), $request->boolean('derogation'));

        return back()->with('success', "Dépôt {$depot->numero_ticket} remis au client.");
    }

    public function rouvrir(Request $request, Depot $depot): RedirectResponse
    {
        $this->depots->changerStatut($depot, 'en_traitement', $request->user(), false, 'Dépôt rouvert');

        return back()->with('success', 'Dépôt rouvert : il repasse « en traitement ».');
    }

    public function signaler(Request $request, Depot $depot, ArticleDepot $article): RedirectResponse
    {
        $data = $request->validate([
            'statut' => 'required|in:perdu,abime',
            'note' => 'required|string|min:3|max:200',
        ]);

        $this->depots->signaler($depot, $article, $data['statut'], $data['note'], $request->user());

        return back()->with('success', 'Article signalé et noté dans l\'historique.');
    }
}
