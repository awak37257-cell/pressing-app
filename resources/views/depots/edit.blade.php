@extends('layouts.app')

@php
    $modes = [
        'especes'      => ['label' => 'Espèces',      'icon' => '💵'],
        'orange_money' => ['label' => 'Orange Money', 'icon' => '📱'],
        'mtn_money'    => ['label' => 'MTN Money',    'icon' => '📱'],
        'wave'         => ['label' => 'Wave',         'icon' => '🌊'],
    ];
    $reste = (int) $depot->reste_a_payer;
@endphp

@section('title', 'Dépôt ' . $depot->numero_ticket)
@section('page-title', 'Dépôt ' . $depot->numero_ticket)
@section('page-subtitle', 'Encaissement du solde et modification des informations')

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
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center shadow-lg shadow-amber-500/30">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Modifier le dépôt</h1>
                <p class="text-xs text-slate-500 font-medium">Mettez à jour la date, les observations ou encaissez le reliquat</p>
            </div>
        </div>
        <a href="{{ route('depots.show', $depot) }}"
           class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 hover:border-slate-300 px-3.5 py-2 rounded-xl transition shadow-sm hover:shadow">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Retour à la fiche
        </a>
    </div>

    <form method="POST" action="{{ route('depots.update', $depot) }}" class="space-y-6">
        @csrf @method('PUT')

        @if($errors->any())
            <div class="form-card p-4 rounded-2xl bg-gradient-to-br from-rose-50 to-pink-50 border border-rose-200 text-rose-700 text-sm flex items-start gap-3 shadow-sm">
                <span class="shrink-0 w-8 h-8 rounded-lg bg-rose-100 flex items-center justify-center">
                    <svg class="h-4 w-4 text-rose-600" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </span>
                <div>
                    <p class="font-bold">Veuillez corriger les erreurs ci-dessous</p>
                    <ul class="mt-1 space-y-0.5 text-rose-600 text-xs">
                        @foreach($errors->all() as $e)<li>· {{ $e }}</li>@endforeach
                    </ul>
                </div>
            </div>
        @endif

        {{-- Info banner --}}
        <div class="form-card flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-2xl bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200/70 text-xs text-blue-700 shadow-sm">
            <span class="flex items-start gap-2">
                <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Pour changer le statut du dépôt (commencer, marquer prêt, remettre au client), utilisez directement les actions sur la fiche du dépôt.</span>
            </span>
            <a href="{{ route('depots.show', $depot) }}" class="font-bold text-blue-700 hover:underline shrink-0 inline-flex items-center gap-1">
                Voir la fiche
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        {{-- Section 1 : Date --}}
        <div class="form-card gradient-border rounded-2xl bg-white shadow-lg shadow-slate-200/50 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-white flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-blue-500/25">
                    <span class="text-xs font-extrabold">01</span>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 text-sm">Date de retrait</h3>
                    <p class="text-[11px] text-slate-500">Modifier la date prévue</p>
                </div>
            </div>
            <div class="p-6">
                <div class="grid sm:grid-cols-2 gap-5">
                    <x-field name="date_retrait_prevue" label="Retrait prévu" type="date" :value="$depot->date_retrait_prevue?->format('Y-m-d')" />
                </div>
            </div>
        </div>

        {{-- Section 2 : Encaissement --}}
        @if($reste > 0 && $depot->statut !== 'annule')
            <div class="form-card gradient-border rounded-2xl bg-white shadow-lg shadow-slate-200/50 overflow-hidden" style="animation-delay: 0.1s">
                <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-amber-50 via-orange-50 to-white flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white shadow-md shadow-emerald-500/25">
                        <span class="text-xs font-extrabold">02</span>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-bold text-slate-800 text-sm">Encaisser le solde</h3>
                        <p class="text-[11px] text-slate-500">Enregistrer le paiement du reliquat</p>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] font-bold text-amber-700 uppercase tracking-wider">Reste à payer</p>
                        <p class="text-lg font-extrabold text-amber-600">{{ number_format($reste, 0, ',', ' ') }} <span class="text-[10px] text-amber-500">FCFA</span></p>
                    </div>
                </div>
                <div class="p-6 space-y-5">
                    <div class="grid sm:grid-cols-3 gap-4">
                        <div>
                            <label for="montant_paye_ajout" class="text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5 block">Montant encaissé</label>
                            <input type="number" id="montant_paye_ajout" name="montant_paye_ajout" min="0" max="{{ $reste }}" inputmode="numeric"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-sm font-semibold text-slate-800 focus:bg-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all"
                                   placeholder="0">
                        </div>
                        <div>
                            <label for="mode_paiement" class="text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5 block">Mode de paiement</label>
                            <div class="relative">
                                <select id="mode_paiement" name="mode_paiement"
                                        class="appearance-none w-full pl-3.5 pr-9 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-sm text-slate-700 focus:bg-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all cursor-pointer">
                                    @foreach($modes as $k => $m)
                                        <option value="{{ $k }}" @selected(old('mode_paiement') === $k)>{{ $m['icon'] }} {{ $m['label'] }}</option>
                                    @endforeach
                                </select>
                                <svg class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>
                        <div>
                            <x-field name="reference" label="Référence (mobile)" placeholder="Ex: MP24..." />
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Section 3 : Observations --}}
        <div class="form-card gradient-border rounded-2xl bg-white shadow-lg shadow-slate-200/50 overflow-hidden" style="animation-delay: 0.2s">
            <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-white flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center text-white shadow-md shadow-violet-500/25">
                    <span class="text-xs font-extrabold">0{{ $reste > 0 ? 3 : 2 }}</span>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 text-sm">Observations</h3>
                    <p class="text-[11px] text-slate-500">Notes particulières</p>
                </div>
            </div>
            <div class="p-6">
                <textarea id="observations" name="observations" rows="3"
                          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-sm text-slate-700 resize-none placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all"
                          placeholder="Notes particulières...">{{ old('observations', $depot->observations) }}</textarea>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex flex-col sm:flex-row items-center justify-end gap-2 pt-2">
            <a href="{{ route('depots.show', $depot) }}"
               class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 bg-white hover:bg-slate-50 text-slate-600 border border-slate-200 font-semibold py-2.5 px-5 rounded-xl text-sm transition">
                Annuler
            </a>
            <button type="submit"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-bold py-2.5 px-5 rounded-xl shadow-lg shadow-amber-500/30 hover:shadow-xl text-sm transition-all duration-200 active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                Enregistrer les modifications
            </button>
        </div>
    </form>
</div>

@endsection