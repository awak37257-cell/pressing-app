@extends('layouts.app')
@section('title', 'Achat #' . $achat->id)
@section('page-title', 'Achat #' . $achat->id)
@section('page-subtitle', $achat->fournisseur?->nom . ' — ' . $achat->date_achat->format('d/m/Y'))

@section('content')
<div class="card max-w-3xl">
    <div class="card-header flex items-center justify-between">
        <div>
            <h2 class="font-semibold text-slate-800">{{ $achat->fournisseur?->nom }}</h2>
            <p class="text-xs text-slate-500">Saisi par {{ $achat->user?->nom }}</p>
        </div>
        <a href="{{ route('achats.index') }}" class="btn-secondary">← Retour</a>
    </div>
    <table class="table-base">
        <thead><tr><th>Produit</th><th>Quantité</th><th>Prix unitaire</th><th class="text-right">Sous-total</th></tr></thead>
        <tbody>
        @foreach($achat->lignes as $l)
            <tr>
                <td>{{ $l->produit?->nom }}</td>
                <td>{{ (float) $l->quantite }} {{ $l->produit?->unite }}</td>
                <td>{{ number_format($l->prix_unitaire, 2, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($l->quantite * $l->prix_unitaire, 0, ',', ' ') }}</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
            <tr><td colspan="3" class="px-4 py-3 text-right font-semibold">Total</td>
                <td class="px-4 py-3 text-right font-bold text-slate-800">{{ number_format($achat->montant_total, 0, ',', ' ') }} FCFA</td></tr>
        </tfoot>
    </table>
</div>
@endsection
