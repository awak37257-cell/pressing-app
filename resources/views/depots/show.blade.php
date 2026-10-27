@extends('layouts.app')

@section('title', 'Dépôt ' . $depot->numero_ticket)
@section('page-title', 'Dépôt ' . $depot->numero_ticket)
@section('page-subtitle', 'Détail, suivi et historique complet')

@php
    $user         = auth()->user();
    $role         = $user->role;
    $gestionnaire = in_array($role, ['admin', 'gerant'], true);
    $caisse       = in_array($role, ['admin', 'gerant', 'caissier'], true);   // voit les montants
    $f            = fn ($n) => number_format($n, 0, ',', ' ');

    $labels = ['depose' => 'Déposé', 'en_traitement' => 'En traitement', 'pret' => 'Prêt à retirer', 'retire' => 'Retiré', 'annule' => 'Annulé'];
    $badges = ['depose' => 'bg-blue-50 text-blue-700 border border-blue-200/60', 'en_traitement' => 'bg-amber-50 text-amber-700 border border-amber-200/60', 'pret' => 'bg-emerald-50 text-emerald-700 border border-emerald-200/60', 'retire' => 'bg-slate-100 text-slate-600 border border-slate-200/60', 'annule' => 'bg-rose-50 text-rose-600 border border-rose-200/60'];
    
    $labelsArticle = ['depose' => 'Déposé', 'en_traitement' => 'En traitement', 'pret' => 'Prêt', 'retire' => 'Retiré', 'perdu' => 'Perdu', 'abime' => 'Abîmé'];
    $badgesArticle = ['depose' => 'bg-blue-50 text-blue-700', 'en_traitement' => 'bg-amber-50 text-amber-700', 'pret' => 'bg-emerald-50 text-emerald-700', 'retire' => 'bg-slate-100 text-slate-600', 'perdu' => 'bg-rose-50 text-rose-700', 'abime' => 'bg-rose-50 text-rose-700'];

    $enRetard = $depot->date_retrait_prevue?->isPast() && in_array($depot->statut, ['depose', 'en_traitement'], true);
    $termine  = in_array($depot->statut, ['retire', 'annule'], true);
    $reste    = (int) $depot->reste_a_payer;
    $coutTotal = $depot->consommations->sum(fn ($c) => $c->quantite * $c->cout_unitaire);
@endphp

@section('content')

@if ($errors->any())
    <div role="alert" class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-sm flex items-start gap-3 shadow-2xs">
        <svg class="h-5 w-5 shrink-0 text-rose-500 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
        </svg>
        <div>
            <p class="font-semibold">Attention</p>
            <ul class="mt-1 list-disc list-inside text-xs space-y-0.5">
                @foreach ($errors->all() as $erreur)<li>{{ $erreur }}</li>@endforeach
            </ul>
        </div>
    </div>
@endif

