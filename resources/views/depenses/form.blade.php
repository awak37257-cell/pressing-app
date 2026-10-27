@extends('layouts.app')

@php
    $edition = $depense->exists;
    $modes = ['especes' => 'Espèces', 'orange_money' => 'Orange Money', 'mtn_money' => 'MTN Money', 'wave' => 'Wave', 'autre' => 'Autre'];
@endphp

@section('title', $edition ? 'Modifier la dépense' : 'Nouvelle dépense')
@section('page-title', $edition ? 'Modifier la dépense' : 'Nouvelle dépense')
@section('page-subtitle', 'Gestion des charges et dépenses du pressing')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <div class="h-1.5 bg-gradient-to-r from-amber-400 to-amber-500"></div>
        
        <div class="card-header px-6 py-4.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div>
                <h3 class="font-bold text-slate-800 text-base">{{ $edition ? 'Modifier la dépense' : 'Enregistrer une nouvelle dépense' }}</h3>
                <p class="text-xs text-slate-500 mt-0.5">Renseignez les informations de la charge financière ci-dessous.</p>
            </div>
            <a href="{{ route('depenses.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 px-3 py-2 rounded-xl transition shadow-2xs">
                ← Retour aux dépenses
            </a>
        </div>

        <div class="card-body p-6">
            <form method="POST" action="{{ $edition ? route('depenses.update', $depense) : route('depenses.store') }}" class="space-y-5">
                @csrf
                @if($edition) @method('PUT') @endif

                <x-field name="libelle" label="Libellé *" :value="$depense->libelle" required />

                <div class="grid sm:grid-cols-2 gap-4">
                    <x-field name="montant" label="Montant (FCFA) *" type="number" min="1" :value="$depense->montant ? (int) $depense->montant : null" required />
                    <x-field name="date_depense" label="Date de la dépense *" type="date" :value="$depense->date_depense?->format('Y-m-d') ?? now()->format('Y-m-d')" required />
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="categorie_depense_id" class="form-label font-semibold text-xs text-slate-700 mb-1.5 block">Catégorie *</label>
                        <select id="categorie_depense_id" name="categorie_depense_id" class="form-select w-full rounded-xl border-slate-200 text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500" required>
                            <option value="">— Choisir une catégorie —</option>
                            @foreach($categories as $c)
                                <option value="{{ $c->id }}" @selected(old('categorie_depense_id', $depense->categorie_depense_id) == $c->id)>{{ $c->nom }}</option>
                            @endforeach
                        </select>
                        @error('categorie_depense_id')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="mode_paiement" class="form-label font-semibold text-xs text-slate-700 mb-1.5 block">Mode de paiement *</label>
                        <select id="mode_paiement" name="mode_paiement" class="form-select w-full rounded-xl border-slate-200 text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                            @foreach($modes as $k => $v)
                                <option value="{{ $k }}" @selected(old('mode_paiement', $depense->mode_paiement ?? 'especes') === $k)>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-4 border-t border-slate-100">
                    <button type="submit" class="inline-flex items-center gap-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-bold py-2.5 px-5 rounded-xl shadow-md shadow-amber-500/20 text-sm transition">
                        {{ $edition ? 'Mettre à jour' : 'Enregistrer la dépense' }}
                    </button>
                    <a href="{{ route('depenses.index') }}" class="inline-flex items-center justify-center bg-white hover:bg-slate-50 text-slate-600 border border-slate-200 font-semibold py-2.5 px-4 rounded-xl text-sm transition">
                        Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection