@extends('layouts.app')

@section('title', 'Produits & Stock')
@section('page-title', 'Produits & Stock')
@section('page-subtitle', 'Gestion des produits consommables et matières premières du pressing')

@section('content')

{{-- Cartes de synthèse modernes --}}
<div class="grid lg:grid-cols-3 gap-5 mb-6">
    <div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
        <div class="h-1.5 bg-gradient-to-r from-blue-400 to-blue-500"></div>
        <div class="card-body p-5 flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total produits</p>
                <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ $produits->total() }}</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600 shadow-2xs">
                📦
            </div>
        </div>
    </div>

    <div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
        <div class="h-1.5 bg-gradient-to-r from-rose-400 to-rose-600"></div>
        <div class="card-body p-5 flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">En alerte stock</p>
                <p class="text-2xl font-extrabold {{ $totalAlerte > 0 ? 'text-rose-600' : 'text-slate-900' }} mt-1">{{ $totalAlerte }}</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-rose-50 flex items-center justify-center text-rose-600 shadow-2xs">
                ⚠️
            </div>
        </div>
    </div>

    <div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
        <div class="h-1.5 bg-gradient-to-r from-amber-400 to-amber-500"></div>
        <div class="card-body p-5 flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Catégories</p>
                <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ $categories->count() }}</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600 shadow-2xs">
                🏷️
            </div>
        </div>
    </div>
</div>

<div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
    {{-- En-tête et filtres combinés --}}
    <div class="card-header px-6 py-4.5 border-b border-slate-100 flex flex-wrap items-center justify-between gap-4 bg-slate-50/50">
        <form method="GET" action="{{ route('produits.index') }}" class="flex flex-wrap gap-3 items-center flex-1">
            <div class="relative flex-1 sm:max-w-xs">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher un produit…"
                       class="form-input w-full pl-9 rounded-xl border-slate-200 text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
            </div>

            <select name="categorie" class="form-select w-full sm:w-48 rounded-xl border-slate-200 text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                <option value="">Toutes les catégories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(request('categorie') == $cat->id)>{{ $cat->nom }}</option>
                @endforeach
            </select>

            <label class="flex items-center gap-2 text-xs font-semibold text-slate-700 cursor-pointer bg-white px-3 py-2.5 rounded-xl border border-slate-200 shadow-2xs hover:bg-slate-50 transition">
                <input type="checkbox" name="alerte" value="1" @checked(request()->boolean('alerte'))
                       class="rounded border-slate-300 text-amber-500 focus:ring-amber-500">
                Alertes uniquement
            </label>

            <div class="flex items-center gap-2">
                <button type="submit" class="inline-flex items-center gap-1.5 bg-slate-800 hover:bg-slate-900 text-white font-medium px-4 py-2.5 rounded-xl text-sm transition shadow-xs">
                    Filtrer
                </button>
                @if(request()->hasAny(['search', 'categorie', 'alerte']))
                    <a href="{{ route('produits.index') }}" class="inline-flex items-center px-3 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-slate-600 text-sm font-medium transition">
                        Réinitialiser
                    </a>
                @endif
            </div>
        </form>

        {{-- Bouton Nouveau produit masqué pour la caissière --}}
      @if(in_array(strtolower(trim(auth()->user()->role ?? '')), ['admin', 'gerant'], true))
    <a href="{{ route('produits.create') }}" id="btn-nouveau-produit" class="inline-flex items-center gap-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-semibold px-4 py-2.5 rounded-xl shadow-md shadow-amber-500/20 text-sm transition-all duration-200">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Nouveau produit
    </a>
@endif
    </div>

    {{-- Tableau élégant --}}
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50/80 text-slate-400 uppercase text-[11px] font-bold tracking-wider border-b border-slate-200/60">
                    <th class="py-3.5 px-6">Produit</th>
                    <th class="py-3.5 px-6">Catégorie</th>
                    <th class="py-3.5 px-6">Unité</th>
                    <th class="py-3.5 px-6">Stock actuel</th>
                    <th class="py-3.5 px-6">Seuil alerte</th>
                    <th class="py-3.5 px-6">Prix moy. achat</th>
                    <th class="py-3.5 px-6">Statut</th>
                    <th class="py-3.5 px-6 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                @forelse($produits as $produit)
                    @php $enAlerte = $produit->stock_actuel <= $produit->seuil_alerte; @endphp
                    <tr class="hover:bg-slate-50/80 transition-colors {{ $enAlerte ? 'bg-rose-50/40' : '' }}">
                        <td class="py-4 px-6 font-bold text-slate-800">{{ $produit->nom }}</td>
                        <td class="py-4 px-6">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700">
                                {{ $produit->categorie?->nom ?? '—' }}
                            </span>
                        </td>
                        <td class="py-4 px-6 text-slate-500 text-xs">{{ $produit->unite }}</td>
                        <td class="py-4 px-6">
                            <span class="font-extrabold text-base {{ $enAlerte ? 'text-rose-600' : 'text-slate-900' }}">
                                {{ (float) $produit->stock_actuel }}
                            </span>
                            <span class="text-xs text-slate-500 ml-1">{{ $produit->unite }}</span>
                        </td>
                        <td class="py-4 px-6 text-xs text-slate-500 font-medium">{{ (float) $produit->seuil_alerte }} {{ $produit->unite }}</td>
                        <td class="py-4 px-6 font-semibold text-slate-700">{{ number_format($produit->prix_achat_moyen, 0, ',', ' ') }} FCFA</td>
                        <td class="py-4 px-6">
                            @if($enAlerte)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-extrabold bg-rose-100 text-rose-700 border border-rose-200/60 shadow-2xs">
                                    ⚠️ Alerte stock
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                    ✓ Stock OK
                                </span>
                            @endif
                        </td>
                       <td class="py-4 px-6 text-right">
    <div class="flex items-center justify-end gap-2">
        {{-- Bouton Voir accessible à tous --}}
        <a href="{{ route('produits.show', $produit) }}" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition shadow-2xs" title="Voir la fiche">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
        </a>

        {{-- Boutons Modifier et Supprimer réservés aux admins / gérants --}}
        @if(in_array(strtolower(trim(auth()->user()->role ?? '')), ['admin', 'gerant'], true))
            <a href="{{ route('produits.edit', $produit) }}" class="p-2 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-600 hover:text-amber-800 transition shadow-2xs" title="Modifier">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            </a>
            <form method="POST" action="{{ route('produits.destroy', $produit) }}" class="inline" onsubmit="return confirm('Supprimer ce produit ?');">
                @csrf 
                @method('DELETE')
                <button type="submit" class="p-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 hover:text-rose-800 transition shadow-2xs" title="Supprimer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </form>
        @endif
    </div>
</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-16 text-slate-400 bg-slate-50/30">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-14 h-14 bg-slate-100 rounded-full flex items-center justify-center mb-3 text-slate-400">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                </div>
                                <p class="font-medium text-slate-600 text-base">Aucun produit trouvé</p>
                                <p class="text-xs text-slate-400 mt-0.5">Essayez de modifier vos filtres.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($produits->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/40">
            {{ $produits->links() }}
        </div>
    @endif
</div>

@endsection