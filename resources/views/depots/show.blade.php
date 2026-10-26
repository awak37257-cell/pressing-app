@extends('layouts.app')

@section('title', 'Dépôt ' . $depot->numero_ticket)
@section('page-title', 'Dépôt ' . $depot->numero_ticket)
@section('page-subtitle', 'Détail, suivi et historique')

@php
    $user         = auth()->user();
    $role         = $user->role;
    $gestionnaire = in_array($role, ['admin', 'gerant'], true);
    $caisse       = in_array($role, ['admin', 'gerant', 'caissier'], true);   // voit les montants
    $f            = fn ($n) => number_format($n, 0, ',', ' ');

    $labels = ['depose' => 'Déposé', 'en_traitement' => 'En traitement', 'pret' => 'Prêt à retirer', 'retire' => 'Retiré', 'annule' => 'Annulé'];
    $badges = ['depose' => 'badge-info', 'en_traitement' => 'badge-warning', 'pret' => 'badge-success', 'retire' => 'badge-neutral', 'annule' => 'badge-danger'];
    $labelsArticle = ['depose' => 'Déposé', 'en_traitement' => 'En traitement', 'pret' => 'Prêt', 'retire' => 'Retiré', 'perdu' => 'Perdu', 'abime' => 'Abîmé'];
    $badgesArticle = ['depose' => 'badge-info', 'en_traitement' => 'badge-warning', 'pret' => 'badge-success', 'retire' => 'badge-neutral', 'perdu' => 'badge-danger', 'abime' => 'badge-danger'];

    $enRetard = $depot->date_retrait_prevue?->isPast() && in_array($depot->statut, ['depose', 'en_traitement'], true);
    $termine  = in_array($depot->statut, ['retire', 'annule'], true);
    $reste    = (int) $depot->reste_a_payer;
    $coutTotal = $depot->consommations->sum(fn ($c) => $c->quantite * $c->cout_unitaire);
@endphp

@section('content')

@if ($errors->any())
    <div role="alert" class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm">
        @foreach ($errors->all() as $erreur)<p>{{ $erreur }}</p>@endforeach
    </div>
@endif

