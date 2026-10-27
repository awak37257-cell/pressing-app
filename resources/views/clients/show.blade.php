@extends('layouts.app')

@section('title', $client->nom)
@section('page-title', $client->nom . ' ' . $client->prenom)
@section('page-subtitle', 'Téléphone : ' . $client->telephone . ($client->email ? ' · Email : ' . $client->email : ''))

@section('content')

{{-- Cartes statistiques modernes --}}
<div class="grid sm:grid-cols-3 gap-5 mb-6">
    <div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
        <div class="h-1 bg-gradient-to-r from-amber-400 to-amber-500"></div>
        <div class="card-body p-5 flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total dépôts</p>
                <p class="text-2xl font-extrabold text-slate-800 mt-1">{{ $client->depots_count }}</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600 shadow-2xs">
                🧺
            </div>
        </div>
    </div>

    <div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
        <div class="h-1 bg-gradient-to-r from-emerald-400 to-emerald-500"></div>
        <div class="card-body p-5 flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total payé</p>
                <p class="text-2xl font-extrabold text-emerald-600 mt-1">{{ number_format($totalPaye, 0, ',', ' ') }} <span class="text-xs font-semibold text-slate-500">FCFA</span></p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600 shadow-2xs">
                💳
            </div>
        </div>
    </div>

    <div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
        <div class="h-1 bg-gradient-to-r from-rose-400 to-rose-500"></div>
        <div class="card-body p-5 flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Reste à payer</p>
                <p class="text-2xl font-extrabold text-rose-600 mt-1">{{ number_format($resteTotal, 0, ',', ' ') }} <span class="text-xs font-semibold text-slate-500">FCFA</span></p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-rose-50 flex items-center justify-center text-rose-600 shadow-2xs">
                ⏳
            </div>
        </div>
    </div>
</div>

<div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
    <div class="card-header px-6 py-4.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
        <div>
            <h2 class="font-bold text-slate-800 text-base">Historique des dépôts</h2>
            <p class="text-xs text-slate-500 mt-0.5">Retrouvez l'ensemble des vêtements déposés par ce client.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('clients.edit', $client) }}" class="inline-flex items-center gap-1.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold px-3.5 py-2 rounded-xl text-xs transition shadow-2xs">
                ✎ Modifier la fiche
            </a>
            <a href="{{ route('clients.index') }}" class="inline-flex items-center gap-1.5 bg-slate-800 hover:bg-slate-900 text-white font-medium px-3.5 py-2 rounded-xl text-xs transition shadow-2xs">
                ← Retour
            </a>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50/80 text-slate-400 uppercase text-[11px] font-bold tracking-wider border-b border-slate-200/60">
                    <th class="py-3.5 px-6">Ticket</th>
                    <th class="py-3.5 px-6">Date</th>
                    <th class="py-3.5 px-6">Statut</th>
                    <th class="py-3.5 px-6 text-right">Total</th>
                    <th class="py-3.5 px-6 text-right">Reste</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
            @forelse($depots as $depot)
                @php
                    $badgeClass = match($depot->statut) {
                        'depose'        => 'bg-blue-50 text-blue-700 border border-blue-200/60',
                        'en_traitement' => 'bg-amber-50 text-amber-700 border border-amber-200/60',
                        'pret'          => 'bg-emerald-50 text-emerald-700 border border-emerald-200/60',
                        'retire'        => 'bg-slate-100 text-slate-600 border border-slate-200/60',
                        'annule'        => 'bg-rose-50 text-rose-600 border border-rose-200/60',
                        default         => 'bg-slate-100 text-slate-600',
                    };
                    $labelStatut = match($depot->statut) {
                        'depose'        => 'Déposé',
                        'en_traitement' => 'En cours',
                        'pret'          => 'Prêt',
                        'retire'        => 'Retiré',
                        'annule'        => 'Annulé',
                        default         => ucfirst(str_replace('_', ' ', $depot->statut)),
                    };
                @endphp
                <tr class="hover:bg-slate-50/80 transition-colors">
                    <td class="py-4 px-6">
                        <a href="{{ route('depots.show', $depot) }}" class="font-bold text-amber-600 hover:text-amber-700 hover:underline">
                            {{ $depot->numero_ticket }}
                        </a>
                    </td>
                    <td class="py-4 px-6 text-slate-500 text-xs">{{ $depot->date_depot->format('d/m/Y') }}</td>
                    <td class="py-4 px-6">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold {{ $badgeClass }}">
                            {{ $labelStatut }}
                        </span>
                    </td>
                    <td class="py-4 px-6 text-right font-bold text-slate-800">{{ number_format($depot->montant_total, 0, ',', ' ') }} FCFA</td>
                    <td class="py-4 px-6 text-right font-bold {{ $depot->reste_a_payer > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                        {{ number_format($depot->reste_a_payer, 0, ',', ' ') }} FCFA
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center py-16 text-slate-400 bg-slate-50/30">
                        <div class="flex flex-col items-center justify-center">
                            <div class="w-14 h-14 bg-slate-100 rounded-full flex items-center justify-center mb-3 text-slate-400">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            </div>
                            <p class="font-medium text-slate-600 text-base">Aucun dépôt enregistré</p>
                            <p class="text-xs text-slate-400 mt-0.5">Ce client n'a pas encore effectué de dépôt.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($depots->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/40">
            {{ $depots->links() }}
        </div>
    @endif
</div>
@endsection