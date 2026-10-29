@extends('layouts.app')

@section('title', 'Quantités standard')
@section('page-title', 'Quantités standard')
@section('page-subtitle', 'Configuration des consommations de produits par service et par article')

@section('content')

<style>
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes pulseRing { 0% { transform: scale(1); opacity: 0.6; } 70% { transform: scale(1.8); opacity: 0; } 100% { transform: scale(1.8); opacity: 0; } }
    .stat-card { animation: fadeInUp 0.5s ease-out backwards; }
    .stat-card:nth-child(1) { animation-delay: 0.05s; }
    .stat-card:nth-child(2) { animation-delay: 0.10s; }
    .form-card { animation: fadeInUp 0.5s ease-out backwards; animation-delay: 0.15s; }
    .service-card { animation: fadeInUp 0.5s ease-out backwards; }
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
</style>

{{-- ══════ HERO ══════ --}}
<div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center shadow-lg shadow-violet-500/30">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
            </svg>
        </div>
        <div>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Règles de consommation</h1>
            <p class="text-xs text-slate-500 font-medium">Doses automatiques des produits par service et par article</p>
        </div>
    </div>
</div>

{{-- ══════ ERREURS ══════ --}}
@if ($errors->any())
    <div role="alert" class="mb-6 p-4 rounded-2xl bg-gradient-to-br from-rose-50 to-pink-50 border border-rose-200/80 text-rose-800 text-sm flex items-start gap-3 shadow-sm">
        <span class="shrink-0 w-8 h-8 rounded-lg bg-rose-100 flex items-center justify-center">
            <svg class="h-4 w-4 text-rose-600" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
        </span>
        <div>
            <p class="font-bold">Erreur de validation</p>
            <ul class="mt-1 space-y-0.5 text-rose-600 text-xs">
                @foreach ($errors->all() as $erreur)<li>· {{ $erreur }}</li>@endforeach
            </ul>
        </div>
    </div>
@endif

