@extends('layouts.app')

@php $edition = isset($produit) && $produit->exists; @endphp

@section('title', $edition ? 'Modifier le produit' : 'Nouveau produit')
@section('page-title', $edition ? 'Modifier le produit' : 'Nouveau produit')
@section('page-subtitle', 'Gestion du catalogue et des seuils de stock')

@section('content')

<style>
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
    .form-card { animation: fadeInUp 0.5s ease-out backwards; }
    .form-card:nth-child(1) { animation-delay: 0s; }
    .form-card:nth-child(2) { animation-delay: 0.08s; }
    .form-card:nth-child(3) { animation-delay: 0.16s; }
    .gradient-border { position: relative; background: white; }
    .gradient-border::before {
        content: ''; position: absolute; inset: 0; padding: 1px; border-radius: inherit;
        background: linear-gradient(135deg, rgba(251,191,36,0.3), rgba(245,158,11,0.1), rgba(251,191,36,0.3));
        -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor; mask-composite: exclude; pointer-events: none;
    }
</style>

<div class="max-w-3xl mx-auto space-y-6">

    {{-- Hero --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center shadow-lg shadow-amber-500/30">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">
                    {{ $edition ? 'Modifier le produit' : 'Ajouter un nouveau produit' }}
                </h1>
                <p class="text-xs text-slate-500 font-medium">
                    {{ $edition ? 'Mettez à jour les caractéristiques du produit' : 'Renseignez les détails du produit et ses unités de gestion' }}
                </p>
            </div>
        </div>
        <a href="{{ route('produits.index') }}"
           class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 hover:border-slate-300 px-3.5 py-2 rounded-xl transition shadow-sm hover:shadow">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Retour aux produits
        </a>
    </div>

    {{-- Formulaire --}}
    <form method="POST" action="{{ $edition ? route('produits.update', $produit) : route('produits.store') }}" class="space-y-6">
        @csrf
        @if($edition) @method('PUT') @endif

        {{-- Section 1 : Identité --}}
        <div class="form-card gradient-border rounded-2xl bg-white shadow-lg shadow-slate-200/50 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-white flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-white shadow-md shadow-amber-500/25">
                    <span class="text-xs font-extrabold">01</span>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 text-sm">Identité du produit</h3>
                    <p class="text-[11px] text-slate-500">Nom, catégorie et unité de mesure</p>
                </div>
            </div>
            <div class="p-6 space-y-5">
                <div>
                    <label for="nom" class="text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5 block">
                        Nom du produit <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="nom" name="nom" value="{{ old('nom', $edition ? $produit->nom : '') }}"
                           class="w-full px-3.5 py-2.5 rounded-xl border {{ $errors->has('nom') ? 'border-rose-400' : 'border-slate-200' }} bg-slate-50/50 text-sm text-slate-700 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all"
                           placeholder="Ex : Perchloréthylène, Cintre, Housse plastique…" required>
                    @error('nom')<p class="text-rose-500 text-xs font-bold mt-1.5">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="categorie_produit_id" class="text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5 block">Catégorie</label>
                    <div class="relative">
                        <select id="categorie_produit_id" name="categorie_produit_id"
                                class="appearance-none w-full pl-3.5 pr-9 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-sm text-slate-700 focus:bg-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all cursor-pointer">
                            <option value="">— Aucune catégorie —</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" @selected(old('categorie_produit_id', $edition ? $produit->categorie_produit_id : '') == $cat->id)>
                                    {{ $cat->nom }}
                                </option>
                            @endforeach
                        </select>
                        <svg class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-5">
                    <div>
                        <label for="unite" class="text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5 block">
                            Unité de mesure <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="unite" name="unite" value="{{ old('unite', $edition ? $produit->unite : '') }}"
                               class="w-full px-3.5 py-2.5 rounded-xl border {{ $errors->has('unite') ? 'border-rose-400' : 'border-slate-200' }} bg-slate-50/50 text-sm text-slate-700 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all"
                               placeholder="L, kg, unité, m…" required>
                        @error('unite')<p class="text-rose-500 text-xs font-bold mt-1.5">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="prix_achat_moyen" class="text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5 block">Prix d'achat moyen</label>
                        <div class="relative">
                            <input type="number" id="prix_achat_moyen" name="prix_achat_moyen"
                                   value="{{ old('prix_achat_moyen', $edition ? $produit->prix_achat_moyen : '') }}" min="0"
                                   class="w-full pl-3.5 pr-14 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-sm text-slate-700 focus:bg-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all" placeholder="0">
                            <span class="absolute inset-y-0 right-3 flex items-center text-[10px] font-bold text-slate-400 pointer-events-none">FCFA</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 2 : Conditionnement --}}
        <div class="form-card gradient-border rounded-2xl bg-white shadow-lg shadow-slate-200/50 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-white flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-blue-500/25">
                    <span class="text-xs font-extrabold">02</span>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 text-sm">Conditionnement (optionnel)</h3>
                    <p class="text-[11px] text-slate-500">Achat en bidons, cartons ou sacs tout en suivant en unité</p>
                </div>
            </div>
            <div class="p-6 grid sm:grid-cols-2 gap-5">
                <div>
                    <label for="contenance" class="text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5 block">Contenance</label>
                    <input type="number" id="contenance" name="contenance" value="{{ old('contenance', $edition ? $produit->contenance : '') }}"
                           min="0" step="0.001"
                           class="w-full px-3.5 py-2.5 rounded-xl border {{ $errors->has('contenance') ? 'border-rose-400' : 'border-slate-200' }} bg-slate-50/50 text-sm text-slate-700 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all"
                           placeholder="Ex : 12 pour un bidon de 12 L">
                    <p class="text-[10px] text-slate-400 mt-1.5 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Facultatif. Achat en conditionné, stock en unité.
                    </p>
                    @error('contenance')<p class="text-rose-500 text-xs font-bold mt-1.5">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="libelle_conditionnement" class="text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5 block">Nom du conditionnement</label>
                    <input type="text" id="libelle_conditionnement" name="libelle_conditionnement" value="{{ old('libelle_conditionnement', $edition ? $produit->libelle_conditionnement : '') }}"
                           maxlength="50"
                           class="w-full px-3.5 py-2.5 rounded-xl border {{ $errors->has('libelle_conditionnement') ? 'border-rose-400' : 'border-slate-200' }} bg-slate-50/50 text-sm text-slate-700 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all"
                           placeholder="Bidon, carton, sac…">
                    @error('libelle_conditionnement')<p class="text-rose-500 text-xs font-bold mt-1.5">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Section 3 : Stock --}}
        <div class="form-card gradient-border rounded-2xl bg-white shadow-lg shadow-slate-200/50 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-white flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white shadow-md shadow-emerald-500/25">
                    <span class="text-xs font-extrabold">03</span>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 text-sm">Stock & seuil d'alerte</h3>
                    <p class="text-[11px] text-slate-500">Valeurs initiales et déclenchement des alertes</p>
                </div>
            </div>
            <div class="p-6 grid sm:grid-cols-2 gap-5">
                <div>
                    <label for="stock_actuel" class="text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5 block">
                        Stock initial <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" id="stock_actuel" name="stock_actuel"
                           value="{{ old('stock_actuel', $edition ? $produit->stock_actuel : 0) }}" min="0" step="0.001"
                           class="w-full px-3.5 py-2.5 rounded-xl border {{ $errors->has('stock_actuel') ? 'border-rose-400' : 'border-slate-200' }} bg-slate-50/50 text-sm text-slate-700 focus:bg-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all" required>
                    @error('stock_actuel')<p class="text-rose-500 text-xs font-bold mt-1.5">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="seuil_alerte" class="text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5 block">
                        Seuil d'alerte <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" id="seuil_alerte" name="seuil_alerte"
                           value="{{ old('seuil_alerte', $edition ? $produit->seuil_alerte : 5) }}" min="0" step="0.001"
                           class="w-full px-3.5 py-2.5 rounded-xl border {{ $errors->has('seuil_alerte') ? 'border-rose-400' : 'border-slate-200' }} bg-slate-50/50 text-sm text-slate-700 focus:bg-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all" required>
                    <p class="text-[10px] text-slate-400 mt-1.5 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        Alerte affichée si le stock tombe sous ce seuil.
                    </p>
                    @error('seuil_alerte')<p class="text-rose-500 text-xs font-bold mt-1.5">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex flex-col sm:flex-row items-center justify-end gap-2 pt-2">
            <a href="{{ route('produits.index') }}"
               class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 bg-white hover:bg-slate-50 text-slate-600 border border-slate-200 font-semibold py-2.5 px-5 rounded-xl text-sm transition">
                Annuler
            </a>
            <button type="submit"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-bold py-2.5 px-5 rounded-xl shadow-lg shadow-amber-500/30 hover:shadow-xl text-sm transition-all duration-200 active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                {{ $edition ? 'Mettre à jour' : 'Enregistrer le produit' }}
            </button>
        </div>
    </form>
</div>

@endsection