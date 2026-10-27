@extends('layouts.app')

@section('title', $produit->nom . ' — Fiche Stock')
@section('page-title', $produit->nom)
@section('page-subtitle', 'Fiche détaillée, indicateurs et historique des mouvements')

@section('content')

<div class="grid lg:grid-cols-3 gap-6 items-start">

    {{-- Infos produit + ajustement stock --}}
    <div class="lg:col-span-1 space-y-6">

        {{-- Fiche --}}
        <div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
            <div class="h-1.5 bg-gradient-to-r from-amber-400 to-amber-500"></div>
            <div class="card-header px-6 py-4.5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h3 class="font-bold text-slate-800 text-base">Fiche produit</h3>
                <a href="{{ route('produits.edit', $produit) }}" class="inline-flex items-center gap-1.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold px-3 py-1.5 rounded-xl text-xs transition shadow-2xs">
                    ✎ Modifier
                </a>
            </div>
            <div class="card-body p-6 space-y-4">
                <div class="flex justify-between items-center text-sm">
                    <span class="text-slate-500 font-medium">Catégorie</span>
                    <span class="font-bold text-slate-800 bg-slate-100 px-2.5 py-1 rounded-lg text-xs">{{ $produit->categorie?->nom ?? '—' }}</span>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-slate-500 font-medium">Unité de mesure</span>
                    <span class="font-bold text-slate-800">{{ $produit->unite }}</span>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-slate-500 font-medium">Prix moy. achat</span>
                    <span class="font-bold text-slate-800">{{ number_format($produit->prix_achat_moyen, 0, ',', ' ') }} FCFA</span>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-slate-500 font-medium">Seuil d'alerte</span>
                    <span class="font-bold text-slate-800">{{ (float) $produit->seuil_alerte }} {{ $produit->unite }}</span>
                </div>

                <div class="pt-4 border-t border-slate-100">
                    @php 
                        $enAlerte = $produit->stock_actuel <= $produit->seuil_alerte; 
                        $stockVal = (float) $produit->stock_actuel;
                    @endphp
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Stock actuel</p>
                    <div class="flex items-baseline justify-between">
                        <span class="text-3xl font-extrabold {{ $enAlerte ? 'text-rose-600' : 'text-emerald-600' }}">
                            {{ $stockVal }}
                        </span>
                        <span class="text-slate-500 text-sm font-bold">{{ $produit->unite }}</span>
                    </div>

                    @if($enAlerte)
                    <div class="mt-3 flex items-center gap-2 text-xs font-bold text-rose-700 bg-rose-50 border border-rose-200/60 rounded-xl px-3.5 py-2.5 shadow-2xs">
                        <svg class="w-4 h-4 shrink-0 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                        Stock en dessous du seuil d'alerte !
                    </div>
                    @endif
                </div>

                {{-- Barre de stock dynamique --}}
                @php
                    $seuil = (float) $produit->seuil_alerte;
                    $pct = $seuil > 0 ? min(100, ($stockVal / ($seuil * 2)) * 100) : 100;
                    $barColor = $enAlerte ? 'bg-rose-500' : 'bg-emerald-500';
                @endphp
                <div class="h-2 bg-slate-100 rounded-full overflow-hidden shadow-inner mt-2">
                    <div class="{{ $barColor }} h-full rounded-full transition-all duration-500"
                         style="width: {{ max(6, $pct) }}%"></div>
                </div>
            </div>
        </div>

        {{-- Ajustement stock --}}
        <div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
            <div class="card-header px-6 py-4.5 border-b border-slate-100 bg-slate-50/50">
                <h3 class="font-bold text-slate-800 text-base">Ajuster le stock</h3>
                <p class="text-xs text-slate-500 mt-0.5">Enregistrez un mouvement manuel sur ce produit.</p>
            </div>
            <div class="card-body p-6">
                <form method="POST" action="{{ route('produits.ajuster-stock', $produit) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="type" class="form-label font-semibold text-xs text-slate-700 mb-1.5 block">Type de mouvement *</label>
                        <select id="type" name="type" class="form-select w-full rounded-xl border-slate-200 text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500" required>
                            <option value="entree">📥 Entrée de stock</option>
                            <option value="sortie">📤 Sortie de stock</option>
                            <option value="perte">🗑️ Perte / Casse</option>
                            <option value="ajustement">🔄 Ajustement (valeur exacte)</option>
                        </select>
                    </div>
                    <div>
                        <label for="quantite" class="form-label font-semibold text-xs text-slate-700 mb-1.5 block">Quantité *</label>
                        <input type="number" id="quantite" name="quantite" min="0" step="0.001"
                               class="form-input w-full rounded-xl border-slate-200 text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500" placeholder="0" required>
                    </div>
                    <div>
                        <label for="motif" class="form-label font-semibold text-xs text-slate-700 mb-1.5 block">Motif *</label>
                        <input type="text" id="motif" name="motif"
                               class="form-input w-full rounded-xl border-slate-200 text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500" placeholder="Ex: Inventaire, Casse, Réappro…" required>
                    </div>
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-bold py-2.5 px-4 rounded-xl shadow-md shadow-amber-500/20 text-sm transition">
                        Valider l'ajustement
                    </button>
                </form>
            </div>
        </div>

    </div>

    {{-- Historique mouvements --}}
    <div class="lg:col-span-2">
        <div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
            <div class="card-header px-6 py-4.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h3 class="font-bold text-slate-800 text-base">Historique des mouvements</h3>
                <span class="text-xs font-semibold text-slate-500 bg-slate-100 px-2.5 py-1 rounded-lg">Traçabilité stock</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 text-slate-400 uppercase text-[11px] font-bold tracking-wider border-b border-slate-200/60">
                            <th class="py-3.5 px-6">Date</th>
                            <th class="py-3.5 px-6">Type</th>
                            <th class="py-3.5 px-6">Quantité</th>
                            <th class="py-3.5 px-6">Stock après</th>
                            <th class="py-3.5 px-6">Motif</th>
                            <th class="py-3.5 px-6">Opérateur</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse($mouvements as $mouvement)
                        @php
                            $badgeClass = match($mouvement->type) {
                                'entree'     => 'bg-emerald-50 text-emerald-700 border border-emerald-200/60',
                                'sortie'     => 'bg-amber-50 text-amber-700 border border-amber-200/60',
                                'perte'      => 'bg-rose-50 text-rose-700 border border-rose-200/60',
                                'ajustement' => 'bg-blue-50 text-blue-700 border border-blue-200/60',
                                default      => 'bg-slate-100 text-slate-600',
                            };
                            $labelType = match($mouvement->type) {
                                'entree'     => '📥 Entrée',
                                'sortie'     => '📤 Sortie',
                                'perte'      => '🗑️ Perte',
                                'ajustement' => '🔄 Ajustement',
                                default      => ucfirst($mouvement->type),
                            };
                            $isPositif = in_array($mouvement->type, ['entree', 'ajustement'], true) && $mouvement->quantite > 0;
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-4 px-6 text-slate-500 text-xs">{{ $mouvement->date_mouvement->format('d/m/Y H:i') }}</td>
                            <td class="py-4 px-6">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold {{ $badgeClass }}">
                                    {{ $labelType }}
                                </span>
                            </td>
                            <td class="py-4 px-6 font-extrabold {{ $mouvement->type === 'entree' ? 'text-emerald-600' : ($mouvement->type === 'sortie' ? 'text-amber-600' : 'text-rose-600') }}">
                                {{ $isPositif ? '+' : '' }}{{ (float) $mouvement->quantite }} <span class="text-xs font-normal text-slate-500">{{ $produit->unite }}</span>
                            </td>
                            <td class="py-4 px-6 font-bold text-slate-800">
                                {{ (float) $mouvement->stock_apres }} <span class="text-xs font-normal text-slate-500">{{ $produit->unite }}</span>
                            </td>
                            <td class="py-4 px-6 text-slate-600 text-xs">{{ $mouvement->motif ?? '—' }}</td>
                            <td class="py-4 px-6 text-slate-500 text-xs font-medium">{{ $mouvement->user?->nom ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-16 text-slate-400 bg-slate-50/30">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-14 h-14 bg-slate-100 rounded-full flex items-center justify-center mb-3 text-slate-400">
                                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <p class="font-medium text-slate-600 text-base">Aucun mouvement enregistré</p>
                                    <p class="text-xs text-slate-400 mt-0.5">Les entrées et sorties de stock de ce produit apparaîtront ici.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($mouvements->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/40">
                {{ $mouvements->links() }}
            </div>
            @endif
        </div>
    </div>

</div>
@endsection