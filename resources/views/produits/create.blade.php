@extends('layouts.app')

@php $edition = isset($produit) && $produit->exists; @endphp

@section('title', $edition ? 'Modifier le produit' : 'Nouveau produit')
@section('page-title', $edition ? 'Modifier le produit' : 'Nouveau produit')
@section('page-subtitle', 'Gestion du catalogue et des seuils de stock')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <div class="h-1.5 bg-gradient-to-r from-amber-400 to-amber-500"></div>
        
        <div class="card-header px-6 py-4.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div>
                <h3 class="font-bold text-slate-800 text-base">{{ $edition ? 'Modifier les informations du produit' : 'Ajouter un nouveau produit' }}</h3>
                <p class="text-xs text-slate-500 mt-0.5">Renseignez les détails du produit et ses unités de gestion pour le stock.</p>
            </div>
            <a href="{{ route('produits.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 px-3 py-2 rounded-xl transition shadow-2xs">
                ← Retour aux produits
            </a>
        </div>

        <div class="card-body p-6">
            <form method="POST" action="{{ $edition ? route('produits.update', $produit) : route('produits.store') }}" class="space-y-5">
                @csrf
                @if($edition) @method('PUT') @endif

                <div>
                    <label for="nom" class="form-label font-semibold text-xs text-slate-700 mb-1.5 block">Nom du produit *</label>
                    <input type="text" id="nom" name="nom" value="{{ old('nom', $edition ? $produit->nom : '') }}"
                           class="form-input w-full rounded-xl border-slate-200 text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 @error('nom') border-rose-400 @enderror"
                           placeholder="Ex: Perchloréthylène, Cintre, Housse plastique…" required>
                    @error('nom')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="categorie_produit_id" class="form-label font-semibold text-xs text-slate-700 mb-1.5 block">Catégorie</label>
                    <select id="categorie_produit_id" name="categorie_produit_id" class="form-select w-full rounded-xl border-slate-200 text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                        <option value="">— Aucune catégorie —</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(old('categorie_produit_id', $edition ? $produit->categorie_produit_id : '') == $cat->id)>
                                {{ $cat->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="unite" class="form-label font-semibold text-xs text-slate-700 mb-1.5 block">Unité de mesure *</label>
                        <input type="text" id="unite" name="unite" value="{{ old('unite', $edition ? $produit->unite : '') }}"
                               class="form-input w-full rounded-xl border-slate-200 text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 @error('unite') border-rose-400 @enderror"
                               placeholder="L, kg, unité, m…" required>
                        @error('unite')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="prix_achat_moyen" class="form-label font-semibold text-xs text-slate-700 mb-1.5 block">Prix d'achat moyen (FCFA)</label>
                        <input type="number" id="prix_achat_moyen" name="prix_achat_moyen"
                               value="{{ old('prix_achat_moyen', $edition ? $produit->prix_achat_moyen : '') }}" min="0"
                               class="form-input w-full rounded-xl border-slate-200 text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500" placeholder="0">
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-200/60">
                    <div>
                        <label for="contenance" class="form-label font-semibold text-xs text-slate-700 mb-1.5 block">Contenance d'un conditionnement</label>
                        <input type="number" id="contenance" name="contenance" value="{{ old('contenance', $edition ? $produit->contenance : '') }}"
                               min="0" step="0.001" class="form-input w-full rounded-xl border-slate-200 text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 @error('contenance') border-rose-400 @enderror"
                               placeholder="Ex. : 12 pour un bidon de 12 L">
                        <p class="text-[11px] text-slate-400 mt-1">Facultatif. Permet d'acheter en bidons/cartons tout en suivant le stock en unité.</p>
                        @error('contenance')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="libelle_conditionnement" class="form-label font-semibold text-xs text-slate-700 mb-1.5 block">Nom du conditionnement</label>
                        <input type="text" id="libelle_conditionnement" name="libelle_conditionnement" value="{{ old('libelle_conditionnement', $edition ? $produit->libelle_conditionnement : '') }}"
                               maxlength="50" class="form-input w-full rounded-xl border-slate-200 text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 @error('libelle_conditionnement') border-rose-400 @enderror" placeholder="Bidon, carton, sac…">
                        @error('libelle_conditionnement')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="stock_actuel" class="form-label font-semibold text-xs text-slate-700 mb-1.5 block">Stock initial *</label>
                        <input type="number" id="stock_actuel" name="stock_actuel"
                               value="{{ old('stock_actuel', $edition ? $produit->stock_actuel : 0) }}" min="0" step="0.001"
                               class="form-input w-full rounded-xl border-slate-200 text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 @error('stock_actuel') border-rose-400 @enderror" required>
                        @error('stock_actuel')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="seuil_alerte" class="form-label font-semibold text-xs text-slate-700 mb-1.5 block">Seuil d'alerte *</label>
                        <input type="number" id="seuil_alerte" name="seuil_alerte"
                               value="{{ old('seuil_alerte', $edition ? $produit->seuil_alerte : 5) }}" min="0" step="0.001"
                               class="form-input w-full rounded-xl border-slate-200 text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 @error('seuil_alerte') border-rose-400 @enderror" required>
                        <p class="text-[11px] text-slate-400 mt-1">Alerte affichée si le stock tombe sous ce seuil.</p>
                        @error('seuil_alerte')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-4 border-t border-slate-100">
                    <button type="submit" class="inline-flex items-center gap-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-bold py-2.5 px-5 rounded-xl shadow-md shadow-amber-500/20 text-sm transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        {{ $edition ? 'Mettre à jour' : 'Enregistrer le produit' }}
                    </button>
                    <a href="{{ route('produits.index') }}" class="inline-flex items-center justify-center bg-white hover:bg-slate-50 text-slate-600 border border-slate-200 font-semibold py-2.5 px-4 rounded-xl text-sm transition">
                        Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection