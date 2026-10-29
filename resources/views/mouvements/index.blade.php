@extends('layouts.app')

@section('title', 'Mouvements de stock')
@section('page-title', 'Mouvements de stock')
@section('page-subtitle', 'Historique complet des entrées, sorties, pertes et ajustements')

@php
    $types = [
        'entree'     => [
            'label' => 'Entrée',
            'icon' => 'M7 11l5-5m0 0l5 5m-5-5v12',
            'bg' => 'from-emerald-500 to-teal-600',
            'glow' => 'bg-teal-400/25',
            'badge' => 'bg-emerald-100 text-emerald-700 border-emerald-200/70',
            'dot' => 'bg-emerald-500',
            'value' => 'text-emerald-600',
        ],
        'sortie'     => [
            'label' => 'Sortie',
            'icon' => 'M17 13l-5 5m0 0l-5-5m5 5V6',
            'bg' => 'from-amber-500 to-orange-600',
            'glow' => 'bg-yellow-300/25',
            'badge' => 'bg-amber-100 text-amber-700 border-amber-200/70',
            'dot' => 'bg-amber-500',
            'value' => 'text-amber-600',
        ],
        'perte'      => [
            'label' => 'Perte',
            'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
            'bg' => 'from-rose-500 to-pink-600',
            'glow' => 'bg-pink-400/25',
            'badge' => 'bg-rose-100 text-rose-700 border-rose-200/70',
            'dot' => 'bg-rose-500',
            'value' => 'text-rose-600',
        ],
        'ajustement' => [
            'label' => 'Ajustement',
            'icon' => 'M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4',
            'bg' => 'from-blue-500 to-indigo-600',
            'glow' => 'bg-blue-400/25',
            'badge' => 'bg-blue-100 text-blue-700 border-blue-200/70',
            'dot' => 'bg-blue-500',
            'value' => 'text-blue-600',
        ],
    ];
    $mouvementsByType = $mouvements->getCollection()->groupBy('type');
@endphp

@section('content')

<style>
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes pulseRing { 0% { transform: scale(1); opacity: 0.6; } 70% { transform: scale(1.8); opacity: 0; } 100% { transform: scale(1.8); opacity: 0; } }
    .stat-card { animation: fadeInUp 0.5s ease-out backwards; }
    .stat-card:nth-child(1) { animation-delay: 0.05s; }
    .stat-card:nth-child(2) { animation-delay: 0.10s; }
    .stat-card:nth-child(3) { animation-delay: 0.15s; }
    .stat-card:nth-child(4) { animation-delay: 0.20s; }
    .table-row { transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
    .table-row:hover { background: linear-gradient(90deg, #fffbeb 0%, #ffffff 40%, #ffffff 100%); }
    .table-row:hover .row-avatar { transform: scale(1.1) rotate(-3deg); }
    .gradient-border { position: relative; background: white; }
    .gradient-border::before {
        content: ''; position: absolute; inset: 0; padding: 1px; border-radius: inherit;
        background: linear-gradient(135deg, rgba(251,191,36,0.3), rgba(245,158,11,0.1), rgba(251,191,36,0.3));
        -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor; mask-composite: exclude; pointer-events: none;
    }
    .ring-pulse::before { content: ''; position: absolute; inset: 0; border-radius: 9999px; background: currentColor; animation: pulseRing 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
</style>

{{-- ══════ HERO ══════ --}}
<div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-8">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-blue-500/30">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
            </svg>
        </div>
        <div>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Journal des mouvements</h1>
            <p class="text-xs text-slate-500 font-medium">Suivi complet des entrées, sorties et ajustements de stock</p>
        </div>
    </div>
</div>

{{-- ══════ STATS ══════ --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    @foreach($types as $key => $cfg)
        @php $count = ($mouvementsByType[$key] ?? collect())->count(); @endphp
        <div class="stat-card relative overflow-hidden rounded-2xl bg-gradient-to-br {{ $cfg['bg'] }} p-5 text-white shadow-xl shadow-slate-900/10">
            <div class="absolute top-0 right-0 w-32 h-32 {{ $cfg['glow'] }} rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
            <div class="absolute bottom-0 left-0 w-24 h-24 {{ $cfg['glow'] }} rounded-full blur-2xl translate-y-1/2 -translate-x-1/2 opacity-50"></div>
            <div class="relative">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-white/15 backdrop-blur-sm border border-white/25 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $cfg['icon'] }}"/>
                        </svg>
                    </div>
                    <span class="text-[9px] font-bold bg-white/20 px-2 py-1 rounded-md tracking-wider uppercase">{{ $cfg['label'] }}</span>
                </div>
                <p class="text-3xl font-extrabold tracking-tight">{{ $count }}</p>
                <p class="text-[10px] text-white/75 mt-1 flex items-center gap-1.5">
                    <span class="w-1 h-1 rounded-full bg-white/80"></span>
                    {{ $count === 0 ? 'Aucun' : 'Mouvement' . ($count > 1 ? 's' : '') }} sur cette page
                </p>
            </div>
        </div>
    @endforeach
</div>

{{-- ══════ CONTENEUR PRINCIPAL ══════ --}}
<div class="gradient-border rounded-2xl bg-white shadow-lg shadow-slate-200/50 overflow-hidden">

    {{-- Filtres --}}
    <div class="px-6 py-4 border-b border-slate-100 bg-white">
        <form method="GET" action="{{ route('mouvements.index') }}" class="flex flex-col lg:flex-row gap-3 lg:items-center">
            <div class="flex flex-wrap gap-2.5 items-center flex-1">
                {{-- Produit --}}
                <div class="relative">
                    <select name="produit"
                            class="appearance-none pl-3.5 pr-9 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-sm text-slate-700 focus:bg-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all cursor-pointer min-w-[180px]">
                        <option value="">Tous les produits</option>
                        @foreach($produits as $p)
                            <option value="{{ $p->id }}" @selected(request('produit') == $p->id)>{{ $p->nom }}</option>
                        @endforeach
                    </select>
                    <svg class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>

                {{-- Type --}}
                <div class="relative">
                    <select name="type"
                            class="appearance-none pl-3.5 pr-9 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-sm text-slate-700 focus:bg-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all cursor-pointer min-w-[160px]">
                        <option value="">Tous les types</option>
                        @foreach($types as $k => $t)
                            <option value="{{ $k }}" @selected(request('type') === $k)>{{ $t['label'] }}</option>
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
                @if(request()->hasAny(['produit', 'type', 'date_debut', 'date_fin']))
                    <a href="{{ route('mouvements.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 text-sm font-medium transition">
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
                    <th class="py-3.5 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">Produit</th>
                    <th class="py-3.5 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">Type</th>
                    <th class="py-3.5 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">Quantité</th>
                    <th class="py-3.5 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">Stock après</th>
                    <th class="py-3.5 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">Motif</th>
                    <th class="py-3.5 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">Par</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
            @forelse($mouvements as $m)
                @php
                    $cfg = $types[$m->type] ?? [
                        'label' => 'Inconnu',
                        'icon' => 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                        'bg' => 'from-slate-500 to-slate-600',
                        'glow' => 'bg-slate-400/20',
                        'badge' => 'bg-slate-100 text-slate-600 border-slate-200/70',
                        'dot' => 'bg-slate-400',
                        'value' => 'text-slate-600',
                    ];
                    $sign = in_array($m->type, ['entree', 'ajustement'], true) && $m->quantite > 0 ? '+' : '';
                    $quantiteClass = match($m->type) {
                        'entree'     => 'text-emerald-600',
                        'sortie'     => 'text-amber-600',
                        'perte'      => 'text-rose-600',
                        'ajustement' => 'text-blue-600',
                        default      => 'text-slate-600',
                    };
                    $quantiteBg = match($m->type) {
                        'entree'     => 'bg-emerald-50',
                        'sortie'     => 'bg-amber-50',
                        'perte'      => 'bg-rose-50',
                        'ajustement' => 'bg-blue-50',
                        default      => 'bg-slate-50',
                    };
                @endphp
                <tr class="table-row group">
                    <td class="py-3.5 px-6">
                        <div class="text-xs font-semibold text-slate-700">{{ $m->date_mouvement->format('d/m/Y') }}</div>
                        <div class="text-[10px] text-slate-400 mt-0.5 font-mono">{{ $m->date_mouvement->format('H:i') }}</div>
                    </td>
                    <td class="py-3.5 px-6">
                        <div class="flex items-center gap-2.5">
                            <div class="row-avatar w-8 h-8 rounded-lg bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center text-[10px] font-extrabold text-slate-600 uppercase transition-all duration-300">
                                {{ mb_substr($m->produit?->nom ?? '?', 0, 2) }}
                            </div>
                            <span class="font-bold text-slate-900 truncate">{{ $m->produit?->nom ?? '—' }}</span>
                        </div>
                    </td>
                    <td class="py-3.5 px-6">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $cfg['badge'] }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $cfg['dot'] }}"></span>
                            {{ $cfg['label'] }}
                        </span>
                    </td>
                    <td class="py-3.5 px-6">
                        <div class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg {{ $quantiteBg }}">
                            <span class="font-extrabold text-sm {{ $quantiteClass }}">{{ $sign }}{{ (float) $m->quantite }}</span>
                            <span class="text-[10px] font-semibold text-slate-500">{{ $m->produit?->unite }}</span>
                        </div>
                    </td>
                    <td class="py-3.5 px-6">
                        <div class="flex items-baseline gap-1">
                            <span class="font-extrabold text-slate-900">{{ (float) $m->stock_apres }}</span>
                            <span class="text-[10px] text-slate-400">{{ $m->produit?->unite }}</span>
                        </div>
                    </td>
                    <td class="py-3.5 px-6">
                        @if($m->motif)
                            <span class="text-xs text-slate-600 line-clamp-1 max-w-xs" title="{{ $m->motif }}">{{ $m->motif }}</span>
                        @else
                            <span class="text-xs text-slate-400 italic">—</span>
                        @endif
                    </td>
                    <td class="py-3.5 px-6">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-md bg-slate-100 text-slate-600 text-[10px] font-extrabold flex items-center justify-center uppercase">
                                {{ mb_substr($m->user?->nom ?? 'S', 0, 1) }}
                            </span>
                            <span class="text-xs font-medium text-slate-700 truncate">{{ $m->user?->nom ?? 'Système' }}</span>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="py-20 text-center">
                        <div class="flex flex-col items-center">
                            <div class="relative mb-5">
                                <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center text-slate-400 rotate-6">
                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                    </svg>
                                </div>
                                <div class="absolute -bottom-2 -right-2 w-8 h-8 rounded-full bg-amber-100 border-2 border-white flex items-center justify-center">
                                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01"/>
                                    </svg>
                                </div>
                            </div>
                            <p class="font-bold text-slate-700 text-base">Aucun mouvement de stock</p>
                            <p class="text-xs text-slate-400 mt-1 max-w-xs">Les entrées et sorties de stock apparaîtront ici dès qu'elles seront enregistrées.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($mouvements->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 bg-gradient-to-r from-slate-50/40 to-white flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-xs text-slate-500">
                Affichage de <span class="font-bold text-slate-700">{{ $mouvements->firstItem() }}</span>
                à <span class="font-bold text-slate-700">{{ $mouvements->lastItem() }}</span>
                sur <span class="font-bold text-slate-700">{{ $mouvements->total() }}</span> mouvements
            </p>
            <div>{{ $mouvements->links() }}</div>
        </div>
    @endif
</div>

@endsection