@extends('layouts.app')
@section('title', 'Mouvements de stock')
@section('page-title', 'Mouvements de stock')
@section('page-subtitle', 'Historique complet des entrées et sorties')

@php
    $types = ['entree' => ['Entrée', 'badge-success'], 'sortie' => ['Sortie', 'badge-warning'], 'perte' => ['Perte', 'badge-danger'], 'ajustement' => ['Ajustement', 'badge-info']];
@endphp

@section('content')
<div class="card">
    <div class="card-header">
        <form method="GET" class="flex flex-wrap gap-2">
            <select name="produit" class="form-select">
                <option value="">Tous les produits</option>
                @foreach($produits as $p)
                    <option value="{{ $p->id }}" @selected(request('produit') == $p->id)>{{ $p->nom }}</option>
                @endforeach
            </select>
            <select name="type" class="form-select">
                <option value="">Tous les types</option>
                @foreach($types as $k => $t)
                    <option value="{{ $k }}" @selected(request('type') === $k)>{{ $t[0] }}</option>
                @endforeach
            </select>
            <input type="date" name="date_debut" value="{{ request('date_debut') }}" class="form-input">
            <input type="date" name="date_fin" value="{{ request('date_fin') }}" class="form-input">
            <button class="btn-secondary">Filtrer</button>
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="table-base">
            <thead><tr><th>Date</th><th>Produit</th><th>Type</th><th>Quantité</th><th>Stock après</th><th>Motif</th><th>Par</th></tr></thead>
            <tbody>
            @forelse($mouvements as $m)
                <tr>
                    <td>{{ $m->date_mouvement->format('d/m/Y H:i') }}</td>
                    <td>{{ $m->produit?->nom }}</td>
                    <td><span class="{{ $types[$m->type][1] }}">{{ $types[$m->type][0] }}</span></td>
                    <td>{{ (float) $m->quantite }}</td>
                    <td>{{ (float) $m->stock_apres }} {{ $m->produit?->unite }}</td>
                    <td>{{ $m->motif }}</td>
                    <td>{{ $m->user?->nom }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-slate-400 py-8">Aucun mouvement.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-6 py-3">{{ $mouvements->links() }}</div>
</div>
@endsection
