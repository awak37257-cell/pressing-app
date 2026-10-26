@extends('layouts.app')
@section('title', 'Achats fournisseurs')
@section('page-title', 'Achats fournisseurs')
@section('page-subtitle', 'Approvisionnement en produits')

@section('content')
<div class="stat-card mb-6 max-w-xs">
    <p class="text-xs font-semibold text-slate-500 uppercase">Achats du mois</p>
    <p class="text-3xl font-bold text-slate-800 mt-1">{{ number_format($totalMois, 0, ',', ' ') }} <span class="text-sm font-normal">FCFA</span></p>
</div>
<div class="card">
    <div class="card-header flex flex-wrap items-center justify-between gap-4">
        <form method="GET" class="flex flex-wrap gap-2">
            <select name="fournisseur" class="form-select">
                <option value="">Tous les fournisseurs</option>
                @foreach($fournisseurs as $f)
                    <option value="{{ $f->id }}" @selected(request('fournisseur') == $f->id)>{{ $f->nom }}</option>
                @endforeach
            </select>
            <input type="date" name="date_debut" value="{{ request('date_debut') }}" class="form-input">
            <input type="date" name="date_fin" value="{{ request('date_fin') }}" class="form-input">
            <button class="btn-secondary">Filtrer</button>
        </form>
        <a href="{{ route('achats.create') }}" class="btn-primary">+ Nouvel achat</a>
    </div>
    <div class="overflow-x-auto">
        <table class="table-base">
            <thead><tr><th>N°</th><th>Date</th><th>Fournisseur</th><th>Montant</th><th class="text-right"></th></tr></thead>
            <tbody>
            @forelse($achats as $a)
                <tr>
                    <td>#{{ $a->id }}</td>
                    <td>{{ $a->date_achat->format('d/m/Y') }}</td>
                    <td>{{ $a->fournisseur?->nom }}</td>
                    <td class="font-semibold">{{ number_format($a->montant_total, 0, ',', ' ') }} FCFA</td>
                    <td class="text-right"><a href="{{ route('achats.show', $a) }}" class="text-amber-600 hover:underline text-sm">Détail</a></td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-slate-400 py-8">Aucun achat.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-6 py-3">{{ $achats->links() }}</div>
</div>
@endsection
