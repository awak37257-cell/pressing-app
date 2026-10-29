@extends('layouts.app')

@section('title', 'Produits & Stock')
@section('page-title', 'Produits & Stock')
@section('page-subtitle', 'Gestion des produits consommables et matières premières du pressing')

@section('content')

{{-- ═══════════════════════════════════════════════════════════════
     STYLES LOCAUX — Animations & effets signature
     ═══════════════════════════════════════════════════════════════ --}}
<style>
    @keyframes shimmer {
        0%   { background-position: -200% 0; }
        100% { background-position: 200% 0; }
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(8px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes pulseRing {
        0%   { transform: scale(1);   opacity: 0.6; }
        70%  { transform: scale(1.8); opacity: 0; }
        100% { transform: scale(1.8); opacity: 0; }
    }
    .stat-card { animation: fadeInUp 0.5s ease-out backwards; }
    .stat-card:nth-child(1) { animation-delay: 0.05s; }
    .stat-card:nth-child(2) { animation-delay: 0.10s; }
    .stat-card:nth-child(3) { animation-delay: 0.15s; }
    .row-animate { animation: fadeInUp 0.4s ease-out backwards; }
    .ring-pulse::before {
        content: '';
        position: absolute; inset: 0;
        border-radius: 9999px;
        background: currentColor;
        animation: pulseRing 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
    .table-row { transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
    .table-row:hover { background: linear-gradient(90deg, #fffbeb 0%, #ffffff 40%, #ffffff 100%); }
    .table-row:hover .avatar-badge { transform: scale(1.08) rotate(-3deg); }
    .table-row:hover .action-btn { opacity: 1; transform: translateX(0); }
    .action-btn { opacity: 0.4; transform: translateX(4px); transition: all 0.25s ease; }
    .gradient-border {
        position: relative;
        background: white;
    }
    .gradient-border::before {
        content: '';
        position: absolute;
        inset: 0;
        padding: 1px;
        border-radius: inherit;
        background: linear-gradient(135deg, rgba(251,191,36,0.3), rgba(245,158,11,0.1), rgba(251,191,36,0.3));
        -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
        pointer-events: none;
    }
    .scrollbar-thin::-webkit-scrollbar { height: 6px; }
    .scrollbar-thin::-webkit-scrollbar-track { background: #f1f5f9; }
    .scrollbar-thin::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
</style>

{{-- ═══════════════════════════════════════════════════════════════
     SECTION HERO — Titre + Actions rapides
     ═══════════════════════════════════════════════════════════════ --}}
<div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-8">
    <div>
        <div class="flex items-center gap-3 mb-1">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center shadow-lg shadow-amber-500/30">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Inventaire & Stock</h1>
                <p class="text-xs text-slate-500 font-medium">Suivi en temps réel de vos consommables</p>
            </div>
        </div>
    </div>

    {{-- Actions rapides --}}
    <div class="flex items-center gap-2">
        @if(in_array(strtolower(trim(auth()->user()->role ?? '')), ['admin', 'gerant'], true))
            <a href="{{ route('produits.create') }}" id="btn-nouveau-produit"
               class="group inline-flex items-center gap-2 bg-gradient-to-r from-amber-500 via-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-bold px-5 py-2.5 rounded-xl shadow-lg shadow-amber-500/30 hover:shadow-xl hover:shadow-amber-500/40 text-sm transition-all duration-300 hover:-translate-y-0.5 active:scale-95">
                <svg class="w-4 h-4 group-hover:rotate-90 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Nouveau produit
            </a>
        @endif
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     STATS CARDS — Design premium
     ═══════════════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">

    {{-- Total produits --}}
    <div class="stat-card relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 p-6 text-white shadow-xl shadow-slate-900/20 group hover:shadow-2xl hover:shadow-slate-900/30 transition-all duration-300">
        <div class="absolute top-0 right-0 w-40 h-40 bg-blue-500/20 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2 group-hover:bg-blue-500/30 transition-all duration-500"></div>
        <div class="absolute bottom-0 left-0 w-32 h-32 bg-indigo-500/10 rounded-full blur-2xl translate-y-1/2 -translate-x-1/2"></div>
        <div class="relative">
            <div class="flex items-start justify-between mb-4">
                <div class="w-11 h-11 rounded-xl bg-white/10 backdrop-blur-sm border border-white/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <span class="text-[10px] font-bold text-blue-300 bg-blue-500/20 px-2 py-1 rounded-md tracking-wider uppercase">Total</span>
            </div>
            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-[0.1em] mb-1">Références produits</p>
            <p class="text-4xl font-extrabold tracking-tight">{{ $produits->total() }}</p>
            <p class="text-xs text-slate-400 mt-2 flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-blue-400 ring-pulse relative"></span>
                Enregistrées dans l'inventaire
            </p>
        </div>
    </div>

    {{-- Alertes --}}
    <div class="stat-card relative overflow-hidden rounded-2xl p-6 text-white shadow-xl transition-all duration-300 group hover:shadow-2xl
        {{ $totalAlerte > 0 ? 'bg-gradient-to-br from-rose-600 via-rose-600 to-pink-700 shadow-rose-600/30 hover:shadow-rose-600/40' : 'bg-gradient-to-br from-emerald-600 via-emerald-600 to-teal-700 shadow-emerald-600/20' }}">
        <div class="absolute top-0 right-0 w-40 h-40 {{ $totalAlerte > 0 ? 'bg-pink-400/25' : 'bg-teal-400/20' }} rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
        <div class="absolute bottom-0 left-0 w-32 h-32 {{ $totalAlerte > 0 ? 'bg-rose-400/15' : 'bg-emerald-400/10' }} rounded-full blur-2xl translate-y-1/2 -translate-x-1/2"></div>
        <div class="relative">
            <div class="flex items-start justify-between mb-4">
                <div class="w-11 h-11 rounded-xl bg-white/15 backdrop-blur-sm border border-white/25 flex items-center justify-center">
                    @if($totalAlerte > 0)
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    @else
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    @endif
                </div>
                <span class="text-[10px] font-bold bg-white/20 px-2 py-1 rounded-md tracking-wider uppercase">
                    {{ $totalAlerte > 0 ? 'Critique' : 'Sain' }}
                </span>
            </div>
            <p class="text-[11px] font-semibold text-white/70 uppercase tracking-[0.1em] mb-1">Alertes stock</p>
            <p class="text-4xl font-extrabold tracking-tight">{{ $totalAlerte }}</p>
            <p class="text-xs text-white/80 mt-2 flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-white {{ $totalAlerte > 0 ? 'ring-pulse relative' : '' }}"></span>
                {{ $totalAlerte > 0 ? 'Produits à réapprovisionner' : 'Aucune action requise' }}
            </p>
        </div>
    </div>

    {{-- Catégories --}}
    <div class="stat-card relative overflow-hidden rounded-2xl p-6 text-white shadow-xl shadow-violet-600/20 hover:shadow-2xl hover:shadow-violet-600/30 transition-all duration-300 group bg-gradient-to-br from-violet-600 via-violet-600 to-purple-700">
        <div class="absolute top-0 right-0 w-40 h-40 bg-fuchsia-400/20 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
        <div class="absolute bottom-0 left-0 w-32 h-32 bg-purple-400/10 rounded-full blur-2xl translate-y-1/2 -translate-x-1/2"></div>
        <div class="relative">
            <div class="flex items-start justify-between mb-4">
                <div class="w-11 h-11 rounded-xl bg-white/15 backdrop-blur-sm border border-white/25 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                </div>
                <span class="text-[10px] font-bold text-fuchsia-200 bg-fuchsia-500/20 px-2 py-1 rounded-md tracking-wider uppercase">Familles</span>
            </div>
            <p class="text-[11px] font-semibold text-white/70 uppercase tracking-[0.1em] mb-1">Catégories</p>
            <p class="text-4xl font-extrabold tracking-tight">{{ $categories->count() }}</p>
            <p class="text-xs text-white/80 mt-2 flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-fuchsia-300"></span>
                Familles de produits actives
            </p>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     CONTENEUR PRINCIPAL
     ═══════════════════════════════════════════════════════════════ --}}
<div class="gradient-border rounded-2xl bg-white shadow-lg shadow-slate-200/50 overflow-hidden">

    {{-- ─── Barre de filtres ─── --}}
    <div class="px-6 py-4 border-b border-slate-100 bg-white">
        <form method="GET" action="{{ route('produits.index') }}" class="flex flex-col lg:flex-row gap-3 lg:items-center">

            {{-- Groupe recherche + filtres --}}
            <div class="flex flex-wrap gap-2.5 items-center flex-1">
                {{-- Recherche --}}
                <div class="relative flex-1 min-w-[220px] max-w-md">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher un produit…"
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-sm text-slate-700 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all duration-200">
                </div>

                {{-- Catégorie --}}
                <div class="relative">
                    <select name="categorie" class="appearance-none pl-3.5 pr-9 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-sm text-slate-700 focus:bg-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all duration-200 cursor-pointer">
                        <option value="">Toutes les catégories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(request('categorie') == $cat->id)>{{ $cat->nom }}</option>
                        @endforeach
                    </select>
                    <svg class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>

                {{-- Alertes --}}
                <label class="flex items-center gap-2 text-xs font-semibold text-slate-700 cursor-pointer bg-slate-50/50 px-3.5 py-2.5 rounded-xl border border-slate-200 hover:border-rose-300 hover:bg-rose-50 transition-all duration-200 group">
                    <input type="checkbox" name="alerte" value="1" @checked(request()->boolean('alerte'))
                           class="rounded border-slate-300 text-rose-500 focus:ring-rose-500 focus:ring-offset-0">
                    <span class="group-hover:text-rose-600 transition-colors flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        Alertes uniquement
                    </span>
                </label>
            </div>

            {{-- Boutons d'action --}}
            <div class="flex items-center gap-2">
                <button type="submit" class="inline-flex items-center gap-1.5 bg-slate-900 hover:bg-slate-800 text-white font-semibold px-4 py-2.5 rounded-xl text-sm transition-all duration-200 shadow-sm hover:shadow-md active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    Filtrer
                </button>
                @if(request()->hasAny(['search', 'categorie', 'alerte']))
                    <a href="{{ route('produits.index') }}"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 hover:text-slate-800 text-sm font-medium transition-all duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Réinitialiser
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- ─── Tableau ─── --}}
    <div class="overflow-x-auto scrollbar-thin">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50/70 border-b border-slate-200/60">
                    <th class="py-3.5 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">Produit</th>
                    <th class="py-3.5 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">Catégorie</th>
                    <th class="py-3.5 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">Unité</th>
                    <th class="py-3.5 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">Stock</th>
                    <th class="py-3.5 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">Seuil</th>
                    <th class="py-3.5 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">Prix moy.</th>
                    <th class="py-3.5 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">Statut</th>
                    <th class="py-3.5 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em] text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                @forelse($produits as $produit)
                    @php
                        $enAlerte = $produit->stock_actuel <= $produit->seuil_alerte;
                        $ratio = $produit->seuil_alerte > 0 ? min(100, ($produit->stock_actuel / ($produit->seuil_alerte * 2)) * 100) : 100;
                    @endphp
                    <tr class="table-row group {{ $enAlerte ? 'bg-rose-50/20' : '' }}">

                        {{-- Produit (avatar + nom) --}}
                        <td class="py-3.5 px-6">
                            <div class="flex items-center gap-3">
                                <div class="avatar-badge w-10 h-10 rounded-xl flex items-center justify-center text-xs font-extrabold transition-all duration-300
                                    {{ $enAlerte ? 'bg-gradient-to-br from-rose-100 to-rose-200 text-rose-700 ring-2 ring-rose-200' : 'bg-gradient-to-br from-slate-100 to-slate-200 text-slate-600 group-hover:from-amber-100 group-hover:to-amber-200 group-hover:text-amber-700' }}">
                                    {{ strtoupper(substr($produit->nom, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 leading-tight">{{ $produit->nom }}</div>
                                    <div class="text-[11px] text-slate-400 mt-0.5">Réf. #{{ str_pad($produit->id, 4, '0', STR_PAD_LEFT) }}</div>
                                </div>
                            </div>
                        </td>

                        {{-- Catégorie --}}
                        <td class="py-3.5 px-6">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200/60">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                                {{ $produit->categorie?->nom ?? 'Sans catégorie' }}
                            </span>
                        </td>

                        {{-- Unité --}}
                        <td class="py-3.5 px-6">
                            <span class="text-xs font-medium text-slate-500">{{ $produit->unite }}</span>
                        </td>

                        {{-- Stock + mini-barre --}}
                        <td class="py-3.5 px-6">
                            <div class="flex items-baseline gap-1 mb-1.5">
                                <span class="font-extrabold text-lg leading-none {{ $enAlerte ? 'text-rose-600' : 'text-slate-900' }}">
                                    {{ (float) $produit->stock_actuel }}
                                </span>
                                <span class="text-[10px] text-slate-400 font-medium">{{ $produit->unite }}</span>
                            </div>
                            <div class="w-20 h-1.5 rounded-full bg-slate-100 overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-500
                                    {{ $enAlerte ? 'bg-gradient-to-r from-rose-400 to-rose-600' : ($ratio < 50 ? 'bg-gradient-to-r from-amber-400 to-amber-500' : 'bg-gradient-to-r from-emerald-400 to-emerald-500') }}"
                                    style="width: {{ min(100, $ratio) }}%"></div>
                            </div>
                        </td>

                        {{-- Seuil --}}
                        <td class="py-3.5 px-6">
                            <span class="text-xs text-slate-500 font-medium">{{ (float) $produit->seuil_alerte }} {{ $produit->unite }}</span>
                        </td>

                        {{-- Prix --}}
                        <td class="py-3.5 px-6">
                            <span class="font-bold text-slate-800">{{ number_format($produit->prix_achat_moyen, 0, ',', ' ') }}</span>
                            <span class="text-[10px] text-slate-400 font-normal ml-1">FCFA</span>
                        </td>

                        {{-- Statut --}}
                        <td class="py-3.5 px-6">
                            @if($enAlerte)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-100 text-rose-700 border border-rose-200/70">
                                    <span class="relative flex w-1.5 h-1.5">
                                        <span class="absolute inline-flex w-full h-full rounded-full bg-rose-500 opacity-75 animate-ping"></span>
                                        <span class="relative inline-flex w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                                    </span>
                                    Alerte
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/70">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    OK
                                </span>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td class="py-3.5 px-6">
                            <div class="flex items-center justify-end gap-1">
                                {{-- Voir --}}
                                <a href="{{ route('produits.show', $produit) }}"
                                   class="action-btn p-2 rounded-lg bg-slate-100 hover:bg-slate-900 text-slate-600 hover:text-white transition-all duration-200 hover:scale-110"
                                   title="Voir la fiche">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>

                                @if(in_array(strtolower(trim(auth()->user()->role ?? '')), ['admin', 'gerant'], true))
                                    {{-- Modifier --}}
                                    <a href="{{ route('produits.edit', $produit) }}"
                                       class="action-btn p-2 rounded-lg bg-amber-50 hover:bg-amber-500 text-amber-600 hover:text-white transition-all duration-200 hover:scale-110"
                                       title="Modifier">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    {{-- Supprimer --}}
                                    <form method="POST" action="{{ route('produits.destroy', $produit) }}" class="inline" onsubmit="return confirm('Supprimer ce produit ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="action-btn p-2 rounded-lg bg-rose-50 hover:bg-rose-500 text-rose-600 hover:text-white transition-all duration-200 hover:scale-110"
                                                title="Supprimer">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="py-20 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="relative mb-5">
                                    <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center text-slate-400 rotate-6">
                                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                        </svg>
                                    </div>
                                    <div class="absolute -bottom-2 -right-2 w-8 h-8 rounded-full bg-amber-100 border-2 border-white flex items-center justify-center">
                                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01"/>
                                        </svg>
                                    </div>
                                </div>
                                <p class="font-bold text-slate-700 text-base">Aucun produit trouvé</p>
                                <p class="text-xs text-slate-400 mt-1 max-w-xs">Essayez de modifier vos filtres ou ajoutez un nouveau produit à votre inventaire.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ─── Pagination ─── --}}
    @if($produits->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 bg-gradient-to-r from-slate-50/40 to-white flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-xs text-slate-500">
                Affichage de <span class="font-bold text-slate-700">{{ $produits->firstItem() }}</span>
                à <span class="font-bold text-slate-700">{{ $produits->lastItem() }}</span>
                sur <span class="font-bold text-slate-700">{{ $produits->total() }}</span> produits
            </p>
            <div>{{ $produits->links() }}</div>
        </div>
    @endif
</div>

@endsection