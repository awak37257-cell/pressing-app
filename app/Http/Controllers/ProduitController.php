<?php

namespace App\Http\Controllers;

use App\Models\CategorieProduit;
use App\Models\MouvementStock;
use App\Models\Produit;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProduitController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('role:admin,gerant', except: ['index', 'show', 'ajusterStock'])];
    }

    public function index(Request $request): View
    {
        $query = Produit::with('categorie')->orderBy('nom');

        if ($request->filled('search')) {
            $query->where('nom', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('categorie')) {
            $query->where('categorie_produit_id', $request->categorie);
        }
        if ($request->boolean('alerte')) {
            $query->whereColumn('stock_actuel', '<=', 'seuil_alerte');
        }

        $produits    = $query->paginate(15)->withQueryString();
        $categories  = CategorieProduit::orderBy('nom')->get();
        $totalAlerte = Produit::whereColumn('stock_actuel', '<=', 'seuil_alerte')->count();

        return view('produits.index', compact('produits', 'categories', 'totalAlerte'));
    }

    public function create(): View
    {
        if (!in_array(auth()->user()->role, ['admin', 'gerant'], true)) {
        abort(403, "Action non autorisée.");
    }
        $categories = CategorieProduit::orderBy('nom')->get();

        return view('produits.create', compact('categories'));
    }

    public function store(Request $request, StockService $stock): RedirectResponse
    {
        if (!in_array(auth()->user()->role, ['admin', 'gerant'], true)) {
        abort(403, "Action non autorisée.");
    }
        $pid  = $this->pressingId();
        $data = $request->validate([
            'nom'                  => ['required', 'string', 'max:100', Rule::unique('produits', 'nom')->where('pressing_id', $pid)],
            'categorie_produit_id' => ['required', Rule::exists('categories_produits', 'id')->where('pressing_id', $pid)],
            'unite'                => 'required|string|max:20',
            'contenance'           => 'nullable|numeric|gt:0|max:100000',
            'libelle_conditionnement' => 'nullable|string|max:50|required_with:contenance',
            'stock_actuel'         => 'required|numeric|min:0',
            'seuil_alerte'         => 'required|numeric|min:0',
            'prix_achat_moyen'     => 'nullable|integer|min:0',
        ]);

        $initial = (float) $data['stock_actuel'];

        $produit = Produit::create([
            ...$data,
            'stock_actuel'     => 0,
            'prix_achat_moyen' => $data['prix_achat_moyen'] ?? 0,
        ]);

        if ($initial > 0) {
            $stock->mouvement($produit, 'entree', $initial, 'Stock initial', $request->user());
        }

        return redirect()->route('produits.index')->with('success', "Produit « {$produit->nom} » créé.");
    }

    public function show(Produit $produit): View
    {
        $produit->load('categorie');
        $mouvements = MouvementStock::where('produit_id', $produit->id)
            ->with('user')
            ->orderByDesc('date_mouvement')
            ->paginate(20);

        return view('produits.show', compact('produit', 'mouvements'));
    }

    public function edit(Produit $produit): View
    {
        $categories = CategorieProduit::orderBy('nom')->get();

        return view('produits.edit', compact('produit', 'categories'));
    }

    public function update(Request $request, Produit $produit): RedirectResponse
    {
        $pid  = $this->pressingId();
        $data = $request->validate([
            'nom'                  => ['required', 'string', 'max:100', Rule::unique('produits', 'nom')->where('pressing_id', $pid)->ignore($produit->id)],
            'categorie_produit_id' => ['required', Rule::exists('categories_produits', 'id')->where('pressing_id', $pid)],
            'unite'                => 'required|string|max:20',
            'contenance'           => 'nullable|numeric|gt:0|max:100000',
            'libelle_conditionnement' => 'nullable|string|max:50|required_with:contenance',
            'seuil_alerte'         => 'required|numeric|min:0',
            'prix_achat_moyen'     => 'nullable|integer|min:0',
        ]);

        $produit->update($data);

        return redirect()->route('produits.index')->with('success', "Produit « {$produit->nom} » modifié.");
    }

    public function destroy(Produit $produit): RedirectResponse
    {
        $produit->delete();

        return redirect()->route('produits.index')->with('success', "Produit « {$produit->nom} » supprimé.");
    }

    public function ajusterStock(Request $request, Produit $produit, StockService $stock): RedirectResponse
    {
        $data = $request->validate([
            'type'     => 'required|in:entree,sortie,perte,ajustement',
            'quantite' => ['required', 'numeric', $request->input('type') === 'ajustement' ? 'min:0' : 'gt:0'],
            'motif'    => 'required|string|max:255',
        ]);

        // Un opérateur peut seulement déclarer des sorties
        abort_if(
            $data['type'] !== 'sortie' && ! in_array($request->user()->role, ['admin', 'gerant'], true),
            403,
            'Seul un gérant peut effectuer ce type de mouvement.'
        );

        $mouvement = $stock->mouvement($produit, $data['type'], (float) $data['quantite'], $data['motif'], $request->user());

        return redirect()->route('produits.show', $produit)
            ->with('success', 'Stock mis à jour. Nouveau stock : ' . (float) $mouvement->stock_apres . ' ' . $produit->unite);
    }
}
