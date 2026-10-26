@extends('layouts.app')

@section('title', 'Quantités standard')
@section('page-title', 'Quantités standard')
@section('page-subtitle', 'Produits consommés par service')

@section('content')

@if ($errors->any())
    <div role="alert" class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm">
        @foreach ($errors->all() as $erreur)<p>{{ $erreur }}</p>@endforeach
    </div>
@endif

<div class="grid lg:grid-cols-3 gap-6 items-start">

    {{-- Formulaire d'ajout --}}
    <div class="card lg:sticky lg:top-6 lg:order-2">
        <div class="card-header">
            <h3 class="font-semibold text-slate-800">Ajouter une quantité standard</h3>
            <p class="text-xs text-slate-500 mt-0.5">Quantité de produit consommée <strong>par article</strong> traité.</p>
        </div>
        <div class="card-body">
            @if($services->isEmpty() || $produits->isEmpty())
                <p class="text-sm text-amber-700">Créez d'abord au moins un service et un produit.</p>
            @else
                <form method="POST" action="{{ route('quantites-standard.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="service_id" class="form-label">Service *</label>
                        <select id="service_id" name="service_id" class="form-select" required>
                            <option value="">— Service —</option>
                            @foreach($services as $s)
                                <option value="{{ $s->id }}" @selected(old('service_id') == $s->id)>{{ $s->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="type_article_id" class="form-label">Type d'article</label>
                        <select id="type_article_id" name="type_article_id" class="form-select">
                            <option value="">Tous les articles</option>
                            @foreach($types as $t)
                                <option value="{{ $t->id }}" @selected(old('type_article_id') == $t->id)>{{ $t->nom }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-slate-400 mt-1">Laissez « Tous » sauf si la dose change selon l'article (ex. couette).</p>
                    </div>
                    <div>
                        <label for="produit_id" class="form-label">Produit *</label>
                        <select id="produit_id" name="produit_id" class="form-select" required>
                            <option value="">— Produit —</option>
                            @foreach($produits as $p)
                                <option value="{{ $p->id }}" @selected(old('produit_id') == $p->id)>{{ $p->nom }} ({{ $p->unite }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="quantite_par_article" class="form-label">Quantité par article *</label>
                        <input type="number" id="quantite_par_article" name="quantite_par_article" step="0.001" min="0.001"
                               value="{{ old('quantite_par_article') }}" class="form-input" placeholder="Ex. : 0.05" required>
                        <p class="text-xs text-slate-400 mt-1">Dans l'unité du produit (L, kg, pièce…).</p>
                    </div>
                    <button class="btn-primary w-full justify-center">Enregistrer</button>
                </form>
            @endif
        </div>
    </div>

    {{-- Liste par service --}}
    <div class="lg:col-span-2 lg:order-1 space-y-6">
        @forelse($services as $service)
            <div class="card">
                <div class="card-header flex items-center justify-between">
                    <h3 class="font-semibold text-slate-800">{{ $service->nom }}</h3>
                    <span class="text-xs text-slate-400">{{ $service->quantitesStandard->count() }} produit(s)</span>
                </div>
                @if($service->quantitesStandard->isEmpty())
                    <p class="px-6 py-5 text-sm text-slate-400">Ce service ne consomme aucun produit : la page « produits utilisés » ne s'affichera pas.</p>
                @else
                    <table class="table-base">
                        <thead><tr><th>Produit</th><th>S'applique à</th><th>Quantité / article</th><th class="text-right"></th></tr></thead>
                        <tbody>
                        @foreach($service->quantitesStandard as $std)
                            <tr>
                                <td class="font-medium text-slate-800">{{ $std->produit?->nom }}</td>
                                <td>{{ $std->typeArticle?->nom ?? 'Tous les articles' }}</td>
                                <td>{{ $std->quantite_par_article + 0 }} {{ $std->produit?->unite }}</td>
                                <td class="text-right">
                                    <form method="POST" action="{{ route('quantites-standard.destroy', $std) }}" class="inline" onsubmit="return confirm('Supprimer cette quantité standard ?')">
                                        @csrf @method('DELETE')
                                        <button class="text-red-500 hover:underline text-sm">Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        @empty
            <div class="card"><div class="card-body text-sm text-slate-500">Aucun service n'est configuré.</div></div>
        @endforelse
    </div>
</div>
@endsection
