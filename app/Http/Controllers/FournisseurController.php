<?php

namespace App\Http\Controllers;

use App\Models\Fournisseur;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class FournisseurController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('role:admin,gerant')];
    }

    public function index(Request $request): View
    {
        $query = Fournisseur::withCount('achats')->orderBy('nom');

        if ($request->filled('search')) {
            $query->where('nom', 'like', '%' . $request->search . '%');
        }

        $fournisseurs = $query->paginate(15)->withQueryString();

        return view('fournisseurs.index', compact('fournisseurs'));
    }

    public function create(): View
    {
        return view('fournisseurs.form', ['fournisseur' => new Fournisseur]);
    }

    public function store(Request $request): RedirectResponse
    {
        $fournisseur = Fournisseur::create($request->validate($this->rules()));

        return redirect()->route('fournisseurs.index')
            ->with('success', "Fournisseur « {$fournisseur->nom} » enregistré.");
    }

    public function edit(Fournisseur $fournisseur): View
    {
        return view('fournisseurs.form', compact('fournisseur'));
    }

    public function update(Request $request, Fournisseur $fournisseur): RedirectResponse
    {
        $fournisseur->update($request->validate($this->rules()));

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

    private function rules(): array
    {
        return [
            'nom'       => 'required|string|max:100',
            'telephone' => 'nullable|string|max:30',
            'adresse'   => 'nullable|string|max:500',
        ];
    }
}
