@extends('layouts.app')

@section('title', 'Modifier le paiement')
@section('page-title', 'Modifier le paiement')
@section('page-subtitle', 'Dépôt ' . $paiement->depot?->numero_ticket . ' — ' . $paiement->depot?->client?->nom)

@section('content')

<style>
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
    .form-card { animation: fadeInUp 0.5s ease-out backwards; }
    .gradient-border { position: relative; background: white; }
    .gradient-border::before {
        content: ''; position: absolute; inset: 0; padding: 1px; border-radius: inherit;
        background: linear-gradient(135deg, rgba(251,191,36,0.3), rgba(245,158,11,0.1), rgba(251,191,36,0.3));
        -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor; mask-composite: exclude; pointer-events: none;
    }
</style>

<div class="max-w-3xl mx-auto space-y-6">

    {{-- Hero --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg shadow-emerald-500/30">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Modifier le paiement</h1>
                <p class="text-xs text-slate-500 font-medium">
                    Dépôt <span class="font-bold text-amber-600">{{ $paiement->depot?->numero_ticket }}</span>
                    · {{ $paiement->depot?->client?->nom }}
                </p>
            </div>
        </div>
        <a href="{{ route('depots.show', $paiement->depot_id) }}"
           class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 hover:border-slate-300 px-3.5 py-2 rounded-xl transition shadow-sm hover:shadow">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Retour au dépôt
        </a>
    </div>

    {{-- Info : Paiement enregistré --}}
    <div class="form-card flex items-start gap-3 p-4 rounded-2xl bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200/70 text-blue-800 text-sm shadow-sm">
        <span class="shrink-0 w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </span>
        <div>
            <p class="font-bold text-blue-900">Paiement existant</p>
            <p class="text-blue-700 text-xs mt-0.5">
                Enregistré le <span class="font-bold">{{ $paiement->date_paiement?->format('d/m/Y à H:i') }}</span>
                par <span class="font-bold">{{ $paiement->user?->nom ?? '—' }}</span>
            </p>
        </div>
    </div>

    {{-- Formulaire --}}
    <form method="POST" action="{{ route('paiements.update', $paiement) }}" class="space-y-6">
        @csrf @method('PUT')

        {{-- Section 1 : Montant --}}
        <div class="form-card gradient-border rounded-2xl bg-white shadow-lg shadow-slate-200/50 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-white flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white shadow-md shadow-emerald-500/25">
                    <span class="text-xs font-extrabold">01</span>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 text-sm">Montant du paiement</h3>
                    <p class="text-[11px] text-slate-500">Modifier le montant encaissé</p>
                </div>
            </div>
            <div class="p-6">
                <label for="montant" class="text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5 block">
                    Montant <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <input type="number" id="montant" name="montant"
                           value="{{ old('montant', (int)$paiement->montant) }}"
                           class="w-full pl-4 pr-16 py-3 rounded-xl border {{ $errors->has('montant') ? 'border-rose-400' : 'border-slate-200' }} bg-slate-50/50 text-base font-bold text-slate-900 focus:bg-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all"
                           required min="1">
                    <span class="absolute inset-y-0 right-4 flex items-center text-sm font-bold text-slate-400 pointer-events-none">FCFA</span>
                </div>
                @error('montant')<p class="text-rose-500 text-xs font-bold mt-1.5">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- Section 2 : Mode de paiement & Référence --}}
        <div class="form-card gradient-border rounded-2xl bg-white shadow-lg shadow-slate-200/50 overflow-hidden" style="animation-delay: 0.1s">
            <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-white flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-blue-500/25">
                    <span class="text-xs font-extrabold">02</span>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 text-sm">Mode & référence</h3>
                    <p class="text-[11px] text-slate-500">Mode de règlement et numéro de transaction</p>
                </div>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label for="mode" class="text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5 block">
                        Mode de paiement <span class="text-rose-500">*</span>
                    </label>
                    <select id="mode" name="mode"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-sm text-slate-700 focus:bg-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all cursor-pointer"
                            required>
                        <option value="especes" @selected(old('mode', $paiement->mode) === 'especes')>💵 Espèces</option>
                        <option value="orange_money" @selected(old('mode', $paiement->mode) === 'orange_money')>📱 Orange Money</option>
                        <option value="mtn_money" @selected(old('mode', $paiement->mode) === 'mtn_money')>📱 MTN Money</option>
                        <option value="wave" @selected(old('mode', $paiement->mode) === 'wave')>🌊 Wave</option>
                        <option value="carte" @selected(old('mode', $paiement->mode) === 'carte')>💳 Carte bancaire</option>
                    </select>
                    @error('mode')<p class="text-rose-500 text-xs font-bold mt-1.5">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="reference" class="text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5 block">Référence de transaction</label>
                    <input type="text" id="reference" name="reference"
                           value="{{ old('reference', $paiement->reference) }}"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-sm text-slate-700 focus:bg-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all"
                           placeholder="Ex: Ref transaction mobile...">
                    @error('reference')<p class="text-rose-500 text-xs font-bold mt-1.5">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex flex-col sm:flex-row items-center justify-end gap-2 pt-2">
            <a href="{{ route('depots.show', $paiement->depot_id) }}"
               class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 bg-white hover:bg-slate-50 text-slate-600 border border-slate-200 font-semibold py-2.5 px-5 rounded-xl text-sm transition">
                Annuler
            </a>
            <button type="submit"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-bold py-2.5 px-5 rounded-xl shadow-lg shadow-amber-500/30 hover:shadow-xl text-sm transition-all duration-200 active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                Enregistrer la modification
            </button>
        </div>
    </form>
</div>

@endsection