@extends('layouts.app')

@section('title', 'Quantités standard')
@section('page-title', 'Quantités standard')
@section('page-subtitle', 'Configuration des consommations de produits par service et par article')

@section('content')

@if ($errors->any())
    <div role="alert" class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200/80 text-rose-800 text-sm flex items-start gap-3 shadow-xs">
        <svg class="h-5 w-5 shrink-0 text-rose-600 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
        </svg>
        <div class="space-y-1">
            <p class="font-bold">Erreur de validation</p>
            @foreach ($errors->all() as $erreur)<p class="text-xs">{{ $erreur }}</p>@endforeach
        </div>
    </div>
@endif

<div class="grid lg:grid-cols-3 gap-6 items-start">

    {{-- Formulaire d'ajout (colonne de droite sur grand écran) --}}
    <div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs lg:sticky lg:top-6 lg:order-2">
        <div class="h-1.5 bg-gradient-to-r from-amber-400 to-amber-500"></div>
        <div class="card-header px-6 py-4.5 border-b border-slate-100 bg-slate-50/50">
            <h3 class="font-bold text-slate-800 text-base">Ajouter une référence</h3>
            <p class="text-xs text-slate-500 mt-0.5">Quantité de produit consommée par article traité.</p>
        </div>
        <div class="card-body p-6">
            @if($services->isEmpty() || $produits->isEmpty())
                <div class="p-4 rounded-xl bg-amber-50 border border-amber-200/60 text-amber-800 text-xs font-medium">
                    ⚠️ Veuillez d'abord créer au moins un service et un produit dans le catalogue.
                </div>
            @else
                <form method="POST" action="{{ route('quantites-standard.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="service_id" class="form-label font-semibold text-xs text-slate-700 mb-1.5 block">Service *</label>
                        <select id="service_id" name="service_id" class="form-select w-full rounded-xl border-slate-200 text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500" required>
                            <option value="">— Choisir un service —</option>
                            @foreach($services as $s)
                                <option value="{{ $s->id }}" @selected(old('service_id') == $s->id)>{{ $s->nom }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="type_article_id" class="form-label font-semibold text-xs text-slate-700 mb-1.5 block">Type d'article</label>
                        <select id="type_article_id" name="type_article_id" class="form-select w-full rounded-xl border-slate-200 text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                            <option value="">Tous les articles (standard)</option>
                            @foreach($types as $t)
                                <option value="{{ $t->id }}" @selected(old('type_article_id') == $t->id)>{{ $t->nom }}</option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-slate-400 mt-1">Laissez sur « Tous » sauf si la dose change pour un article spécifique (ex. couette).</p>
                    </div>

                    <div>
                        <label for="produit_id" class="form-label font-semibold text-xs text-slate-700 mb-1.5 block">Produit consommé *</label>
                        <select id="produit_id" name="produit_id" class="form-select w-full rounded-xl border-slate-200 text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500" required>
                            <option value="">— Choisir un produit —</option>
                            @foreach($produits as $p)
                                <option value="{{ $p->id }}" @selected(old('produit_id') == $p->id)>{{ $p->nom }} ({{ $p->unite }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="quantite_par_article" class="form-label font-semibold text-xs text-slate-700 mb-1.5 block">Quantité par article *</label>
                        <input type="number" id="quantite_par_article" name="quantite_par_article" step="0.001" min="0.001"
                               value="{{ old('quantite_par_article') }}" class="form-input w-full rounded-xl border-slate-200 text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500" placeholder="Ex. : 0.05" required>
                        <p class="text-[11px] text-slate-400 mt-1">Exprimée dans l'unité du produit (L, kg, pièce…).</p>
                    </div>

                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-bold py-2.5 px-4 rounded-xl shadow-md shadow-amber-500/20 text-sm transition">
                        Enregistrer la règle
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- Liste par service (colonne de gauche sur grand écran) --}}
    <div class="lg:col-span-2 lg:order-1 space-y-6">
        @forelse($services as $service)
            <div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
                <div class="card-header px-6 py-4.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <h3 class="font-bold text-slate-800 text-base">{{ $service->nom }}</h3>
                    <span class="text-xs font-semibold text-slate-500 bg-slate-100 px-2.5 py-1 rounded-lg">
                        {{ $service->quantitesStandard->count() }} produit(s) associé(s)
                    </span>
                </div>
                @if($service->quantitesStandard->isEmpty())
                    <div class="p-6 text-center text-slate-400 italic text-xs">
                        Ce service ne consomme aucun produit enregistré (la page de déclaration des produits utilisés ne s'affichera pas pour ce service).
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/80 text-slate-400 uppercase text-[11px] font-bold tracking-wider border-b border-slate-200/60">
                                    <th class="py-3.5 px-6">Produit</th>
                                    <th class="py-3.5 px-6">S'applique à</th>
                                    <th class="py-3.5 px-6">Quantité / article</th>
                                    <th class="py-3.5 px-6 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm">
                            @foreach($service->quantitesStandard as $std)
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="py-4 px-6 font-bold text-slate-800">{{ $std->produit?->nom }}</td>
                                    <td class="py-4 px-6 text-slate-600 font-medium">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700">
                                            {{ $std->typeArticle?->nom ?? 'Tous les articles' }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 font-extrabold text-amber-600">
                                        {{ $std->quantite_par_article + 0 }} <span class="text-xs font-normal text-slate-500">{{ $std->produit?->unite }}</span>
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <form method="POST" action="{{ route('quantites-standard.destroy', $std) }}" class="inline" onsubmit="return confirm('Supprimer cette quantité standard ?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="p-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 hover:text-rose-800 transition shadow-2xs" title="Supprimer">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
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
            <div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs p-8 text-center text-slate-400">
                Aucun service n'est configuré dans l'application.
            </div>
        @endforelse
    </div>
</div>
@endsection