{{-- ============ EN-TÊTE + ACTIONS ============ --}}
<section class="relative overflow-hidden rounded-2xl p-6 mb-6 text-white shadow-lg"
         style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 55%, #78350f 130%);">
    <div aria-hidden="true" class="absolute -top-16 -left-12 w-64 h-64 rounded-full bg-amber-500/25 blur-3xl"></div>
    <div aria-hidden="true" class="absolute -bottom-20 right-0 w-72 h-72 rounded-full bg-amber-400/20 blur-3xl"></div>

    <div class="relative flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
        <div>
            <div class="flex flex-wrap items-center gap-3">
                <h2 class="text-2xl font-bold tracking-tight">{{ $depot->numero_ticket }}</h2>
                <span class="px-3 py-1 rounded-lg text-xs font-semibold shadow-2xs {{ $badges[$depot->statut] ?? 'bg-slate-100 text-slate-700' }}">{{ $labels[$depot->statut] ?? $depot->statut }}</span>
                @if($depot->express)<span class="px-2.5 py-1 rounded-full text-xs font-extrabold bg-rose-500 text-white shadow-2xs">⚡ Express</span>@endif
            </div>
            <p class="mt-2 text-slate-200 text-sm">
                Client : <span class="font-bold text-white">{{ $depot->client?->nom }} {{ $depot->client?->prenom }}</span>
                @if($caisse && $depot->client?->telephone) <span class="text-slate-400">·</span> {{ $depot->client->telephone }} @endif
            </p>
            <p class="mt-1 text-xs {{ $enRetard ? 'text-rose-300 font-bold' : 'text-slate-400' }}">
                Déposé le {{ $depot->date_depot?->format('d/m/Y à H:i') }} · Retrait prévu le {{ $depot->date_retrait_prevue?->format('d/m/Y') }}
                @if($enRetard) <span class="bg-rose-500/20 px-2 py-0.5 rounded text-rose-200 font-bold ml-1">⚠ En retard</span> @endif
            </p>
        </div>

        {{-- Boutons du workflow --}}
        <div class="flex flex-wrap gap-2.5">
            @if($depot->statut === 'depose')
                <form method="POST" action="{{ route('depots.commencer', $depot) }}">@csrf
                    <button class="inline-flex items-center gap-2 rounded-xl bg-amber-500 hover:bg-amber-600 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-amber-500/30 transition">
                        ▶ Commencer le traitement
                    </button>
                </form>
            @endif

            @if($depot->statut === 'en_traitement')
                <form method="POST" action="{{ route('depots.pret', $depot) }}">@csrf
                    <button class="inline-flex items-center gap-2 rounded-xl bg-emerald-500 hover:bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-emerald-500/30 transition">
                        ✔ Marquer prêt{{ $consommationRequise ? ' et déclarer les produits' : '' }}
                    </button>
                </form>
            @endif

            @if($depot->statut === 'pret' && $caisse)
                <form method="POST" action="{{ route('depots.retirer', $depot) }}" class="flex flex-wrap items-center gap-3">@csrf
                    @if($reste > 0 && $gestionnaire)
                        <label class="flex items-center gap-2 text-xs text-slate-200 cursor-pointer bg-black/20 px-3 py-2 rounded-xl border border-white/10">
                            <input type="checkbox" name="derogation" value="1" class="rounded border-slate-300 text-amber-500 focus:ring-amber-400">
                            Remettre malgré le solde ({{ $f($reste) }} FCFA)
                        </label>
                    @endif
                    <button @disabled($reste > 0 && ! $gestionnaire)
                            class="inline-flex items-center gap-2 rounded-xl bg-emerald-500 hover:bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-emerald-500/30 transition disabled:opacity-50 disabled:cursor-not-allowed">
                        🤝 Remettre au client
                    </button>
                </form>
                @if($reste > 0 && ! $gestionnaire)
                    <p class="w-full text-xs text-amber-300 font-medium">Encaissez d'abord le solde de {{ $f($reste) }} FCFA avant la remise.</p>
                @endif
            @endif

            @if($caisse)
                <a href="{{ route('depots.ticket', ['depot' => $depot, 'imprimer' => 1]) }}" target="_blank"
                   class="inline-flex items-center gap-2 rounded-xl bg-white/10 hover:bg-white/20 ring-1 ring-white/20 px-4 py-2.5 text-sm font-medium transition backdrop-blur-xs">
                    🖨 Ticket
                </a>
                @php $lienWhatsapp = $depot->statut === 'pret' ? $whatsappPret : $whatsappTicket; @endphp
                @if($lienWhatsapp)
                    <a href="{{ $lienWhatsapp }}" target="_blank" rel="noopener"
                       class="inline-flex items-center gap-2 rounded-xl bg-emerald-600/40 hover:bg-emerald-600/60 ring-1 ring-emerald-400/30 px-4 py-2.5 text-sm font-medium transition backdrop-blur-xs">
                        💬 WhatsApp
                    </a>
                @endif
                @if($depot->statut !== 'annule')
                    <a href="{{ route('depots.edit', $depot) }}"
                       class="inline-flex items-center gap-2 rounded-xl bg-white/10 hover:bg-white/20 ring-1 ring-white/20 px-4 py-2.5 text-sm font-medium transition backdrop-blur-xs">
                        {{ $reste > 0 ? '💰 Encaisser' : '✎ Modifier' }}
                    </a>
                @endif
            @endif

            @if($depot->statut === 'pret' && $gestionnaire)
                <form method="POST" action="{{ route('depots.rouvrir', $depot) }}" onsubmit="return confirm('Remettre ce dépôt « en traitement » ?')">@csrf
                    <button class="inline-flex items-center gap-2 rounded-xl bg-white/10 hover:bg-white/20 ring-1 ring-white/20 px-4 py-2.5 text-sm font-medium transition backdrop-blur-xs">↺ Rouvrir</button>
                </form>
            @endif

            @if($gestionnaire && ! $termine)
                <form method="POST" action="{{ route('depots.destroy', $depot) }}" onsubmit="return confirm('Annuler ce dépôt ?')">
                    @csrf @method('DELETE')
                    <button class="inline-flex items-center gap-2 rounded-xl bg-rose-600/80 hover:bg-rose-600 px-4 py-2.5 text-sm font-medium transition shadow-2xs">Annuler le dépôt</button>
                </form>
            @endif
        </div>
    </div>
