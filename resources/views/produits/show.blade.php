@extends('layouts.app')

@section('title', $produit->nom . ' — Stock')
@section('page-title', $produit->nom)
@section('page-subtitle', 'Fiche produit et historique des mouvements')

@section('content')

<div class="grid grid-cols-3 gap-6">

    {{-- Infos produit + ajustement stock --}}
    <div class="col-span-3 lg:col-span-1 space-y-6">

        {{-- Fiche --}}
        <div class="card">
            <div class="card-header flex justify-between items-center">
                <h3 class="font-semibold text-slate-800">Fiche produit</h3>
                <a href="{{ route('produits.edit', $produit) }}" class="btn-secondary text-xs px-3 py-1.5">Modifier</a>
            </div>
            <div class="card-body space-y-3">
                <div class="flex justify-between">
                    <span class="text-sm text-slate-500">Catégorie</span>
                    <span class="text-sm font-medium text-slate-800">{{ $produit->categorie?->nom ?? '—' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-slate-500">Unité</span>
                    <span class="text-sm font-medium text-slate-800">{{ $produit->unite }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-slate-500">Prix moy. achat</span>
                    <span class="text-sm font-medium text-slate-800">{{ number_format($produit->prix_achat_moyen, 0, ',', ' ') }} FCFA</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-slate-500">Seuil alerte</span>
                    <span class="text-sm font-medium text-slate-800">{{ $produit->seuil_alerte }} {{ $produit->unite }}</span>
                </div>

                <div class="pt-3 border-t border-slate-100">
                    @php $enAlerte = $produit->stock_actuel <= $produit->seuil_alerte; @endphp
                    <p class="text-xs text-slate-500 mb-2">Stock actuel</p>
                    <div class="flex items-end justify-between">
                        <span class="text-4xl font-bold {{ $enAlerte ? 'text-red-600' : 'text-emerald-600' }}">
                            {{ $produit->stock_actuel }}
                        </span>
                        <span class="text-slate-500 text-sm mb-1">{{ $produit->unite }}</span>
                    </div>
                    @if($enAlerte)
                    <div class="mt-2 flex items-center gap-2 text-xs text-red-600 bg-red-50 rounded-lg px-3 py-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                        Stock en dessous du seuil d'alerte !
                    </div>
                    @endif
                </div>

                {{-- Barre de stock --}}
                @php
                    $pct = $produit->seuil_alerte > 0
                        ? min(100, ($produit->stock_actuel / ($produit->seuil_alerte * 3)) * 100)
                        : 100;
                    $barColor = $enAlerte ? 'bg-red-500' : 'bg-emerald-500';
                @endphp
                <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                    <div class="{{ $barColor }} h-full rounded-full transition-all duration-500"
                         style="width: {{ $pct }}%"></div>
                </div>
            </div>
        </div>

        {{-- Ajustement stock --}}
        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold text-slate-800">Ajuster le stock</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('produits.ajuster-stock', $produit) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="type" class="form-label">Type de mouvement</label>
                        <select id="type" name="type" class="form-select">
                            <option value="entree">📥 Entrée</option>
                            <option value="sortie">📤 Sortie</option>
                            <option value="perte">🗑️ Perte</option>
                            <option value="ajustement">🔄 Ajustement (valeur exacte)</option>
                        </select>
                    </div>
                    <div>
                        <label for="quantite" class="form-label">Quantité</label>
                        <input type="number" id="quantite" name="quantite" min="0" step="0.001"
                               class="form-input" placeholder="0">
                    </div>
                    <div>
                        <label for="motif" class="form-label">Motif *</label>
                        <input type="text" id="motif" name="motif"
                               class="form-input" placeholder="Ex: Inventaire, Casse, etc.">
                    </div>
                    <button type="submit" class="btn-primary w-full justify-center">
                        Valider l'ajustement
                    </button>
                </form>
            </div>
        </div>

    </div>

    {{-- Historique mouvements --}}
    <div class="col-span-3 lg:col-span-2">
        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold text-slate-800">Historique des mouvements</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="table-base">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Quantité</th>
                            <th>Stock après</th>
                            <th>Motif</th>
                            <th>Opérateur</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mouvements as $mouvement)
                        <tr>
                            <td class="text-slate-500">{{ $mouvement->date_mouvement->format('d/m/Y H:i') }}</td>
                            <td>
                                @if($mouvement->type === 'entree')
                                    <span class="badge-success">📥 Entrée</span>
                                @elseif($mouvement->type === 'sortie')
                                    <span class="badge-danger">📤 Sortie</span>
                                @else
                                    <span class="badge-info">🔄 Ajustement</span>
                                @endif
                            </td>
                            <td class="font-semibold {{ $mouvement->type === 'sortie' ? 'text-red-600' : 'text-emerald-600' }}">
                                {{ $mouvement->type === 'sortie' ? '-' : '+' }}{{ $mouvement->quantite }} {{ $produit->unite }}
                            </td>
                            <td class="font-bold text-slate-800">{{ $mouvement->stock_apres }} {{ $produit->unite }}</td>
                            <td class="text-slate-500">{{ $mouvement->motif ?? '—' }}</td>
                            <td class="text-slate-400">{{ $mouvement->user?->nom ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-slate-400">Aucun mouvement enregistré</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($mouvements->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $mouvements->links() }}
            </div>
            @endif
        </div>
    </div>

</div>
@endsection
