@extends('layouts.app')

@section('title', 'Dépenses')
@section('page-title', 'Dépenses')
@section('page-subtitle', 'Suivi des charges financières et des dépenses du pressing')

@section('content')

<style>
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(8px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes pulseRing {
        0%   { transform: scale(1); opacity: 0.6; }
        70%  { transform: scale(1.8); opacity: 0; }
        100% { transform: scale(1.8); opacity: 0; }
    }
    .stat-card { animation: fadeInUp 0.5s ease-out backwards; }
    .stat-card:nth-child(1) { animation-delay: 0.05s; }
    .stat-card:nth-child(2) { animation-delay: 0.10s; }
    .table-row { transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
    .table-row:hover { background: linear-gradient(90deg, #fff1f2 0%, #ffffff 40%, #ffffff 100%); }
    .table-row:hover .row-icon { transform: scale(1.1) rotate(-3deg); }
    .table-row:hover .action-btn { opacity: 1; transform: translateX(0); }
    .action-btn { opacity: 0.5; transform: translateX(4px); transition: all 0.25s ease; }
    .gradient-border { position: relative; background: white; }
    .gradient-border::before {
        content: ''; position: absolute; inset: 0; padding: 1px; border-radius: inherit;
        background: linear-gradient(135deg, rgba(251,191,36,0.3), rgba(245,158,11,0.1), rgba(251,191,36,0.3));
        -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor; mask-composite: exclude; pointer-events: none;
    }
    .ring-pulse::before {
        content: ''; position: absolute; inset: 0; border-radius: 9999px;
        background: currentColor; animation: pulseRing 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
</style>

{{-- ══════ HERO ══════ --}}
<div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-8">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-rose-500 to-pink-600 flex items-center justify-center shadow-lg shadow-rose-500/30">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
        </div>
        <div>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Suivi des dépenses</h1>
            <p class="text-xs text-slate-500 font-medium">Charges et sorties financières du pressing</p>
        </div>
    </div>
    <a href="{{ route('depenses.create') }}"
       class="group inline-flex items-center gap-2 bg-gradient-to-r from-amber-500 via-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-bold px-5 py-2.5 rounded-xl shadow-lg shadow-amber-500/30 hover:shadow-xl text-sm transition-all duration-300 hover:-translate-y-0.5 active:scale-95">
        <svg class="w-4 h-4 group-hover:rotate-90 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Nouvelle dépense
    </a>
</div>

{{-- ══════ STATS ══════ --}}
<div class="grid lg:grid-cols-3 gap-5 mb-8">

    {{-- Total du mois --}}
    <div class="stat-card relative overflow-hidden rounded-2xl bg-gradient-to-br from-rose-600 via-rose-600 to-pink-700 p-6 text-white shadow-xl shadow-rose-600/20">
        <div class="absolute top-0 right-0 w-40 h-40 bg-pink-400/25 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
        <div class="absolute bottom-0 left-0 w-32 h-32 bg-rose-400/10 rounded-full blur-2xl translate-y-1/2 -translate-x-1/2"></div>
        <div class="relative">
            <div class="flex items-start justify-between mb-4">
                <div class="w-11 h-11 rounded-xl bg-white/15 backdrop-blur-sm border border-white/25 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                    </svg>
                </div>
                <span class="text-[10px] font-bold bg-white/20 px-2 py-1 rounded-md tracking-wider uppercase">Ce mois</span>
            </div>
            <p class="text-[11px] font-semibold text-white/70 uppercase tracking-[0.1em] mb-1">Total dépenses</p>
            <p class="text-3xl font-extrabold tracking-tight">{{ number_format($totalMois, 0, ',', ' ') }} <span class="text-sm font-semibold text-white/60">FCFA</span></p>
            <p class="text-xs text-white/80 mt-2 flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-white/80 ring-pulse relative"></span>
                Charges cumulées du mois
            </p>
        </div>
    </div>

    {{-- Répartition par catégorie --}}
    <div class="stat-card relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 p-6 text-white shadow-xl shadow-slate-900/20 lg:col-span-2">
        <div class="absolute top-0 right-0 w-48 h-48 bg-amber-500/15 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/10 backdrop-blur-sm border border-white/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Ce mois</p>
                        <p class="text-sm font-bold">Répartition par catégorie</p>
                    </div>
                </div>
                <span class="text-[10px] font-bold text-amber-300 bg-amber-500/20 px-2.5 py-1 rounded-md tracking-wider uppercase">{{ $parCategorie->count() }} catégorie(s)</span>
            </div>

            <div class="flex flex-wrap gap-2">
                @forelse($parCategorie as $p)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-white/[0.08] hover:bg-white/[0.15] text-slate-200 border border-white/10 hover:border-amber-400/30 shadow-sm backdrop-blur-sm transition-all duration-200">
                        <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                        {{ $p->categorie?->nom ?? '—' }}
                        <span class="text-amber-300">·</span>
                        <span class="text-white font-extrabold">{{ number_format($p->total, 0, ',', ' ') }} FCFA</span>
                    </span>
                @empty
                    <span class="text-xs text-slate-500 italic py-2">Aucune dépense enregistrée ce mois-ci.</span>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- ══════ CONTENEUR PRINCIPAL ══════ --}}
<div class="gradient-border rounded-2xl bg-white shadow-lg shadow-slate-200/50 overflow-hidden">

    {{-- Filtres --}}
    <div class="px-6 py-4 border-b border-slate-100 bg-white">
        <form method="GET" action="{{ route('depenses.index') }}" class="flex flex-col lg:flex-row gap-3 lg:items-center">
            <div class="flex flex-wrap gap-2.5 items-center flex-1">
                {{-- Catégorie --}}
                <div class="relative">
                    <select name="categorie" class="appearance-none pl-3.5 pr-9 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-sm text-slate-700 focus:bg-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all cursor-pointer">
                        <option value="">Toutes les catégories</option>
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}" @selected(request('categorie') == $c->id)>{{ $c->nom }}</option>
                        @endforeach
                    </select>
                    <svg class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>

                {{-- Dates --}}
                <div class="flex items-center gap-2 bg-slate-50/50 rounded-xl border border-slate-200 px-3 py-1">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <input type="date" name="date_debut" value="{{ request('date_debut') }}" class="bg-transparent border-0 text-sm text-slate-700 focus:ring-0 focus:outline-none p-1">
                    <span class="text-slate-400 text-xs">→</span>
                    <input type="date" name="date_fin" value="{{ request('date_fin') }}" class="bg-transparent border-0 text-sm text-slate-700 focus:ring-0 focus:outline-none p-1">
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="inline-flex items-center gap-1.5 bg-slate-900 hover:bg-slate-800 text-white font-semibold px-4 py-2.5 rounded-xl text-sm transition shadow-sm hover:shadow-md active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    Filtrer
                </button>
                @if(request()->hasAny(['categorie', 'date_debut', 'date_fin']))
                    <a href="{{ route('depenses.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 text-sm font-medium transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Réinitialiser
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Tableau --}}
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50/70 border-b border-slate-200/60">
                    <th class="py-3.5 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">Date</th>
                    <th class="py-3.5 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">Libellé</th>
                    <th class="py-3.5 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">Catégorie</th>
                    <th class="py-3.5 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">Mode</th>
                    <th class="py-3.5 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em] text-right">Montant</th>
                    <th class="py-3.5 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em] text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
            @forelse($depenses as $d)
                @php
                    $modeIcon = match($d->mode_paiement) {
                        'especes'      => '💵',
                        'orange_money' => '📱',
                        'mtn_money'    => '📱',
                        'wave'         => '🌊',
                        default        => '💳',
                    };
                    $modeLabel = match($d->mode_paiement) {
                        'especes'      => 'Espèces',
                        'orange_money' => 'Orange Money',
                        'mtn_money'    => 'MTN Money',
                        'wave'         => 'Wave',
                        default        => 'Autre',
                    };
                @endphp
                <tr class="table-row group">
                    <td class="py-3.5 px-6">
                        <div class="text-xs font-semibold text-slate-700">{{ $d->date_depense->format('d/m/Y') }}</div>
                        <div class="text-[10px] text-slate-400 mt-0.5">{{ $d->date_depense->translatedFormat('l') }}</div>
                    </td>
                    <td class="py-3.5 px-6">
                        <div class="flex items-center gap-2.5">
                            <div class="row-icon w-8 h-8 rounded-lg bg-gradient-to-br from-rose-100 to-pink-200 flex items-center justify-center text-rose-600 transition-all duration-300">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <span class="font-bold text-slate-900 truncate">{{ $d->libelle }}</span>
                        </div>
                    </td>
                    <td class="py-3.5 px-6">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200/60">
                            <svg class="w-3 h-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                            {{ $d->categorie?->nom ?? '—' }}
                        </span>
                    </td>
                    <td class="py-3.5 px-6">
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600">
                            <span class="text-base">{{ $modeIcon }}</span>
                            {{ $modeLabel }}
                        </span>
                    </td>
                    <td class="py-3.5 px-6 text-right">
                        <span class="font-extrabold text-rose-600">− {{ number_format($d->montant, 0, ',', ' ') }}</span>
                        <span class="text-[10px] font-semibold text-rose-400 ml-1">FCFA</span>
                    </td>
                    <td class="py-3.5 px-6 text-right">
                        @if($d->achat_id)
                            <a href="{{ route('achats.show', $d->achat_id) }}"
                               class="action-btn inline-flex items-center gap-1.5 bg-slate-100 hover:bg-slate-900 text-slate-700 hover:text-white font-semibold text-xs px-3 py-1.5 rounded-lg transition-all duration-200 hover:scale-105">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Voir l'achat
                            </a>
                        @else
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('depenses.edit', $d) }}"
                                   class="action-btn p-2 rounded-lg bg-amber-50 hover:bg-amber-500 text-amber-600 hover:text-white transition-all duration-200 hover:scale-110"
                                   title="Modifier">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <form method="POST" action="{{ route('depenses.destroy', $d) }}" class="inline" onsubmit="return confirm('Supprimer cette dépense ?');">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="action-btn p-2 rounded-lg bg-rose-50 hover:bg-rose-500 text-rose-600 hover:text-white transition-all duration-200 hover:scale-110"
                                            title="Supprimer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="py-20 text-center">
                        <div class="flex flex-col items-center">
                            <div class="relative mb-5">
                                <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center text-slate-400 rotate-6">
                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                </div>
                                <div class="absolute -bottom-2 -right-2 w-8 h-8 rounded-full bg-amber-100 border-2 border-white flex items-center justify-center">
                                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                    </svg>
                                </div>
                            </div>
                            <p class="font-bold text-slate-700 text-base">Aucune dépense enregistrée</p>
                            <p class="text-xs text-slate-400 mt-1 max-w-xs">Enregistrez vos charges pour suivre la rentabilité du pressing.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($depenses->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 bg-gradient-to-r from-slate-50/40 to-white flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-xs text-slate-500">
                Affichage de <span class="font-bold text-slate-700">{{ $depenses->firstItem() }}</span>
                à <span class="font-bold text-slate-700">{{ $depenses->lastItem() }}</span>
                sur <span class="font-bold text-slate-700">{{ $depenses->total() }}</span> dépenses
            </p>
            <div>{{ $depenses->links() }}</div>
        </div>
    @endif
</div>

@endsection