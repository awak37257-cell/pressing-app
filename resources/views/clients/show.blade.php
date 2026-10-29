@extends('layouts.app')

@section('title', $client->nom)
@section('page-title', $client->nom . ' ' . $client->prenom)
@section('page-subtitle', 'Téléphone : ' . $client->telephone . ($client->email ? ' · Email : ' . $client->email : ''))

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
    .stat-card:nth-child(3) { animation-delay: 0.15s; }
    .table-row { transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
    .table-row:hover { background: linear-gradient(90deg, #fffbeb 0%, #ffffff 40%, #ffffff 100%); }
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

<div class="space-y-6">

    {{-- ══════ HERO : Identité + actions ══════ --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white text-lg font-extrabold shadow-lg shadow-amber-500/30">
                {{ strtoupper(substr($client->nom, 0, 1)) }}{{ strtoupper(substr($client->prenom ?? '', 0, 1)) }}
            </div>
            <div>
                <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">{{ $client->nom }} {{ $client->prenom }}</h1>
                <div class="flex flex-wrap items-center gap-3 mt-1.5 text-xs text-slate-500">
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        <span class="font-mono font-semibold text-slate-700">{{ $client->telephone }}</span>
                    </span>
                    @if($client->email)
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            {{ $client->email }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('clients.edit', $client) }}"
               class="inline-flex items-center gap-1.5 bg-white border border-slate-200 hover:border-amber-300 hover:bg-amber-50 text-slate-700 hover:text-amber-700 font-semibold px-3.5 py-2 rounded-xl text-xs transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Modifier
            </a>
            <a href="{{ route('clients.index') }}"
               class="inline-flex items-center gap-1.5 bg-slate-900 hover:bg-slate-800 text-white font-semibold px-3.5 py-2 rounded-xl text-xs transition shadow-sm hover:shadow-md active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour
            </a>
        </div>
    </div>

    {{-- ══════ STATS CARDS ══════ --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

        {{-- Total dépôts --}}
        <div class="stat-card relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 p-6 text-white shadow-xl shadow-slate-900/20">
            <div class="absolute top-0 right-0 w-40 h-40 bg-blue-500/20 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
            <div class="relative">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-11 h-11 rounded-xl bg-white/10 backdrop-blur-sm border border-white/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <span class="text-[10px] font-bold text-blue-300 bg-blue-500/20 px-2 py-1 rounded-md tracking-wider uppercase">Total</span>
                </div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-[0.1em] mb-1">Dépôts enregistrés</p>
                <p class="text-3xl font-extrabold tracking-tight">{{ $client->depots_count }}</p>
                <p class="text-xs text-slate-400 mt-2 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-400 ring-pulse relative"></span>
                    Historique du client
                </p>
            </div>
        </div>

        {{-- Total payé --}}
        <div class="stat-card relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-600 via-emerald-600 to-teal-700 p-6 text-white shadow-xl shadow-emerald-600/20">
            <div class="absolute top-0 right-0 w-40 h-40 bg-teal-400/20 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
            <div class="relative">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-11 h-11 rounded-xl bg-white/15 backdrop-blur-sm border border-white/25 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span class="text-[10px] font-bold bg-white/20 px-2 py-1 rounded-md tracking-wider uppercase">Payé</span>
                </div>
                <p class="text-[11px] font-semibold text-white/70 uppercase tracking-[0.1em] mb-1">Total payé</p>
                <p class="text-3xl font-extrabold tracking-tight">{{ number_format($totalPaye, 0, ',', ' ') }} <span class="text-sm font-semibold text-white/60">FCFA</span></p>
                <p class="text-xs text-white/80 mt-2 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-teal-300"></span>
                    Encaissements cumulés
                </p>
            </div>
        </div>

        {{-- Reste à payer --}}
        <div class="stat-card relative overflow-hidden rounded-2xl p-6 text-white shadow-xl transition-all duration-300 group hover:shadow-2xl
            {{ $resteTotal > 0 ? 'bg-gradient-to-br from-rose-600 via-rose-600 to-pink-700 shadow-rose-600/30' : 'bg-gradient-to-br from-slate-600 via-slate-600 to-slate-700 shadow-slate-600/20' }}">
            <div class="absolute top-0 right-0 w-40 h-40 {{ $resteTotal > 0 ? 'bg-pink-400/25' : 'bg-slate-400/15' }} rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
            <div class="relative">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-11 h-11 rounded-xl bg-white/15 backdrop-blur-sm border border-white/25 flex items-center justify-center">
                        @if($resteTotal > 0)
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        @else
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        @endif
                    </div>
                    <span class="text-[10px] font-bold bg-white/20 px-2 py-1 rounded-md tracking-wider uppercase">
                        {{ $resteTotal > 0 ? 'À recouvrer' : 'Sain' }}
                    </span>
                </div>
                <p class="text-[11px] font-semibold text-white/70 uppercase tracking-[0.1em] mb-1">Reste à payer</p>
                <p class="text-3xl font-extrabold tracking-tight">{{ number_format($resteTotal, 0, ',', ' ') }} <span class="text-sm font-semibold text-white/60">FCFA</span></p>
                <p class="text-xs text-white/80 mt-2 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-white {{ $resteTotal > 0 ? 'ring-pulse relative' : '' }}"></span>
                    {{ $resteTotal > 0 ? 'Solde en attente' : 'Client à jour' }}
                </p>
            </div>
        </div>
    </div>

    {{-- ══════ HISTORIQUE DÉPÔTS ══════ --}}
    <div class="gradient-border rounded-2xl bg-white shadow-lg shadow-slate-200/50 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-white flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-white shadow-md shadow-amber-500/25">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 text-sm">Historique des dépôts</h3>
                    <p class="text-[11px] text-slate-500">{{ $depots->total() }} dépôt(s) enregistré(s) pour ce client</p>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/70 border-b border-slate-200/60">
                        <th class="py-3.5 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">Ticket</th>
                        <th class="py-3.5 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">Date</th>
                        <th class="py-3.5 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">Statut</th>
                        <th class="py-3.5 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em] text-right">Total</th>
                        <th class="py-3.5 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em] text-right">Reste</th>
                        <th class="py-3.5 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                @forelse($depots as $depot)
                    @php
                        $badgeClass = match($depot->statut) {
                            'depose'        => 'bg-blue-100 text-blue-700 border-blue-200/70',
                            'en_traitement' => 'bg-amber-100 text-amber-700 border-amber-200/70',
                            'pret'          => 'bg-emerald-100 text-emerald-700 border-emerald-200/70',
                            'retire'        => 'bg-slate-100 text-slate-600 border-slate-200/70',
                            'annule'        => 'bg-rose-100 text-rose-700 border-rose-200/70',
                            default         => 'bg-slate-100 text-slate-600 border-slate-200/70',
                        };
                        $dotClass = match($depot->statut) {
                            'depose'        => 'bg-blue-500',
                            'en_traitement' => 'bg-amber-500',
                            'pret'          => 'bg-emerald-500',
                            'retire'        => 'bg-slate-400',
                            'annule'        => 'bg-rose-500',
                            default         => 'bg-slate-400',
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
                    <tr class="table-row group">
                        <td class="py-3.5 px-6">
                            <a href="{{ route('depots.show', $depot) }}" class="inline-flex items-center gap-1.5 font-bold text-amber-600 hover:text-amber-700 group-hover:underline transition">
                                <span class="w-6 h-6 rounded-md bg-amber-100 flex items-center justify-center">
                                    <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                                    </svg>
                                </span>
                                {{ $depot->numero_ticket }}
                            </a>
                        </td>
                        <td class="py-3.5 px-6">
                            <div class="text-xs font-semibold text-slate-700">{{ $depot->date_depot->format('d/m/Y') }}</div>
                            <div class="text-[10px] text-slate-400 mt-0.5">{{ $depot->date_depot->translatedFormat('l') }}</div>
                        </td>
                        <td class="py-3.5 px-6">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $badgeClass }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $dotClass }}"></span>
                                {{ $labelStatut }}
                            </span>
                        </td>
                        <td class="py-3.5 px-6 text-right">
                            <span class="font-extrabold text-slate-900">{{ number_format($depot->montant_total, 0, ',', ' ') }}</span>
                            <span class="text-[10px] font-semibold text-slate-400 ml-1">FCFA</span>
                        </td>
                        <td class="py-3.5 px-6 text-right">
                            @if($depot->reste_a_payer > 0)
                                <span class="font-extrabold text-rose-600">{{ number_format($depot->reste_a_payer, 0, ',', ' ') }}</span>
                                <span class="text-[10px] font-semibold text-rose-400 ml-1">FCFA</span>
                            @else
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Soldé
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-6 text-right">
                            <a href="{{ route('depots.show', $depot) }}"
                               class="action-btn inline-flex items-center gap-1.5 bg-slate-100 hover:bg-slate-900 text-slate-700 hover:text-white font-semibold text-xs px-3 py-1.5 rounded-lg transition-all duration-200 hover:scale-105">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Détail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-20 text-center">
                            <div class="flex flex-col items-center">
                                <div class="relative mb-5">
                                    <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center text-slate-400 rotate-6">
                                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                        </svg>
                                    </div>
                                    <div class="absolute -bottom-2 -right-2 w-8 h-8 rounded-full bg-amber-100 border-2 border-white flex items-center justify-center">
                                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01"/>
                                        </svg>
                                    </div>
                                </div>
                                <p class="font-bold text-slate-700 text-base">Aucun dépôt enregistré</p>
                                <p class="text-xs text-slate-400 mt-1 max-w-xs">Ce client n'a pas encore effectué de dépôt.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($depots->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-gradient-to-r from-slate-50/40 to-white flex flex-col sm:flex-row items-center justify-between gap-3">
                <p class="text-xs text-slate-500">
                    Affichage de <span class="font-bold text-slate-700">{{ $depots->firstItem() }}</span>
                    à <span class="font-bold text-slate-700">{{ $depots->lastItem() }}</span>
                    sur <span class="font-bold text-slate-700">{{ $depots->total() }}</span> dépôts
                </p>
                <div>{{ $depots->links() }}</div>
            </div>
        @endif
    </div>
</div>

@endsection