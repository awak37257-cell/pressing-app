@extends('layouts.app')
@section('title', 'Dépenses')
@section('page-title', 'Dépenses')
@section('page-subtitle', 'Charges et achats du pressing')

@section('content')
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="stat-card">
        <p class="text-xs font-semibold text-slate-500 uppercase">Total du mois</p>
        <p class="text-3xl font-bold text-slate-800 mt-1">{{ number_format($totalMois, 0, ',', ' ') }} <span class="text-sm font-normal">FCFA</span></p>
    </div>
    <div class="stat-card col-span-2">
        <p class="text-xs font-semibold text-slate-500 uppercase mb-2">Par catégorie (mois)</p>
        <div class="flex flex-wrap gap-2">
            @forelse($parCategorie as $p)
                <span class="badge-neutral">{{ $p->categorie?->nom ?? '—' }} : {{ number_format($p->total, 0, ',', ' ') }}</span>
            @empty
                <span class="text-sm text-slate-400">Aucune dépense ce mois-ci.</span>
            @endforelse
        </div>
    </div>
</div>
<div class="card">
    <div class="card-header flex flex-wrap items-center justify-between gap-4">
        <form method="GET" class="flex flex-wrap gap-2">
            <select name="categorie" class="form-select">
                <option value="">Toutes catégories</option>
                @foreach($categories as $c)
                    <option value="{{ $c->id }}" @selected(request('categorie') == $c->id)>{{ $c->nom }}</option>
                @endforeach
            </select>
            <input type="date" name="date_debut" value="{{ request('date_debut') }}" class="form-input">
            <input type="date" name="date_fin" value="{{ request('date_fin') }}" class="form-input">
            <button class="btn-secondary">Filtrer</button>
        </form>
        <a href="{{ route('depenses.create') }}" class="btn-primary">+ Nouvelle dépense</a>
    </div>
    <div class="overflow-x-auto">
        <table class="table-base">
            <thead><tr><th>Date</th><th>Libellé</th><th>Catégorie</th><th>Mode</th><th>Montant</th><th class="text-right">Actions</th></tr></thead>
            <tbody>
            @forelse($depenses as $d)
                <tr>
                    <td>{{ $d->date_depense->format('d/m/Y') }}</td>
                    <td>{{ $d->libelle }}</td>
                    <td>{{ $d->categorie?->nom }}</td>
                    <td>{{ str_replace('_', ' ', $d->mode_paiement) }}</td>
                    <td class="font-semibold">{{ number_format($d->montant, 0, ',', ' ') }}</td>
                    <td class="text-right">
                        @if($d->achat_id)
                            <a href="{{ route('achats.show', $d->achat_id) }}" class="text-slate-500 hover:underline text-sm">Voir l'achat</a>
                        @else
                            <a href="{{ route('depenses.edit', $d) }}" class="text-amber-600 hover:underline text-sm">Modifier</a>
                            <form method="POST" action="{{ route('depenses.destroy', $d) }}" class="inline" onsubmit="return confirm('Supprimer cette dépense ?')">
                                @csrf @method('DELETE')
                                <button class="text-red-500 hover:underline text-sm ml-3">Supprimer</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-slate-400 py-8">Aucune dépense.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-6 py-3">{{ $depenses->links() }}</div>
</div>
@endsection
