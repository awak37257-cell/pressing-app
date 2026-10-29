@extends('layouts.app')

@section('title', 'Rapports financiers')
@section('page-title', 'Rapports financiers')
@section('page-subtitle', 'Bilan et analyse d\'activité pour ' . \Carbon\Carbon::parse($mois . '-01')->locale('fr')->isoFormat('MMMM YYYY'))

@php
    $f = fn ($n) => number_format($n, 0, ',', ' ');
    $max = max(1, $evolutionJournaliere->max('recette'));
    $labels = [
        'depose'        => ['Déposés', 'bg-blue-100 text-blue-700 border-blue-200/70', 'bg-blue-500'],
        'en_traitement' => ['En traitement', 'bg-amber-100 text-amber-700 border-amber-200/70', 'bg-amber-500'],
        'pret'          => ['Prêts', 'bg-emerald-100 text-emerald-700 border-emerald-200/70', 'bg-emerald-500'],
        'retire'        => ['Retirés', 'bg-slate-100 text-slate-600 border-slate-200/70', 'bg-slate-400'],
        'annule'        => ['Annulés', 'bg-rose-100 text-rose-700 border-rose-200/70', 'bg-rose-500'],
    ];
@endphp

@section('content')

<style>
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes pulseRing { 0% { transform: scale(1); opacity: 0.6; } 70% { transform: scale(1.8); opacity: 0; } 100% { transform: scale(1.8); opacity: 0; } }
    @keyframes growBar { from { width: 0; opacity: 0; } to { opacity: 1; } }
    .stat-card { animation: fadeInUp 0.5s ease-out backwards; }
    .stat-card:nth-child(1) { animation-delay: 0.03s; }
    .stat-card:nth-child(2) { animation-delay: 0.06s; }
    .stat-card:nth-child(3) { animation-delay: 0.09s; }
    .stat-card:nth-child(4) { animation-delay: 0.12s; }
    .stat-card:nth-child(5) { animation-delay: 0.15s; }
    .bar-grow { animation: growBar 0.6s cubic-bezier(.2,.7,.2,1) backwards; }
    .table-row { transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
    .table-row:hover { background: linear-gradient(90deg, #fffbeb 0%, #ffffff 40%, #ffffff 100%); }
    .gradient-border { position: relative; background: white; }
    .gradient-border::before {
        content: ''; position: absolute; inset: 0; padding: 1px; border-radius: inherit;
        background: linear-gradient(135deg, rgba(251,191,36,0.3), rgba(245,158,11,0.1), rgba(251,191,36,0.3));
        -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor; mask-composite: exclude; pointer-events: none;
    }
    .ring-pulse::before { content: ''; position: absolute; inset: 0; border-radius: 9999px; background: currentColor; animation: pulseRing 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
</style>

{{-- ══════ HERO + FILTRE ══════ --}}
<div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-blue-500/30">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
        </div>
        <div>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Analyse financière</h1>
            <p class="text-xs text-slate-500 font-medium">Période : <span class="font-bold text-amber-600">{{ \Carbon\Carbon::parse($mois . '-01')->locale('fr')->isoFormat('MMMM YYYY') }}</span></p>
        </div>
    </div>

    <form method="GET" action="{{ route('rapports.index') }}" class="flex items-center gap-2 bg-white border border-slate-200 rounded-xl shadow-sm p-1.5">
        <label for="mois" class="flex items-center gap-2 px-2 text-xs font-semibold text-slate-600">
            <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            Mois
        </label>
        <input type="month" name="mois" value="{{ $mois }}"
               class="px-2 py-1.5 rounded-lg border-0 bg-transparent text-sm font-semibold text-slate-800 focus:ring-0">
        <button type="submit" class="inline-flex items-center gap-1.5 bg-slate-900 hover:bg-slate-800 text-white font-semibold px-3.5 py-2 rounded-lg text-xs transition active:scale-95">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
            Afficher
        </button>
    </form>
</div>

{{-- ══════ KPIs FINANCIERS ══════ --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">

    {{-- CA --}}
    <div class="stat-card relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 p-5 text-white shadow-xl shadow-slate-900/20">
        <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/20 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
        <div class="relative">
            <div class="flex items-start justify-between mb-3">
                <div class="w-9 h-9 rounded-lg bg-white/10 backdrop-blur-sm border border-white/20 flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <span class="text-[9px] font-bold text-blue-300 bg-blue-500/20 px-1.5 py-0.5 rounded-md tracking-wider uppercase">CA</span>
            </div>
            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-[0.1em] mb-1">Chiffre d'affaires</p>
            <p class="text-xl font-extrabold tracking-tight truncate">{{ $f($chiffreAffaire) }}</p>
            <p class="text-[10px] text-slate-400 mt-1 flex items-center gap-1">
                <span class="w-1 h-1 rounded-full bg-blue-400 ring-pulse relative"></span>
                FCFA
            </p>
        </div>
    </div>

    {{-- Encaissé --}}
    <div class="stat-card relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-600 via-emerald-600 to-teal-700 p-5 text-white shadow-xl shadow-emerald-600/20">
        <div class="absolute top-0 right-0 w-32 h-32 bg-teal-400/25 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
        <div class="relative">
            <div class="flex items-start justify-between mb-3">
                <div class="w-9 h-9 rounded-lg bg-white/15 backdrop-blur-sm border border-white/25 flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="text-[9px] font-bold text-white bg-white/20 px-1.5 py-0.5 rounded-md tracking-wider uppercase">In</span>
            </div>
            <p class="text-[10px] font-semibold text-white/70 uppercase tracking-[0.1em] mb-1">Encaissé</p>
            <p class="text-xl font-extrabold tracking-tight truncate">{{ $f($encaisse) }}</p>
            <p class="text-[10px] text-white/80 mt-1 flex items-center gap-1">
                <span class="w-1 h-1 rounded-full bg-teal-300"></span>
                FCFA
            </p>
        </div>
    </div>

    {{-- Non encaissé --}}
    <div class="stat-card relative overflow-hidden rounded-2xl bg-gradient-to-br from-rose-600 via-rose-600 to-pink-700 p-5 text-white shadow-xl shadow-rose-600/20">
        <div class="absolute top-0 right-0 w-32 h-32 bg-pink-400/25 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
        <div class="relative">
            <div class="flex items-start justify-between mb-3">
                <div class="w-9 h-9 rounded-lg bg-white/15 backdrop-blur-sm border border-white/25 flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="text-[9px] font-bold text-pink-200 bg-pink-500/20 px-1.5 py-0.5 rounded-md tracking-wider uppercase">Out</span>
            </div>
            <p class="text-[10px] font-semibold text-white/70 uppercase tracking-[0.1em] mb-1">Reste à encaisser</p>
            <p class="text-xl font-extrabold tracking-tight truncate">{{ $f($nonEncaisse) }}</p>
            <p class="text-[10px] text-white/80 mt-1 flex items-center gap-1">
                <span class="w-1 h-1 rounded-full bg-pink-300 ring-pulse relative"></span>
                FCFA
            </p>
        </div>
    </div>

    {{-- Dépenses --}}
    <div class="stat-card relative overflow-hidden rounded-2xl bg-gradient-to-br from-amber-500 via-orange-500 to-orange-600 p-5 text-white shadow-xl shadow-amber-500/30">
        <div class="absolute top-0 right-0 w-32 h-32 bg-yellow-300/25 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
        <div class="relative">
            <div class="flex items-start justify-between mb-3">
                <div class="w-9 h-9 rounded-lg bg-white/15 backdrop-blur-sm border border-white/25 flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <span class="text-[9px] font-bold text-white bg-white/20 px-1.5 py-0.5 rounded-md tracking-wider uppercase">Dep</span>
            </div>
            <p class="text-[10px] font-semibold text-white/80 uppercase tracking-[0.1em] mb-1">Dépenses</p>
            <p class="text-xl font-extrabold tracking-tight truncate">{{ $f($totalDepenses) }}</p>
            <p class="text-[9px] text-white/80 mt-1 flex items-center gap-1">
                dont achats : <span class="font-bold">{{ $f($totalAchats) }}</span>
            </p>
        </div>
    </div>

    {{-- Bénéfice --}}
    <div class="stat-card relative overflow-hidden rounded-2xl bg-gradient-to-br {{ $beneficeNet >= 0 ? 'from-violet-600 via-violet-600 to-purple-700 shadow-violet-600/20' : 'from-rose-600 via-rose-600 to-red-700 shadow-rose-600/20' }} p-5 text-white shadow-xl">
        <div class="absolute top-0 right-0 w-32 h-32 {{ $beneficeNet >= 0 ? 'bg-fuchsia-400/20' : 'bg-red-400/25' }} rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
        <div class="relative">
            <div class="flex items-start justify-between mb-3">
                <div class="w-9 h-9 rounded-lg bg-white/15 backdrop-blur-sm border border-white/25 flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
                <span class="text-[9px] font-bold bg-white/20 px-1.5 py-0.5 rounded-md tracking-wider uppercase">
                    {{ $beneficeNet >= 0 ? 'Profit' : 'Perte' }}
                </span>
            </div>
            <p class="text-[10px] font-semibold text-white/70 uppercase tracking-[0.1em] mb-1">Bénéfice net</p>
            <p class="text-xl font-extrabold tracking-tight truncate">{{ $f($beneficeNet) }}</p>
            <p class="text-[10px] text-white/80 mt-1 flex items-center gap-1">
                <span class="w-1 h-1 rounded-full bg-white/80 {{ $beneficeNet >= 0 ? 'ring-pulse relative' : '' }}"></span>
                FCFA
            </p>
        </div>
    </div>
</div>

<div class="grid lg:grid-cols-2 gap-6 mb-6">

    {{-- ══════ DÉPÔTS PAR STATUT ══════ --}}
    <div class="gradient-border rounded-2xl bg-white shadow-lg shadow-slate-200/50 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-white flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-white shadow-md shadow-amber-500/25">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <div>
                <h3 class="font-bold text-slate-800 text-sm">Dépôts par statut</h3>
                <p class="text-[11px] text-slate-500">Vue d'ensemble de la période</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/70 border-b border-slate-200/60">
                        <th class="py-3 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">Statut</th>
                        <th class="py-3 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em] text-center">Nombre</th>
                        <th class="py-3 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em] text-right">Montant</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                @foreach($labels as $k => $l)
                    <tr class="table-row">
                        <td class="py-3.5 px-6">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $l[1] }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $l[2] }}"></span>
                                {{ $l[0] }}
                            </span>
                        </td>
                        <td class="py-3.5 px-6 text-center font-extrabold text-slate-900">{{ $depotsParStatut->get($k)?->count ?? 0 }}</td>
                        <td class="py-3.5 px-6 text-right font-extrabold text-slate-900">{{ $f($depotsParStatut->get($k)?->total ?? 0) }} <span class="text-[10px] font-semibold text-slate-400">FCFA</span></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ══════ MEILLEURS CLIENTS ══════ --}}
    <div class="gradient-border rounded-2xl bg-white shadow-lg shadow-slate-200/50 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-white flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center text-white shadow-md shadow-violet-500/25">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                </svg>
            </div>
            <div>
                <h3 class="font-bold text-slate-800 text-sm">Meilleurs clients</h3>
                <p class="text-[11px] text-slate-500">Top 5 de la période</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/70 border-b border-slate-200/60">
                        <th class="py-3 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">Client</th>
                        <th class="py-3 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em] text-right">Total payé</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                @forelse($topClients as $i => $c)
                    <tr class="table-row">
                        <td class="py-3.5 px-6">
                            <div class="flex items-center gap-2.5">
                                <span class="w-6 h-6 rounded-md bg-gradient-to-br from-amber-100 to-orange-200 text-amber-700 text-[10px] font-extrabold flex items-center justify-center uppercase">
                                    {{ $i + 1 }}
                                </span>
                                <span class="font-bold text-slate-900">{{ $c->nom }} {{ $c->prenom }}</span>
                            </div>
                        </td>
                        <td class="py-3.5 px-6 text-right font-extrabold text-emerald-600">{{ $f($c->total_paye) }} <span class="text-[10px] font-semibold text-emerald-400">FCFA</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="py-12 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mb-2">
                                    <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <p class="text-xs text-slate-400 italic">Aucune donnée client pour cette période.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="grid lg:grid-cols-2 gap-6">

    {{-- ══════ ENCAISSEMENTS PAR JOUR ══════ --}}
    <div class="gradient-border rounded-2xl bg-white shadow-lg shadow-slate-200/50 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-white flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white shadow-md shadow-emerald-500/25">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <div>
                <h3 class="font-bold text-slate-800 text-sm">Encaissements par jour</h3>
                <p class="text-[11px] text-slate-500">Évolution quotidienne du mois</p>
            </div>
        </div>
        <div class="p-6 space-y-2.5">
            @foreach($evolutionJournaliere as $i => $j)
                @php $h = max(6, (int) round(($j['recette'] / $max) * 100)); @endphp
                <div class="flex items-center gap-3 text-xs group">
                    <span class="w-8 font-bold text-slate-600 text-center">{{ $j['jour'] }}</span>
                    <div class="flex-1 bg-slate-100 rounded-full h-3 overflow-hidden shadow-inner">
                        <div class="bar-grow bg-gradient-to-r from-amber-400 via-amber-500 to-orange-500 h-3 rounded-full transition-all duration-300 group-hover:from-amber-500 group-hover:to-orange-600" 
                             style="width: {{ $h }}%; animation-delay: {{ $i * 0.03 }}s;"></div>
                    </div>
                    <span class="w-24 text-right font-extrabold text-slate-800">{{ $f($j['recette']) }} <span class="font-normal text-[9px] text-slate-400">FCFA</span></span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ══════ PRODUITS EN ALERTE ══════ --}}
    <div class="gradient-border rounded-2xl bg-white shadow-lg shadow-slate-200/50 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-white flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-rose-500 to-pink-600 flex items-center justify-center text-white shadow-md shadow-rose-500/25">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div>
                <h3 class="font-bold text-slate-800 text-sm">Produits en alerte de stock</h3>
                <p class="text-[11px] text-slate-500">{{ $produitsEnAlerte->count() }} produit(s) à réapprovisionner</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/70 border-b border-slate-200/60">
                        <th class="py-3 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">Produit</th>
                        <th class="py-3 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">Stock</th>
                        <th class="py-3 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em] text-right">Seuil</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                @forelse($produitsEnAlerte as $p)
                    <tr class="table-row">
                        <td class="py-3.5 px-6">
                            <a href="{{ route('produits.show', $p) }}" class="font-bold text-amber-600 hover:text-amber-700 hover:underline transition">
                                {{ $p->nom }}
                            </a>
                        </td>
                        <td class="py-3.5 px-6">
                            <span class="font-extrabold text-rose-600">{{ (float) $p->stock_actuel }}</span>
                            <span class="text-[10px] font-semibold text-slate-400 ml-1">{{ $p->unite }}</span>
                        </td>
                        <td class="py-3.5 px-6 text-right">
                            <span class="font-bold text-slate-600">{{ (float) $p->seuil_alerte }}</span>
                            <span class="text-[10px] font-semibold text-slate-400 ml-1">{{ $p->unite }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="py-12 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-12 h-12 rounded-full bg-emerald-50 flex items-center justify-center mb-2 shadow-sm">
                                    <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <p class="text-xs font-bold text-slate-700">Stock en parfaite santé</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">Aucune alerte active</p>
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