{{-- ============ EN-TÊTE + ACTIONS ============ --}}
<section class="relative overflow-hidden rounded-2xl p-6 mb-6 text-white shadow-lg"
         style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 55%, #78350f 130%);">
    <div aria-hidden="true" class="absolute -top-16 -left-12 w-64 h-64 rounded-full bg-amber-500/25 blur-3xl"></div>
    <div aria-hidden="true" class="absolute -bottom-20 right-0 w-72 h-72 rounded-full bg-amber-400/20 blur-3xl"></div>

    <div class="relative flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <h2 class="text-2xl font-bold tracking-tight">{{ $depot->numero_ticket }}</h2>
                <span class="{{ $badges[$depot->statut] ?? 'badge-neutral' }}">{{ $labels[$depot->statut] ?? $depot->statut }}</span>
                @if($depot->express)<span class="badge-danger">⚡ Express</span>@endif
            </div>
            <p class="mt-1.5 text-slate-300 text-sm">
                Client : <span class="font-semibold text-white">{{ $depot->client?->nom }} {{ $depot->client?->prenom }}</span>
                @if($caisse && $depot->client?->telephone) · {{ $depot->client->telephone }} @endif
            </p>
            <p class="mt-0.5 text-sm {{ $enRetard ? 'text-red-300 font-semibold' : 'text-slate-400' }}">
                Déposé le {{ $depot->date_depot?->format('d/m/Y à H:i') }} · retrait prévu le {{ $depot->date_retrait_prevue?->format('d/m/Y') }}
                @if($enRetard) — en retard @endif
            </p>
        </div>

        {{-- Boutons du workflow --}}
        <div class="flex flex-wrap gap-2.5">
            @if($depot->statut === 'depose')
                <form method="POST" action="{{ route('depots.commencer', $depot) }}">@csrf
                    <button class="inline-flex items-center gap-2 rounded-xl bg-amber-500 hover:bg-amber-400 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-amber-500/30 transition">
                        ▶ Commencer le traitement
                    </button>
                </form>
            @endif

            @if($depot->statut === 'en_traitement')
                <form method="POST" action="{{ route('depots.pret', $depot) }}">@csrf
                    <button class="inline-flex items-center gap-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-500/30 transition">
                        ✔ Marquer prêt{{ $consommationRequise ? ' et déclarer les produits' : '' }}
                    </button>
                </form>
            @endif

            @if($depot->statut === 'pret' && $caisse)
                <form method="POST" action="{{ route('depots.retirer', $depot) }}" class="flex flex-wrap items-center gap-3">@csrf
                    @if($reste > 0 && $gestionnaire)
                        <label class="flex items-center gap-2 text-xs text-slate-300 cursor-pointer">
                            <input type="checkbox" name="derogation" value="1" class="rounded border-slate-300 text-amber-500">
                            Remettre malgré le solde ({{ $f($reste) }} FCFA)
                        </label>
                    @endif
                    <button @disabled($reste > 0 && ! $gestionnaire)
                            class="inline-flex items-center gap-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-500/30 transition disabled:opacity-50 disabled:cursor-not-allowed">
                        🤝 Remettre au client
                    </button>
                </form>
                @if($reste > 0 && ! $gestionnaire)
                    <p class="w-full text-xs text-amber-300">Encaissez d'abord le solde de {{ $f($reste) }} FCFA.</p>
                @endif
            @endif

            @if($caisse)
                <a href="{{ route('depots.ticket', ['depot' => $depot, 'imprimer' => 1]) }}" target="_blank"
                   class="inline-flex items-center gap-2 rounded-xl bg-white/10 hover:bg-white/20 ring-1 ring-white/20 px-4 py-2.5 text-sm font-medium transition">
                    🖨 Ticket
                </a>
                @php $lienWhatsapp = $depot->statut === 'pret' ? $whatsappPret : $whatsappTicket; @endphp
                @if($lienWhatsapp)
                    <a href="{{ $lienWhatsapp }}" target="_blank" rel="noopener"
                       class="inline-flex items-center gap-2 rounded-xl bg-white/10 hover:bg-white/20 ring-1 ring-white/20 px-4 py-2.5 text-sm font-medium transition">
                        💬 WhatsApp
                    </a>
                @endif
                @if($depot->statut !== 'annule')
                    <a href="{{ route('depots.edit', $depot) }}"
                       class="inline-flex items-center gap-2 rounded-xl bg-white/10 hover:bg-white/20 ring-1 ring-white/20 px-4 py-2.5 text-sm font-medium transition">
                        {{ $reste > 0 ? '💰 Encaisser' : '✎ Modifier' }}
                    </a>
                @endif
            @endif

            @if($depot->statut === 'pret' && $gestionnaire)
                <form method="POST" action="{{ route('depots.rouvrir', $depot) }}" onsubmit="return confirm('Remettre ce dépôt « en traitement » ?')">@csrf
                    <button class="inline-flex items-center gap-2 rounded-xl bg-white/10 hover:bg-white/20 ring-1 ring-white/20 px-4 py-2.5 text-sm font-medium transition">↺ Rouvrir</button>
                </form>
            @endif

            @if($gestionnaire && ! $termine)
                <form method="POST" action="{{ route('depots.destroy', $depot) }}" onsubmit="return confirm('Annuler ce dépôt ?')">
                    @csrf @method('DELETE')
                    <button class="inline-flex items-center gap-2 rounded-xl bg-red-500/80 hover:bg-red-500 px-4 py-2.5 text-sm font-medium transition">Annuler le dépôt</button>
                </form>
            @endif
        </div>
    </div>
</section>

