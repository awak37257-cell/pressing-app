@extends('layouts.app')

@php $edition = $fournisseur->exists; @endphp

@section('title', $edition ? 'Modifier le fournisseur' : 'Nouveau fournisseur')
@section('page-title', $edition ? 'Modifier le fournisseur' : 'Nouveau fournisseur')
@section('page-subtitle', 'Gestion des coordonnées et informations fournisseur')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <div class="h-1.5 bg-gradient-to-r from-amber-400 to-amber-500"></div>
        
        <div class="card-header px-6 py-4.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div>
                <h3 class="font-bold text-slate-800 text-base">{{ $edition ? 'Modifier les informations' : 'Enregistrer un nouveau fournisseur' }}</h3>
                <p class="text-xs text-slate-500 mt-0.5">Remplissez les champs ci-dessous pour gérer vos partenaires d'approvisionnement.</p>
            </div>
            <a href="{{ route('fournisseurs.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 px-3 py-2 rounded-xl transition shadow-2xs">
                ← Retour aux fournisseurs
            </a>
        </div>

        <div class="card-body p-6">
            <form method="POST" action="{{ $edition ? route('fournisseurs.update', $fournisseur) : route('fournisseurs.store') }}" class="space-y-5">
                @csrf
                @if($edition) @method('PUT') @endif

                <x-field name="nom" label="Nom du fournisseur *" :value="$fournisseur->nom" required />

                <div class="grid sm:grid-cols-2 gap-4">
                    <x-field name="telephone" label="Téléphone" :value="$fournisseur->telephone" />
                    <x-field name="adresse" label="Adresse" :value="$fournisseur->adresse" />
                </div>

                <div class="flex items-center gap-3 pt-4 border-t border-slate-100">
                    <button type="submit" class="inline-flex items-center gap-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-bold py-2.5 px-5 rounded-xl shadow-md shadow-amber-500/20 text-sm transition">
                        {{ $edition ? 'Mettre à jour' : 'Enregistrer le fournisseur' }}
                    </button>
                    <a href="{{ route('fournisseurs.index') }}" class="inline-flex items-center justify-center bg-white hover:bg-slate-50 text-slate-600 border border-slate-200 font-semibold py-2.5 px-4 rounded-xl text-sm transition">
                        Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection