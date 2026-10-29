@extends('layouts.app')

@section('title', 'Tableau de bord')
@section('page-title', 'Tableau de bord')
@section('page-subtitle', 'Vue d\'ensemble de votre activité en temps réel')

@php
    $user         = auth()->user();
    $operateur    = $user->role === 'operateur';
    $gestionnaire = in_array($user->role, ['admin', 'gerant'], true);
    $caisse       = ! $operateur;
    $salutation   = now()->hour < 18 ? 'Bonjour' : 'Bonsoir';
    $prenom       = \Illuminate\Support\Str::of($user->nom)->before(' ');
    $f            = fn ($n) => number_format($n, 0, ',', ' ');

    if ($operateur) {
        $kpis = [
            ['label' => 'À commencer', 'value' => $nbADemarrer, 'sub' => 'dépôts en attente',
             'valueClass' => 'text-white', 'gradient' => 'from-blue-600 via-blue-600 to-indigo-700',
             'glow' => 'bg-blue-400/30', 'icon' => 'M5 3l14 9-14 9V3z', 'trend' => 'En attente'],
            ['label' => 'En traitement', 'value' => $nbEnCours, 'sub' => 'en cours de lavage',
             'valueClass' => 'text-white', 'gradient' => 'from-amber-500 via-orange-500 to-orange-600',
             'glow' => 'bg-yellow-300/30', 'icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15', 'trend' => 'En cours'],
            ['label' => 'Prêts à retirer', 'value' => $nbPrets, 'sub' => 'à remettre aux clients',
             'valueClass' => 'text-white', 'gradient' => 'from-emerald-600 via-emerald-600 to-teal-700',
             'glow' => 'bg-teal-400/30', 'icon' => 'M5 13l4 4L19 7', 'trend' => 'Prêt'],
            ['label' => 'En retard', 'value' => $nbEnRetard, 'sub' => 'retrait prévu dépassé',
             'valueClass' => 'text-white', 'gradient' => 'from-rose-600 via-rose-600 to-pink-700',
             'glow' => 'bg-pink-400/30', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'trend' => 'Critique'],
        ];
    } else {
        $kpis = [
            ['label' => "Dépôts aujourd'hui", 'value' => $depotsDuJour, 'sub' => $depotsMois . ' ce mois-ci',
             'valueClass' => 'text-white', 'gradient' => 'from-slate-900 via-slate-800 to-slate-900',
             'glow' => 'bg-blue-500/25', 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', 'trend' => 'Aujourd\'hui'],
            ['label' => 'Recette du jour', 'value' => $f($chiffreAffaireJour), 'sub' => 'FCFA encaissés',
             'valueClass' => 'text-white', 'gradient' => 'from-emerald-600 via-emerald-600 to-teal-700',
             'glow' => 'bg-teal-400/30', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'trend' => 'Encaissé'],
            ['label' => 'Reste à payer', 'value' => $f($resteAPayer), 'sub' => 'FCFA en attente',
             'valueClass' => 'text-white', 'gradient' => 'from-amber-500 via-orange-500 to-orange-600',
             'glow' => 'bg-yellow-300/30', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'trend' => 'En attente'],
            $gestionnaire
                ? ['label' => 'Dépenses du jour', 'value' => $f($depensesJour), 'sub' => $f($depensesMois) . ' ce mois',
                   'valueClass' => 'text-white', 'gradient' => 'from-rose-600 via-rose-600 to-pink-700',
                   'glow' => 'bg-pink-400/25', 'icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z', 'trend' => 'Sortie']
                : ['label' => 'Prêts à retirer', 'value' => $nbPrets, 'sub' => 'à remettre aux clients',
                   'valueClass' => 'text-white', 'gradient' => 'from-teal-500 via-emerald-500 to-emerald-600',
                   'glow' => 'bg-teal-400/25', 'icon' => 'M5 13l4 4L19 7', 'trend' => 'Prêt'],
        ];
    }

    $maxRecette = max(1, (float) $derniers7Jours->max('recette'));
    $dernierIdx = $derniers7Jours->count() - 1;

    $statutsConfig = [
        'depose'        => ['label' => 'Déposé',   'class' => 'bg-blue-500',    'text' => 'text-blue-600'],
        'en_traitement' => ['label' => 'En cours', 'class' => 'bg-amber-500',   'text' => 'text-amber-600'],
        'pret'          => ['label' => 'Prêt',     'class' => 'bg-emerald-500', 'text' => 'text-emerald-600'],
        'retire'        => ['label' => 'Retiré',   'class' => 'bg-slate-400',   'text' => 'text-slate-500'],
        'annule'        => ['label' => 'Annulé',   'class' => 'bg-rose-500',    'text' => 'text-rose-600'],
    ];
    $totalDepots = (int) $statsByStatut->sum('count');
