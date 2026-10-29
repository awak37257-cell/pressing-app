@extends('layouts.app')

@section('title', 'Fournisseurs')
@section('page-title', 'Fournisseurs')
@section('page-subtitle', 'Gestion de vos partenaires d\'approvisionnement')

@section('content')

<style>
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes pulseRing { 0% { transform: scale(1); opacity: 0.6; } 70% { transform: scale(1.8); opacity: 0; } 100% { transform: scale(1.8); opacity: 0; } }
    .stat-card { animation: fadeInUp 0.5s ease-out backwards; }
    .stat-card:nth-child(1) { animation-delay: 0.05s; }
    .stat-card:nth-child(2) { animation-delay: 0.10s; }
    .table-row { transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
    .table-row:hover { background: linear-gradient(90deg, #fffbeb 0%, #ffffff 40%, #ffffff 100%); }
    .table-row:hover .row-avatar { transform: scale(1.1) rotate(-4deg); }
    .table-row:hover .action-btn { opacity: 1; transform: translateX(0); }
    .action-btn { opacity: 0.5; transform: translateX(4px); transition: all 0.25s ease; }
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
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
        </div>
        <div>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Répertoire fournisseurs</h1>
            <p class="text-xs text-slate-500 font-medium">Vos partenaires d'approvisionnement</p>
        </div>
    </div>
    <a href="{{ route('fournisseurs.create') }}"
       class="group inline-flex items-center gap-2 bg-gradient-to-r from-amber-500 via-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-bold px-5 py-2.5 rounded-xl shadow-lg shadow-amber-500/30 hover:shadow-xl text-sm transition-all duration-300 hover:-translate-y-0.5 active:scale-95">
        <svg class="w-4 h-4 group-hover:rotate-90 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Nouveau fournisseur
    </a>
</div>

{{-- ══════ STATS ══════ --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-8">

    {{-- Total fournisseurs --}}
    <div class="stat-card relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 p-6 text-white shadow-xl shadow-slate-900/20">
        <div class="absolute top-0 right-0 w-40 h-40 bg-blue-500/20 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
        <div class="absolute bottom-0 left-0 w-32 h-32 bg-indigo-500/10 rounded-full blur-2xl translate-y-1/2 -translate-x-1/2"></div>
        <div class="relative">
            <div class="flex items-start justify-between mb-4">
                <div class="w-11 h-11 rounded-xl bg-white/10 backdrop-blur-sm border border-white/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <span class="text-[10px] font-bold text-blue-300 bg-blue-500/20 px-2 py-1 rounded-md tracking-wider uppercase">Total</span>
            </div>
            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-[0.1em] mb-1">Fournisseurs enregistrés</p>
            <p class="text-3xl font-extrabold tracking-tight">{{ $fournisseurs->total() }}</p>
            <p class="text-xs text-slate-400 mt-2 flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-blue-400 ring-pulse relative"></span>
                Partenaires d'approvisionnement
            </p>
        </div>
    </div>

    {{-- Fournisseurs actifs --}}
    <div class="stat-card relative overflow-hidden rounded-2xl bg-gradient-to-br from-amber-500 via-orange-500 to-orange-600 p-6 text-white shadow-xl shadow-amber-500/30">
        <div class="absolute top-0 right-0 w-40 h-40 bg-yellow-300/25 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
        <div class="absolute bottom-0 left-0 w-32 h-32 bg-orange-400/15 rounded-full blur-2xl translate-y-1/2 -translate-x-1/2"></div>
        <div class="relative">
            <div class="flex items-start justify-between mb-4">
                <div class="w-11 h-11 rounded-xl bg-white/15 backdrop-blur-sm border border-white/25 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1"/>
                    </svg>
                </div>
                <span class="text-[10px] font-bold bg-white/20 px-2 py-1 rounded-md tracking-wider uppercase">Actifs</span>
            </div>
            <p class="text-[11px] font-semibold text-white/80 uppercase tracking-[0.1em] mb-1">Avec achats enregistrés</p>
            <p class="text-3xl font-extrabold tracking-tight">{{ $fournisseurs->filter(fn($f) => $f->achats_count > 0)->count() }}</p>
            <p class="text-xs text-white/80 mt-2 flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-white/80"></span>
                Partenaires avec au moins un achat
            </p>
        </div>
    </div>
</div>

{{-- ══════ CONTENEUR PRINCIPAL ══════ --}}
<div class="gradient-border rounded-2xl bg-white shadow-lg shadow-slate-200/50 overflow-hidden">

    {{-- Filtres --}}
    <div class="px-6 py-4 border-b border-slate-100 bg-white">
        <form method="GET" action="{{ route('fournisseurs.index') }}" class="flex flex-col lg:flex-row gap-3 lg:items-center">
            <div class="relative flex-1 max-w-md">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher par nom…"
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-sm text-slate-700 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all duration-200">
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="inline-flex items-center gap-1.5 bg-slate-900 hover:bg-slate-800 text-white font-semibold px-4 py-2.5 rounded-xl text-sm transition shadow-sm hover:shadow-md active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Rechercher
                </button>
                @if(request('search'))
                    <a href="{{ route('fournisseurs.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 text-sm font-medium transition">
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
                    <th class="py-3.5 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">Fournisseur</th>
                    <th class="py-3.5 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">Contact</th>
                    <th class="py-3.5 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">Adresse</th>
                    <th class="py-3.5 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em] text-center">Activité</th>
                    <th class="py-3.5 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em] text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
            @forelse($fournisseurs as $f)
                <tr class="table-row group">
                    <td class="py-3.5 px-6">
                        <div class="flex items-center gap-3">
                            <div class="row-avatar w-10 h-10 rounded-xl bg-gradient-to-br from-blue-100 to-indigo-200 flex items-center justify-center text-xs font-extrabold text-blue-700 ring-2 ring-blue-200/60 transition-all duration-300">
                                {{ strtoupper(substr($f->nom, 0, 2)) }}
                            </div>
                            <span class="font-bold text-slate-900 truncate">{{ $f->nom }}</span>
                        </div>
                    </td>
                    <td class="py-3.5 px-6">
                        @if($f->telephone)
                            <div class="flex items-center gap-1.5 text-slate-600">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                <span class="font-mono text-xs font-semibold">{{ $f->telephone }}</span>
                            </div>
                        @else
                            <span class="text-xs text-slate-400 italic">Non renseigné</span>
                        @endif
                    </td>
                    <td class="py-3.5 px-6">
                        @if($f->adresse)
                            <div class="flex items-center gap-1.5 text-slate-600">
                                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span class="text-xs truncate">{{ $f->adresse }}</span>
                            </div>
                        @else
                            <span class="text-xs text-slate-400 italic">Non renseignée</span>
                        @endif
                    </td>
                    <td class="py-3.5 px-6 text-center">
                        @if($f->achats_count > 0)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-700 border border-amber-200/70">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                {{ $f->achats_count }} achat{{ $f->achats_count > 1 ? 's' : '' }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-500 border border-slate-200/60">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                Aucun achat
                            </span>
                        @endif
                    </td>
                    <td class="py-3.5 px-6 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <a href="{{ route('fournisseurs.edit', $f) }}"
                               class="action-btn p-2 rounded-lg bg-amber-50 hover:bg-amber-500 text-amber-600 hover:text-white transition-all duration-200 hover:scale-110"
                               title="Modifier">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <form method="POST" action="{{ route('fournisseurs.destroy', $f) }}" class="inline" onsubmit="return confirm('Supprimer ce fournisseur ?');">
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
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="py-20 text-center">
                        <div class="flex flex-col items-center">
                            <div class="relative mb-5">
                                <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center text-slate-400 rotate-6">
                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                </div>
                                <div class="absolute -bottom-2 -right-2 w-8 h-8 rounded-full bg-amber-100 border-2 border-white flex items-center justify-center">
                                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                    </svg>
                                </div>
                            </div>
                            <p class="font-bold text-slate-700 text-base">Aucun fournisseur enregistré</p>
                            <p class="text-xs text-slate-400 mt-1 max-w-xs">Ajoutez vos fournisseurs pour enregistrer vos achats de produits.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($fournisseurs->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 bg-gradient-to-r from-slate-50/40 to-white flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-xs text-slate-500">
                Affichage de <span class="font-bold text-slate-700">{{ $fournisseurs->firstItem() }}</span>
                à <span class="font-bold text-slate-700">{{ $fournisseurs->lastItem() }}</span>
                sur <span class="font-bold text-slate-700">{{ $fournisseurs->total() }}</span> fournisseurs
            </p>
            <div>{{ $fournisseurs->links() }}</div>
        </div>
    @endif
</div>

@endsection