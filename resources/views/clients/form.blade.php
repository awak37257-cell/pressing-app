@extends('layouts.app')

@php $edition = $client->exists; @endphp

@section('title', $edition ? 'Modifier le client' : 'Nouveau client')
@section('page-title', $edition ? 'Modifier le client' : 'Nouveau client')
@section('page-subtitle', 'Gestion des coordonnées et informations client')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <div class="h-1.5 bg-gradient-to-r from-amber-400 to-amber-500"></div>
        
        <div class="card-header px-6 py-4.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div>
                <h3 class="font-bold text-slate-800 text-base">{{ $edition ? 'Modifier les informations' : 'Enregistrer un nouveau client' }}</h3>
                <p class="text-xs text-slate-500 mt-0.5">Remplissez les champs ci-dessous pour mettre à jour le répertoire.</p>
            </div>
            <a href="{{ route('clients.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 px-3 py-2 rounded-xl transition shadow-2xs">
                ← Retour aux clients
            </a>
        </div>

        <div class="card-body p-6">
            <form method="POST" action="{{ $edition ? route('clients.update', $client) : route('clients.store') }}" class="space-y-5">
                @csrf
                @if($edition) @method('PUT') @endif

                <div class="grid sm:grid-cols-2 gap-4">
                    <x-field name="nom" label="Nom *" :value="$client->nom" required />
                    <x-field name="prenom" label="Prénom" :value="$client->prenom" />
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <x-field name="telephone" label="Téléphone *" :value="$client->telephone" required />
                    <x-field name="email" label="Email" type="email" :value="$client->email" />
                </div>

                <x-field name="adresse" label="Adresse" :value="$client->adresse" />

                <div>
                    <label for="observations" class="form-label font-semibold text-xs text-slate-700 mb-1.5 block">Observations</label>
                    <textarea id="observations" name="observations" rows="3" class="form-input w-full rounded-xl border-slate-200 text-sm resize-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500" placeholder="Notes particulières sur le client...">{{ old('observations', $client->observations) }}</textarea>
                </div>

                <div class="flex items-center gap-3 pt-4 border-t border-slate-100">
                    <button type="submit" class="inline-flex items-center gap-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-bold py-2.5 px-5 rounded-xl shadow-md shadow-amber-500/20 text-sm transition">
                        {{ $edition ? 'Mettre à jour' : 'Enregistrer le client' }}
                    </button>
                    <a href="{{ route('clients.index') }}" class="inline-flex items-center justify-center bg-white hover:bg-slate-50 text-slate-600 border border-slate-200 font-semibold py-2.5 px-4 rounded-xl text-sm transition">
                        Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection