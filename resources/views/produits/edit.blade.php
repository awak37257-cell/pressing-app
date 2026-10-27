@extends('layouts.app')

@section('title', 'Modifier ' . $produit->nom)
@section('page-title', 'Modifier le produit')
@section('page-subtitle', $produit->nom)

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <div class="h-1.5 bg-gradient-to-r from-amber-400 to-amber-500"></div>
        
        <div class="card-header px-6 py-4.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div>
                <h3 class="font-bold text-slate-800 text-base">Modifier les informations</h3>
                <p class="text-xs text-slate-500 mt-0.5">Mettez à jour les caractéristiques, unités et seuils de ce produit.</p>
            </div>
            <a href="{{ route('produits.show', $produit) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 px-3 py-2 rounded-xl transition shadow-2xs">
                ← Retour à la fiche
            </a>
        </div>

        <div class="card-body p-6">
            <form method="POST" action="{{ route('produits.update', $produit) }}" class="space-y-5">
                @csrf @method('PUT')

                <div>
                    <label for="nom" class="form-label font-semibold text-xs text-slate-700 mb-1.5 block">Nom du produit *</label>
                    <input type="text" id="nom" name="nom" value="{{ old('nom', $produit->nom) }}"
                           class="form-input w-full rounded-xl border-slate-200 text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 @error('nom') border-rose-400 @enderror" required>
                    @error('nom')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="categorie_produit_id" class="form-label font-semibold text-xs text-slate-700 mb-1.5 block">Catégorie</label>
                    <select id="categorie_produit_id" name="categorie_produit_id" class="form-select w-full rounded-xl border-slate-200 text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                        <option value="">— Aucune catégorie —</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(old('categorie_produit_id', $produit->categorie_produit_id) == $cat->id)>
                                {{ $cat->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="unite" class="form-label font-semibold text-xs text-slate-700 mb-1.5 block">Unité de mesure *</label>
                        <input type="text" id="unite" name="unite" value="{{ old('unite', $produit->unite) }}"
                               class="form-input w-full rounded-xl border-slate-200 text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500" required>
                    </div>
                    <div>
                        <label for="prix_achat_moyen" class="form-label font-semibold text-xs text-slate-700 mb-1.5 block">Prix moy. achat (FCFA)</label>
                        <input type="number" id="prix_achat_moyen" name="prix_achat_moyen"
                               value="{{ old('prix_achat_moyen', $produit->prix_achat_moyen) }}" min="0"
                               class="form-input w-full rounded-xl border-slate-200 text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-200/60">
                    <div>
                        <label for="contenance" class="form-label font-semibold text-xs text-slate-700 mb-1.5 block">Contenance d'un conditionnement</label>
                        <input type="number" id="contenance" name="contenance" value="{{ old('contenance', $produit->contenance) }}"
                               min="0" step="0.001" class="form-input w-full rounded-xl border-slate-200 text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 @error('contenance') border-rose-400 @enderror"
                               placeholder="Ex. : 12 pour un bidon de 12 L">
                        <p class="text-[11px] text-slate-400 mt-1">Le stock reste compté en unité de mesure, mais permet l'achat en conditionné.</p>
                        @error('contenance')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="libelle_conditionnement" class="form-label font-semibold text-xs text-slate-700 mb-1.5 block">Nom du conditionnement</label>
                        <input type="text" id="libelle_conditionnement" name="libelle_conditionnement" value="{{ old('libelle_conditionnement', $produit->libelle_conditionnement) }}"
                               maxlength="50" class="form-input w-full rounded-xl border-slate-200 text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 @error('libelle_conditionnement') border-rose-400 @enderror" placeholder="Bidon, carton, sac…">
                        @error('libelle_conditionnement')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label for="seuil_alerte" class="form-label font-semibold text-xs text-slate-700 mb-1.5 block">Seuil d'alerte *</label>
                    <input type="number" id="seuil_alerte" name="seuil_alerte"
                           value="{{ old('seuil_alerte', $produit->seuil_alerte) }}" min="0" step="0.001"
                           class="form-input w-full rounded-xl border-slate-200 text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 @error('seuil_alerte') border-rose-400 @enderror" required>
                    <p class="text-xs text-amber-700/80 mt-1.5 bg-amber-50 border border-amber-200/60 p-2.5 rounded-xl font-medium">
                        💡 Pour ajuster directement le stock actuel, utilisez l'option <strong>« Ajuster le stock »</strong> depuis la fiche du produit.
                    </p>
                    @error('seuil_alerte')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="flex items-center gap-3 pt-4 border-t border-slate-100">
                    <button type="submit" class="inline-flex items-center gap-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-bold py-2.5 px-5 rounded-xl shadow-md shadow-amber-500/20 text-sm transition">
                        Enregistrer les modifications
                    </button>
                    <a href="{{ route('produits.show', $produit) }}" class="inline-flex items-center justify-center bg-white hover:bg-slate-50 text-slate-600 border border-slate-200 font-semibold py-2.5 px-4 rounded-xl text-sm transition">
                        Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection