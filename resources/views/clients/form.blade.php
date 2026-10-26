@extends('layouts.app')
@php $edition = $client->exists; @endphp
@section('title', $edition ? 'Modifier le client' : 'Nouveau client')
@section('page-title', $edition ? 'Modifier le client' : 'Nouveau client')
@section('page-subtitle', 'Coordonnées du client')

@section('content')
<div class="max-w-2xl card"><div class="card-body">
    <form method="POST" action="{{ $edition ? route('clients.update', $client) : route('clients.store') }}" class="space-y-5">
        @csrf
        @if($edition) @method('PUT') @endif
        <div class="grid grid-cols-2 gap-4">
            <x-field name="nom" label="Nom" :value="$client->nom" required />
            <x-field name="prenom" label="Prénom" :value="$client->prenom" />
        </div>
        <div class="grid grid-cols-2 gap-4">
            <x-field name="telephone" label="Téléphone" :value="$client->telephone" required />
            <x-field name="email" label="Email" type="email" :value="$client->email" />
        </div>
        <x-field name="adresse" label="Adresse" :value="$client->adresse" />
        <div>
            <label for="observations" class="form-label">Observations</label>
            <textarea id="observations" name="observations" rows="3" class="form-input resize-none">{{ old('observations', $client->observations) }}</textarea>
        </div>
        <div class="flex gap-3 pt-2">
            <button type="submit" class="btn-primary">Enregistrer</button>
            <a href="{{ route('clients.index') }}" class="btn-secondary">Annuler</a>
        </div>
    </form>
</div></div>
@endsection
