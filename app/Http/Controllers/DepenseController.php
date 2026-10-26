<?php

namespace App\Http\Controllers;

use App\Models\CategorieDepense;
use App\Models\Depense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DepenseController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('role:admin,gerant')];
    }

    public function index(Request $request): View
    {
        $query = Depense::with('categorie')->orderByDesc('date_depense');

        if ($request->filled('categorie')) {
            $query->where('categorie_depense_id', $request->categorie);
        }
        if ($request->filled('date_debut')) {
            $query->whereDate('date_depense', '>=', $request->date_debut);
        }
        if ($request->filled('date_fin')) {
            $query->whereDate('date_depense', '<=', $request->date_fin);
        }

        $depenses     = $query->paginate(15)->withQueryString();
        $categories   = CategorieDepense::orderBy('nom')->get();
        $totalMois    = Depense::where('date_depense', '>=', now()->startOfMonth())->sum('montant');
        $parCategorie = Depense::where('date_depense', '>=', now()->startOfMonth())
            ->with('categorie')
            ->selectRaw('categorie_depense_id, SUM(montant) as total')
            ->groupBy('categorie_depense_id')
            ->get();

        return view('depenses.index', compact('depenses', 'categories', 'totalMois', 'parCategorie'));
    }

    public function create(): View
    {
        return view('depenses.form', ['depense' => new Depense, 'categories' => CategorieDepense::orderBy('nom')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());
        $data['user_id'] = $request->user()->id;
        Depense::create($data);

        return redirect()->route('depenses.index')->with('success', 'Dépense enregistrée.');
    }

    public function edit(Depense $depense): View
    {
        $this->verifierModifiable($depense);

        return view('depenses.form', ['depense' => $depense, 'categories' => CategorieDepense::orderBy('nom')->get()]);
    }

    public function update(Request $request, Depense $depense): RedirectResponse
    {
        $this->verifierModifiable($depense);
        $depense->update($request->validate($this->rules()));

        return redirect()->route('depenses.index')->with('success', 'Dépense modifiée.');
    }

    public function destroy(Depense $depense): RedirectResponse
    {
        $this->verifierModifiable($depense);
        $depense->delete();

        return redirect()->route('depenses.index')->with('success', 'Dépense supprimée.');
    }

    private function verifierModifiable(Depense $depense): void
    {
        abort_if($depense->achat_id, 403, "Cette dépense provient d'un achat et ne peut pas être modifiée.");
    }

    private function rules(): array
    {
        return [
            'libelle'              => 'required|string|max:150',
            'montant'              => 'required|integer|min:1',
            'categorie_depense_id' => ['required', Rule::exists('categories_depenses', 'id')->where('pressing_id', $this->pressingId())],
            'date_depense'         => 'required|date',
            'mode_paiement'        => 'required|in:especes,orange_money,mtn_money,wave,autre',
        ];
    }
}
