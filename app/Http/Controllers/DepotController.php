<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Depot;
use App\Models\DepotHistorique;
use App\Models\Service;
use App\Models\TypeArticle;
use App\Services\ConsommationService;
use App\Services\DepotService;
use App\Support\Whatsapp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DepotController extends Controller implements HasMiddleware
{
    private const MODES = 'especes,orange_money,mtn_money,wave';

    public function __construct(
        private DepotService $depots,
        private ConsommationService $consommations
    ) {}

    public static function middleware(): array
    {
        return [
            // L'opérateur n'a accès qu'à la file « À traiter » et à la fiche d'un dépôt (sans montants)
            new Middleware('role:admin,gerant,caissier', only: ['index', 'create', 'store', 'edit', 'update']),
            new Middleware('role:admin,gerant', only: ['destroy']),
        ];
    }

    public function index(Request $request): View|RedirectResponse
    {
        if ($request->query->has('scan_ticket')) {
            $data = $request->validate([
                'scan_ticket' => ['required', 'string', 'max:50'],
            ]);
            $numeroTicket = trim($data['scan_ticket']);
            $depot = Depot::where('numero_ticket', $numeroTicket)->first();

            if (! $depot) {
                return redirect()->route('depots.index')
                    ->withInput()
                    ->with('error', 'Aucun dépôt ne correspond à ce numéro de ticket.');
            }

            return redirect()->route('depots.show', $depot);
        }

        $query = Depot::with(['client', 'user', 'articles.typeArticle'])->orderByDesc('date_depot');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn ($q) => $q
                ->where('numero_ticket', 'like', "%$s%")
                ->orWhereHas('client', fn ($c) => $c->where('nom', 'like', "%$s%")->orWhere('telephone', 'like', "%$s%")));
        }
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        if ($request->filled('date_debut')) {
            $query->whereDate('date_depot', '>=', $request->date_debut);
        }
        if ($request->filled('date_fin')) {
            $query->whereDate('date_depot', '<=', $request->date_fin);
        }

        $depots = $query->paginate(15)->withQueryString();
        $statutsCount = Depot::selectRaw('statut, COUNT(*) as count')->groupBy('statut')->get()->keyBy('statut');

        $roleUtilisateur = $request->user()->role;
        $estPersonnelCaisse = in_array($roleUtilisateur, ['admin', 'gerant', 'caissier'], true);
        $depotsPretsALivrer = $estPersonnelCaisse
            ? Depot::with(['client', 'pressing', 'articles.typeArticle'])
                ->where('pressing_id', $request->user()->pressing_id)
                ->where('statut', 'pret')
                ->latest()
                ->take(6)
                ->get()
            : collect();

        return view('depots.index', compact('depots', 'statutsCount', 'depotsPretsALivrer', 'estPersonnelCaisse'));
    }

    public function prets(Request $request): View
    {
        $numeroTicket = null;
        if ($request->query->has('scan_ticket')) {
            $numeroTicket = trim($request->validate([
                'scan_ticket' => ['required', 'string', 'max:50'],
            ])['scan_ticket']);
        }

        $depots = Depot::query()
            ->with(['client', 'pressing', 'articles.typeArticle'])
            ->where('pressing_id', $request->user()->pressing_id)
            ->where('statut', 'pret')
            ->when($numeroTicket !== null, fn ($query) => $query->where('numero_ticket', $numeroTicket))
            ->latest('date_depot')
            ->paginate(15)
            ->withQueryString();

        return view('depots.prets', compact('depots', 'numeroTicket'));
    }

    public function encaisserDepuisPrets(Request $request, Depot $depot): RedirectResponse
    {
        abort_unless((int) $depot->pressing_id === (int) $request->user()->pressing_id, 404);

        if ($depot->statut !== 'pret') {
            return back()->with('error', 'Ce dépôt n’est plus prêt à être remis au client.');
        }

        $data = $request->validate([
            'montant_paye_ajout' => ['required', 'integer', 'min:1'],
            'mode_paiement' => ['required', 'in:'.self::MODES],
            'reference' => ['nullable', 'string', 'max:100', Rule::requiredIf(fn () => in_array($request->input('mode_paiement'), ['orange_money', 'mtn_money', 'wave'], true))],
        ]);

        $paiement = $this->depots->encaisser(
            $depot,
            (int) $data['montant_paye_ajout'],
            $data['mode_paiement'],
            $request->user(),
            $data['reference'] ?? null,
        );
        $depot->refresh();

        if ((int) $depot->reste_a_payer === 0) {
            return redirect()->route('paiements.recu', ['paiement' => $paiement, 'imprimer' => 1]);
        }

        return redirect()->route('depots.prets', ['scan_ticket' => $depot->numero_ticket])
            ->with('success', 'Paiement enregistré. Le solde reste dû avant la remise des articles.');
    }

    public function alertesPrets(Request $request): JsonResponse
    {
        $pressingId = $request->user()->pressing_id;
        $sinceId = $request->validate([
            'since_id' => ['required', 'integer', 'min:0'],
        ])['since_id'];
        $nombre = Depot::query()
            ->where('pressing_id', $pressingId)
            ->where('statut', 'pret')
            ->count();
        $evenements = DepotHistorique::query()
            ->where('statut', 'pret')
            ->where('id', '>', $sinceId)
            ->whereHas('depot', fn ($query) => $query
                ->where('pressing_id', $pressingId)
                ->where('statut', 'pret'))
            ->with('depot.client')
            ->orderBy('id')
            ->get()
            ->map(fn (DepotHistorique $historique): array => [
                'id' => $historique->id,
                'ticket' => $historique->depot->numero_ticket,
                'client' => $historique->depot->client?->nom,
                'reste' => (int) $historique->depot->reste_a_payer,
                'url' => route('depots.prets', ['scan_ticket' => $historique->depot->numero_ticket]),
            ])
            ->values();

        return response()->json([
            'count' => $nombre,
            'events' => $evenements,
            'last_id' => DepotHistorique::query()
                ->where('statut', 'pret')
                ->whereHas('depot', fn ($query) => $query->where('pressing_id', $pressingId))
                ->max('id') ?? $sinceId,
        ]);
    }

    public function create(): View
    {
        $clients = Client::orderBy('nom')->get();
        $typesArticles = TypeArticle::with('tarifs')->orderBy('nom')->get();
        $services = Service::orderBy('nom')->get();
        $tarifs = $typesArticles->flatMap(fn ($t) => $t->tarifs)->mapWithKeys(fn ($t) => [
            $t->type_article_id.'-'.$t->service_id => ['prix' => (int) $t->prix, 'express' => (int) $t->prix_express],
        ]);
        $numeroTicket = "attribué à l'enregistrement";

        return view('depots.create', compact('clients', 'typesArticles', 'services', 'tarifs', 'numeroTicket'));
    }

    public function store(Request $request): RedirectResponse
    {
        $pid = $this->pressingId();

        $data = $request->validate([
            'client_id' => ['required', Rule::exists('clients', 'id')->where('pressing_id', $pid)->withoutTrashed()],
            'date_retrait_prevue' => 'required|date|after_or_equal:today',
            'remise' => 'nullable|integer|min:0',
            'points_fidelite_utilises' => ['nullable', 'integer', 'min:0'],
            'observations' => 'nullable|string|max:1000',
            'articles' => 'required|array|min:1',
            'articles.*.type_article_id' => ['required', Rule::exists('types_articles', 'id')->where('pressing_id', $pid)],
            'articles.*.service_id' => ['required', Rule::exists('services', 'id')->where('pressing_id', $pid)],
            'articles.*.quantite' => 'required|integer|min:1|max:999',
            'montant_paye' => 'nullable|integer|min:0',
            'mode_paiement' => 'nullable|in:'.self::MODES,
            'reference' => ['nullable', 'string', 'max:100', Rule::requiredIf(fn () => $request->integer('montant_paye') > 0
                && in_array($request->input('mode_paiement'), ['orange_money', 'mtn_money', 'wave'], true))],
        ]);
        $data['express'] = $request->boolean('express');

        $depot = $this->depots->creer($data, $request->user());

        return redirect()->route('depots.show', $depot)
            ->with('success', "Dépôt {$depot->numero_ticket} enregistré. Vous pouvez imprimer le ticket.");
    }

    public function show(Depot $depot): View
    {
        $depot->load([
            'client', 'articles.typeArticle', 'articles.service', 'paiements', 'user',
            'historique.user', 'consommations.produit', 'consommations.user',
        ]);

        $incidentNotice = Whatsapp::articleIncidentNotice($depot->articles);
        $consommationRequise = $this->consommations->requise($depot);
        $pressing = auth()->user()->pressing;

        $whatsappTicket = Whatsapp::url(
            $depot->client?->telephone,
            "Bonjour {$depot->client?->nom}, votre dépôt {$depot->numero_ticket} est bien enregistré chez "
            .($pressing?->nom ?? 'notre pressing').'. Total : '.number_format((int) $depot->montant_total, 0, ',', ' ')
            .' FCFA, reste à payer : '.number_format((int) $depot->reste_a_payer, 0, ',', ' ')
            .' FCFA. Retrait prévu le '.$depot->date_retrait_prevue?->format('d/m/Y').'.'
            .$incidentNotice
        );

        $whatsappPret = Whatsapp::url(
            $depot->client?->telephone,
            "Bonjour {$depot->client?->nom}, votre linge (ticket {$depot->numero_ticket}) est prêt à être retiré chez ".($pressing?->nom ?? 'notre pressing').'. '
            .((int) $depot->reste_a_payer > 0
                ? 'Il vous reste à payer : '.number_format((int) $depot->reste_a_payer, 0, ',', ' ').' FCFA. '
                : 'Votre solde est réglé. ')
            .'Merci de passer le récupérer.'.$incidentNotice
        );

        return view('depots.show', compact('depot', 'consommationRequise', 'whatsappTicket', 'whatsappPret'));
    }

    public function edit(Depot $depot): View
    {
        return view('depots.edit', compact('depot'));
    }

    public function update(Request $request, Depot $depot): RedirectResponse
    {
        $data = $request->validate([
            'date_retrait_prevue' => 'nullable|date',
            'observations' => 'nullable|string|max:1000',
            'montant_paye_ajout' => 'nullable|integer|min:0',
            'mode_paiement' => 'nullable|in:'.self::MODES,
            'reference' => ['nullable', 'string', 'max:100', Rule::requiredIf(fn () => $request->integer('montant_paye_ajout') > 0
                && in_array($request->input('mode_paiement'), ['orange_money', 'mtn_money', 'wave'], true))],
        ]);

        $user = $request->user();

        DB::transaction(function () use ($data, $depot, $user) {
            $depot->observations = $data['observations'] ?? null;
            if (! empty($data['date_retrait_prevue'])) {
                $depot->date_retrait_prevue = $data['date_retrait_prevue'];
            }
            $depot->save();

            if (! empty($data['montant_paye_ajout'])) {
                $this->depots->encaisser($depot, (int) $data['montant_paye_ajout'], $data['mode_paiement'] ?? 'especes', $user, $data['reference'] ?? null);
            }
        });

        return redirect()->route('depots.show', $depot)->with('success', 'Dépôt mis à jour.');
    }

    /** Un dépôt n'est jamais supprimé : il est annulé (historique financier préservé). */
    public function destroy(Request $request, Depot $depot): RedirectResponse
    {
        $this->depots->changerStatut($depot, 'annule', $request->user(), false, 'Dépôt annulé');

        $message = 'Dépôt annulé.';
        if ((int) $depot->refresh()->montant_paye > 0) {
            $message .= ' Des paiements ont été encaissés : enregistrez le remboursement dans les dépenses.';
        }

        return redirect()->route('depots.index')->with('success', $message);
    }

    public function retirer(Request $request, Depot $depot): RedirectResponse
    {
        // Vérifier que le dépôt est bien prêt avant de le marquer comme retiré
        if ($depot->statut !== 'pret') {
            return back()->with('error', "Ce dépôt ne peut pas être retiré car il n'est pas au statut 'prêt'.");
        }

        $user = $request->user();

        DB::transaction(function () use ($depot, $user) {
            // Utiliser votre service de dépôt pour changer le statut proprement (met à jour l'historique)
            $this->depots->changerStatut($depot, 'retire', $user, true, 'Linge retiré par le client');

            $depot->update([
                'date_retrait_reelle' => now(),
            ]);
        });

        return redirect()->route('depots.show', $depot)->with('success', "Le dépôt {$depot->numero_ticket} a été marqué comme retiré avec succès.");
    }
}
