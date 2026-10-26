<?php

namespace App\Http\Controllers;

use App\Models\Produit;
use App\Models\Service;
use App\Models\ServiceProduit;
use App\Models\TypeArticle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/** Réglage des quantités standard de produits consommées par service (admin et gérant). */
class ServiceProduitController extends Controller
{
    public function index(): View
    {
        $services = Service::with(['quantitesStandard.produit', 'quantitesStandard.typeArticle'])->orderBy('nom')->get();
        $produits = Produit::orderBy('nom')->get();
        $types    = TypeArticle::orderBy('nom')->get();

        return view('quantites-standard.index', compact('services', 'produits', 'types'));
    }

    public function store(Request $request): RedirectResponse
    {
        $pid = $this->pressingId();

        $data = $request->validate([
            'service_id'           => ['required', Rule::exists('services', 'id')->where('pressing_id', $pid)],
            'type_article_id'      => ['nullable', Rule::exists('types_articles', 'id')->where('pressing_id', $pid)],
            'produit_id'           => ['required', Rule::exists('produits', 'id')->where('pressing_id', $pid)->withoutTrashed()],
            'quantite_par_article' => 'required|numeric|gt:0|max:100000',
        ]);

        $typeId = $data['type_article_id'] ?? null;

        $existe = ServiceProduit::where('service_id', $data['service_id'])
            ->where('produit_id', $data['produit_id'])
            ->when($typeId, fn ($q) => $q->where('type_article_id', $typeId), fn ($q) => $q->whereNull('type_article_id'))
            ->exists();

        if ($existe) {
            throw ValidationException::withMessages([
                'produit_id' => 'Cette quantité standard existe déjà pour ce service et ce type d\'article : supprimez-la puis recréez-la.',
            ]);
        }

        ServiceProduit::create([
            'service_id'           => $data['service_id'],
            'type_article_id'      => $typeId,
            'produit_id'           => $data['produit_id'],
            'quantite_par_article' => $data['quantite_par_article'],
        ]);

        return back()->with('success', 'Quantité standard enregistrée.');
    }

    public function destroy(ServiceProduit $standard): RedirectResponse
    {
        abort_unless($standard->service, 404);

        $standard->delete();

        return back()->with('success', 'Quantité standard supprimée.');
    }
}
