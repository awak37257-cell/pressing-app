@extends('layouts.app')

@section('title', 'Produits & Stock')
@section('page-title', 'Produits & Stock')
@section('page-subtitle', 'Gestion des produits consommables et matières premières')

@section('content')

{{-- Stats rapides --}}
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="stat-card">
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Total produits</p>
        <p class="text-3xl font-bold text-slate-800 mt-1">{{ $produits->total() }}</p>
    </div>
    <div class="stat-card">
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">En alerte stock</p>
        <p class="text-3xl font-bold text-red-500 mt-1">{{ $totalAlerte }}</p>
    </div>
    <div class="stat-card">
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Catégories</p>
        <p class="text-3xl font-bold text-slate-800 mt-1">{{ $categories->count() }}</p>
    </div>
</div>

<div class="card">
    {{-- Header --}}
    <div class="card-header flex flex-wrap items-center justify-between gap-4">
        <h2 class="font-semibold text-slate-800">Liste des produits</h2>
        <a href="{{ route('produits.create') }}" id="btn-nouveau-produit" class="btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nouveau produit
        </a>
    </div>

    {{-- Filtres --}}
    <div class="px-6 py-3 bg-slate-50 border-b border-slate-100 flex flex-wrap gap-3">
        <form method="GET" action="{{ route('produits.index') }}" class="flex flex-wrap gap-3 items-center w-full">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Rechercher un produit…"
                   class="form-input max-w-xs">
            <select name="categorie" class="form-select max-w-xs">
                <option value="">Toutes catégories</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('categorie') == $cat->id ? 'selected' : '' }}>
                    {{ $cat->nom }}
                </option>
                @endforeach
            </select>
            <label class="flex items-center gap-2 text-sm text-slate-600 cursor-pointer">
                <input type="checkbox" name="alerte" value="1" {{ request()->boolean('alerte') ? 'checked' : '' }}
                       class="rounded border-slate-300 text-amber-500">
                Alertes uniquement
            </label>
            <button type="submit" class="btn-primary">Filtrer</button>
            @if(request()->hasAny(['search', 'categorie', 'alerte']))
            <a href="{{ route('produits.index') }}" class="btn-secondary">Réinitialiser</a>
            @endif
        </form>
    </div>

    {{-- Tableau --}}
    <div class="overflow-x-auto">
        <table class="table-base">
            <thead>
                <tr>
                    <th>Produit</th>
                    <th>Catégorie</th>
                    <th>Unité</th>
                    <th>Stock actuel</th>
                    <th>Seuil alerte</th>
                    <th>Prix moy. achat</th>
                    <th>Statut</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($produits as $produit)
                @php $enAlerte = $produit->stock_actuel <= $produit->seuil_alerte; @endphp
                <tr class="{{ $enAlerte ? 'bg-red-50/50' : '' }}">
                    <td class="font-medium text-slate-800">{{ $produit->nom }}</td>
                    <td>{{ $produit->categorie?->nom ?? '—' }}</td>
                    <td class="text-slate-500">{{ $produit->unite }}</td>
                    <td>
                        <span class="font-bold {{ $enAlerte ? 'text-red-600' : 'text-slate-800' }}">
                            {{ $produit->stock_actuel }}
                        </span>
                        {{ $produit->unite }}
                    </td>
                    <td class="text-slate-500">{{ $produit->seuil_alerte }} {{ $produit->unite }}</td>
                    <td class="text-slate-600">{{ number_format($produit->prix_achat_moyen, 0, ',', ' ') }} FCFA</td>
                    <td>
                        @if($enAlerte)
                        <span class="badge-danger">⚠️ Alerte</span>
                        @else
                        <span class="badge-success">✓ OK</span>
                        @endif
                    </td>
                    <td>
                        <div class="flex justify-end gap-1">
                            <a href="{{ route('produits.show', $produit) }}"
                               class="p-1.5 rounded hover:bg-slate-100 text-slate-500 hover:text-slate-700 transition-colors"
                               title="Voir">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            <a href="{{ route('produits.edit', $produit) }}"
                               class="p-1.5 rounded hover:bg-amber-50 text-amber-500 hover:text-amber-700 transition-colors"
                               title="Modifier">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <form method="POST" action="{{ route('produits.destroy', $produit) }}"
                                  onsubmit="return confirm('Supprimer ce produit ?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="p-1.5 rounded hover:bg-red-50 text-red-400 hover:text-red-600 transition-colors"
                                        title="Supprimer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-12 text-slate-400">
                        <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                        </svg>
                        Aucun produit trouvé
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($produits->hasPages())
    <div class="px-6 py-4 border-t border-slate-100">
        {{ $produits->links() }}
    </div>
    @endif
</div>

@endsection