</section>

<div class="grid lg:grid-cols-3 gap-6">

    {{-- ============ COLONNE GAUCHE ============ --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Articles --}}
        <div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
            <div class="card-header px-6 py-4.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h3 class="font-bold text-slate-800 text-base">Articles déposés ({{ $depot->articles->sum('quantite') })</h3>
                @if($depot->observations)
                    <span class="text-xs text-slate-500 truncate max-w-xs bg-slate-100 px-2.5 py-1 rounded-lg" title="{{ $depot->observations }}">📝 {{ $depot->observations }}</span>
                @endif
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 text-slate-400 uppercase text-[11px] font-bold tracking-wider border-b border-slate-200/60">
                            <th class="py-3 px-6">Article</th>
                            <th class="py-3 px-6">Service</th>
                            <th class="py-3 px-6">Qté</th>
                            @if($caisse)
                                <th class="py-3 px-6 text-right">Prix unit.</th>
                                <th class="py-3 px-6 text-right">Sous-total</th>
                            @endif
                            <th class="py-3 px-6">État</th>
                            <th class="py-3 px-6 text-right"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                    @foreach($depot->articles as $article)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="py-3.5 px-6 font-semibold text-slate-800">{{ $article->typeArticle?->nom }}</td>
                            <td class="py-3.5 px-6 text-slate-600">{{ $article->service?->nom }}</td>
                            <td class="py-3.5 px-6 font-medium text-slate-800">{{ $article->quantite }}</td>
                            @if($caisse)
                                <td class="py-3.5 px-6 text-right text-slate-600">{{ $f($article->prix_unitaire) }}</td>
                                <td class="py-3.5 px-6 text-right font-bold text-slate-800">{{ $f($article->prix_unitaire * $article->quantite) }}</td>
                            @endif
                            <td class="py-3.5 px-6">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-semibold {{ $badgesArticle[$article->statut] ?? 'bg-slate-100 text-slate-600' }}">{{ $labelsArticle[$article->statut] ?? $article->statut }}</span>
                                @if($article->observation)
                                    <p class="text-[11px] text-slate-400 mt-1 max-w-[12rem]">{{ $article->observation }}</p>
                                @endif
                            </td>
                            <td class="py-3.5 px-6 text-right">
                                @if(! $termine && ! in_array($article->statut, ['perdu', 'abime'], true))
                                    <details class="relative inline-block text-left">
                                        <summary class="cursor-pointer list-none text-xs font-semibold text-slate-500 hover:text-rose-600 transition">⚠ Signaler</summary>
                                        <form method="POST" action="{{ route('depots.signaler', [$depot, $article]) }}"
                                              class="absolute right-0 z-10 mt-2 w-64 space-y-2.5 rounded-2xl border border-slate-200 bg-white p-4 shadow-xl">
                                            @csrf
                                            <select name="statut" class="form-select w-full rounded-xl border-slate-200 text-xs" required>
                                                <option value="abime">Article abîmé</option>
                                                <option value="perdu">Article perdu</option>
                                            </select>
                                            <textarea name="note" rows="2" required minlength="3" maxlength="200" class="form-input w-full rounded-xl border-slate-200 text-xs resize-none"
                                                      placeholder="Décrivez le problème (obligatoire)"></textarea>
                                            <button class="w-full bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold py-2 rounded-xl transition shadow-2xs">Enregistrer le signalement</button>
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
            <div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
                <div class="card-header px-6 py-4.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <h3 class="font-bold text-slate-800 text-base">Produits utilisés (Atelier)</h3>
                    <span class="text-xs text-slate-400 font-medium">Déclarés le {{ $depot->consommation_validee_at?->format('d/m/Y à H:i') }}</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 text-slate-400 uppercase text-[11px] font-bold tracking-wider border-b border-slate-200/60">
                                <th class="py-3 px-6">Produit</th>
                                <th class="py-3 px-6">Quantité</th>
                                <th class="py-3 px-6">Par</th>
                                @if($gestionnaire)<th class="py-3 px-6 text-right">Coût</th>@endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                        @foreach($depot->consommations as $c)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-3.5 px-6 font-medium text-slate-800">{{ $c->produit?->nom }}</td>
                                <td class="py-3.5 px-6 text-slate-600">{{ $c->quantite + 0 }} {{ $c->produit?->unite }}</td>
                                <td class="py-3.5 px-6 text-slate-500">{{ $c->user?->nom }}</td>
                                @if($gestionnaire)<td class="py-3.5 px-6 text-right font-medium text-slate-700">{{ $f($c->quantite * $c->cout_unitaire) }}</td>@endif
                            </tr>
                        @endforeach
                        </tbody>
                        @if($gestionnaire)
                            <tfoot class="bg-slate-50/50 border-t border-slate-200/60">
                                <tr>
                                    <td colspan="3" class="px-6 py-3 text-right font-semibold text-slate-600 text-xs uppercase">Coût total des produits</td>
                                    <td class="px-6 py-3 text-right font-bold text-slate-800">{{ $f($coutTotal) }} FCFA</td>
                                </tr>
                                @if($caisse)
                                    <tr>
                                        <td colspan="3" class="px-6 py-2 text-right text-xs text-slate-400">Marge brute estimée sur ce dépôt</td>
                                        <td class="px-6 py-2 text-right text-xs font-bold text-emerald-600">{{ $f($depot->montant_total - $coutTotal) }} FCFA</td>
                                    </tr>
                                @endif
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        @elseif($depot->statut === 'en_traitement' && $consommationRequise)
            <div class="rounded-2xl border border-amber-200 bg-amber-50/80 p-4 text-sm text-amber-800 flex items-center gap-3">
                <span class="text-lg">💡</span>
                <span>Les produits utilisés seront à déclarer lorsque vous cliquerez sur <strong>« Marquer prêt »</strong>.</span>
            </div>
        @endif

        {{-- Paiements --}}
        @if($caisse)
            <div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
                <div class="card-header px-6 py-4.5 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="font-bold text-slate-800 text-base">Historique des paiements</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 text-slate-400 uppercase text-[11px] font-bold tracking-wider border-b border-slate-200/60">
                                <th class="py-3 px-6">Date</th>
                                <th class="py-3 px-6">Mode</th>
                                <th class="py-3 px-6">Référence</th>
                                <th class="py-3 px-6">Reçu par</th>
                                <th class="py-3 px-6 text-right">Montant</th>
                                <th class="py-3 px-6"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse($depot->paiements as $p)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-3.5 px-6 text-slate-500 text-xs">{{ $p->date_paiement?->format('d/m/Y H:i') }}</td>
                                <td class="py-3.5 px-6 font-medium text-slate-800">{{ ucwords(str_replace('_', ' ', $p->mode)) }}</td>
                                <td class="py-3.5 px-6 text-slate-500 font-mono text-xs">{{ $p->reference ?? '—' }}</td>
                                <td class="py-3.5 px-6 text-slate-500">{{ $p->user?->nom }}</td>
                                <td class="py-3.5 px-6 text-right font-bold text-emerald-600">{{ $f($p->montant) }} FCFA</td>
                                <td class="py-3.5 px-6 text-right"><a href="{{ route('paiements.recu', ['paiement' => $p, 'imprimer' => 1]) }}" target="_blank" class="text-xs font-semibold text-amber-600 hover:underline">Reçu 🖨</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-slate-400 py-8 text-xs italic">Aucun paiement enregistré pour le moment.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    {{-- ============ COLONNE DROITE ============ --}}
    <div class="space-y-6">

        @if($caisse)
            <div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
                <div class="h-1.5 bg-gradient-to-r from-amber-400 to-amber-500"></div>
                <div class="card-header px-6 py-4.5 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="font-bold text-slate-800 text-base">Récapitulatif financier</h3>
                </div>
                <div class="card-body p-6">
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between text-slate-600"><dt>Articles</dt><dd class="font-semibold text-slate-800">{{ $f($depot->montant_total + $depot->remise) }}</dd></div>
                        <div class="flex justify-between text-slate-600"><dt>Remise</dt><dd class="font-semibold text-rose-600">− {{ $f($depot->remise) }}</dd></div>
                        <div class="flex justify-between border-t border-slate-100 pt-3"><dt class="font-bold text-slate-800">Total net</dt><dd class="font-bold text-slate-900 text-base">{{ $f($depot->montant_total) }} FCFA</dd></div>
                        <div class="flex justify-between text-slate-600"><dt>Déjà payé</dt><dd class="font-semibold text-emerald-600">{{ $f($depot->montant_paye) }}</dd></div>
                        <div class="flex justify-between items-center rounded-xl bg-slate-50 border border-slate-200/80 px-4 py-3 mt-2">
                            <dt class="font-bold text-slate-700">Reste à payer</dt>
                            <dd class="font-extrabold text-base {{ $reste > 0 ? 'text-amber-600' : 'text-emerald-600' }}">{{ $f($reste) }} FCFA</dd>
                        </div>
                    </dl>
                </div>
            </div>
        @endif

        {{-- Historique --}}
        <div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
            <div class="card-header px-6 py-4.5 border-b border-slate-100 bg-slate-50/50">
                <h3 class="font-bold text-slate-800 text-base">Historique des statuts</h3>
            </div>
            <div class="card-body p-6">
                <ol class="relative space-y-6 border-l-2 border-slate-200 pl-5 ml-2">
                    @foreach($depot->historique->reverse() as $h)
                        <li class="relative">
                            <span class="absolute -left-[27px] top-1 h-3.5 w-3.5 rounded-full border-2 border-white {{ $loop->first ? 'bg-amber-500 shadow-sm ring-4 ring-amber-500/20' : 'bg-slate-300' }}"></span>
                            <p class="text-sm font-bold text-slate-800">{{ $labels[$h->statut] ?? $h->statut }}</p>
                            @if($h->note)<p class="text-xs text-slate-500 mt-0.5">{{ $h->note }}</p>@endif
                            <p class="text-[11px] text-slate-400 mt-1">{{ $h->created_at?->format('d/m/Y à H:i') }} · <span class="font-medium text-slate-600">{{ $h->user?->nom ?? 'Système' }}</span></p>
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>
    </div>
</div>
@endsection