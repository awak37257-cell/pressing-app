<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClientController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('role:admin,gerant,caissier')];
    }

    public function index(Request $request): View
    {
        $query = Client::withCount('depots')->orderBy('nom');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn ($q) => $q->where('nom', 'like', "%$s%")->orWhere('telephone', 'like', "%$s%"));
        }

        $clients = $query->paginate(15)->withQueryString();

        return view('clients.index', compact('clients'));
    }

    public function create(): View
    {
        return view('clients.form', ['client' => new Client]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());

        // Un client supprimé (suppression logique) est restauré au lieu d'être recréé
        $existant = Client::withTrashed()->where('telephone', $data['telephone'])->first();

        if ($existant?->trashed()) {
            $existant->restore();
            $existant->update($data);
            $client = $existant;
        } else {
            $client = Client::create($data);
        }

        return redirect()->route('clients.index')->with('success', "Client « {$client->nom} » enregistré.");
    }

    public function show(Client $client): View
    {
        $client->loadCount('depots');
        $depots     = $client->depots()->orderByDesc('date_depot')->paginate(10);
        $totalPaye  = $client->depots()->where('statut', '!=', 'annule')->sum('montant_paye');
        $resteTotal = $client->depots()->where('statut', '!=', 'annule')->sum('reste_a_payer');

        return view('clients.show', compact('client', 'depots', 'totalPaye', 'resteTotal'));
    }

    public function edit(Client $client): View
    {
        return view('clients.form', compact('client'));
    }

    public function update(Request $request, Client $client): RedirectResponse
    {
        $client->update($request->validate($this->rules($client)));

        return redirect()->route('clients.index')->with('success', "Client « {$client->nom} » modifié.");
    }

    public function destroy(Client $client): RedirectResponse
    {
        $client->delete();

        return redirect()->route('clients.index')->with('success', 'Client supprimé.');
    }

    private function rules(?Client $client = null): array
    {
        return [
            'nom'          => 'required|string|max:100',
            'prenom'       => 'nullable|string|max:100',
            'telephone'    => ['required', 'string', 'max:30',
                Rule::unique('clients', 'telephone')->where('pressing_id', $this->pressingId())->withoutTrashed()->ignore($client?->id)],
            'email'        => 'nullable|email|max:150',
            'adresse'      => 'nullable|string|max:500',
            'observations' => 'nullable|string|max:1000',
        ];
    }
}
