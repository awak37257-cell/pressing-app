@extends('layouts.app')

@section('title', 'Rapports financiers')
@section('page-title', 'Rapports financiers')
@section('page-subtitle', 'Bilan et analyse d’activité pour ' . \Carbon\Carbon::parse($mois . '-01')->locale('fr')->isoFormat('MMMM YYYY'))

@php
    $f = fn ($n) => number_format($n, 0, ',', ' ');
    $max = max(1, $evolutionJournaliere->max('recette'));
    $labels = [
        'depose'        => ['Déposés', 'bg-blue-50 text-blue-700 border border-blue-200/60'],
        'en_traitement' => ['En traitement', 'bg-amber-50 text-amber-700 border border-amber-200/60'],
        'pret'          => ['Prêts', 'bg-emerald-50 text-emerald-700 border border-emerald-200/60'],
        'retire'        => ['Retirés', 'bg-slate-100 text-slate-600 border border-slate-200/60'],
        'annule'        => ['Annulés', 'bg-rose-50 text-rose-600 border border-rose-200/60']
    ];
@endphp

@section('content')

{{-- Filtre par mois --}}
<div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs mb-6">
    <div class="card-body p-5 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h3 class="font-bold text-slate-800 text-base">Période du rapport</h3>
            <p class="text-xs text-slate-500 mt-0.5">Sélectionnez un mois pour afficher les indicateurs financiers correspondants.</p>
        </div>
        <form method="GET" action="{{ route('rapports.index') }}" class="flex items-center gap-3">
            <input type="month" name="mois" value="{{ $mois }}" class="form-input rounded-xl border-slate-200 text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
            <button type="submit" class="inline-flex items-center gap-1.5 bg-slate-800 hover:bg-slate-900 text-white font-medium px-4 py-2.5 rounded-xl text-sm transition shadow-xs">
                Afficher
            </button>
        </form>
    </div>
</div>

{{-- KPIs Financiers --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5 mb-6">
    <div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
        <div class="h-1 bg-gradient-to-r from-blue-400 to-blue-500"></div>
        <div class="card-body p-5">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Chiffre d'affaires</p>
            <p class="text-xl font-extrabold text-slate-900 mt-1 truncate">{{ $f($chiffreAffaire) }} <span class="text-xs font-normal text-slate-500">FCFA</span></p>
        </div>
    </div>

    <div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
        <div class="h-1 bg-gradient-to-r from-emerald-400 to-emerald-500"></div>
        <div class="card-body p-5">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Encaissé</p>
            <p class="text-xl font-extrabold text-emerald-600 mt-1 truncate">{{ $f($encaisse) }} <span class="text-xs font-normal text-slate-500">FCFA</span></p>
        </div>
    </div>

    <div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
        <div class="h-1 bg-gradient-to-r from-rose-400 to-rose-600"></div>
        <div class="card-body p-5">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Reste à encaisser</p>
            <p class="text-xl font-extrabold text-rose-600 mt-1 truncate">{{ $f($nonEncaisse) }} <span class="text-xs font-normal text-slate-500">FCFA</span></p>
        </div>
    </div>

    <div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
        <div class="h-1 bg-gradient-to-r from-amber-400 to-amber-500"></div>
        <div class="card-body p-5">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Dépenses</p>
            <p class="text-xl font-extrabold text-slate-900 mt-1 truncate">{{ $f($totalDepenses) }} <span class="text-xs font-normal text-slate-500">FCFA</span></p>
            <p class="text-[11px] text-slate-400 mt-0.5">dont achats : {{ $f($totalAchats) }}</p>
        </div>
    </div>

    <div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
        <div class="h-1 bg-gradient-to-r {{ $beneficeNet >= 0 ? 'from-emerald-400 to-emerald-500' : 'from-rose-400 to-rose-600' }}"></div>
        <div class="card-body p-5">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Bénéfice net</p>
            <p class="text-xl font-extrabold {{ $beneficeNet >= 0 ? 'text-emerald-600' : 'text-rose-600' }} mt-1 truncate">
                {{ $f($beneficeNet) }} <span class="text-xs font-normal text-slate-500">FCFA</span>
            </p>
        </div>
    </div>
</div>

<div class="grid lg:grid-cols-2 gap-6 mb-6">
    {{-- Dépôts par statut --}}
    <div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
        <div class="card-header px-6 py-4.5 border-b border-slate-100 bg-slate-50/50">
            <h2 class="font-bold text-slate-800 text-base">Dépôts par statut</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 text-slate-400 uppercase text-[11px] font-bold tracking-wider border-b border-slate-200/60">
                        <th class="py-3.5 px-6">Statut</th>
                        <th class="py-3.5 px-6">Nombre</th>
                        <th class="py-3.5 px-6 text-right">Montant</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                @foreach($labels as $k => $l)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="py-4 px-6">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold {{ $l[1] }}">
                                {{ $l[0] }}
                            </span>
                        </td>
                        <td class="py-4 px-6 font-bold text-slate-800">{{ $depotsParStatut->get($k)?->count ?? 0 }}</td>
                        <td class="py-4 px-6 text-right font-extrabold text-slate-900">{{ $f($depotsParStatut->get($k)?->total ?? 0) }} FCFA</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Meilleurs clients --}}
    <div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
        <div class="card-header px-6 py-4.5 border-b border-slate-100 bg-slate-50/50">
            <h2 class="font-bold text-slate-800 text-base">Meilleurs clients</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 text-slate-400 uppercase text-[11px] font-bold tracking-wider border-b border-slate-200/60">
                        <th class="py-3.5 px-6">Client</th>
                        <th class="py-3.5 px-6 text-right">Total payé</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                @forelse($topClients as $c)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="py-4 px-6 font-bold text-slate-800">{{ $c->nom }} {{ $c->prenom }}</td>
                        <td class="py-4 px-6 text-right font-extrabold text-emerald-600">{{ $f($c->total_paye) }} FCFA</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="text-center py-12 text-slate-400 bg-slate-50/30">
                            Aucune donnée client pour cette période.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="grid lg:grid-cols-2 gap-6">
    {{-- Encaissements par jour --}}
    <div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
        <div class="card-header px-6 py-4.5 border-b border-slate-100 bg-slate-50/50">
            <h2 class="font-bold text-slate-800 text-base">Encaissements par jour</h2>
        </div>
        <div class="card-body p-6 space-y-3">
            @foreach($evolutionJournaliere as $j)
                @php $h = max(6, (int) round(($j['recette'] / $max) * 100)); @endphp
                <div class="flex items-center gap-3 text-xs">
                    <span class="w-8 font-semibold text-slate-500">{{ $j['jour'] }}</span>
                    <div class="flex-1 bg-slate-100 rounded-full h-3 overflow-hidden shadow-inner">
                        <div class="bg-gradient-to-r from-amber-400 to-amber-500 h-3 rounded-full transition-all duration-300" style="width: {{ $h }}%"></div>
                    </div>
                    <span class="w-24 text-right font-bold text-slate-800">{{ $f($j['recette']) }} <span class="font-normal text-[10px] text-slate-500">FCFA</span></span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Produits en alerte de stock --}}
    <div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
        <div class="card-header px-6 py-4.5 border-b border-slate-100 bg-slate-50/50">
            <h2 class="font-bold text-slate-800 text-base">Produits en alerte de stock</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 text-slate-400 uppercase text-[11px] font-bold tracking-wider border-b border-slate-200/60">
                        <th class="py-3.5 px-6">Produit</th>
                        <th class="py-3.5 px-6">Stock actuel</th>
                        <th class="py-3.5 px-6 text-right">Seuil d'alerte</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                @forelse($produitsEnAlerte as $p)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="py-4 px-6 font-bold">
                            <a href="{{ route('produits.show', $p) }}" class="text-amber-600 hover:text-amber-700 hover:underline">
                                {{ $p->nom }}
                            </a>
                        </td>
                        <td class="py-4 px-6 font-extrabold text-rose-600">
                            {{ (float) $p->stock_actuel }} <span class="text-xs font-normal text-slate-500">{{ $p->unite }}</span>
                        </td>
                        <td class="py-4 px-6 text-right font-semibold text-slate-600">
                            {{ (float) $p->seuil_alerte }} <span class="text-xs font-normal text-slate-500">{{ $p->unite }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center py-12 text-slate-400 bg-slate-50/30">
                            <div class="flex flex-col items-center justify-center">
                                <span class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mb-2 shadow-2xs font-bold">✓</span>
                                <p class="text-xs font-semibold text-slate-600">Aucune alerte stock active</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection