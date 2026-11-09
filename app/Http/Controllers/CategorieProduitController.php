<?php

namespace App\Http\Controllers;

use App\Models\CategorieProduit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;

class CategorieProduitController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('role:admin,gerant')];
    }

    public function index(): RedirectResponse
    {
        return redirect()->to(route('services.index').'#categories-produits');
    }

    public function store(Request $request): RedirectResponse
    {
        $pressingId = $request->user()->pressing_id;
        $data = $request->validate([
            'nom_categorie' => ['required', 'string', 'max:50', Rule::unique('categories_produits', 'nom')->where('pressing_id', $pressingId)],
        ]);

        CategorieProduit::create([
            'pressing_id' => $pressingId,
            'nom' => $data['nom_categorie'],
        ]);

        return redirect()->to(route('services.index').'#categories-produits')
            ->with('success', 'Catégorie de produit ajoutée.');
    }

    public function update(Request $request, CategorieProduit $categoriesProduit): RedirectResponse
    {
        $data = $request->validate([
            'nom_categorie' => [
                'required',
                'string',
                'max:50',
                Rule::unique('categories_produits', 'nom')
                    ->where('pressing_id', $request->user()->pressing_id)
                    ->ignore($categoriesProduit->id),
            ],
        ]);

        $categoriesProduit->update(['nom' => $data['nom_categorie']]);

        return redirect()->to(route('services.index').'#categories-produits')
            ->with('success', 'Catégorie de produit modifiée.');
    }

    public function destroy(CategorieProduit $categoriesProduit): RedirectResponse
    {
        if ($categoriesProduit->produits()->withTrashed()->exists()) {
            return back()->with('error', 'Cette catégorie ne peut pas être supprimée car elle est utilisée par un ou plusieurs produits.');
        }

        $categoriesProduit->delete();

        return redirect()->to(route('services.index').'#categories-produits')
            ->with('success', 'Catégorie de produit supprimée.');
    }
}