{{-- ══════ STATS ══════ --}}
@php
    $totalRegles = $services->sum(fn($s) => $s->quantitesStandard->count());
    $servicesConfigures = $services->filter(fn($s) => $s->quantitesStandard->isNotEmpty())->count();
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
    {{-- Total règles --}}
    <div class="stat-card relative overflow-hidden rounded-2xl bg-gradient-to-br from-violet-600 via-violet-600 to-purple-700 p-6 text-white shadow-xl shadow-violet-600/20">
        <div class="absolute top-0 right-0 w-40 h-40 bg-fuchsia-400/20 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
        <div class="relative">
            <div class="flex items-start justify-between mb-4">
                <div class="w-11 h-11 rounded-xl bg-white/15 backdrop-blur-sm border border-white/25 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                    </svg>
                </div>
                <span class="text-[10px] font-bold text-fuchsia-200 bg-fuchsia-500/20 px-2 py-1 rounded-md tracking-wider uppercase">Total</span>
            </div>
            <p class="text-[11px] font-semibold text-white/70 uppercase tracking-[0.1em] mb-1">Règles configurées</p>
            <p class="text-3xl font-extrabold tracking-tight">{{ $totalRegles }}</p>
            <p class="text-xs text-white/80 mt-2 flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-fuchsia-300 ring-pulse relative"></span>
                Consommations automatiques
            </p>
        </div>
    </div>

    {{-- Services configurés --}}
    <div class="stat-card relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 p-6 text-white shadow-xl shadow-slate-900/20">
        <div class="absolute top-0 right-0 w-40 h-40 bg-amber-500/15 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
        <div class="relative">
            <div class="flex items-start justify-between mb-4">
                <div class="w-11 h-11 rounded-xl bg-white/10 backdrop-blur-sm border border-white/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                    </svg>
                </div>
                <span class="text-[10px] font-bold text-amber-300 bg-amber-500/20 px-2 py-1 rounded-md tracking-wider uppercase">Services</span>
            </div>
            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-[0.1em] mb-1">Services configurés</p>
            <p class="text-3xl font-extrabold tracking-tight">{{ $servicesConfigures }}<span class="text-lg text-slate-500">/{{ $services->count() }}</span></p>
            <p class="text-xs text-slate-400 mt-2 flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                Avec règles de consommation
            </p>
        </div>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-6 items-start">

    {{-- ══════ FORMULAIRE AJOUT ══════ --}}
    <div class="form-card gradient-border rounded-2xl bg-white shadow-lg shadow-slate-200/50 overflow-hidden lg:sticky lg:top-6 lg:order-2">
        <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-white flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white shadow-md shadow-emerald-500/25">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
            </div>
            <div>
                <h3 class="font-bold text-slate-800 text-sm">Ajouter une règle</h3>
                <p class="text-[11px] text-slate-500">Quantité par article traité</p>
            </div>
        </div>
        <div class="p-6">
            @if($services->isEmpty() || $produits->isEmpty())
                <div class="flex items-start gap-2.5 p-3 rounded-xl bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-200/70 text-amber-800 text-xs">
                    <svg class="w-4 h-4 shrink-0 mt-0.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span>Veuillez d'abord créer au moins un service et un produit dans le catalogue.</span>
                </div>
            @else
                <form method="POST" action="{{ route('quantites-standard.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="service_id" class="text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5 block">Service <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <select id="service_id" name="service_id" required
                                    class="appearance-none w-full pl-3.5 pr-9 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-sm text-slate-700 focus:bg-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all cursor-pointer">
                                <option value="">— Choisir un service —</option>
                                @foreach($services as $s)
                                    <option value="{{ $s->id }}" @selected(old('service_id') == $s->id)>{{ $s->nom }}</option>
                                @endforeach
                            </select>
                            <svg class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>

                    <div>
                        <label for="type_article_id" class="text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5 block">Type d'article</label>
                        <div class="relative">
                            <select id="type_article_id" name="type_article_id"
                                    class="appearance-none w-full pl-3.5 pr-9 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-sm text-slate-700 focus:bg-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all cursor-pointer">
                                <option value="">Tous les articles (standard)</option>
                                @foreach($types as $t)
                                    <option value="{{ $t->id }}" @selected(old('type_article_id') == $t->id)>{{ $t->nom }}</option>
                                @endforeach
                            </select>
                            <svg class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                        <p class="text-[10px] text-slate-400 mt-1.5 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Sauf si la dose change (ex. couette)
                        </p>
                    </div>

                    <div>
                        <label for="produit_id" class="text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5 block">Produit consommé <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <select id="produit_id" name="produit_id" required
                                    class="appearance-none w-full pl-3.5 pr-9 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-sm text-slate-700 focus:bg-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all cursor-pointer">
                                <option value="">— Choisir un produit —</option>
                                @foreach($produits as $p)
                                    <option value="{{ $p->id }}" @selected(old('produit_id') == $p->id)>{{ $p->nom }} ({{ $p->unite }})</option>
                                @endforeach
                            </select>
                            <svg class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>

                    <div>
                        <label for="quantite_par_article" class="text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5 block">Quantité par article <span class="text-rose-500">*</span></label>
                        <input type="number" id="quantite_par_article" name="quantite_par_article" step="0.001" min="0.001"
                               value="{{ old('quantite_par_article') }}"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-sm text-slate-700 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all"
                               placeholder="Ex : 0.05" required>
                        <p class="text-[10px] text-slate-400 mt-1.5 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Dans l'unité du produit (L, kg, pièce…)
                        </p>
                    </div>

                    <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-2 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-bold py-2.5 px-4 rounded-xl shadow-lg shadow-amber-500/30 hover:shadow-xl text-sm transition-all duration-200 active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        Enregistrer la règle
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- ══════ LISTE PAR SERVICE ══════ --}}
    <div class="lg:col-span-2 lg:order-1 space-y-5">
        @forelse($services as $service)
            <div class="service-card gradient-border rounded-2xl bg-white shadow-lg shadow-slate-200/50 overflow-hidden" style="animation-delay: {{ $loop->index * 0.05 }}s">
                <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-white flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center text-white shadow-md shadow-violet-500/25">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-800 text-sm">{{ $service->nom }}</h3>
                            <p class="text-[11px] text-slate-500">Service de traitement</p>
                        </div>
                    </div>
                    @if($service->quantitesStandard->isEmpty())
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-500 border border-slate-200/60">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                            Non configuré
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-200/70">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            {{ $service->quantitesStandard->count() }} produit(s)
                        </span>
                    @endif
                </div>

                @if($service->quantitesStandard->isEmpty())
                    <div class="p-6 text-center">
                        <div class="flex flex-col items-center">
                            <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mb-2">
                                <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                </svg>
                            </div>
                            <p class="text-xs text-slate-500 italic">Ce service ne consomme aucun produit enregistré.</p>
                            <p class="text-[10px] text-slate-400 mt-1">La déclaration des produits utilisés ne s'affichera pas pour ce service.</p>
                        </div>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/70 border-b border-slate-200/60">
                                    <th class="py-3 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">Produit</th>
                                    <th class="py-3 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">S'applique à</th>
                                    <th class="py-3 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">Quantité / article</th>
                                    <th class="py-3 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em] text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm">
                            @foreach($service->quantitesStandard as $std)
                                <tr class="table-row group">
                                    <td class="py-3.5 px-6">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-amber-100 to-orange-200 flex items-center justify-center text-[10px] font-extrabold text-amber-700 uppercase">
                                                {{ mb_substr($std->produit?->nom ?? '?', 0, 2) }}
                                            </div>
                                            <span class="font-bold text-slate-900 truncate">{{ $std->produit?->nom ?? '—' }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-6">
                                        @if($std->typeArticle)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200/60">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                                {{ $std->typeArticle->nom }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200/60">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                                                Tous les articles
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-6">
                                        <span class="inline-flex items-baseline gap-1 px-2.5 py-1 rounded-lg bg-amber-50">
                                            <span class="font-extrabold text-amber-700">{{ $std->quantite_par_article + 0 }}</span>
                                            <span class="text-[10px] font-semibold text-amber-600">{{ $std->produit?->unite }}</span>
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-6 text-right">
                                        <form method="POST" action="{{ route('quantites-standard.destroy', $std) }}" class="inline" onsubmit="return confirm('Supprimer cette quantité standard ?');">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                    class="p-2 rounded-lg bg-rose-50 hover:bg-rose-500 text-rose-600 hover:text-white transition-all duration-200 hover:scale-110 opacity-60 group-hover:opacity-100"
                                                    title="Supprimer">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @empty
            <div class="gradient-border rounded-2xl bg-white shadow-lg shadow-slate-200/50 p-12 text-center">
                <div class="flex flex-col items-center">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center mb-4 rotate-6">
                        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                        </svg>
                    </div>
                    <p class="font-bold text-slate-700 text-base">Aucun service configuré</p>
                    <p class="text-xs text-slate-400 mt-1">Commencez par créer des services dans le catalogue.</p>
                </div>
            </div>
        @endforelse
    </div>
</div>

@endsection