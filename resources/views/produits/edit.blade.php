@extends('layouts.app')

@section('title', 'Modifier ' . $produit->nom)
@section('page-title', 'Modifier le produit')
@section('page-subtitle', $produit->nom)

@section('content')
<div class="max-w-2xl">
    <div class="card">
        <div class="card-header">
            <h2 class="font-semibold text-slate-800">Modifier les informations</h2>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('produits.update', $produit) }}" class="space-y-5">
                @csrf @method('PUT')

                <div>
                    <label for="nom" class="form-label">Nom du produit *</label>
                    <input type="text" id="nom" name="nom" value="{{ old('nom', $produit->nom) }}"
                           class="form-input @error('nom') border-red-400 @enderror">
                    @error('nom')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="categorie_produit_id" class="form-label">Catégorie</label>
                    <select id="categorie_produit_id" name="categorie_produit_id" class="form-select">
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('categorie_produit_id', $produit->categorie_produit_id) == $cat->id ? 'selected' : '' }}>
                            {{ $cat->nom }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="unite" class="form-label">Unité de mesure *</label>
                        <input type="text" id="unite" name="unite" value="{{ old('unite', $produit->unite) }}"
                               class="form-input">
                    </div>
                    <div>
                        <label for="contenance" class="form-label">Contenance d'un conditionnement</label>
                        <input type="number" id="contenance" name="contenance" value="{{ old('contenance', $produit->contenance) }}"
                               min="0" step="0.001" class="form-input @error('contenance') border-red-400 @enderror"
                               placeholder="Ex. : 12 pour un bidon de 12 L">
                        <p class="text-xs text-slate-400 mt-1">Facultatif. Le stock reste compté en l'unité de mesure, mais vous pourrez acheter en bidons.</p>
                        @error('contenance')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="libelle_conditionnement" class="form-label">Nom du conditionnement</label>
                        <input type="text" id="libelle_conditionnement" name="libelle_conditionnement" value="{{ old('libelle_conditionnement', $produit->libelle_conditionnement) }}"
                               maxlength="50" class="form-input @error('libelle_conditionnement') border-red-400 @enderror" placeholder="Bidon, carton, sac…">
                        @error('libelle_conditionnement')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="prix_achat_moyen" class="form-label">Prix moy. achat (FCFA)</label>
                        <input type="number" id="prix_achat_moyen" name="prix_achat_moyen"
                               value="{{ old('prix_achat_moyen', $produit->prix_achat_moyen) }}" min="0"
                               class="form-input">
                    </div>
                </div>

                <div>
                    <label for="seuil_alerte" class="form-label">Seuil d'alerte *</label>
                    <input type="number" id="seuil_alerte" name="seuil_alerte"
                           value="{{ old('seuil_alerte', $produit->seuil_alerte) }}" min="0" step="0.001"
                           class="form-input @error('seuil_alerte') border-red-400 @enderror">
                    <p class="text-xs text-slate-400 mt-1">
                        Pour ajuster le stock, utilisez la fiche produit → "Ajuster le stock"
                    </p>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="btn-primary">Enregistrer les modifications</button>
                    <a href="{{ route('produits.show', $produit) }}" class="btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
