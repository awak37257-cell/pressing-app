@extends('layouts.app')

@php $edition = $client->exists; @endphp

@section('title', $edition ? 'Modifier le client' : 'Nouveau client')
@section('page-title', $edition ? 'Modifier le client' : 'Nouveau client')
@section('page-subtitle', 'Gestion des coordonnées et informations client')

@section('content')

<style>
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(8px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .form-card { animation: fadeInUp 0.5s ease-out backwards; }
    .input-focus:focus-within .left-icon { color: #f59e0b; transform: scale(1.05); }
    .left-icon { transition: all 0.25s ease; }
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
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center shadow-lg shadow-amber-500/30">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                    @if($edition)
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    @else
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    @endif
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">
                    {{ $edition ? 'Modifier la fiche client' : 'Créer un nouveau client' }}
                </h1>
                <p class="text-xs text-slate-500 font-medium">
                    {{ $edition ? 'Mettez à jour les coordonnées et informations du client' : 'Ajoutez un nouveau client à votre répertoire' }}
                </p>
            </div>
        </div>
        <a href="{{ route('clients.index') }}"
           class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 hover:border-slate-300 px-3.5 py-2 rounded-xl transition shadow-sm hover:shadow">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Retour
        </a>
    </div>

    {{-- ══════ FORMULAIRE ══════ --}}
    <form method="POST" action="{{ $edition ? route('clients.update', $client) : route('clients.store') }}" class="space-y-6">
        @csrf
        @if($edition) @method('PUT') @endif

        {{-- Section 1 : Identité --}}
        <div class="form-card gradient-border rounded-2xl bg-white shadow-lg shadow-slate-200/50 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-white flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-blue-500/25">
                    <span class="text-xs font-extrabold">01</span>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 text-sm">Identité du client</h3>
                    <p class="text-[11px] text-slate-500">Nom et prénom du client</p>
                </div>
            </div>
            <div class="p-6 grid sm:grid-cols-2 gap-5">
                <x-field name="nom" label="Nom *" :value="$client->nom" required />
                <x-field name="prenom" label="Prénom" :value="$client->prenom" />
            </div>
        </div>

        {{-- Section 2 : Coordonnées --}}
        <div class="form-card gradient-border rounded-2xl bg-white shadow-lg shadow-slate-200/50 overflow-hidden" style="animation-delay: 0.1s">
            <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-white flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white shadow-md shadow-emerald-500/25">
                    <span class="text-xs font-extrabold">02</span>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 text-sm">Coordonnées</h3>
                    <p class="text-[11px] text-slate-500">Comment joindre le client</p>
                </div>
            </div>
            <div class="p-6 grid sm:grid-cols-2 gap-5">
                <x-field name="telephone" label="Téléphone *" :value="$client->telephone" required />
                <x-field name="email" label="Email" type="email" :value="$client->email" />
                <div class="sm:col-span-2">
                    <x-field name="adresse" label="Adresse" :value="$client->adresse" />
                </div>
            </div>
        </div>

        {{-- Section 3 : Notes --}}
        <div class="form-card gradient-border rounded-2xl bg-white shadow-lg shadow-slate-200/50 overflow-hidden" style="animation-delay: 0.2s">
            <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-white flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center text-white shadow-md shadow-violet-500/25">
                    <span class="text-xs font-extrabold">03</span>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 text-sm">Notes internes</h3>
                    <p class="text-[11px] text-slate-500">Observations particulières (optionnel)</p>
                </div>
            </div>
            <div class="p-6">
                <label for="observations" class="text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5 block">Observations</label>
                <textarea id="observations" name="observations" rows="4"
                          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-sm text-slate-700 resize-none placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all"
                          placeholder="Notes particulières sur le client…">{{ old('observations', $client->observations) }}</textarea>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex flex-col sm:flex-row items-center justify-end gap-2 pt-2">
            <a href="{{ route('clients.index') }}"
               class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 bg-white hover:bg-slate-50 text-slate-600 border border-slate-200 font-semibold py-2.5 px-5 rounded-xl text-sm transition">
                Annuler
            </a>
            <button type="submit"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-bold py-2.5 px-5 rounded-xl shadow-lg shadow-amber-500/30 hover:shadow-xl text-sm transition-all duration-200 active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                {{ $edition ? 'Mettre à jour' : 'Enregistrer le client' }}
            </button>
        </div>
    </form>
</div>

@endsection