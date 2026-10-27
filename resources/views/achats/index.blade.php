@extends('layouts.app')

@section('title', 'Achats fournisseurs')
@section('page-title', 'Achats fournisseurs')
@section('page-subtitle', 'Suivi des approvisionnements et gestion des stocks')

@section('content')

{{-- Carte statistique moderne --}}
<div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs max-w-sm mb-6">
    <div class="h-1 bg-gradient-to-r from-amber-400 to-amber-500"></div>
    <div class="card-body p-5 flex items-center justify-between">
        <div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Achats du mois</p>
            <p class="text-2xl font-extrabold text-slate-800 mt-1">{{ number_format($totalMois, 0, ',', ' ') }} <span class="text-sm font-semibold text-slate-500">FCFA</span></p>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600 shadow-2xs">
            📦
        </div>
    </div>
</div>

<div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
    {{-- Filtres et bouton d'action --}}
    <div class="card-header px-6 py-4.5 border-b border-slate-100 flex flex-wrap items-center justify-between gap-4 bg-slate-50/50">
        <form method="GET" class="flex flex-wrap gap-3 items-center flex-1">
            <select name="fournisseur" class="form-select w-full sm:w-52 rounded-xl border-slate-200 text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                <option value="">Tous les fournisseurs</option>
                @foreach($fournisseurs as $f)
                    <option value="{{ $f->id }}" @selected(request('fournisseur') == $f->id)>{{ $f->nom }}</option>
                @endforeach
            </select>

            <input type="date" name="date_debut" value="{{ request('date_debut') }}" class="form-input w-full sm:w-40 rounded-xl border-slate-200 text-sm">
            <input type="date" name="date_fin" value="{{ request('date_fin') }}" class="form-input w-full sm:w-40 rounded-xl border-slate-200 text-sm">

            <div class="flex items-center gap-2">
                <button type="submit" class="inline-flex items-center gap-1.5 bg-slate-800 hover:bg-slate-900 text-white font-medium px-4 py-2.5 rounded-xl text-sm transition shadow-xs">
                    Filtrer
                </button>
                @if(request()->hasAny(['fournisseur', 'date_debut', 'date_fin']))
                    <a href="{{ route('achats.index') }}" class="inline-flex items-center px-3 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-slate-600 text-sm font-medium transition">
                        Réinitialiser
                    </a>
                @endif
            </div>
        </form>

        <a href="{{ route('achats.create') }}" class="inline-flex items-center gap-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-semibold px-4 py-2.5 rounded-xl shadow-md shadow-amber-500/20 text-sm transition-all duration-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nouvel achat
        </a>
    </div>

    {{-- Tableau élégant --}}
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50/80 text-slate-400 uppercase text-[11px] font-bold tracking-wider border-b border-slate-200/60">
                    <th class="py-3.5 px-6">N°</th>
                    <th class="py-3.5 px-6">Date</th>
                    <th class="py-3.5 px-6">Fournisseur</th>
                    <th class="py-3.5 px-6 text-right">Montant</th>
                    <th class="py-3.5 px-6 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
            @forelse($achats as $a)
                <tr class="hover:bg-slate-50/80 transition-colors">
                    <td class="py-4 px-6 font-bold text-amber-600">#{{ $a->id }}</td>
                    <td class="py-4 px-6 text-slate-500 text-xs">{{ $a->date_achat->format('d/m/Y') }}</td>
                    <td class="py-4 px-6 font-semibold text-slate-800">{{ $a->fournisseur?->nom }}</td>
                    <td class="py-4 px-6 text-right font-bold text-slate-900">{{ number_format($a->montant_total, 0, ',', ' ') }} FCFA</td>
                    <td class="py-4 px-6 text-right">
                        <a href="{{ route('achats.show', $a) }}" class="inline-flex items-center gap-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs px-3 py-1.5 rounded-xl transition">
                            Détail
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center py-16 text-slate-400 bg-slate-50/30">
                        <div class="flex flex-col items-center justify-center">
                            <div class="w-14 h-14 bg-slate-100 rounded-full flex items-center justify-center mb-3 text-slate-400">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            </div>
                            <p class="font-medium text-slate-600 text-base">Aucun achat enregistré</p>
                            <p class="text-xs text-slate-400 mt-0.5">Enregistrez un nouvel approvisionnement pour alimenter vos stocks.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($achats->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/40">
            {{ $achats->links() }}
        </div>
    @endif
</div>
@endsection