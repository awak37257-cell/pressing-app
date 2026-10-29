<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Tableau de bord') — PressManager</title>
    <meta name="description" content="Logiciel de gestion des stocks et opérations d'un pressing professionnel.">
    <meta name="theme-color" content="#0f172a">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(6px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes pulseRing {
            0%   { transform: scale(1); opacity: 0.6; }
            70%  { transform: scale(1.8); opacity: 0; }
            100% { transform: scale(1.8); opacity: 0; }
        }
        @keyframes slideInRight {
            from { opacity: 0; transform: translateX(20px); }
            to   { opacity: 1; transform: translateX(0); }
        }
        @keyframes shimmer {
            0%   { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        .nav-link { position: relative; transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
        .nav-link::before {
            content: ''; position: absolute; left: 0; top: 50%; transform: translateY(-50%);
            width: 3px; height: 0; background: linear-gradient(180deg, #fbbf24, #f59e0b);
            border-radius: 0 3px 3px 0;
            transition: height 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .nav-link:hover::before { height: 60%; }
        .nav-link.active::before { height: 100%; }
        .nav-link.active { background: linear-gradient(90deg, rgba(251,191,36,0.15), rgba(251,191,36,0.05)); color: #fbbf24; }
        .nav-link.active::after {
            content: ''; position: absolute; inset: 0; border-radius: 12px;
            pointer-events: none;
        }
        .ring-pulse::before {
            content: ''; position: absolute; inset: 0; border-radius: 9999px;
            background: currentColor; animation: pulseRing 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        .flash-msg { animation: slideInRight 0.4s cubic-bezier(0.2, 0.7, 0.2, 1); }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(148, 163, 184, 0.3); border-radius: 2px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(148, 163, 184, 0.5); }
        @media (prefers-reduced-motion: reduce) {
            .nav-link, .nav-link::before, .flash-msg, .ring-pulse::before { animation: none !important; transition: none !important; }
        }
    </style>
</head>
<body class="h-full bg-slate-50/80 font-sans antialiased text-slate-900">

<div class="flex h-full">

    {{-- ═══════════════════════════════════════════════════════════════
         SIDEBAR
         ═══════════════════════════════════════════════════════════════ --}}
    <aside id="sidebar"
           class="fixed inset-y-0 left-0 z-50 w-64 flex flex-col shadow-2xl shadow-slate-900/40"
           style="background: linear-gradient(180deg, #0a0f1e 0%, #0f172a 50%, #0a0f1e 100%);">

        {{-- Décors de fond --}}
        <div aria-hidden="true" class="absolute top-0 left-0 w-full h-40 bg-gradient-to-b from-amber-500/5 to-transparent pointer-events-none"></div>
        <div aria-hidden="true" class="absolute top-0 left-1/2 -translate-x-1/2 w-32 h-32 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>

        {{-- Logo --}}
        <div class="relative flex items-center gap-3 px-5 py-5 border-b border-white/5 shrink-0">
            <div class="relative w-10 h-10 rounded-xl bg-gradient-to-br from-amber-400 via-amber-500 to-orange-500 flex items-center justify-center shadow-lg shadow-amber-500/40 ring-1 ring-white/20">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2"
                          d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-white font-extrabold text-sm tracking-tight leading-tight">PressManager</p>
                <p class="text-slate-500 text-[10.5px] font-medium">Gestion pressing SaaS</p>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="relative flex-1 px-3 py-4 space-y-0.5 overflow-y-auto custom-scrollbar">

            {{-- Section : Principal --}}
            <p class="px-3 py-2 text-slate-500 text-[10px] font-bold uppercase tracking-[0.12em] flex items-center gap-2">
                <span class="w-1 h-1 rounded-full bg-amber-500/60"></span>
                Principal
            </p>

            <a href="{{ route('dashboard') }}"
               class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('dashboard') ? 'active' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                <span class="w-8 h-8 rounded-lg {{ request()->routeIs('dashboard') ? 'bg-amber-500/25 ring-1 ring-amber-400/40' : 'bg-white/5' }} flex items-center justify-center transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                </span>
                <span class="truncate">Tableau de bord</span>
            </a>

            {{-- Section : Opérations --}}
            <p class="px-3 py-2 mt-3 text-slate-500 text-[10px] font-bold uppercase tracking-[0.12em] flex items-center gap-2">
                <span class="w-1 h-1 rounded-full bg-blue-500/60"></span>
                Opérations
            </p>

            @php $nbATraiter = \App\Models\Depot::whereIn('statut', ['depose', 'en_traitement'])->count(); @endphp
            <a href="{{ route('traitement.index') }}"
               class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('traitement.*') ? 'active' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                <span class="w-8 h-8 rounded-lg {{ request()->routeIs('traitement.*') ? 'bg-amber-500/25 ring-1 ring-amber-400/40' : 'bg-white/5' }} flex items-center justify-center transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </span>
                <span class="truncate">À traiter</span>
                @if($nbATraiter > 0)
                    <span class="ml-auto relative inline-flex items-center justify-center min-w-[22px] h-5 rounded-full bg-gradient-to-r from-rose-500 to-pink-600 px-1.5 text-[10px] font-extrabold leading-none text-white shadow-lg shadow-rose-500/40">
                        <span class="absolute inset-0 rounded-full bg-rose-400 animate-ping opacity-40"></span>
                        <span class="relative">{{ $nbATraiter }}</span>
                    </span>
                @endif
            </a>

            @if(auth()->user()->role !== 'operateur')
            <a href="{{ route('depots.index') }}"
               class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('depots.*') ? 'active' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                <span class="w-8 h-8 rounded-lg {{ request()->routeIs('depots.*') ? 'bg-amber-500/25 ring-1 ring-amber-400/40' : 'bg-white/5' }} flex items-center justify-center transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </span>
                <span class="truncate">Dépôts & Retraits</span>
            </a>
            @endif

            @if(auth()->user()->role !== 'operateur')
            <a href="{{ route('clients.index') }}"
               class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('clients.*') ? 'active' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                <span class="w-8 h-8 rounded-lg {{ request()->routeIs('clients.*') ? 'bg-amber-500/25 ring-1 ring-amber-400/40' : 'bg-white/5' }} flex items-center justify-center transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </span>
                <span class="truncate">Clients</span>
            </a>
            @endif

            {{-- Section : Stocks --}}
            <p class="px-3 py-2 mt-3 text-slate-500 text-[10px] font-bold uppercase tracking-[0.12em] flex items-center gap-2">
                <span class="w-1 h-1 rounded-full bg-emerald-500/60"></span>
                Stocks
            </p>

            @php $nbAlertesStock = \App\Models\Produit::whereColumn('stock_actuel', '<=', 'seuil_alerte')->count(); @endphp
            <a href="{{ route('produits.index') }}"
               class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('produits.*') ? 'active' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                <span class="w-8 h-8 rounded-lg {{ request()->routeIs('produits.*') ? 'bg-amber-500/25 ring-1 ring-amber-400/40' : 'bg-white/5' }} flex items-center justify-center transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </span>
                <span class="truncate">Produits & Stock</span>
                @if($nbAlertesStock > 0)
                    <span class="ml-auto inline-flex items-center justify-center min-w-[20px] h-4.5 rounded-md bg-gradient-to-r from-amber-500 to-orange-500 px-1.5 text-[10px] font-extrabold leading-none text-white shadow-sm">
                        {{ $nbAlertesStock }}
                    </span>
                @endif
            </a>

            @if(in_array(auth()->user()->role, ['admin', 'gerant'], true))
            <a href="{{ route('achats.index') }}"
               class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('achats.*') ? 'active' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                <span class="w-8 h-8 rounded-lg {{ request()->routeIs('achats.*') ? 'bg-amber-500/25 ring-1 ring-amber-400/40' : 'bg-white/5' }} flex items-center justify-center transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </span>
                <span class="truncate">Achats fournisseurs</span>
            </a>
            @endif

            <a href="{{ route('mouvements.index') }}"
               class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('mouvements.*') ? 'active' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                <span class="w-8 h-8 rounded-lg {{ request()->routeIs('mouvements.*') ? 'bg-amber-500/25 ring-1 ring-amber-400/40' : 'bg-white/5' }} flex items-center justify-center transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                    </svg>
                </span>
                <span class="truncate">Mouvements stock</span>
            </a>

            @if(in_array(auth()->user()->role, ['admin', 'gerant'], true))
            <a href="{{ route('quantites-standard.index') }}"
               class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('quantites-standard.*') ? 'active' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                <span class="w-8 h-8 rounded-lg {{ request()->routeIs('quantites-standard.*') ? 'bg-amber-500/25 ring-1 ring-amber-400/40' : 'bg-white/5' }} flex items-center justify-center transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                    </svg>
                </span>
                <span class="truncate">Quantités standard</span>
            </a>
            @endif

            {{-- Section : Finance --}}
            @if(in_array(auth()->user()->role, ['admin', 'gerant'], true))
            <p class="px-3 py-2 mt-3 text-slate-500 text-[10px] font-bold uppercase tracking-[0.12em] flex items-center gap-2">
                <span class="w-1 h-1 rounded-full bg-violet-500/60"></span>
                Finance
            </p>

            <a href="{{ route('depenses.index') }}"
               class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('depenses.*') ? 'active' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                <span class="w-8 h-8 rounded-lg {{ request()->routeIs('depenses.*') ? 'bg-amber-500/25 ring-1 ring-amber-400/40' : 'bg-white/5' }} flex items-center justify-center transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </span>
                <span class="truncate">Dépenses</span>
            </a>

            <a href="{{ route('rapports.index') }}"
               class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('rapports.*') ? 'active' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                <span class="w-8 h-8 rounded-lg {{ request()->routeIs('rapports.*') ? 'bg-amber-500/25 ring-1 ring-amber-400/40' : 'bg-white/5' }} flex items-center justify-center transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </span>
                <span class="truncate">Rapports</span>
            </a>
            @endif

            {{-- Section : Paramètres --}}
            @if(in_array(auth()->user()->role, ['admin', 'gerant'], true))
            <p class="px-3 py-2 mt-3 text-slate-500 text-[10px] font-bold uppercase tracking-[0.12em] flex items-center gap-2">
                <span class="w-1 h-1 rounded-full bg-slate-500/60"></span>
                Paramètres
            </p>

         {{-- ══════ Lien Employés (Admin uniquement) ══════ --}}
@if(strtolower(trim(auth()->user()->role ?? '')) === 'admin')
    <a href="{{ route('employes.index') }}"
       class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('employes.*') ? 'active' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
        <span class="w-8 h-8 rounded-lg {{ request()->routeIs('employes.*') ? 'bg-amber-500/25 ring-1 ring-amber-400/40' : 'bg-white/5' }} flex items-center justify-center transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
        </span>
        <span class="truncate">Gestion des employés</span>
    </a>
@endif
            <a href="{{ route('services.index') }}"
               class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('services.*') || request()->routeIs('tarifs.*') ? 'active' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                <span class="w-8 h-8 rounded-lg {{ request()->routeIs('services.*') || request()->routeIs('tarifs.*') ? 'bg-amber-500/25 ring-1 ring-amber-400/40' : 'bg-white/5' }} flex items-center justify-center transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                </span>
                <span class="truncate">Services & Tarifs</span>
            </a>

            <a href="{{ route('fournisseurs.index') }}"
               class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('fournisseurs.*') ? 'active' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                <span class="w-8 h-8 rounded-lg {{ request()->routeIs('fournisseurs.*') ? 'bg-amber-500/25 ring-1 ring-amber-400/40' : 'bg-white/5' }} flex items-center justify-center transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </span>
                <span class="truncate">Fournisseurs</span>
            </a>
            @endif

            @if(auth()->user()->role === 'admin')
            <a href="{{ route('pressings.index') }}"
               class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('pressings.*') ? 'active' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                <span class="w-8 h-8 rounded-lg {{ request()->routeIs('pressings.*') ? 'bg-amber-500/25 ring-1 ring-amber-400/40' : 'bg-white/5' }} flex items-center justify-center transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </span>
                <span class="truncate">Mes pressings</span>
            </a>
            @endif

            <div class="h-4"></div>
        </nav>

        {{-- Footer : Profil utilisateur --}}
        <div class="relative px-3 py-3 border-t border-white/5 shrink-0">
            <div class="flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-white/5 transition-colors group">
                <a href="{{ route('profile.edit') }}"
                   class="relative w-9 h-9 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white font-extrabold text-xs uppercase shadow-lg shadow-amber-500/30 ring-1 ring-white/20 hover:scale-105 transition-transform"
                   title="Mon profil">
                    {{ mb_substr(auth()->user()->nom ?? 'U', 0, 2) }}
                    <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full bg-emerald-500 border-2 border-slate-900"></span>
                </a>
                <div class="flex-1 min-w-0">
                    <a href="{{ route('profile.edit') }}" class="block hover:text-amber-300 transition-colors">
                        <p class="text-white text-xs font-bold truncate">{{ auth()->user()->nom }}</p>
                    </a>
                    <p class="text-slate-400 text-[10.5px] truncate font-medium">{{ ucfirst(auth()->user()->role ?? '') }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="p-2 rounded-lg text-slate-500 hover:text-rose-400 hover:bg-rose-500/10 transition-all"
                            title="Se déconnecter">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- ═══════════════════════════════════════════════════════════════
         MAIN CONTENT
         ═══════════════════════════════════════════════════════════════ --}}
    <div class="flex-1 flex flex-col min-h-screen ml-64">

        {{-- Top bar --}}
        <header class="bg-white/90 backdrop-blur-md border-b border-slate-200/80 px-8 py-4 flex items-center justify-between sticky top-0 z-40 shadow-sm">
            <div class="min-w-0">
                <h1 class="text-lg font-extrabold text-slate-900 tracking-tight truncate">@yield('page-title', 'Tableau de bord')</h1>
                <p class="text-xs text-slate-500 font-medium truncate">@yield('page-subtitle', '')</p>
            </div>
            <div class="flex items-center gap-2.5">
                {{-- Date --}}
                <span class="hidden md:inline-flex items-center gap-2 text-xs font-semibold text-slate-600 bg-slate-100/80 px-3 py-1.5 rounded-xl border border-slate-200/60 shadow-sm">
                    <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    {{ now()->locale('fr')->isoFormat('dddd D MMMM YYYY') }}
                </span>

                {{-- Alertes stock --}}
                <a href="{{ route('produits.index', ['alerte' => 1]) }}"
                   class="group relative p-2.5 rounded-xl bg-slate-100/80 hover:bg-amber-50 text-slate-600 hover:text-amber-600 border border-slate-200/60 hover:border-amber-300 transition-all"
                   title="Alertes stock">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    @if(($nbAlertesStock ?? 0) > 0)
                        <span class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-gradient-to-r from-rose-500 to-pink-600 text-[9px] font-extrabold text-white flex items-center justify-center shadow-md shadow-rose-500/40">
                            {{ $nbAlertesStock }}
                        </span>
                        <span class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-rose-400 animate-ping opacity-30"></span>
                    @endif
                </a>

                {{-- Notifications traitements --}}
                @if(($nbATraiter ?? 0) > 0)
                    <a href="{{ route('traitement.index') }}"
                       class="hidden sm:flex group items-center gap-2 px-3 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white text-xs font-bold shadow-md shadow-amber-500/30 hover:shadow-lg transition-all"
                       title="Dépôts à traiter">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        {{ $nbATraiter }} à traiter
                    </a>
                @endif

                {{-- Profil --}}
                <a href="{{ route('profile.edit') }}"
                   class="flex items-center gap-2 p-1 pr-3 rounded-xl bg-slate-100/80 hover:bg-slate-200 border border-slate-200/60 transition-all group"
                   title="Mon profil">
                    <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white font-extrabold text-[10px] uppercase shadow-sm">
                        {{ mb_substr(auth()->user()->nom ?? 'U', 0, 2) }}
                    </span>
                    <span class="hidden md:block text-xs font-bold text-slate-700 group-hover:text-slate-900 truncate max-w-[100px]">
                        {{ auth()->user()->nom }}
                    </span>
                </a>
            </div>
        </header>

        {{-- Flash messages --}}
        @if(session('success'))
        <div id="flash-success"
             class="flash-msg mx-8 mt-6 flex items-start gap-3 px-4 py-3.5 bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-200/80 rounded-2xl text-emerald-800 text-sm shadow-md shadow-emerald-500/10">
            <span class="shrink-0 w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </span>
            <div class="flex-1">
                <p class="font-bold">Succès</p>
                <p class="text-emerald-700 text-xs mt-0.5">{{ session('success') }}</p>
            </div>
            <button onclick="document.getElementById('flash-success').remove()"
                    class="shrink-0 w-7 h-7 rounded-lg text-emerald-600 hover:text-white hover:bg-emerald-500 transition-all flex items-center justify-center"
                    aria-label="Fermer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        @endif

        @if(session('error'))
        <div id="flash-error"
             class="flash-msg mx-8 mt-6 flex items-start gap-3 px-4 py-3.5 bg-gradient-to-br from-rose-50 to-pink-50 border border-rose-200/80 rounded-2xl text-rose-800 text-sm shadow-md shadow-rose-500/10">
            <span class="shrink-0 w-8 h-8 rounded-lg bg-rose-100 flex items-center justify-center">
                <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </span>
            <div class="flex-1">
                <p class="font-bold">Erreur</p>
                <p class="text-rose-700 text-xs mt-0.5">{{ session('error') }}</p>
            </div>
            <button onclick="document.getElementById('flash-error').remove()"
                    class="shrink-0 w-7 h-7 rounded-lg text-rose-600 hover:text-white hover:bg-rose-500 transition-all flex items-center justify-center"
                    aria-label="Fermer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        @endif

        {{-- Page content --}}
        <main class="flex-1 p-8">
            @yield('content')
        </main>

        {{-- Footer --}}
        <footer class="px-8 py-4 border-t border-slate-200/60 text-xs text-slate-400 bg-white/40">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-2">
                <p>
                    <span class="font-semibold text-slate-600">PressManager</span>
                    &copy; {{ date('Y') }} — Logiciel de gestion professionnelle pour pressing
                </p>
                <div class="flex items-center gap-4">
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        Système opérationnel
                    </span>
                    <span class="text-slate-300">·</span>
                    <span class="font-mono text-[10px]">v2.0</span>
                </div>
            </div>
        </footer>
    </div>
</div>

<script>
    // Auto-dismiss flash messages after 5 seconds
    setTimeout(() => {
        ['flash-success', 'flash-error'].forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.style.opacity = '0';
                el.style.transform = 'translateX(20px)';
                el.style.transition = 'all 0.5s cubic-bezier(0.4, 0, 0.2, 1)';
                setTimeout(() => el.remove(), 500);
            }
        });
    }, 5000);
</script>

</body>
</html>