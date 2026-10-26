@extends('layouts.app')
@php $edition = $fournisseur->exists; @endphp
@section('title', $edition ? 'Modifier le fournisseur' : 'Nouveau fournisseur')
@section('page-title', $edition ? 'Modifier le fournisseur' : 'Nouveau fournisseur')
@section('page-subtitle', 'Coordonnées du fournisseur')

@section('content')
<div class="max-w-2xl card"><div class="card-body">
    <form method="POST" action="{{ $edition ? route('fournisseurs.update', $fournisseur) : route('fournisseurs.store') }}" class="space-y-5">
        @csrf
        @if($edition) @method('PUT') @endif
        <x-field name="nom" label="Nom" :value="$fournisseur->nom" required />
        <x-field name="telephone" label="Téléphone" :value="$fournisseur->telephone" />
        <x-field name="adresse" label="Adresse" :value="$fournisseur->adresse" />
        <div class="flex gap-3 pt-2">
            <button type="submit" class="btn-primary">Enregistrer</button>
            <a href="{{ route('fournisseurs.index') }}" class="btn-secondary">Annuler</a>
        </div>
    </form>
</div></div>
@endsection
