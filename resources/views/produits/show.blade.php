@extends('layouts.app')

@section('title', $produit->nom . ' — Fiche Stock')
@section('page-title', $produit->nom)
@section('page-subtitle', 'Fiche détaillée, indicateurs et historique des mouvements')

@php
    $enAlerte = $produit->stock_actuel <= $produit->seuil_alerte;
    $stockVal = (float) $produit->stock_actuel;
    $seuil    = (float) $produit->seuil_alerte;
    $pct      = $seuil > 0 ? min(100, ($stockVal / ($seuil * 2)) * 100) : 100;
@endphp

@section('content')

<style>
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes pulseRing { 0% { transform: scale(1); opacity: 0.6; } 70% { transform: scale(1.8); opacity: 0; } 100% { transform: scale(1.8); opacity: 0; } }
    .stat-card { animation: fadeInUp 0.5s ease-out backwards; }
    .table-row { transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
    .table-row:hover { background: linear-gradient(90deg, #fffbeb 0%, #ffffff 40%, #ffffff 100%); }
    .gradient-border { position: relative; background: white; }
    .gradient-border::before {
        content: ''; position: absolute; inset: 0; padding: 1px; border-radius: inherit;
        background: linear-gradient(135deg, rgba(251,191,36,0.3), rgba(245,158,11,0.1), rgba(251,191,36,0.3));
        -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor; mask-composite: exclude; pointer-events: none;
    }
    .ring-pulse::before { content: ''; position: absolute; inset: 0; border-radius: 9999px; background: currentColor; animation: pulseRing 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
    .stock-bar { transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1); }
</style>

<div class="space-y-6">

    {{-- ══════ HERO ══════ --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white text-lg font-extrabold shadow-lg shadow-amber-500/30">
                {{ strtoupper(substr($produit->nom, 0, 2)) }}
            </div>
            <div>
                <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">{{ $produit->nom }}</h1>
                <div class="flex flex-wrap items-center gap-3 mt-1.5 text-xs text-slate-500">
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 font-semibold">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                        {{ $produit->categorie?->nom ?? 'Sans catégorie' }}
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                        {{ $produit->unite }}
                    </span>
                    @if($produit->contenance)
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            {{ $produit->contenance }} {{ $produit->unite }}/{{ strtolower($produit->libelle_conditionnement ?? 'cond.') }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('produits.edit', $produit) }}"
               class="inline-flex items-center gap-1.5 bg-white border border-slate-200 hover:border-amber-300 hover:bg-amber-50 text-slate-700 hover:text-amber-700 font-semibold px-3.5 py-2 rounded-xl text-xs transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Modifier
            </a>
            <a href="{{ route('produits.index') }}"
               class="inline-flex items-center gap-1.5 bg-slate-900 hover:bg-slate-800 text-white font-semibold px-3.5 py-2 rounded-xl text-xs transition shadow-sm hover:shadow-md active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour
            </a>
        </div>
    </div>

    {{-- ══════ CARTES STATS ══════ --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

        {{-- Stock actuel --}}
        <div class="stat-card relative overflow-hidden rounded-2xl p-6 text-white shadow-xl transition-all duration-300 hover:shadow-2xl
            {{ $enAlerte ? 'bg-gradient-to-br from-rose-600 via-rose-600 to-pink-700 shadow-rose-600/30' : 'bg-gradient-to-br from-emerald-600 via-emerald-600 to-teal-700 shadow-emerald-600/20' }}">
            <div class="absolute top-0 right-0 w-40 h-40 {{ $enAlerte ? 'bg-pink-400/25' : 'bg-teal-400/25' }} rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
            <div class="relative">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-11 h-11 rounded-xl bg-white/15 backdrop-blur-sm border border-white/25 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                    <span class="text-[10px] font-bold bg-white/20 px-2 py-1 rounded-md tracking-wider uppercase">
                        {{ $enAlerte ? 'Critique' : 'Sain' }}
                    </span>
                </div>
                <p class="text-[11px] font-semibold text-white/70 uppercase tracking-[0.1em] mb-1">Stock actuel</p>
                <p class="text-3xl font-extrabold tracking-tight">{{ $stockVal }} <span class="text-sm font-semibold text-white/60">{{ $produit->unite }}</span></p>
                <p class="text-xs text-white/80 mt-2 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-white {{ $enAlerte ? 'ring-pulse relative' : '' }}"></span>
                    {{ $enAlerte ? 'Sous le seuil d\'alerte' : 'Stock disponible' }}
                </p>
                <div class="h-1.5 rounded-full bg-white/20 overflow-hidden mt-3">
                    <div class="stock-bar h-full rounded-full bg-white/90" style="width: {{ max(6, $pct) }}%"></div>
                </div>
            </div>
        </div>

        {{-- Seuil --}}
        <div class="stat-card relative overflow-hidden rounded-2xl bg-gradient-to-br from-amber-500 via-orange-500 to-orange-600 p-6 text-white shadow-xl shadow-amber-500/30">
            <div class="absolute top-0 right-0 w-40 h-40 bg-yellow-300/25 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
            <div class="relative">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-11 h-11 rounded-xl bg-white/15 backdrop-blur-sm border border-white/25 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <span class="text-[10px] font-bold bg-white/20 px-2 py-1 rounded-md tracking-wider uppercase">Seuil</span>
                </div>
                <p class="text-[11px] font-semibold text-white/70 uppercase tracking-[0.1em] mb-1">Seuil d'alerte</p>
                <p class="text-3xl font-extrabold tracking-tight">{{ $seuil }} <span class="text-sm font-semibold text-white/60">{{ $produit->unite }}</span></p>
                <p class="text-xs text-white/80 mt-2 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-white/70"></span>
                    Déclenchement automatique
                </p>
            </div>
        </div>

        {{-- Prix --}}
        <div class="stat-card relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 p-6 text-white shadow-xl shadow-slate-900/20">
            <div class="absolute top-0 right-0 w-40 h-40 bg-amber-500/20 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
            <div class="relative">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-11 h-11 rounded-xl bg-white/10 backdrop-blur-sm border border-white/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span class="text-[10px] font-bold text-amber-300 bg-amber-500/20 px-2 py-1 rounded-md tracking-wider uppercase">Prix</span>
                </div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-[0.1em] mb-1">Prix moyen achat</p>
                <p class="text-3xl font-extrabold tracking-tight">{{ number_format($produit->prix_achat_moyen, 0, ',', ' ') }} <span class="text-sm font-semibold text-slate-400">FCFA</span></p>
                <p class="text-xs text-slate-400 mt-2 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                    Par {{ strtolower($produit->unite) }}
                </p>
            </div>
        </div>
    </div>

    {{-- ══════ COLONNES ══════ --}}
    <div class="grid lg:grid-cols-3 gap-6 items-start">

        {{-- Ajustement stock --}}
        <div class="lg:col-span-1 space-y-6">
            <div class="gradient-border rounded-2xl bg-white shadow-lg shadow-slate-200/50 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-white flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-blue-500/25">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800 text-sm">Ajuster le stock</h3>
                        <p class="text-[11px] text-slate-500">Mouvement manuel</p>
                    </div>
                </div>
                <div class="p-6">
                    <form method="POST" action="{{ route('produits.ajuster-stock', $produit) }}" class="space-y-4">
                        @csrf
                        <div>
                            <label for="type" class="text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5 block">Type de mouvement <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <select id="type" name="type" required
                                        class="appearance-none w-full pl-3.5 pr-9 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-sm text-slate-700 focus:bg-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all cursor-pointer">
                                    <option value="entree">📥 Entrée de stock</option>
                                    <option value="sortie">📤 Sortie de stock</option>
                                    <option value="perte">🗑️ Perte / Casse</option>
                                    <option value="ajustement">🔄 Ajustement (valeur exacte)</option>
                                </select>
                                <svg class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                        </div>
                        <div>
                            <label for="quantite" class="text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5 block">Quantité <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <input type="number" id="quantite" name="quantite" min="0" step="0.001"
                                       class="w-full pl-3.5 pr-14 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-sm text-slate-700 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all"
                                       placeholder="0" required>
                                <span class="absolute inset-y-0 right-3 flex items-center text-[10px] font-bold text-slate-400 pointer-events-none">{{ $produit->unite }}</span>
                            </div>
                        </div>
                        <div>
                            <label for="motif" class="text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5 block">Motif <span class="text-rose-500">*</span></label>
                            <input type="text" id="motif" name="motif"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-sm text-slate-700 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all"
                                   placeholder="Ex : Inventaire, Casse, Réappro…" required>
                        </div>
                        <button type="submit"
                                class="w-full inline-flex items-center justify-center gap-2 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-bold py-2.5 px-4 rounded-xl shadow-lg shadow-emerald-500/30 hover:shadow-xl text-sm transition-all duration-200 active:scale-95">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            Valider l'ajustement
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Historique --}}
        <div class="lg:col-span-2">
            <div class="gradient-border rounded-2xl bg-white shadow-lg shadow-slate-200/50 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-white flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center text-white shadow-md shadow-violet-500/25">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-800 text-sm">Historique des mouvements</h3>
                            <p class="text-[11px] text-slate-500">Traçabilité complète du stock</p>
                        </div>
                    </div>
                    <span class="text-[10px] font-bold text-violet-700 bg-violet-100 px-2.5 py-1 rounded-md">{{ $mouvements->total() }} mouvement(s)</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/70 border-b border-slate-200/60">
                                <th class="py-3 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">Date</th>
                                <th class="py-3 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">Type</th>
                                <th class="py-3 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">Quantité</th>
                                <th class="py-3 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">Stock après</th>
                                <th class="py-3 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">Motif</th>
                                <th class="py-3 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">Opérateur</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @forelse($mouvements as $mouvement)
                            @php
                                $badgeClass = match($mouvement->type) {
                                    'entree'     => 'bg-emerald-100 text-emerald-700 border-emerald-200/70',
                                    'sortie'     => 'bg-amber-100 text-amber-700 border-amber-200/70',
                                    'perte'      => 'bg-rose-100 text-rose-700 border-rose-200/70',
                                    'ajustement' => 'bg-blue-100 text-blue-700 border-blue-200/70',
                                    default      => 'bg-slate-100 text-slate-600 border-slate-200/70',
                                };
                                $dotClass = match($mouvement->type) {
                                    'entree'     => 'bg-emerald-500',
                                    'sortie'     => 'bg-amber-500',
                                    'perte'      => 'bg-rose-500',
                                    'ajustement' => 'bg-blue-500',
                                    default      => 'bg-slate-400',
                                };
                                $labelType = match($mouvement->type) {
                                    'entree'     => 'Entrée',
                                    'sortie'     => 'Sortie',
                                    'perte'      => 'Perte',
                                    'ajustement' => 'Ajustement',
                                    default      => ucfirst($mouvement->type),
                                };
                                $isPositif = in_array($mouvement->type, ['entree', 'ajustement'], true) && $mouvement->quantite > 0;
                                $qteClass = match($mouvement->type) {
                                    'entree'     => 'text-emerald-600',
                                    'sortie'     => 'text-amber-600',
                                    'perte'      => 'text-rose-600',
                                    'ajustement' => 'text-blue-600',
                                    default      => 'text-slate-600',
                                };
                            @endphp
                            <tr class="table-row">
                                <td class="py-3.5 px-6">
                                    <div class="text-xs font-semibold text-slate-700">{{ $mouvement->date_mouvement->format('d/m/Y') }}</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5 font-mono">{{ $mouvement->date_mouvement->format('H:i') }}</div>
                                </td>
                                <td class="py-3.5 px-6">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $badgeClass }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $dotClass }}"></span>
                                        {{ $labelType }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-6">
                                    <span class="font-extrabold {{ $qteClass }}">{{ $isPositif ? '+' : '' }}{{ (float) $mouvement->quantite }}</span>
                                    <span class="text-[10px] font-semibold text-slate-500 ml-1">{{ $produit->unite }}</span>
                                </td>
                                <td class="py-3.5 px-6">
                                    <span class="font-extrabold text-slate-900">{{ (float) $mouvement->stock_apres }}</span>
                                    <span class="text-[10px] font-semibold text-slate-500 ml-1">{{ $produit->unite }}</span>
                                </td>
                                <td class="py-3.5 px-6">
                                    <span class="text-xs text-slate-600 line-clamp-1">{{ $mouvement->motif ?? '—' }}</span>
                                </td>
                                <td class="py-3.5 px-6">
                                    <div class="flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-md bg-slate-100 text-slate-600 text-[10px] font-extrabold flex items-center justify-center uppercase">
                                            {{ mb_substr($mouvement->user?->nom ?? 'S', 0, 1) }}
                                        </span>
                                        <span class="text-xs font-medium text-slate-700 truncate">{{ $mouvement->user?->nom ?? 'Système' }}</span>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="py-20 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="relative mb-5">
                                            <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center text-slate-400 rotate-6">
                                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </div>
                                            <div class="absolute -bottom-2 -right-2 w-8 h-8 rounded-full bg-amber-100 border-2 border-white flex items-center justify-center">
                                                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                                </svg>
                                            </div>
                                        </div>
                                        <p class="font-bold text-slate-700 text-base">Aucun mouvement enregistré</p>
                                        <p class="text-xs text-slate-400 mt-1 max-w-xs">Les entrées et sorties de ce produit apparaîtront ici.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($mouvements->hasPages())
                <div class="px-6 py-4 border-t border-slate-100 bg-gradient-to-r from-slate-50/40 to-white flex flex-col sm:flex-row items-center justify-between gap-3">
                    <p class="text-xs text-slate-500">
                        Affichage de <span class="font-bold text-slate-700">{{ $mouvements->firstItem() }}</span>
                        à <span class="font-bold text-slate-700">{{ $mouvements->lastItem() }}</span>
                        sur <span class="font-bold text-slate-700">{{ $mouvements->total() }}</span> mouvements
                    </p>
                    <div>{{ $mouvements->links() }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection