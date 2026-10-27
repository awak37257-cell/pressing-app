@extends('layouts.app')

@section('title', 'Tableau de bord')
@section('page-title', 'Tableau de bord')
@section('page-subtitle', 'Vue d\'ensemble de votre activité en temps réel')

@php
    $user         = auth()->user();
    $operateur    = $user->role === 'operateur';
    $gestionnaire = in_array($user->role, ['admin', 'gerant'], true);
    $caisse       = ! $operateur;                    // voit les montants
    $salutation   = now()->hour < 18 ? 'Bonjour' : 'Bonsoir';
    $prenom       = \Illuminate\Support\Str::of($user->nom)->before(' ');
    $f            = fn ($n) => number_format($n, 0, ',', ' ');

    // Cartes de chiffres : l'opérateur ne voit aucun montant
    if ($operateur) {
        $kpis = [
            ['label' => 'À commencer', 'value' => $nbADemarrer, 'sub' => 'dépôts en attente', 'valueClass' => 'text-slate-800',
             'bar' => 'from-blue-400 to-blue-500', 'iconBg' => 'bg-blue-50', 'iconText' => 'text-blue-500',
             'icon' => 'M5 3l14 9-14 9V3z'],
            ['label' => 'En traitement', 'value' => $nbEnCours, 'sub' => 'en cours de lavage', 'valueClass' => 'text-slate-800',
             'bar' => 'from-amber-400 to-amber-500', 'iconBg' => 'bg-amber-50', 'iconText' => 'text-amber-500',
             'icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15'],
            ['label' => 'Prêts à retirer', 'value' => $nbPrets, 'sub' => 'à remettre aux clients', 'valueClass' => 'text-emerald-600',
             'bar' => 'from-emerald-400 to-emerald-500', 'iconBg' => 'bg-emerald-50', 'iconText' => 'text-emerald-500',
             'icon' => 'M5 13l4 4L19 7'],
            ['label' => 'En retard', 'value' => $nbEnRetard, 'sub' => 'retrait prévu dépassé', 'valueClass' => $nbEnRetard > 0 ? 'text-rose-600' : 'text-slate-800',
             'bar' => 'from-rose-400 to-rose-600', 'iconBg' => 'bg-rose-50', 'iconText' => 'text-rose-600',
             'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        ];
    } else {
        $kpis = [
            ['label' => "Dépôts aujourd'hui", 'value' => $depotsDuJour, 'sub' => $depotsMois . ' ce mois-ci', 'valueClass' => 'text-slate-800',
             'bar' => 'from-blue-400 to-blue-500', 'iconBg' => 'bg-blue-50', 'iconText' => 'text-blue-500',
             'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
            ['label' => 'Recette du jour', 'value' => $f($chiffreAffaireJour), 'sub' => 'FCFA encaissés', 'valueClass' => 'text-emerald-600',
             'bar' => 'from-emerald-400 to-emerald-500', 'iconBg' => 'bg-emerald-50', 'iconText' => 'text-emerald-500',
             'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['label' => 'Reste à payer', 'value' => $f($resteAPayer), 'sub' => 'FCFA en attente', 'valueClass' => 'text-amber-600',
             'bar' => 'from-amber-400 to-amber-500', 'iconBg' => 'bg-amber-50', 'iconText' => 'text-amber-500',
             'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
            $gestionnaire
                ? ['label' => 'Dépenses du jour', 'value' => $f($depensesJour), 'sub' => $f($depensesMois) . ' ce mois', 'valueClass' => 'text-rose-600',
                   'bar' => 'from-rose-400 to-rose-600', 'iconBg' => 'bg-rose-50', 'iconText' => 'text-rose-600',
                   'icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z']
                : ['label' => 'Prêts à retirer', 'value' => $nbPrets, 'sub' => 'à remettre aux clients', 'valueClass' => 'text-emerald-600',
                   'bar' => 'from-teal-400 to-emerald-500', 'iconBg' => 'bg-teal-50', 'iconText' => 'text-teal-600',
                   'icon' => 'M5 13l4 4L19 7'],
        ];
    }

    // Graphique 7 jours
    $maxRecette = max(1, (float) $derniers7Jours->max('recette'));
    $dernierIdx = $derniers7Jours->count() - 1;

    // Statuts
    $statutsConfig = [
        'depose'        => ['label' => 'Déposé',   'class' => 'bg-blue-500'],
        'en_traitement' => ['label' => 'En cours', 'class' => 'bg-amber-500'],
        'pret'          => ['label' => 'Prêt',     'class' => 'bg-emerald-500'],
        'retire'        => ['label' => 'Retiré',   'class' => 'bg-slate-400'],
        'annule'        => ['label' => 'Annulé',   'class' => 'bg-rose-500'],
    ];
    $totalDepots = (int) $statsByStatut->sum('count');
@endphp

@section('content')

{{-- ============ BANDEAU D'ACCUEIL ============ --}}
<section class="relative overflow-hidden rounded-2xl p-6 sm:p-8 mb-6 text-white shadow-lg"
         style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 55%, #78350f 130%);">
    <div aria-hidden="true" class="absolute -top-20 -left-16 w-72 h-72 rounded-full bg-amber-500/25 blur-3xl"></div>
    <div aria-hidden="true" class="absolute -bottom-24 right-0 w-80 h-80 rounded-full bg-amber-400/20 blur-3xl"></div>
    <div aria-hidden="true" class="absolute inset-0 opacity-[0.07]"
         style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 22px 22px;"></div>

    <div class="relative flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
        <div>
            <p class="text-xs font-bold tracking-wider text-amber-300 uppercase">{{ now()->locale('fr')->isoFormat('dddd D MMMM YYYY') }}</p>
            <h2 class="mt-1 text-2xl sm:text-3xl font-extrabold tracking-tight">{{ $salutation }}, {{ $prenom }} 👋</h2>
            <p class="mt-2 text-slate-300 text-sm">
                @if($operateur)
                    <span class="font-bold text-white">{{ $nbADemarrer }}</span> dépôt{{ $nbADemarrer > 1 ? 's' : '' }} à commencer,
                    <span class="font-bold text-white">{{ $nbEnCours }}</span> en cours de traitement.
                @else
                    @if($depotsDuJour > 0)
                        Déjà <span class="font-bold text-white">{{ $depotsDuJour }}</span> dépôt{{ $depotsDuJour > 1 ? 's' : '' }} enregistré{{ $depotsDuJour > 1 ? 's' : '' }} aujourd'hui.
                    @else
                        Aucun dépôt enregistré pour l'instant aujourd'hui.
                    @endif
                    @if($nbPrets > 0)
                        <span class="font-bold text-emerald-300 ml-1">{{ $nbPrets }}</span> prêt{{ $nbPrets > 1 ? 's' : '' }} à remettre aux clients.
                    @endif
                @endif
            </p>
        </div>

        <div class="flex flex-wrap gap-2.5">
            @if($operateur)
                <a href="{{ route('traitement.index') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-amber-500 hover:bg-amber-600 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-amber-500/30 transition">
                    Ouvrir la liste « À traiter »
                </a>
            @else
                <a href="{{ route('depots.create') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-amber-500 hover:bg-amber-600 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-amber-500/30 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Nouveau dépôt
                </a>
                <a href="{{ route('traitement.index') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-white/10 hover:bg-white/20 ring-1 ring-white/20 px-4 py-2.5 text-sm font-medium transition backdrop-blur-xs">
                    À traiter
                </a>
                <a href="{{ route('clients.create') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-white/10 hover:bg-white/20 ring-1 ring-white/20 px-4 py-2.5 text-sm font-medium transition backdrop-blur-xs">
                    Nouveau client
                </a>
            @endif
            @if($gestionnaire)
                <a href="{{ route('achats.create') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-white/10 hover:bg-white/20 ring-1 ring-white/20 px-4 py-2.5 text-sm font-medium transition backdrop-blur-xs">
                    Nouvel achat
                </a>
                <a href="{{ route('depenses.create') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-white/10 hover:bg-white/20 ring-1 ring-white/20 px-4 py-2.5 text-sm font-medium transition backdrop-blur-xs">
                    Nouvelle dépense
                </a>
            @endif
            <a href="{{ route('produits.index') }}"
               class="inline-flex items-center gap-2 rounded-xl bg-white/10 hover:bg-white/20 ring-1 ring-white/20 px-4 py-2.5 text-sm font-medium transition backdrop-blur-xs">
                Stocks
            </a>
        </div>
    </div>
</section>

{{-- ============ CARTES DE CHIFFRES ============ --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @foreach($kpis as $k)
        <div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs relative transition hover:-translate-y-0.5 hover:shadow-md">
            <span aria-hidden="true" class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r {{ $k['bar'] }}"></span>
            <div class="card-body p-5 flex items-start justify-between">
                <div class="min-w-0">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ $k['label'] }}</p>
                    <p class="text-2xl font-extrabold {{ $k['valueClass'] }} mt-1 truncate">{{ $k['value'] }}</p>
                    <p class="text-xs text-slate-400 mt-1">{{ $k['sub'] }}</p>
                </div>
                <div class="w-11 h-11 shrink-0 rounded-xl {{ $k['iconBg'] }} flex items-center justify-center shadow-2xs">
                    <svg class="w-5 h-5 {{ $k['iconText'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $k['icon'] }}"/>
                    </svg>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="grid grid-cols-3 gap-6">

    {{-- ============ COLONNE GAUCHE ============ --}}
    <div class="col-span-3 lg:col-span-2 space-y-6">

        {{-- Activité 7 jours (montants : pas pour l'opérateur) --}}
        @if($caisse)
            <div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
                <div class="card-header px-6 py-4.5 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3 bg-slate-50/50">
                    <div>
                        <h3 class="font-bold text-slate-800 text-base">Activité des 7 derniers jours</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Hauteur des barres : recette · Chiffre : nombre de dépôts</p>
                    </div>
                    <div class="flex gap-5 text-right bg-white px-4 py-2 rounded-xl border border-slate-200/60 shadow-2xs">
                        <div>
                            <p class="text-[10px] uppercase font-bold text-slate-400">Recette</p>
                            <p class="text-xs font-extrabold text-slate-800">{{ $f($derniers7Jours->sum('recette')) }} <span class="font-normal text-slate-400 text-[10px]">FCFA</span></p>
                        </div>
                        <div class="border-l border-slate-100 pl-4">
                            <p class="text-[10px] uppercase font-bold text-slate-400">Dépôts</p>
                            <p class="text-xs font-extrabold text-slate-800">{{ $derniers7Jours->sum('depots') }}</p>
                        </div>
                    </div>
                </div>
                <div class="card-body p-6">
                    <div class="flex items-end gap-3 h-48 border-b border-slate-100 pb-2">
                        @foreach($derniers7Jours as $i => $jour)
                            @php
                                $h = max(8, (int) round($jour['recette'] / $maxRecette * 120));
                                $aujourdhui = $i === $dernierIdx;
                            @endphp
                            <div class="group flex-1 flex flex-col items-center justify-end gap-1.5 h-full">
                                <span class="text-[11px] font-bold {{ $aujourdhui ? 'text-amber-600' : 'text-slate-500' }}">{{ $jour['depots'] }}</span>
                                <div class="w-full rounded-t-xl transition-all duration-300 shadow-2xs
                                        {{ $aujourdhui ? 'bg-gradient-to-t from-amber-500 to-amber-300 shadow-amber-500/30 ring-2 ring-amber-400/20' : 'bg-gradient-to-t from-slate-200 to-slate-100 group-hover:from-amber-400 group-hover:to-amber-300' }}"
                                     style="height: {{ $h }}px;"
                                     title="{{ $f($jour['recette']) }} FCFA"></div>
                            </div>
                        @endforeach
                    </div>
                    <div class="flex gap-3 mt-3">
                        @foreach($derniers7Jours as $i => $jour)
                            <span class="flex-1 text-center text-xs {{ $i === $dernierIdx ? 'font-bold text-amber-600' : 'text-slate-400 font-medium' }}">{{ $jour['date'] }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- Derniers dépôts --}}
        <div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
            <div class="card-header px-6 py-4.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h3 class="font-bold text-slate-800 text-base">Derniers dépôts</h3>
                <a href="{{ $operateur ? route('traitement.index') : route('depots.index') }}" class="text-xs font-semibold text-amber-600 hover:text-amber-700 hover:underline">Voir tout →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 text-slate-400 uppercase text-[11px] font-bold tracking-wider border-b border-slate-200/60">
                            <th class="py-3.5 px-6">N° Ticket</th>
                            <th class="py-3.5 px-6">Client</th>
                            <th class="py-3.5 px-6">Statut</th>
                            <th class="py-3.5 px-6">Retrait prévu</th>
                            @if($caisse)
                                <th class="py-3.5 px-6 text-right">Montant</th>
                                <th class="py-3.5 px-6 text-right">Reste</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse($depotsRecents as $depot)
                            @php
                                $badgeClass = match($depot->statut) {
                                    'depose'        => 'bg-blue-50 text-blue-700 border border-blue-200/60',
                                    'en_traitement' => 'bg-amber-50 text-amber-700 border border-amber-200/60',
                                    'pret'          => 'bg-emerald-50 text-emerald-700 border border-emerald-200/60',
                                    'annule'        => 'bg-rose-50 text-rose-600 border border-rose-200/60',
                                    default         => 'bg-slate-100 text-slate-600',
                                };
                                $labelStatut = match($depot->statut) {
                                    'depose'        => 'Déposé',
                                    'en_traitement' => 'En cours',
                                    'pret'          => 'Prêt',
                                    'retire'        => 'Retiré',
                                    'annule'        => 'Annulé',
                                    default         => $depot->statut,
                                };
                                $enRetard = $depot->date_retrait_prevue?->isPast()
                                    && in_array($depot->statut, ['depose', 'en_traitement'], true);
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-4 px-6 font-semibold">
                                    <a href="{{ route('depots.show', $depot) }}" class="text-amber-600 hover:text-amber-700 hover:underline font-bold">
                                        {{ $depot->numero_ticket }}
                                    </a>
                                    @if($depot->express)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-700 ml-1.5 uppercase shadow-2xs">⚡ Express</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-7 h-7 shrink-0 rounded-xl bg-slate-100 text-slate-600 text-xs font-bold flex items-center justify-center uppercase">
                                            {{ mb_substr($depot->client->nom ?? '?', 0, 1) }}
                                        </span>
                                        <span class="font-medium text-slate-800 truncate">{{ $depot->client->nom ?? '—' }}</span>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold {{ $badgeClass }}">
                                        {{ $labelStatut }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-xs {{ $enRetard ? 'text-rose-600 font-bold' : 'text-slate-500' }}">
                                    {{ $depot->date_retrait_prevue?->format('d/m/Y') ?? '—' }}
                                    @if($enRetard)<span class="block text-[10px] text-rose-500 font-bold">⚠ En retard</span>@endif
                                </td>
                                @if($caisse)
                                    <td class="py-4 px-6 text-right font-bold text-slate-800">{{ $f($depot->montant_total) }}</td>
                                    <td class="py-4 px-6 text-right font-bold {{ $depot->reste_a_payer > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                                        {{ $f($depot->reste_a_payer) }}
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $caisse ? 6 : 4 }}" class="py-12">
                                    <div class="flex flex-col items-center text-center">
                                        <span class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mb-3 text-slate-400">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                        </span>
                                        <p class="text-sm font-medium text-slate-600">Aucun dépôt enregistré</p>
                                        @if($caisse)
                                            <a href="{{ route('depots.create') }}" class="mt-1 text-xs font-semibold text-amber-600 hover:underline">Créer le premier dépôt →</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ============ COLONNE DROITE ============ --}}
    <div class="col-span-3 lg:col-span-1 space-y-6">

        {{-- Répartition par statut --}}
        <div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
            <div class="card-header px-6 py-4.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h3 class="font-bold text-slate-800 text-base">Statuts ce mois</h3>
                <span class="text-xs font-bold text-slate-500 bg-slate-100 px-2.5 py-1 rounded-lg">{{ $totalDepots }} dépôt(s)</span>
            </div>
            <div class="card-body p-6">
                <div class="flex h-3 rounded-full overflow-hidden bg-slate-100 mb-5 shadow-inner">
                    @foreach($statutsConfig as $statut => $cfg)
                        @php $n = (int) ($statsByStatut->get($statut)?->count ?? 0); @endphp
                        @if($n > 0 && $totalDepots > 0)
                            <div class="{{ $cfg['class'] }} h-full transition-all" style="width: {{ $n / $totalDepots * 100 }}%" title="{{ $cfg['label'] }} : {{ $n }}"></div>
                        @endif
                    @endforeach
                </div>
                <ul class="space-y-3">
                    @foreach($statutsConfig as $statut => $cfg)
                        @php
                            $n   = (int) ($statsByStatut->get($statut)?->count ?? 0);
                            $pct = $totalDepots > 0 ? round($n / $totalDepots * 100) : 0;
                        @endphp
                        <li class="flex items-center justify-between text-sm">
                            <span class="flex items-center gap-2.5 text-slate-600 font-medium">
                                <span class="w-3 h-3 rounded-full {{ $cfg['class'] }} shadow-2xs"></span>{{ $cfg['label'] }}
                            </span>
                            <span class="font-bold text-slate-800">{{ $n }} <span class="text-xs font-normal text-slate-400">· {{ $pct }}%</span></span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- Alertes stock --}}
        <div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
            <div class="card-header px-6 py-4.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h3 class="font-bold text-slate-800 text-base">⚠️ Alertes stock</h3>
                <a href="{{ route('produits.index', ['alerte' => 1]) }}" class="text-xs font-semibold text-amber-600 hover:underline">Voir tout →</a>
            </div>
            <div class="card-body p-6 space-y-4">
                @forelse($produitsEnAlerte as $produit)
                    @php
                        $stock  = (float) $produit->stock_actuel;
                        $seuil  = (float) $produit->seuil_alerte;
                        $niveau = $seuil > 0 ? min(100, round($stock / $seuil * 100)) : 0;
                    @endphp
                    <div>
                        <div class="flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-slate-800 truncate">{{ $produit->nom }}</p>
                                <p class="text-xs text-slate-400">{{ $produit->categorie?->nom ?? '—' }}</p>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-sm font-extrabold text-rose-600">{{ $stock + 0 }} {{ $produit->unite }}</p>
                                <p class="text-xs text-slate-400">Seuil : {{ $seuil + 0 }}</p>
                            </div>
                        </div>
                        <div class="mt-2 h-2 bg-slate-100 rounded-full overflow-hidden shadow-inner">
                            <div class="h-full rounded-full {{ $niveau < 50 ? 'bg-rose-500' : 'bg-amber-500' }}" style="width: {{ max(6, $niveau) }}%"></div>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center py-6 text-center">
                        <span class="w-12 h-12 rounded-full bg-emerald-50 flex items-center justify-center mb-2 shadow-2xs">
                            <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </span>
                        <p class="text-sm font-semibold text-slate-600">Stock en parfaite santé</p>
                        <p class="text-xs text-slate-400 mt-0.5">Aucun produit sous le seuil d'alerte.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Achats récents (gérants uniquement) --}}
        @if($gestionnaire)
            <div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
                <div class="card-header px-6 py-4.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <h3 class="font-bold text-slate-800 text-base">Achats récents</h3>
                    <a href="{{ route('achats.index') }}" class="text-xs font-semibold text-amber-600 hover:underline">Voir tout →</a>
                </div>
                <div class="card-body p-6 space-y-3.5">
                    @forelse($achatsRecents as $achat)
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <div class="min-w-0">
                                <p class="font-bold text-slate-800 truncate">{{ $achat->fournisseur?->nom ?? 'Inconnu' }}</p>
                                <p class="text-xs text-slate-400">{{ $achat->date_achat->format('d/m/Y') }}</p>
                            </div>
                            <span class="font-extrabold text-slate-900 shrink-0">{{ $f($achat->montant_total) }} <span class="text-xs font-normal text-slate-500">FCFA</span></span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 py-4 text-center italic">Aucun achat récent enregistré</p>
                    @endforelse
                </div>
            </div>
        @endif

    </div>
</div>

@endsection