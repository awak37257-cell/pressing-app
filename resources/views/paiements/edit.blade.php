@extends('layouts.app')

@section('title', 'Modifier le paiement')
@section('page-title', 'Modifier le paiement')
@section('page-subtitle', 'Dépôt ' . $paiement->depot?->numero_ticket . ' — ' . $paiement->depot?->client?->nom)

@section('content')

<div class="max-w-lg mx-auto">
    <div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <div class="h-1.5 bg-gradient-to-r from-amber-400 to-amber-500"></div>
        <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50">
            <h3 class="font-bold text-slate-800">Modifier le paiement</h3>
            <p class="text-xs text-slate-500 mt-0.5">
                Enregistré le {{ $paiement->date_paiement?->format('d/m/Y à H:i') }}
                par {{ $paiement->user?->nom }}
            </p>
        </div>

        <form method="POST" action="{{ route('paiements.update', $paiement) }}" class="p-6 space-y-5">
            @csrf @method('PUT')

            {{-- Montant --}}
            <div>
                <label for="montant" class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Montant (FCFA) <span class="text-rose-500">*</span>
                </label>
                <input type="number" id="montant" name="montant"
                       value="{{ old('montant', (int)$paiement->montant) }}"
                       class="form-input w-full rounded-xl border-slate-200 text-sm @error('montant') border-rose-400 @enderror"
                       required min="1">
                @error('montant')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Mode de paiement --}}
            <div>
                <label for="mode" class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Mode de paiement <span class="text-rose-500">*</span>
                </label>
                <select id="mode" name="mode"
                        class="form-select w-full rounded-xl border-slate-200 text-sm @error('mode') border-rose-400 @enderror"
                        required>
                    <option value="especes" @selected(old('mode', $paiement->mode) === 'especes')>Espèces</option>
                    <option value="orange_money" @selected(old('mode', $paiement->mode) === 'orange_money')>Orange Money</option>
                    <option value="mtn_money" @selected(old('mode', $paiement->mode) === 'mtn_money')>MTN Money</option>
                    <option value="wave" @selected(old('mode', $paiement->mode) === 'wave')>Wave</option>
                </select>
                @error('mode')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Référence --}}
            <div>
                <label for="reference" class="block text-sm font-semibold text-slate-700 mb-1.5">Référence transaction</label>
                <input type="text" id="reference" name="reference"
                       value="{{ old('reference', $paiement->reference) }}"
                       class="form-input w-full rounded-xl border-slate-200 text-sm font-mono @error('reference') border-rose-400 @enderror"
                       placeholder="Numéro de transaction (optionnel)">
                @error('reference')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="flex-1 inline-flex items-center justify-center gap-2 rounded-xl bg-amber-500 hover:bg-amber-600 px-5 py-2.5 text-sm font-bold text-white shadow-md shadow-amber-500/25 transition">
                    💾 Enregistrer la modification
                </button>
                <a href="{{ route('depots.show', $paiement->depot_id) }}"
                   class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-sm font-semibold text-slate-700 transition">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>

@endsection
