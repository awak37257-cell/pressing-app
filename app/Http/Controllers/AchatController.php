<?php

namespace App\Http\Controllers;

use App\Models\Achat;
use App\Models\Fournisseur;
use App\Models\Produit;
use App\Services\AchatService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AchatController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('role:admin,gerant')];
    }

    public function index(Request $request): View
    {
        $query = Achat::with('fournisseur')->orderByDesc('date_achat');

        if ($request->filled('fournisseur')) {
            $query->where('fournisseur_id', $request->fournisseur);
        }
        if ($request->filled('date_debut')) {
            $query->whereDate('date_achat', '>=', $request->date_debut);
        }
        if ($request->filled('date_fin')) {
            $query->whereDate('date_achat', '<=', $request->date_fin);
        }

        $achats       = $query->paginate(15)->withQueryString();
        $fournisseurs = Fournisseur::orderBy('nom')->get();
        $totalMois    = Achat::where('date_achat', '>=', now()->startOfMonth())->sum('montant_total');

        return view('achats.index', compact('achats', 'fournisseurs', 'totalMois'));
    }

    public function create(): View
    {
        return view('achats.create', [
            'fournisseurs' => Fournisseur::orderBy('nom')->get(),
            'produits'     => Produit::orderBy('nom')->get(),
        ]);
    }

    public function store(Request $request, AchatService $achats): RedirectResponse
    {
        $pid = $this->pressingId();

        $data = $request->validate([
            'fournisseur_id' => ['required', Rule::exists('fournisseurs', 'id')->where('pressing_id', $pid)],
            'date_achat'     => 'required|date|before_or_equal:today',
            'mode_paiement'  => 'required|in:especes,orange_money,mtn_money,wave,autre',
            'lignes'         => 'required|array|min:1',
            'lignes.*.produit_id'    => ['required', Rule::exists('produits', 'id')->where('pressing_id', $pid)->withoutTrashed()],
            'lignes.*.quantite'      => 'required|numeric|gt:0',
            'lignes.*.prix_unitaire' => 'required|numeric|min:0',
            'lignes.*.conditionnement' => 'nullable|boolean',
        ]);

        // Achat saisi en bidons (ou autre conditionnement) : conversion en litres / unités de stock
        foreach ($data['lignes'] as $i => $ligne) {
            if (! empty($ligne['conditionnement'])) {
                $produit = Produit::findOrFail($ligne['produit_id']);

                if ((float) $produit->contenance > 0) {
                    $data['lignes'][$i]['quantite']      = (float) $ligne['quantite'] * (float) $produit->contenance;
                    $data['lignes'][$i]['prix_unitaire'] = (float) $ligne['prix_unitaire'] / (float) $produit->contenance;
                }
            }
        }

        $achat = $achats->creer($data, $request->user());

        return redirect()->route('achats.show', $achat)->with('success', 'Achat enregistré, stock mis à jour.');
    }

    public function show(Achat $achat): View
    {
        $achat->load(['fournisseur', 'lignes.produit', 'user']);

        return view('achats.show', compact('achat'));
    }
}
