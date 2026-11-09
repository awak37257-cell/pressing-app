<?php

namespace App\Http\Controllers;

use App\Models\CategorieProduit;
use App\Models\Fournisseur;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FournisseurController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('role:admin,gerant')];
    }

    public function index(Request $request): View
    {
        $query = Fournisseur::withCount('achats')->with('categories')->orderBy('nom');

        if ($request->filled('search')) {
            $query->where('nom', 'like', '%'.$request->search.'%');
        }

        $fournisseurs = $query->paginate(15)->withQueryString();

        return view('fournisseurs.index', compact('fournisseurs'));
    }

    public function create(): View
    {
        return view('fournisseurs.form', [
            'fournisseur' => new Fournisseur,
            'categories' => CategorieProduit::orderBy('nom')->get(),
            'categoriesSelectionnees' => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules($request));
        $fournisseur = Fournisseur::create([
            'nom' => $data['nom'],
            'telephone' => $data['telephone'] ?? null,
            'adresse' => $data['adresse'] ?? null,
        ]);
        $fournisseur->categories()->sync($data['categorie_produit_ids']);

        return redirect()->route('fournisseurs.index')
            ->with('success', "Fournisseur « {$fournisseur->nom} » enregistré.");
    }

    public function edit(Fournisseur $fournisseur): View
    {
        $fournisseur->load('categories');

        return view('fournisseurs.form', [
            'fournisseur' => $fournisseur,
            'categories' => CategorieProduit::orderBy('nom')->get(),
            'categoriesSelectionnees' => $fournisseur->categories->modelKeys(),
        ]);
    }

    public function update(Request $request, Fournisseur $fournisseur): RedirectResponse
    {
        $data = $request->validate($this->rules($request));
        $fournisseur->update([
            'nom' => $data['nom'],
            'telephone' => $data['telephone'] ?? null,
            'adresse' => $data['adresse'] ?? null,
        ]);
        $fournisseur->categories()->sync($data['categorie_produit_ids']);

        return redirect()->route('fournisseurs.index')
            ->with('success', "Fournisseur « {$fournisseur->nom} » modifié.");
    }

    public function destroy(Fournisseur $fournisseur): RedirectResponse
    {
        if ($fournisseur->achats()->exists()) {
            return redirect()->route('fournisseurs.index')
                ->with('error', 'Ce fournisseur a des achats enregistrés et ne peut pas être supprimé.');
        }

        $fournisseur->delete();

        return redirect()->route('fournisseurs.index')->with('success', 'Fournisseur supprimé.');
    }

    private function rules(Request $request): array
    {
        return [
            'nom' => ['required', 'string', 'max:100'],
            'telephone' => ['nullable', 'string', 'max:30'],
            'adresse' => ['nullable', 'string', 'max:500'],
            'categorie_produit_ids' => ['required', 'array', 'min:1'],
            'categorie_produit_ids.*' => [
                'required',
                'integer',
                Rule::exists('categories_produits', 'id')
                    ->where('pressing_id', $request->user()->pressing_id),
            ],
        ];
    }
}
