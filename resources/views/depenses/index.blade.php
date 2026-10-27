@extends('layouts.app')

@section('title', 'Dépenses')
@section('page-title', 'Dépenses')
@section('page-subtitle', 'Suivi des charges financières et des dépenses du pressing')

@section('content')

{{-- Cartes de synthèse modernes --}}
<div class="grid lg:grid-cols-3 gap-5 mb-6">
    <div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
        <div class="h-1.5 bg-gradient-to-r from-rose-400 to-rose-600"></div>
        <div class="card-body p-5 flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total du mois</p>
                <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ number_format($totalMois, 0, ',', ' ') }} <span class="text-xs font-semibold text-slate-500">FCFA</span></p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-rose-50 flex items-center justify-center text-rose-600 shadow-2xs">
                📉
            </div>
        </div>
    </div>

    <div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs lg:col-span-2">
        <div class="h-1.5 bg-gradient-to-r from-amber-400 to-amber-500"></div>
        <div class="card-body p-5">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2.5">Par catégorie (ce mois)</p>
            <div class="flex flex-wrap gap-2">
                @forelse($parCategorie as $p)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200/60 shadow-2xs">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        {{ $p->categorie?->nom ?? '—' }} : <span class="text-slate-900">{{ number_format($p->total, 0, ',', ' ') }} FCFA</span>
                    </span>
                @empty
                    <span class="text-xs text-slate-400 italic">Aucune dépense enregistrée ce mois-ci.</span>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
    {{-- Filtres et action --}}
    <div class="card-header px-6 py-4.5 border-b border-slate-100 flex flex-wrap items-center justify-between gap-4 bg-slate-50/50">
        <form method="GET" action="{{ route('depenses.index') }}" class="flex flex-wrap gap-3 items-center flex-1">
            <select name="categorie" class="form-select w-full sm:w-52 rounded-xl border-slate-200 text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                <option value="">Toutes les catégories</option>
                @foreach($categories as $c)
                    <option value="{{ $c->id }}" @selected(request('categorie') == $c->id)>{{ $c->nom }}</option>
                @endforeach
            </select>

            <input type="date" name="date_debut" value="{{ request('date_debut') }}" class="form-input w-full sm:w-40 rounded-xl border-slate-200 text-sm">
            <input type="date" name="date_fin" value="{{ request('date_fin') }}" class="form-input w-full sm:w-40 rounded-xl border-slate-200 text-sm">

            <div class="flex items-center gap-2">
                <button type="submit" class="inline-flex items-center gap-1.5 bg-slate-800 hover:bg-slate-900 text-white font-medium px-4 py-2.5 rounded-xl text-sm transition shadow-xs">
                    Filtrer
                </button>
                @if(request()->hasAny(['categorie', 'date_debut', 'date_fin']))
                    <a href="{{ route('depenses.index') }}" class="inline-flex items-center px-3 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-slate-600 text-sm font-medium transition">
                        Réinitialiser
                    </a>
                @endif
            </div>
        </form>

        <a href="{{ route('depenses.create') }}" class="inline-flex items-center gap-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-semibold px-4 py-2.5 rounded-xl shadow-md shadow-amber-500/20 text-sm transition-all duration-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nouvelle dépense
        </a>
    </div>

    {{-- Tableau élégant --}}
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50/80 text-slate-400 uppercase text-[11px] font-bold tracking-wider border-b border-slate-200/60">
                    <th class="py-3.5 px-6">Date</th>
                    <th class="py-3.5 px-6">Libellé</th>
                    <th class="py-3.5 px-6">Catégorie</th>
                    <th class="py-3.5 px-6">Mode</th>
                    <th class="py-3.5 px-6 text-right">Montant</th>
                    <th class="py-3.5 px-6 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
            @forelse($depenses as $d)
                <tr class="hover:bg-slate-50/80 transition-colors">
                    <td class="py-4 px-6 text-slate-500 text-xs">{{ $d->date_depense->format('d/m/Y') }}</td>
                    <td class="py-4 px-6 font-semibold text-slate-800">{{ $d->libelle }}</td>
                    <td class="py-4 px-6">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700">
                            {{ $d->categorie?->nom ?? '—' }}
                        </span>
                    </td>
                    <td class="py-4 px-6 text-slate-600 capitalize">{{ str_replace('_', ' ', $d->mode_paiement) }}</td>
                    <td class="py-4 px-6 text-right font-extrabold text-rose-600">{{ number_format($d->montant, 0, ',', ' ') }} FCFA</td>
                    <td class="py-4 px-6 text-right">
                        @if($d->achat_id)
                            <a href="{{ route('achats.show', $d->achat_id) }}" class="inline-flex items-center gap-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs px-3 py-1.5 rounded-xl transition">
                                Voir l'achat
                            </a>
                        @else
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('depenses.edit', $d) }}" class="p-2 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-600 hover:text-amber-800 transition shadow-2xs" title="Modifier">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form method="POST" action="{{ route('depenses.destroy', $d) }}" class="inline" onsubmit="return confirm('Supprimer cette dépense ?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 hover:text-rose-800 transition shadow-2xs" title="Supprimer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-16 text-slate-400 bg-slate-50/30">
                        <div class="flex flex-col items-center justify-center">
                            <div class="w-14 h-14 bg-slate-100 rounded-full flex items-center justify-center mb-3 text-slate-400">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            </div>
                            <p class="font-medium text-slate-600 text-base">Aucune dépense enregistrée</p>
                            <p class="text-xs text-slate-400 mt-0.5">Enregistrez vos charges pour suivre la rentabilité du pressing.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($depenses->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/40">
            {{ $depenses->links() }}
        </div>
    @endif
</div>
@endsection