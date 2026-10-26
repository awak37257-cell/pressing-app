@extends('layouts.app')
@section('title', 'Rapports')
@section('page-title', 'Rapports')
@section('page-subtitle', \Carbon\Carbon::parse($mois . '-01')->locale('fr')->isoFormat('MMMM YYYY'))

@php
    $f = fn ($n) => number_format($n, 0, ',', ' ');
    $max = max(1, $evolutionJournaliere->max('recette'));
    $labels = ['depose' => 'Déposés', 'en_traitement' => 'En traitement', 'pret' => 'Prêts', 'retire' => 'Retirés', 'annule' => 'Annulés'];
@endphp

@section('content')
<form method="GET" class="flex gap-2 mb-6">
    <input type="month" name="mois" value="{{ $mois }}" class="form-input max-w-xs">
    <button class="btn-primary">Afficher</button>
</form>

<div class="grid grid-cols-5 gap-4 mb-6">
    <div class="stat-card"><p class="text-xs text-slate-500 uppercase font-semibold">Chiffre d'affaires</p><p class="text-2xl font-bold text-slate-800 mt-1">{{ $f($chiffreAffaire) }}</p></div>
    <div class="stat-card"><p class="text-xs text-slate-500 uppercase font-semibold">Encaissé</p><p class="text-2xl font-bold text-emerald-600 mt-1">{{ $f($encaisse) }}</p></div>
    <div class="stat-card"><p class="text-xs text-slate-500 uppercase font-semibold">Reste à encaisser</p><p class="text-2xl font-bold text-red-500 mt-1">{{ $f($nonEncaisse) }}</p></div>
    <div class="stat-card"><p class="text-xs text-slate-500 uppercase font-semibold">Dépenses</p><p class="text-2xl font-bold text-slate-800 mt-1">{{ $f($totalDepenses) }}</p><p class="text-xs text-slate-400">dont achats : {{ $f($totalAchats) }}</p></div>
    <div class="stat-card"><p class="text-xs text-slate-500 uppercase font-semibold">Bénéfice net</p><p class="text-2xl font-bold {{ $beneficeNet >= 0 ? 'text-emerald-600' : 'text-red-500' }} mt-1">{{ $f($beneficeNet) }}</p></div>
</div>

<div class="grid grid-cols-2 gap-6 mb-6">
    <div class="card">
        <div class="card-header"><h2 class="font-semibold text-slate-800">Dépôts par statut</h2></div>
        <table class="table-base">
            <thead><tr><th>Statut</th><th>Nombre</th><th>Montant</th></tr></thead>
            <tbody>
            @foreach($labels as $k => $l)
                <tr><td>{{ $l }}</td><td>{{ $depotsParStatut->get($k)?->count ?? 0 }}</td><td>{{ $f($depotsParStatut->get($k)?->total ?? 0) }}</td></tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="card">
        <div class="card-header"><h2 class="font-semibold text-slate-800">Meilleurs clients</h2></div>
        <table class="table-base">
            <thead><tr><th>Client</th><th>Payé</th></tr></thead>
            <tbody>
            @forelse($topClients as $c)
                <tr><td>{{ $c->nom }} {{ $c->prenom }}</td><td>{{ $f($c->total_paye) }}</td></tr>
            @empty
                <tr><td colspan="2" class="text-center text-slate-400 py-6">Aucune donnée.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="grid grid-cols-2 gap-6">
    <div class="card">
        <div class="card-header"><h2 class="font-semibold text-slate-800">Encaissements par jour</h2></div>
        <div class="card-body space-y-1.5">
            @foreach($evolutionJournaliere as $j)
                <div class="flex items-center gap-3 text-xs">
                    <span class="w-6 text-slate-500">{{ $j['jour'] }}</span>
                    <div class="flex-1 bg-slate-100 rounded h-3"><div class="bg-amber-400 h-3 rounded" style="width: {{ round($j['recette'] / $max * 100) }}%"></div></div>
                    <span class="w-20 text-right text-slate-600">{{ $f($j['recette']) }}</span>
                </div>
            @endforeach
        </div>
    </div>
    <div class="card">
        <div class="card-header"><h2 class="font-semibold text-slate-800">Produits en alerte de stock</h2></div>
        <table class="table-base">
            <thead><tr><th>Produit</th><th>Stock</th><th>Seuil</th></tr></thead>
            <tbody>
            @forelse($produitsEnAlerte as $p)
                <tr><td><a href="{{ route('produits.show', $p) }}" class="text-amber-600 hover:underline">{{ $p->nom }}</a></td>
                    <td class="text-red-500 font-semibold">{{ (float) $p->stock_actuel }} {{ $p->unite }}</td><td>{{ (float) $p->seuil_alerte }}</td></tr>
            @empty
                <tr><td colspan="3" class="text-center text-slate-400 py-6">Aucune alerte.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