<div class="grid lg:grid-cols-3 gap-6">

    {{-- ============ COLONNE GAUCHE ============ --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Articles --}}
        <div class="card">
            <div class="card-header flex items-center justify-between">
                <h3 class="font-semibold text-slate-800">Articles ({{ $depot->articles->sum('quantite') }})</h3>
                @if($depot->observations)
                    <span class="text-xs text-slate-500 truncate max-w-xs" title="{{ $depot->observations }}">📝 {{ $depot->observations }}</span>
                @endif
            </div>
            <div class="overflow-x-auto">
                <table class="table-base">
                    <thead>
                        <tr>
                            <th>Article</th><th>Service</th><th>Qté</th>
                            @if($caisse)<th>Prix unit.</th><th>Sous-total</th>@endif
                            <th>État</th><th class="text-right"></th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($depot->articles as $article)
                        <tr>
                            <td class="font-medium text-slate-800">{{ $article->typeArticle?->nom }}</td>
                            <td>{{ $article->service?->nom }}</td>
                            <td>{{ $article->quantite }}</td>
                            @if($caisse)
                                <td>{{ $f($article->prix_unitaire) }}</td>
                                <td class="font-semibold">{{ $f($article->prix_unitaire * $article->quantite) }}</td>
                            @endif
                            <td>
                                <span class="{{ $badgesArticle[$article->statut] ?? 'badge-neutral' }}">{{ $labelsArticle[$article->statut] ?? $article->statut }}</span>
                                @if($article->observation)
                                    <p class="text-[11px] text-slate-400 mt-1 max-w-[12rem]">{{ $article->observation }}</p>
                                @endif
                            </td>
                            <td class="text-right">
                                @if(! $termine && ! in_array($article->statut, ['perdu', 'abime'], true))
                                    <details class="relative inline-block text-left">
                                        <summary class="cursor-pointer list-none text-xs font-medium text-slate-500 hover:text-red-600">⚠ Signaler</summary>
                                        <form method="POST" action="{{ route('depots.signaler', [$depot, $article]) }}"
                                              class="absolute right-0 z-10 mt-2 w-64 space-y-2 rounded-xl border border-slate-200 bg-white p-3 shadow-xl">
                                            @csrf
                                            <select name="statut" class="form-select text-xs" required>
                                                <option value="abime">Article abîmé</option>
                                                <option value="perdu">Article perdu</option>
                                            </select>
                                            <textarea name="note" rows="2" required minlength="3" maxlength="200" class="form-input text-xs resize-none"
                                                      placeholder="Décrivez le problème (obligatoire)"></textarea>
                                            <button class="btn-danger w-full justify-center text-xs">Enregistrer le signalement</button>
                                        </form>
                                    </details>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Produits utilisés --}}
        @if($depot->consommations->isNotEmpty())
            <div class="card">
                <div class="card-header flex items-center justify-between">
                    <h3 class="font-semibold text-slate-800">Produits utilisés</h3>
                    <span class="text-xs text-slate-400">Déclarés le {{ $depot->consommation_validee_at?->format('d/m/Y à H:i') }}</span>
                </div>
                <table class="table-base">
                    <thead><tr><th>Produit</th><th>Quantité</th><th>Par</th>@if($gestionnaire)<th class="text-right">Coût</th>@endif</tr></thead>
                    <tbody>
                    @foreach($depot->consommations as $c)
                        <tr>
                            <td>{{ $c->produit?->nom }}</td>
                            <td>{{ $c->quantite + 0 }} {{ $c->produit?->unite }}</td>
                            <td class="text-slate-500">{{ $c->user?->nom }}</td>
                            @if($gestionnaire)<td class="text-right">{{ $f($c->quantite * $c->cout_unitaire) }}</td>@endif
                        </tr>
                    @endforeach
                    </tbody>
                    @if($gestionnaire)
                        <tfoot>
                            <tr><td colspan="3" class="px-4 py-3 text-right font-semibold text-slate-600">Coût des produits</td>
                                <td class="px-4 py-3 text-right font-bold text-slate-800">{{ $f($coutTotal) }} FCFA</td></tr>
                            @if($caisse)
                                <tr><td colspan="3" class="px-4 py-2 text-right text-xs text-slate-400">Marge sur ce dépôt (avant autres charges)</td>
                                    <td class="px-4 py-2 text-right text-xs font-semibold text-emerald-600">{{ $f($depot->montant_total - $coutTotal) }} FCFA</td></tr>
                            @endif
                        </tfoot>
                    @endif
                </table>
            </div>
        @elseif($depot->statut === 'en_traitement' && $consommationRequise)
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                Les produits utilisés seront à déclarer quand vous cliquerez sur <strong>« Marquer prêt »</strong>.
            </div>
        @endif

        {{-- Paiements (caisse uniquement) --}}
        @if($caisse)
            <div class="card">
                <div class="card-header"><h3 class="font-semibold text-slate-800">Paiements</h3></div>
                <table class="table-base">
                    <thead><tr><th>Date</th><th>Mode</th><th>Référence</th><th>Reçu par</th><th class="text-right">Montant</th><th></th></tr></thead>
                    <tbody>
                    @forelse($depot->paiements as $p)
                        <tr>
                            <td>{{ $p->date_paiement?->format('d/m/Y H:i') }}</td>
                            <td>{{ ucwords(str_replace('_', ' ', $p->mode)) }}</td>
                            <td class="text-slate-500">{{ $p->reference ?? '—' }}</td>
                            <td class="text-slate-500">{{ $p->user?->nom }}</td>
                            <td class="text-right font-semibold">{{ $f($p->montant) }}</td>
                            <td class="text-right"><a href="{{ route('paiements.recu', ['paiement' => $p, 'imprimer' => 1]) }}" target="_blank" class="text-xs font-medium text-amber-600 hover:underline">Reçu</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-slate-400 py-6">Aucun paiement enregistré.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- ============ COLONNE DROITE ============ --}}
    <div class="space-y-6">

        @if($caisse)
            <div class="card overflow-hidden">
                <div class="h-1 bg-gradient-to-r from-amber-400 to-amber-500"></div>
                <div class="card-header"><h3 class="font-semibold text-slate-800">Récapitulatif</h3></div>
                <div class="card-body">
                    <dl class="space-y-2.5 text-sm">
                        <div class="flex justify-between"><dt class="text-slate-500">Articles</dt><dd class="font-semibold">{{ $f($depot->montant_total + $depot->remise) }}</dd></div>
                        <div class="flex justify-between"><dt class="text-slate-500">Remise</dt><dd class="font-semibold">− {{ $f($depot->remise) }}</dd></div>
                        <div class="flex justify-between border-t border-slate-100 pt-2.5"><dt class="font-semibold text-slate-700">Total</dt><dd class="font-bold text-slate-900">{{ $f($depot->montant_total) }} FCFA</dd></div>
                        <div class="flex justify-between"><dt class="text-slate-500">Payé</dt><dd class="font-semibold text-emerald-600">{{ $f($depot->montant_paye) }}</dd></div>
                        <div class="flex justify-between rounded-lg bg-slate-50 px-3 py-2">
                            <dt class="font-semibold text-slate-700">Reste à payer</dt>
                            <dd class="font-bold {{ $reste > 0 ? 'text-amber-600' : 'text-emerald-600' }}">{{ $f($reste) }} FCFA</dd>
                        </div>
                    </dl>
                </div>
            </div>
        @endif

        {{-- Historique --}}
        <div class="card">
            <div class="card-header"><h3 class="font-semibold text-slate-800">Historique</h3></div>
            <div class="card-body">
                <ol class="relative space-y-5 border-l border-slate-200 pl-5">
                    @foreach($depot->historique->reverse() as $h)
                        <li class="relative">
                            <span class="absolute -left-[26px] top-1 h-3 w-3 rounded-full border-2 border-white {{ $loop->first ? 'bg-amber-500' : 'bg-slate-300' }}"></span>
                            <p class="text-sm font-medium text-slate-800">{{ $labels[$h->statut] ?? $h->statut }}</p>
                            @if($h->note)<p class="text-xs text-slate-500">{{ $h->note }}</p>@endif
                            <p class="text-[11px] text-slate-400">{{ $h->created_at?->format('d/m/Y H:i') }} · {{ $h->user?->nom ?? 'Système' }}</p>
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>
    </div>
</div>
@endsection
