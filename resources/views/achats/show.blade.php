@extends('layouts.app')

@section('title', 'Achat #' . $achat->id)
@section('page-title', 'Achat #' . str_pad($achat->id, 4, '0', STR_PAD_LEFT))
@section('page-subtitle', $achat->fournisseur?->nom . ' — ' . $achat->date_achat->format('d/m/Y'))

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
    .line-row { animation: fadeInUp 0.4s ease-out backwards; }
    .line-row:nth-child(1) { animation-delay: 0.1s; }
    .line-row:nth-child(2) { animation-delay: 0.15s; }
    .line-row:nth-child(3) { animation-delay: 0.2s; }
    .line-row:nth-child(4) { animation-delay: 0.25s; }
    .line-row:hover { background: linear-gradient(90deg, #fffbeb 0%, #ffffff 40%, #ffffff 100%); }
    .line-row:hover .line-avatar { transform: scale(1.08) rotate(-3deg); }
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

<div class="max-w-4xl mx-auto space-y-6">

    {{-- ══════ HERO + RETOUR ══════ --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center shadow-lg shadow-amber-500/30">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Achat #{{ str_pad($achat->id, 4, '0', STR_PAD_LEFT) }}</h1>
                <p class="text-xs text-slate-500 font-medium">Bon de commande fournisseur</p>
            </div>
        </div>
        <a href="{{ route('achats.index') }}"
           class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 hover:border-slate-300 px-3.5 py-2 rounded-xl transition shadow-sm hover:shadow">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Retour aux achats
        </a>
    </div>

    {{-- ══════ STATS CARDS ══════ --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        {{-- Fournisseur --}}
        <div class="stat-card relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 p-5 text-white shadow-xl shadow-slate-900/20">
            <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/20 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
            <div class="relative">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-white/10 backdrop-blur-sm border border-white/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <span class="text-[10px] font-bold text-blue-300 bg-blue-500/20 px-2 py-1 rounded-md tracking-wider uppercase">Fournisseur</span>
                </div>
                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Commandé chez</p>
                <p class="text-base font-extrabold truncate">{{ $achat->fournisseur?->nom ?? '—' }}</p>
            </div>
        </div>

        {{-- Date --}}
        <div class="stat-card relative overflow-hidden rounded-2xl bg-gradient-to-br from-violet-600 via-violet-600 to-purple-700 p-5 text-white shadow-xl shadow-violet-600/20">
            <div class="absolute top-0 right-0 w-32 h-32 bg-fuchsia-400/20 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
            <div class="relative">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-white/15 backdrop-blur-sm border border-white/25 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <span class="text-[10px] font-bold text-fuchsia-200 bg-fuchsia-500/20 px-2 py-1 rounded-md tracking-wider uppercase">Date</span>
                </div>
                <p class="text-[10px] font-semibold text-white/70 uppercase tracking-wider mb-1">Achat effectué le</p>
                <p class="text-base font-extrabold">{{ $achat->date_achat->format('d/m/Y') }}</p>
                <p class="text-[10px] text-white/70 mt-0.5">{{ $achat->date_achat->translatedFormat('l') }}</p>
            </div>
        </div>

        {{-- Montant --}}
        <div class="stat-card relative overflow-hidden rounded-2xl bg-gradient-to-br from-amber-500 via-orange-500 to-orange-600 p-5 text-white shadow-xl shadow-amber-500/30">
            <div class="absolute top-0 right-0 w-32 h-32 bg-yellow-300/25 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
            <div class="relative">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-white/15 backdrop-blur-sm border border-white/25 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span class="text-[10px] font-bold bg-white/20 px-2 py-1 rounded-md tracking-wider uppercase">Total</span>
                </div>
                <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider mb-1">Montant total</p>
                <p class="text-base font-extrabold">{{ number_format($achat->montant_total, 0, ',', ' ') }} <span class="text-[10px] font-semibold text-white/70">FCFA</span></p>
            </div>
        </div>
    </div>

    {{-- ══════ DÉTAIL DES LIGNES ══════ --}}
    <div class="gradient-border rounded-2xl bg-white shadow-lg shadow-slate-200/50 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-white flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-white shadow-md shadow-amber-500/25">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 text-sm">Produits commandés</h3>
                    <p class="text-[11px] text-slate-500">{{ $achat->lignes->count() }} ligne(s) — enregistré par <span class="font-semibold text-slate-700">{{ $achat->user?->nom ?? 'Système' }}</span></p>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/70 border-b border-slate-200/60">
                        <th class="py-3 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">Produit</th>
                        <th class="py-3 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">Quantité</th>
                        <th class="py-3 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em] text-right">Prix unitaire</th>
                        <th class="py-3 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em] text-right">Sous-total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                @foreach($achat->lignes as $l)
                    <tr class="line-row group">
                        <td class="py-3.5 px-6">
                            <div class="flex items-center gap-2.5">
                                <div class="line-avatar w-8 h-8 rounded-lg bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center text-[10px] font-extrabold text-slate-600 transition-all duration-300">
                                    {{ strtoupper(substr($l->produit?->nom ?? '??', 0, 2)) }}
                                </div>
                                <span class="font-semibold text-slate-800">{{ $l->produit?->nom ?? '—' }}</span>
                            </div>
                        </td>
                        <td class="py-3.5 px-6">
                            <span class="font-bold text-slate-900">{{ (float) $l->quantite }}</span>
                            <span class="text-xs font-medium text-slate-400 ml-1">{{ $l->produit?->unite }}</span>
                        </td>
                        <td class="py-3.5 px-6 text-right text-slate-600">{{ number_format($l->prix_unitaire, 2, ',', ' ') }} <span class="text-[10px] text-slate-400">FCFA</span></td>
                        <td class="py-3.5 px-6 text-right font-extrabold text-slate-900">{{ number_format($l->quantite * $l->prix_unitaire, 0, ',', ' ') }} <span class="text-[10px] font-semibold text-slate-400">FCFA</span></td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot class="bg-gradient-to-r from-amber-50/80 via-orange-50/40 to-white border-t-2 border-amber-200/60">
                    <tr>
                        <td colspan="3" class="px-6 py-4 text-right font-bold text-slate-700 text-sm">
                            <span class="inline-flex items-center gap-2">
                                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/>
                                </svg>
                                Montant total
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="text-lg font-extrabold text-amber-600">{{ number_format($achat->montant_total, 0, ',', ' ') }}</span>
                            <span class="text-[10px] font-semibold text-amber-500 ml-1">FCFA</span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

@endsection