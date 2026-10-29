@extends('layouts.app')

@php
    $edition = $depense->exists;
    $modes = [
        'especes'      => ['label' => 'Espèces',      'icon' => '💵'],
        'orange_money' => ['label' => 'Orange Money', 'icon' => '📱'],
        'mtn_money'    => ['label' => 'MTN Money',    'icon' => '📱'],
        'wave'         => ['label' => 'Wave',         'icon' => '🌊'],
        'autre'        => ['label' => 'Autre',        'icon' => '💳'],
    ];
@endphp

@section('title', $edition ? 'Modifier la dépense' : 'Nouvelle dépense')
@section('page-title', $edition ? 'Modifier la dépense' : 'Nouvelle dépense')
@section('page-subtitle', 'Gestion des charges et dépenses du pressing')

@section('content')

<style>
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(8px); }
        to   { opacity: 1; transform: translateY(0); }
    }
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

    {{-- ══════ HERO ══════ --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-rose-500 to-pink-600 flex items-center justify-center shadow-lg shadow-rose-500/30">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                    @if($edition)
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    @else
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    @endif
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">
                    {{ $edition ? 'Modifier la dépense' : 'Enregistrer une nouvelle dépense' }}
                </h1>
                <p class="text-xs text-slate-500 font-medium">
                    {{ $edition ? 'Mettez à jour les informations de la charge financière' : 'Renseignez les informations de la charge financière' }}
                </p>
            </div>
        </div>
        <a href="{{ route('depenses.index') }}"
           class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 hover:border-slate-300 px-3.5 py-2 rounded-xl transition shadow-sm hover:shadow">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Retour aux dépenses
        </a>
    </div>

    {{-- ══════ FORMULAIRE ══════ --}}
    <form method="POST" action="{{ $edition ? route('depenses.update', $depense) : route('depenses.store') }}" class="space-y-6">
        @csrf
        @if($edition) @method('PUT') @endif

        {{-- Section 1 : Détails --}}
        <div class="form-card gradient-border rounded-2xl bg-white shadow-lg shadow-slate-200/50 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-white flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-rose-500 to-pink-600 flex items-center justify-center text-white shadow-md shadow-rose-500/25">
                    <span class="text-xs font-extrabold">01</span>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 text-sm">Détails de la dépense</h3>
                    <p class="text-[11px] text-slate-500">Libellé, montant et date</p>
                </div>
            </div>
            <div class="p-6 space-y-5">
                <x-field name="libelle" label="Libellé *" :value="$depense->libelle" required />

                <div class="grid sm:grid-cols-2 gap-5">
                    <x-field name="montant" label="Montant (FCFA) *" type="number" min="1" :value="$depense->montant ? (int) $depense->montant : null" required />
                    <x-field name="date_depense" label="Date de la dépense *" type="date" :value="$depense->date_depense?->format('Y-m-d') ?? now()->format('Y-m-d')" required />
                </div>
            </div>
        </div>

        {{-- Section 2 : Classification --}}
        <div class="form-card gradient-border rounded-2xl bg-white shadow-lg shadow-slate-200/50 overflow-hidden" style="animation-delay: 0.1s">
            <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-white flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-blue-500/25">
                    <span class="text-xs font-extrabold">02</span>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 text-sm">Classification & paiement</h3>
                    <p class="text-[11px] text-slate-500">Catégorie et mode de règlement</p>
                </div>
            </div>
            <div class="p-6 grid sm:grid-cols-2 gap-5">
                {{-- Catégorie --}}
                <div>
                    <label for="categorie_depense_id" class="text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5 block">Catégorie <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <select id="categorie_depense_id" name="categorie_depense_id" required
                                class="appearance-none w-full pl-3.5 pr-9 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-sm text-slate-700 focus:bg-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all cursor-pointer">
                            <option value="">— Choisir une catégorie —</option>
                            @foreach($categories as $c)
                                <option value="{{ $c->id }}" @selected(old('categorie_depense_id', $depense->categorie_depense_id) == $c->id)>{{ $c->nom }}</option>
                            @endforeach
                        </select>
                        <svg class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                    @error('categorie_depense_id')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Mode de paiement --}}
                <div>
                    <label for="mode_paiement" class="text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5 block">Mode de paiement <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <select id="mode_paiement" name="mode_paiement"
                                class="appearance-none w-full pl-3.5 pr-9 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-sm text-slate-700 focus:bg-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all cursor-pointer">
                            @foreach($modes as $k => $m)
                                <option value="{{ $k }}" @selected(old('mode_paiement', $depense->mode_paiement ?? 'especes') === $k)>{{ $m['icon'] }} {{ $m['label'] }}</option>
                            @endforeach
                        </select>
                        <svg class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex flex-col sm:flex-row items-center justify-end gap-2 pt-2">
            <a href="{{ route('depenses.index') }}"
               class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 bg-white hover:bg-slate-50 text-slate-600 border border-slate-200 font-semibold py-2.5 px-5 rounded-xl text-sm transition">
                Annuler
            </a>
            <button type="submit"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-bold py-2.5 px-5 rounded-xl shadow-lg shadow-amber-500/30 hover:shadow-xl text-sm transition-all duration-200 active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                {{ $edition ? 'Mettre à jour' : 'Enregistrer la dépense' }}
            </button>
        </div>
    </form>
</div>

@endsection