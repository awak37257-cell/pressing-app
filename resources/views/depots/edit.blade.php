@extends('layouts.app')
@php
    $modes = ['especes' => 'Espèces', 'orange_money' => 'Orange Money', 'mtn_money' => 'MTN Money', 'wave' => 'Wave'];
    $reste = (int) $depot->reste_a_payer;
@endphp
@section('title', 'Dépôt ' . $depot->numero_ticket)
@section('page-title', 'Dépôt ' . $depot->numero_ticket)
@section('page-subtitle', 'Encaissement et informations')

@section('content')
<div class="max-w-2xl card"><div class="card-body">
    <form method="POST" action="{{ route('depots.update', $depot) }}" class="space-y-5">
        @csrf @method('PUT')

        @if($errors->any())
            <div class="p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
                @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
            </div>
        @endif

        <p class="text-sm text-slate-500">
            Pour changer le statut du dépôt (commencer, marquer prêt, remettre au client), utilisez les boutons de la
            <a href="{{ route('depots.show', $depot) }}" class="font-medium text-amber-600 hover:underline">fiche du dépôt</a>.
        </p>

        <x-field name="date_retrait_prevue" label="Retrait prévu" type="date" :value="$depot->date_retrait_prevue?->format('Y-m-d')" />

        @if($reste > 0 && $depot->statut !== 'annule')
            <div class="p-4 bg-amber-50 rounded-xl border border-amber-100 space-y-3">
                <p class="text-sm font-semibold text-amber-800">Reste à payer : {{ number_format($reste, 0, ',', ' ') }} FCFA</p>
                <div class="grid grid-cols-3 gap-3">
                    <x-field name="montant_paye_ajout" label="Montant encaissé" type="number" min="0" max="{{ $reste }}" />
                    <div>
                        <label for="mode_paiement" class="form-label">Mode</label>
                        <select id="mode_paiement" name="mode_paiement" class="form-select">
                            @foreach($modes as $k => $v)<option value="{{ $k }}" @selected(old('mode_paiement') === $k)>{{ $v }}</option>@endforeach
                        </select>
                    </div>
                    <x-field name="reference" label="Référence (mobile)" />
                </div>
            </div>
        @endif

        <div>
            <label for="observations" class="form-label">Observations</label>
            <textarea id="observations" name="observations" rows="3" class="form-input resize-none">{{ old('observations', $depot->observations) }}</textarea>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="btn-primary">Enregistrer</button>
            <a href="{{ route('depots.show', $depot) }}" class="btn-secondary">Annuler</a>
        </div>
    </form>
</div></div>
@endsection
