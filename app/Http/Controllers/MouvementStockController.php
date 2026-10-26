<?php

namespace App\Http\Controllers;

use App\Models\MouvementStock;
use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MouvementStockController extends Controller
{
    public function index(Request $request): View
    {
        $query = MouvementStock::with(['produit', 'user', 'achat'])->orderByDesc('date_mouvement');

        if ($request->filled('produit')) {
            $query->where('produit_id', $request->produit);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('date_debut')) {
            $query->whereDate('date_mouvement', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('date_mouvement', '<=', $request->date_fin);
        }

        $mouvements = $query->paginate(20)->withQueryString();
        $produits   = Produit::orderBy('nom')->get();

        return view('mouvements.index', compact('mouvements', 'produits'));
    }
}