@endphp

@section('content')

<style>
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(10px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes pulseRing {
        0%   { transform: scale(1); opacity: 0.6; }
        70%  { transform: scale(1.8); opacity: 0; }
        100% { transform: scale(1.8); opacity: 0; }
    }
    @keyframes float {
        0%,100% { transform: translateY(0); }
        50% { transform: translateY(-8px); }
    }
    @keyframes growBar {
        from { height: 0; opacity: 0; }
        to   { opacity: 1; }
    }
    .stat-card { animation: fadeInUp 0.5s ease-out backwards; }
    .stat-card:nth-child(1) { animation-delay: 0.05s; }
    .stat-card:nth-child(2) { animation-delay: 0.10s; }
    .stat-card:nth-child(3) { animation-delay: 0.15s; }
    .stat-card:nth-child(4) { animation-delay: 0.20s; }
    .float-orb { animation: float 8s ease-in-out infinite; }
    .float-orb-2 { animation: float 10s ease-in-out infinite; animation-delay: 2s; }
    .ring-pulse::before {
        content: ''; position: absolute; inset: 0; border-radius: 9999px;
        background: currentColor; animation: pulseRing 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
    .bar-grow { animation: growBar 0.8s cubic-bezier(.2,.7,.2,1) backwards; }
    .table-row { transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
    .table-row:hover { background: linear-gradient(90deg, #fffbeb 0%, #ffffff 40%, #ffffff 100%); }
    .table-row:hover .row-avatar { transform: scale(1.1) rotate(-3deg); }
    .gradient-border { position: relative; background: white; }
    .gradient-border::before {
        content: ''; position: absolute; inset: 0; padding: 1px; border-radius: inherit;
        background: linear-gradient(135deg, rgba(251,191,36,0.3), rgba(245,158,11,0.1), rgba(251,191,36,0.3));
        -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor; mask-composite: exclude; pointer-events: none;
    }
    @media (prefers-reduced-motion: reduce) {
        .stat-card, .float-orb, .float-orb-2, .bar-grow, .ring-pulse::before { animation: none !important; }
    }
</style>

{{-- ═══════════════════════════════════════════════════════════════
     HERO BANNER — Cinématographique
     ═══════════════════════════════════════════════════════════════ --}}
<section class="relative overflow-hidden rounded-2xl p-6 sm:p-8 lg:p-10 mb-8 text-white shadow-2xl shadow-slate-900/30 stat-card"
         style="background: linear-gradient(135deg, #0a0f1e 0%, #1e293b 45%, #7c2d12 120%);">

    {{-- Orbes flottants --}}
    <div aria-hidden="true" class="float-orb absolute -top-20 -left-16 w-72 h-72 rounded-full bg-amber-500/25 blur-3xl"></div>
    <div aria-hidden="true" class="float-orb-2 absolute -bottom-24 right-0 w-80 h-80 rounded-full bg-orange-500/20 blur-3xl"></div>
    <div aria-hidden="true" class="absolute inset-0 opacity-[0.07]"
         style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 22px 22px;"></div>
    <div aria-hidden="true" class="absolute inset-0 opacity-[0.03]"
         style="background-image: linear-gradient(#fff 1px, transparent 1px), linear-gradient(90deg, #fff 1px, transparent 1px); background-size: 80px 80px;"></div>

    <div class="relative flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
        <div class="max-w-2xl">
            {{-- Badge date --}}
            <div class="inline-flex items-center gap-2 text-[10px] font-bold tracking-wider text-amber-300 bg-amber-500/15 border border-amber-400/30 px-3 py-1.5 rounded-full uppercase mb-4">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-400 ring-pulse relative"></span>
                {{ now()->locale('fr')->isoFormat('dddd D MMMM YYYY') }}
            </div>

            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight leading-[1.1]">
                {{ $salutation }}, <span class="bg-gradient-to-r from-amber-300 via-amber-400 to-orange-400 bg-clip-text text-transparent">{{ $prenom }}</span>
            </h2>

            <p class="mt-3 text-slate-300 text-sm sm:text-base leading-relaxed">
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

        {{-- Actions --}}
        <div class="flex flex-wrap gap-2.5 shrink-0">
            @if($operateur)
                <a href="{{ route('traitement.index') }}"
                   class="group inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-amber-500/30 hover:shadow-xl transition-all duration-200 hover:-translate-y-0.5 active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 3l14 9-14 9V3z"/>
                    </svg>
                    Ouvrir la liste « À traiter »
                </a>
            @else
                <a href="{{ route('depots.create') }}"
                   class="group inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-amber-500/30 hover:shadow-xl transition-all duration-200 hover:-translate-y-0.5 active:scale-95">
                    <svg class="w-4 h-4 group-hover:rotate-90 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nouveau dépôt
                </a>
                <a href="{{ route('traitement.index') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-white/10 hover:bg-white/20 ring-1 ring-white/20 hover:ring-white/30 px-4 py-2.5 text-sm font-semibold transition backdrop-blur-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    À traiter
                </a>
                <a href="{{ route('clients.create') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-white/10 hover:bg-white/20 ring-1 ring-white/20 hover:ring-white/30 px-4 py-2.5 text-sm font-semibold transition backdrop-blur-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    Nouveau client
                </a>
            @endif
            @if($gestionnaire)
                <a href="{{ route('achats.create') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-white/10 hover:bg-white/20 ring-1 ring-white/20 hover:ring-white/30 px-4 py-2.5 text-sm font-semibold transition backdrop-blur-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1"/>
                    </svg>
                    Nouvel achat
                </a>
                <a href="{{ route('depenses.create') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-white/10 hover:bg-white/20 ring-1 ring-white/20 hover:ring-white/30 px-4 py-2.5 text-sm font-semibold transition backdrop-blur-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Dépense
                </a>
            @endif
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════════
     KPI CARDS — Gradient vibrant
     ═══════════════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    @foreach($kpis as $k)
        <div class="stat-card group relative overflow-hidden rounded-2xl bg-gradient-to-br {{ $k['gradient'] }} p-5 text-white shadow-xl shadow-slate-900/10 hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
            <div class="absolute top-0 right-0 w-32 h-32 {{ $k['glow'] }} rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
            <div class="absolute bottom-0 left-0 w-24 h-24 {{ $k['glow'] }} rounded-full blur-2xl translate-y-1/2 -translate-x-1/2 opacity-50"></div>
            <div class="relative">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-11 h-11 rounded-xl bg-white/15 backdrop-blur-sm border border-white/25 flex items-center justify-center group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $k['icon'] }}"/>
                        </svg>
                    </div>
                    <span class="text-[9px] font-bold bg-white/20 px-2 py-1 rounded-md tracking-wider uppercase">{{ $k['trend'] }}</span>
                </div>
                <p class="text-[10.5px] font-semibold text-white/70 uppercase tracking-[0.1em] mb-1">{{ $k['label'] }}</p>
                <p class="text-3xl font-extrabold tracking-tight {{ $k['valueClass'] }}">{{ $k['value'] }}</p>
                <p class="text-[11px] text-white/75 mt-2 flex items-center gap-1.5">
                    <span class="w-1 h-1 rounded-full bg-white/70"></span>
                    {{ $k['sub'] }}
                </p>
            </div>
        </div>
    @endforeach
</div>

<div class="grid grid-cols-3 gap-6">

    {{-- ═══════════════════════════════════════════════════════════════
         COLONNE GAUCHE (2/3)
         ═══════════════════════════════════════════════════════════════ --}}
    <div class="col-span-3 lg:col-span-2 space-y-6">

        {{-- Activité 7 jours --}}
        @if($caisse)
            <div class="gradient-border rounded-2xl bg-white shadow-lg shadow-slate-200/50 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-white flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-blue-500/25">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-800 text-sm">Activité des 7 derniers jours</h3>
                            <p class="text-[11px] text-slate-500">Hauteur des barres : recette · Chiffre : nombre de dépôts</p>
                        </div>
                    </div>
                    <div class="flex gap-5 text-right bg-white px-4 py-2 rounded-xl border border-slate-200/60 shadow-sm">
                        <div>
                            <p class="text-[9px] uppercase font-bold text-slate-400 tracking-wider">Recette</p>
                            <p class="text-sm font-extrabold text-slate-900">{{ $f($derniers7Jours->sum('recette')) }} <span class="font-normal text-slate-400 text-[10px]">FCFA</span></p>
                        </div>
                        <div class="border-l border-slate-100 pl-4">
                            <p class="text-[9px] uppercase font-bold text-slate-400 tracking-wider">Dépôts</p>
                            <p class="text-sm font-extrabold text-slate-900">{{ $derniers7Jours->sum('depots') }}</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="flex items-end gap-3 h-52 border-b border-slate-100 pb-2">
                        @foreach($derniers7Jours as $i => $jour)
                            @php
                                $h = max(8, (int) round($jour['recette'] / $maxRecette * 130));
                                $aujourdhui = $i === $dernierIdx;
                            @endphp
                            <div class="group flex-1 flex flex-col items-center justify-end gap-1.5 h-full">
                                <span class="text-[11px] font-bold {{ $aujourdhui ? 'text-amber-600' : 'text-slate-500' }}">{{ $jour['depots'] }}</span>
                                <div class="bar-grow w-full rounded-t-xl transition-all duration-300 shadow-sm
                                        {{ $aujourdhui ? 'bg-gradient-to-t from-amber-500 via-amber-400 to-amber-300 shadow-amber-500/40 ring-2 ring-amber-400/30' : 'bg-gradient-to-t from-slate-300 to-slate-100 group-hover:from-amber-500 group-hover:via-amber-400 group-hover:to-amber-300' }}"
                                     style="height: {{ $h }}px; animation-delay: {{ $i * 0.06 }}s;"
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
        <div class="gradient-border rounded-2xl bg-white shadow-lg shadow-slate-200/50 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-white shadow-md shadow-amber-500/25">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800 text-sm">Derniers dépôts</h3>
                        <p class="text-[11px] text-slate-500">Les dernières entrées enregistrées</p>
                    </div>
                </div>
                <a href="{{ $operateur ? route('traitement.index') : route('depots.index') }}"
                   class="text-xs font-semibold text-amber-600 hover:text-amber-700 hover:underline flex items-center gap-1 group">
                    Voir tout
                    <svg class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/70 border-b border-slate-200/60">
                            <th class="py-3 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">Ticket</th>
                            <th class="py-3 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">Client</th>
                            <th class="py-3 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">Statut</th>
                            <th class="py-3 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">Retrait</th>
                            @if($caisse)
                                <th class="py-3 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em] text-right">Montant</th>
                                <th class="py-3 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em] text-right">Reste</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse($depotsRecents as $depot)
                            @php
                                $badgeClass = match($depot->statut) {
                                    'depose'        => 'bg-blue-100 text-blue-700 border-blue-200/70',
                                    'en_traitement' => 'bg-amber-100 text-amber-700 border-amber-200/70',
                                    'pret'          => 'bg-emerald-100 text-emerald-700 border-emerald-200/70',
                                    'annule'        => 'bg-rose-100 text-rose-700 border-rose-200/70',
                                    default         => 'bg-slate-100 text-slate-600 border-slate-200/70',
                                };
                                $dotClass = match($depot->statut) {
                                    'depose'        => 'bg-blue-500',
                                    'en_traitement' => 'bg-amber-500',
                                    'pret'          => 'bg-emerald-500',
                                    'annule'        => 'bg-rose-500',
                                    default         => 'bg-slate-400',
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
                            <tr class="table-row group">
                                <td class="py-3.5 px-6">
                                    <a href="{{ route('depots.show', $depot) }}" class="inline-flex items-center gap-1.5 font-bold text-amber-600 hover:text-amber-700 group-hover:underline transition">
                                        <span class="w-6 h-6 rounded-md bg-amber-100 flex items-center justify-center">
                                            <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                                            </svg>
                                        </span>
                                        {{ $depot->numero_ticket }}
                                    </a>
                                    @if($depot->express)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-gradient-to-r from-rose-500 to-pink-600 text-white ml-1.5 uppercase shadow-sm">
                                            <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20"><path d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z"/></svg>
                                            Express
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-6">
                                    <div class="flex items-center gap-2.5">
                                        <span class="row-avatar w-8 h-8 shrink-0 rounded-lg bg-gradient-to-br from-amber-100 to-orange-200 text-amber-700 text-xs font-extrabold flex items-center justify-center uppercase ring-2 ring-amber-200/60 transition-all duration-300">
                                            {{ mb_substr($depot->client->nom ?? '?', 0, 1) }}
                                        </span>
                                        <span class="font-semibold text-slate-800 truncate">{{ $depot->client->nom ?? '—' }}</span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-6">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $badgeClass }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $dotClass }}"></span>
                                        {{ $labelStatut }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-6">
                                    <div class="text-xs {{ $enRetard ? 'text-rose-600 font-bold' : 'text-slate-600 font-medium' }}">
                                        {{ $depot->date_retrait_prevue?->format('d/m/Y') ?? '—' }}
                                    </div>
                                    @if($enRetard)
                                        <div class="text-[10px] text-rose-500 font-bold mt-0.5 flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                            En retard
                                        </div>
                                    @endif
                                </td>
                                @if($caisse)
                                    <td class="py-3.5 px-6 text-right font-extrabold text-slate-900">{{ $f($depot->montant_total) }}</td>
                                    <td class="py-3.5 px-6 text-right">
                                        @if($depot->reste_a_payer > 0)
                                            <span class="font-extrabold text-rose-600">{{ $f($depot->reste_a_payer) }}</span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                Soldé
                                            </span>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $caisse ? 6 : 4 }}" class="py-16">
                                    <div class="flex flex-col items-center text-center">
                                        <div class="relative mb-4">
                                            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center text-slate-400 rotate-6">
                                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                            </div>
                                            <div class="absolute -bottom-2 -right-2 w-7 h-7 rounded-full bg-amber-100 border-2 border-white flex items-center justify-center">
                                                <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                            </div>
                                        </div>
                                        <p class="text-sm font-bold text-slate-700">Aucun dépôt enregistré</p>
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

    {{-- ═══════════════════════════════════════════════════════════════
         COLONNE DROITE (1/3)
         ═══════════════════════════════════════════════════════════════ --}}
    <div class="col-span-3 lg:col-span-1 space-y-6">

        {{-- Statuts ce mois --}}
        <div class="gradient-border rounded-2xl bg-white shadow-lg shadow-slate-200/50 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center text-white shadow-md shadow-violet-500/25">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800 text-sm">Répartition ce mois</h3>
                        <p class="text-[11px] text-slate-500">Vue par statut</p>
                    </div>
                </div>
                <span class="text-[10px] font-bold text-violet-700 bg-violet-100 px-2 py-1 rounded-md">{{ $totalDepots }}</span>
            </div>
            <div class="p-6">
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
                        <li class="flex items-center justify-between text-sm group cursor-default">
                            <span class="flex items-center gap-2.5 text-slate-700 font-semibold">
                                <span class="w-2.5 h-2.5 rounded-full {{ $cfg['class'] }} shadow-sm group-hover:scale-125 transition-transform"></span>
                                {{ $cfg['label'] }}
                            </span>
                            <span class="font-extrabold text-slate-900">{{ $n }} <span class="text-[10px] font-semibold text-slate-400">· {{ $pct }}%</span></span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- Alertes stock --}}
        <div class="gradient-border rounded-2xl bg-white shadow-lg shadow-slate-200/50 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-rose-500 to-pink-600 flex items-center justify-center text-white shadow-md shadow-rose-500/25">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800 text-sm">Alertes stock</h3>
                        <p class="text-[11px] text-slate-500">{{ $produitsEnAlerte->count() }} produit(s) critique(s)</p>
                    </div>
                </div>
                <a href="{{ route('produits.index', ['alerte' => 1]) }}"
                   class="text-xs font-semibold text-rose-600 hover:underline">Voir tout →</a>
            </div>
            <div class="p-6 space-y-4">
                @forelse($produitsEnAlerte as $produit)
                    @php
                        $stock  = (float) $produit->stock_actuel;
                        $seuil  = (float) $produit->seuil_alerte;
                        $niveau = $seuil > 0 ? min(100, round($stock / $seuil * 100)) : 0;
                    @endphp
                    <div>
                        <div class="flex items-center justify-between gap-3 mb-1.5">
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-slate-800 truncate">{{ $produit->nom }}</p>
                                <p class="text-[10px] text-slate-400 uppercase tracking-wider">{{ $produit->categorie?->nom ?? '—' }}</p>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-sm font-extrabold text-rose-600">{{ $stock + 0 }} <span class="text-[10px] font-semibold text-rose-400">{{ $produit->unite }}</span></p>
                                <p class="text-[10px] text-slate-400">Seuil : {{ $seuil + 0 }}</p>
                            </div>
                        </div>
                        <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden shadow-inner">
                            <div class="h-full rounded-full {{ $niveau < 50 ? 'bg-gradient-to-r from-rose-400 to-rose-600' : 'bg-gradient-to-r from-amber-400 to-orange-500' }}" style="width: {{ max(6, $niveau) }}%"></div>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center py-6 text-center">
                        <div class="w-12 h-12 rounded-full bg-emerald-50 flex items-center justify-center mb-2 shadow-sm">
                            <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <p class="text-sm font-bold text-slate-700">Stock en parfaite santé</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">Aucun produit sous le seuil d'alerte.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Achats récents (gérants) --}}
        @if($gestionnaire)
            <div class="gradient-border rounded-2xl bg-white shadow-lg shadow-slate-200/50 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-white flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-blue-500/25">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-800 text-sm">Achats récents</h3>
                            <p class="text-[11px] text-slate-500">Derniers approvisionnements</p>
                        </div>
                    </div>
                    <a href="{{ route('achats.index') }}" class="text-xs font-semibold text-amber-600 hover:underline">Voir tout →</a>
                </div>
                <div class="p-6 space-y-2">
                    @forelse($achatsRecents as $achat)
                        <div class="flex items-center justify-between gap-3 py-2 px-2 rounded-lg hover:bg-slate-50 transition-colors">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="w-8 h-8 shrink-0 rounded-lg bg-gradient-to-br from-slate-100 to-slate-200 text-slate-600 text-[10px] font-extrabold flex items-center justify-center uppercase">
                                    {{ mb_substr($achat->fournisseur?->nom ?? '?', 0, 2) }}
                                </span>
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-slate-800 truncate">{{ $achat->fournisseur?->nom ?? 'Inconnu' }}</p>
                                    <p class="text-[10px] text-slate-400">{{ $achat->date_achat->format('d/m/Y') }}</p>
                                </div>
                            </div>
                            <span class="font-extrabold text-slate-900 text-sm shrink-0">{{ $f($achat->montant_total) }} <span class="text-[10px] font-semibold text-slate-400">FCFA</span></span>
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