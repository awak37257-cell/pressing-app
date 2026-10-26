@extends('layouts.app')
@section('title', $client->nom)
@section('page-title', $client->nom . ' ' . $client->prenom)
@section('page-subtitle', $client->telephone)

@section('content')
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="stat-card"><p class="text-xs font-semibold text-slate-500 uppercase">Dépôts</p><p class="text-3xl font-bold text-slate-800 mt-1">{{ $client->depots_count }}</p></div>
    <div class="stat-card"><p class="text-xs font-semibold text-slate-500 uppercase">Total payé</p><p class="text-3xl font-bold text-emerald-600 mt-1">{{ number_format($totalPaye, 0, ',', ' ') }}</p></div>
    <div class="stat-card"><p class="text-xs font-semibold text-slate-500 uppercase">Reste à payer</p><p class="text-3xl font-bold text-red-500 mt-1">{{ number_format($resteTotal, 0, ',', ' ') }}</p></div>
</div>
<div class="card">
    <div class="card-header flex items-center justify-between">
        <h2 class="font-semibold text-slate-800">Historique des dépôts</h2>
        <a href="{{ route('clients.edit', $client) }}" class="btn-secondary">Modifier la fiche</a>
    </div>
    <div class="overflow-x-auto">
        <table class="table-base">
            <thead><tr><th>Ticket</th><th>Date</th><th>Statut</th><th>Total</th><th>Reste</th></tr></thead>
            <tbody>
            @forelse($depots as $depot)
                <tr>
                    <td><a href="{{ route('depots.show', $depot) }}" class="text-amber-600 hover:underline">{{ $depot->numero_ticket }}</a></td>
                    <td>{{ $depot->date_depot->format('d/m/Y') }}</td>
                    <td><span class="badge-neutral">{{ ucfirst(str_replace('_', ' ', $depot->statut)) }}</span></td>
                    <td>{{ number_format($depot->montant_total, 0, ',', ' ') }}</td>
                    <td>{{ number_format($depot->reste_a_payer, 0, ',', ' ') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-slate-400 py-8">Aucun dépôt.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-6 py-3">{{ $depots->links() }}</div>
</div>
@endsection
