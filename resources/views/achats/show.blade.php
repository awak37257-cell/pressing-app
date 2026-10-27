@extends('layouts.app')

@section('title', 'Achat #' . $achat->id)
@section('page-title', 'Achat #' . $achat->id)
@section('page-subtitle', $achat->fournisseur?->nom . ' — ' . $achat->date_achat->format('d/m/Y'))

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
        <div class="h-1.5 bg-gradient-to-r from-amber-400 to-amber-500"></div>
        
        {{-- En-tête de la carte --}}
        <div class="card-header px-6 py-4.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div>
                <h2 class="font-bold text-slate-800 text-base">{{ $achat->fournisseur?->nom }}</h2>
                <p class="text-xs text-slate-500 mt-0.5">Enregistré par <span class="font-semibold text-slate-700">{{ $achat->user?->nom ?? 'Système' }}</span></p>
            </div>
            <a href="{{ route('achats.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 px-3 py-2 rounded-xl transition shadow-2xs">
                ← Retour aux achats
            </a>
        </div>

        {{-- Tableau des lignes d'achat --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 text-slate-400 uppercase text-[11px] font-bold tracking-wider border-b border-slate-200/60">
                        <th class="py-3.5 px-6">Produit</th>
                        <th class="py-3.5 px-6">Quantité</th>
                        <th class="py-3.5 px-6 text-right">Prix unitaire</th>
                        <th class="py-3.5 px-6 text-right">Sous-total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                @foreach($achat->lignes as $l)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="py-4 px-6 font-semibold text-slate-800">{{ $l->produit?->nom }}</td>
                        <td class="py-4 px-6 text-slate-600">{{ (float) $l->quantite }} <span class="text-xs font-medium text-slate-400">{{ $l->produit?->unite }}</span></td>
                        <td class="py-4 px-6 text-right text-slate-600">{{ number_format($l->prix_unitaire, 2, ',', ' ') }} FCFA</td>
                        <td class="py-4 px-6 text-right font-bold text-slate-900">{{ number_format($l->quantite * $l->prix_unitaire, 0, ',', ' ') }} FCFA</td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot class="bg-slate-50/80 border-t border-slate-200/60">
                    <tr>
                        <td colspan="3" class="px-6 py-4 text-right font-bold text-slate-700 text-sm">Montant total</td>
                        <td class="px-6 py-4 text-right font-extrabold text-amber-600 text-base">{{ number_format($achat->montant_total, 0, ',', ' ') }} FCFA</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection