@extends('layouts.app')
@php
    $edition = $depense->exists;
    $modes = ['especes' => 'Espèces', 'orange_money' => 'Orange Money', 'mtn_money' => 'MTN Money', 'wave' => 'Wave', 'autre' => 'Autre'];
@endphp
@section('title', $edition ? 'Modifier la dépense' : 'Nouvelle dépense')
@section('page-title', $edition ? 'Modifier la dépense' : 'Nouvelle dépense')
@section('page-subtitle', 'Charges du pressing')

@section('content')
<div class="max-w-2xl card"><div class="card-body">
    <form method="POST" action="{{ $edition ? route('depenses.update', $depense) : route('depenses.store') }}" class="space-y-5">
        @csrf
        @if($edition) @method('PUT') @endif
        <x-field name="libelle" label="Libellé" :value="$depense->libelle" required />
        <div class="grid grid-cols-2 gap-4">
            <x-field name="montant" label="Montant (FCFA)" type="number" min="1" :value="$depense->montant ? (int) $depense->montant : null" required />
            <x-field name="date_depense" label="Date" type="date" :value="$depense->date_depense?->format('Y-m-d') ?? now()->format('Y-m-d')" required />
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="categorie_depense_id" class="form-label">Catégorie *</label>
                <select id="categorie_depense_id" name="categorie_depense_id" class="form-select" required>
                    <option value="">— Choisir —</option>
                    @foreach($categories as $c)
                        <option value="{{ $c->id }}" @selected(old('categorie_depense_id', $depense->categorie_depense_id) == $c->id)>{{ $c->nom }}</option>
                    @endforeach
                </select>
                @error('categorie_depense_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="mode_paiement" class="form-label">Mode de paiement *</label>
                <select id="mode_paiement" name="mode_paiement" class="form-select">
                    @foreach($modes as $k => $v)
                        <option value="{{ $k }}" @selected(old('mode_paiement', $depense->mode_paiement ?? 'especes') === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="flex gap-3 pt-2">
            <button type="submit" class="btn-primary">Enregistrer</button>
            <a href="{{ route('depenses.index') }}" class="btn-secondary">Annuler</a>
        </div>
    </form>
</div></div>
@endsection
