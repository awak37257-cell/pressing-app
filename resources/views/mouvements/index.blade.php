@extends('layouts.app')

@section('title', 'Mouvements de stock')
@section('page-title', 'Mouvements de stock')
@section('page-subtitle', 'Historique complet des entrées, sorties, pertes et ajustements')

@php
    $types = [
        'entree'     => ['Entrée', 'bg-emerald-50 text-emerald-700 border border-emerald-200/60'],
        'sortie'     => ['Sortie', 'bg-amber-50 text-amber-700 border border-amber-200/60'],
        'perte'      => ['Perte', 'bg-rose-50 text-rose-700 border border-rose-200/60'],
        'ajustement' => ['Ajustement', 'bg-blue-50 text-blue-700 border border-blue-200/60']
    ];
@endphp

@section('content')
<div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
    {{-- Filtres --}}
    <div class="card-header px-6 py-4.5 border-b border-slate-100 flex flex-wrap items-center justify-between gap-4 bg-slate-50/50">
        <form method="GET" action="{{ route('mouvements.index') }}" class="flex flex-wrap gap-3 items-center flex-1">
            <select name="produit" class="form-select w-full sm:w-48 rounded-xl border-slate-200 text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                <option value="">Tous les produits</option>
                @foreach($produits as $p)
                    <option value="{{ $p->id }}" @selected(request('produit') == $p->id)>{{ $p->nom }}</option>
                @endforeach
            </select>

            <select name="type" class="form-select w-full sm:w-40 rounded-xl border-slate-200 text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                <option value="">Tous les types</option>
                @foreach($types as $k => $t)
                    <option value="{{ $k }}" @selected(request('type') === $k)>{{ $t[0] }}</option>
                @endforeach
            </select>

            <input type="date" name="date_debut" value="{{ request('date_debut') }}" class="form-input w-full sm:w-40 rounded-xl border-slate-200 text-sm">
            <input type="date" name="date_fin" value="{{ request('date_fin') }}" class="form-input w-full sm:w-40 rounded-xl border-slate-200 text-sm">

            <div class="flex items-center gap-2">
                <button type="submit" class="inline-flex items-center gap-1.5 bg-slate-800 hover:bg-slate-900 text-white font-medium px-4 py-2.5 rounded-xl text-sm transition shadow-xs">
                    Filtrer
                </button>
                @if(request()->hasAny(['produit', 'type', 'date_debut', 'date_fin']))
                    <a href="{{ route('mouvements.index') }}" class="inline-flex items-center px-3 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-slate-600 text-sm font-medium transition">
                        Réinitialiser
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Tableau élégant --}}
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50/80 text-slate-400 uppercase text-[11px] font-bold tracking-wider border-b border-slate-200/60">
                    <th class="py-3.5 px-6">Date</th>
                    <th class="py-3.5 px-6">Produit</th>
                    <th class="py-3.5 px-6">Type</th>
                    <th class="py-3.5 px-6">Quantité</th>
                    <th class="py-3.5 px-6">Stock après</th>
                    <th class="py-3.5 px-6">Motif</th>
                    <th class="py-3.5 px-6">Par</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
            @forelse($mouvements as $m)
                @php
                    $badge = $types[$m->type] ?? ['Inconnu', 'bg-slate-100 text-slate-600'];
                    $sign = in_array($m->type, ['entree', 'ajustement'], true) && $m->quantite > 0 ? '+' : '';
                @endphp
                <tr class="hover:bg-slate-50/80 transition-colors">
                    <td class="py-4 px-6 text-slate-500 text-xs">{{ $m->date_mouvement->format('d/m/Y H:i') }}</td>
                    <td class="py-4 px-6 font-semibold text-slate-800">{{ $m->produit?->nom }}</td>
                    <td class="py-4 px-6">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold {{ $badge[1] }}">
                            {{ $badge[0] }}
                        </span>
                    </td>
                    <td class="py-4 px-6 font-bold {{ $m->type === 'entree' ? 'text-emerald-600' : ($m->type === 'sortie' ? 'text-amber-600' : 'text-rose-600') }}">
                        {{ $sign }}{{ (float) $m->quantite }} <span class="text-xs font-normal text-slate-500">{{ $m->produit?->unite }}</span>
                    </td>
                    <td class="py-4 px-6 font-medium text-slate-700">
                        {{ (float) $m->stock_apres }} <span class="text-xs text-slate-400">{{ $m->produit?->unite }}</span>
                    </td>
                    <td class="py-4 px-6 text-slate-600 text-xs">{{ $m->motif ?? '—' }}</td>
                    <td class="py-4 px-6 text-slate-600 text-xs font-medium">{{ $m->user?->nom ?? 'Système' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-16 text-slate-400 bg-slate-50/30">
                        <div class="flex flex-col items-center justify-center">
                            <div class="w-14 h-14 bg-slate-100 rounded-full flex items-center justify-center mb-3 text-slate-400">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                            </div>
                            <p class="font-medium text-slate-600 text-base">Aucun mouvement de stock</p>
                            <p class="text-xs text-slate-400 mt-0.5">Les entrées et sorties de stock apparaîtront ici.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($mouvements->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/40">
            {{ $mouvements->links() }}
        </div>
    @endif
</div>
@endsection