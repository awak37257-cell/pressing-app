<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Tableau de bord') — PressManager</title>
    <meta name="description" content="Logiciel de gestion des stocks et opérations d'un pressing professionnel.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-slate-100/60 font-sans antialiased text-slate-900">

<div class="flex h-full">

    {{-- ===== SIDEBAR ===== --}}
    <aside id="sidebar"
           class="fixed inset-y-0 left-0 z-50 w-64 flex flex-col shadow-xl"
           style="background-color: #0f172a;">

        {{-- Logo --}}
        <div class="flex items-center gap-3 px-6 py-5 border-b border-slate-800/80 bg-slate-950/20 shrink-0">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-amber-500 to-amber-400 flex items-center justify-center shadow-lg shadow-amber-500/20">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                </svg>
            </div>
            <div>
                <p class="text-white font-extrabold text-sm tracking-wide leading-tight">PressManager</p>
                <p class="text-slate-400 text-xs">Gestion pressing SaaS</p>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto custom-scrollbar">

            <p class="px-4 py-1.5 text-slate-500 text-[11px] font-bold uppercase tracking-wider">Principal</p>

            <a href="{{ route('dashboard') }}"
               class="sidebar-link flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('dashboard') ? 'bg-amber-500 text-white font-bold shadow-md shadow-amber-500/20' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Tableau de bord
            </a>

            <p class="px-4 py-1.5 mt-4 text-slate-500 text-[11px] font-bold uppercase tracking-wider">Opérations</p>

            @php $nbATraiter = \App\Models\Depot::whereIn('statut', ['depose', 'en_traitement'])->count(); @endphp
            <a href="{{ route('traitement.index') }}"
               class="sidebar-link flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('traitement.*') ? 'bg-amber-500 text-white font-bold shadow-md shadow-amber-500/20' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                À traiter
                @if($nbATraiter > 0)
                    <span class="ml-auto rounded-full bg-rose-500 px-2 py-0.5 text-[11px] font-extrabold leading-none text-white shadow-2xs">{{ $nbATraiter }}</span>
                @endif
            </a>

            @if(auth()->user()->role !== 'operateur')
            <a href="{{ route('depots.index') }}"
               class="sidebar-link flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('depots.*') ? 'bg-amber-500 text-white font-bold shadow-md shadow-amber-500/20' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                Dépôts & Retraits
            </a>
            @endif

            @if(auth()->user()->role !== 'operateur')
            <a href="{{ route('clients.index') }}"
               class="sidebar-link flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('clients.*') ? 'bg-amber-500 text-white font-bold shadow-md shadow-amber-500/20' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Clients
            </a>
            @endif

            <p class="px-4 py-1.5 mt-4 text-slate-500 text-[11px] font-bold uppercase tracking-wider">Stocks</p>

            <a href="{{ route('produits.index') }}"
               class="sidebar-link flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('produits.*') ? 'bg-amber-500 text-white font-bold shadow-md shadow-amber-500/20' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                Produits & Stock
            </a>

            @if(in_array(auth()->user()->role, ['admin', 'gerant'], true))
            <a href="{{ route('achats.index') }}"
               class="sidebar-link flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('achats.*') ? 'bg-amber-500 text-white font-bold shadow-md shadow-amber-500/20' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Achats fournisseurs
            </a>
            @endif

            <a href="{{ route('mouvements.index') }}"
               class="sidebar-link flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('mouvements.*') ? 'bg-amber-500 text-white font-bold shadow-md shadow-amber-500/20' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                </svg>
                Mouvements stock
            </a>

            @if(in_array(auth()->user()->role, ['admin', 'gerant'], true))
            <a href="{{ route('quantites-standard.index') }}"
               class="sidebar-link flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('quantites-standard.*') ? 'bg-amber-500 text-white font-bold shadow-md shadow-amber-500/20' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                </svg>
                Quantités standard
            </a>
            @endif

            <p class="px-4 py-1.5 mt-4 text-slate-500 text-[11px] font-bold uppercase tracking-wider">Finance</p>

            @if(in_array(auth()->user()->role, ['admin', 'gerant'], true))
            <a href="{{ route('depenses.index') }}"
               class="sidebar-link flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('depenses.*') ? 'bg-amber-500 text-white font-bold shadow-md shadow-amber-500/20' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Dépenses
            </a>
            @endif

            @if(in_array(auth()->user()->role, ['admin', 'gerant'], true))
            <a href="{{ route('rapports.index') }}"
               class="sidebar-link flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('rapports.*') ? 'bg-amber-500 text-white font-bold shadow-md shadow-amber-500/20' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Rapports
            </a>
            @endif

            <p class="px-4 py-1.5 mt-4 text-slate-500 text-[11px] font-bold uppercase tracking-wider">Paramètres</p>

            @if(in_array(auth()->user()->role, ['admin', 'gerant'], true))
            <a href="{{ route('services.index') }}"
               class="sidebar-link flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('services.*') || request()->routeIs('tarifs.*') ? 'bg-amber-500 text-white font-bold shadow-md shadow-amber-500/20' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
                Services & Tarifs
            </a>
            @endif

            @if(in_array(auth()->user()->role, ['admin', 'gerant'], true))
            <a href="{{ route('fournisseurs.index') }}"
               class="sidebar-link flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('fournisseurs.*') ? 'bg-amber-500 text-white font-bold shadow-md shadow-amber-500/20' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                Fournisseurs
            </a>
            @endif

            @if(auth()->user()->role === 'admin')
            <a href="{{ route('pressings.index') }}"
               class="sidebar-link flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('pressings.*') ? 'bg-amber-500 text-white font-bold shadow-md shadow-amber-500/20' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Mes pressings
            </a>
            @endif

        </nav>

        {{-- Footer sidebar --}}
        <div class="px-4 py-3.5 border-t border-slate-800 bg-slate-950/30 shrink-0">
            <div class="flex items-center gap-3">
                <a href="{{ route('profile.edit') }}"
                   class="w-9 h-9 rounded-xl bg-amber-500/20 flex items-center justify-center text-amber-400 font-bold text-xs uppercase shadow-2xs hover:bg-amber-500/30 transition"
                   title="Mon profil">
                    {{ mb_substr(auth()->user()->nom ?? 'U', 0, 2) }}
                </a>
                <div class="flex-1 min-w-0">
                    <a href="{{ route('profile.edit') }}" class="hover:text-amber-300 transition">
                        <p class="text-white text-xs font-bold truncate">
                            {{ auth()->user()->nom }}
                        </p>
                    </a>
                    <p class="text-slate-400 text-[11px] truncate">
                        {{ ucfirst(auth()->user()->role ?? '') }}
                    </p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 transition" title="Se déconnecter">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- ===== MAIN CONTENT ===== --}}
    <div class="flex-1 flex flex-col min-h-screen ml-64">

        {{-- Top bar --}}
        <header class="bg-white/80 backdrop-blur-md border-b border-slate-200/80 px-8 py-4 flex items-center justify-between sticky top-0 z-40 shadow-2xs">
            <div>
                <h1 class="text-lg font-extrabold text-slate-800 tracking-tight">@yield('page-title', 'Tableau de bord')</h1>
                <p class="text-xs text-slate-500 font-medium">@yield('page-subtitle', '')</p>
            </div>
            <div class="flex items-center gap-3">
                {{-- Date --}}
                <span class="hidden sm:inline-flex items-center text-xs font-semibold text-slate-600 bg-slate-100 px-3 py-1.5 rounded-xl border border-slate-200/60 shadow-2xs">
                    📅 {{ now()->locale('fr')->isoFormat('dddd D MMMM YYYY') }}
                </span>
                {{-- Alert badge --}}
                <a href="{{ route('produits.index', ['alerte' => 1]) }}"
                   class="relative p-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition shadow-2xs" title="Alertes stock">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </a>
                {{-- Mon profil --}}
                <a href="{{ route('profile.edit') }}"
                   class="p-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition shadow-2xs" title="Mon profil">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </a>
            </div>
        </header>

        {{-- Flash messages --}}
        @if(session('success'))
        <div id="flash-success"
             class="mx-8 mt-6 flex items-center gap-3 px-4.5 py-3.5 bg-emerald-50 border border-emerald-200/80 rounded-2xl text-emerald-800 text-sm shadow-xs transition-all duration-300">
            <svg class="w-5 h-5 shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span class="font-medium">{{ session('success') }}</span>
            <button onclick="document.getElementById('flash-success').remove()" class="ml-auto text-emerald-600 hover:text-emerald-900 font-bold">✕</button>
        </div>
        @endif

        @if(session('error'))
        <div id="flash-error"
             class="mx-8 mt-6 flex items-center gap-3 px-4.5 py-3.5 bg-rose-50 border border-rose-200/80 rounded-2xl text-rose-800 text-sm shadow-xs transition-all duration-300">
            <svg class="w-5 h-5 shrink-0 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            <span class="font-medium">{{ session('error') }}</span>
            <button onclick="document.getElementById('flash-error').remove()" class="ml-auto text-rose-600 hover:text-rose-900 font-bold">✕</button>
        </div>
        @endif

        {{-- Page content --}}
        <main class="flex-1 p-8">
            @yield('content')
        </main>

        {{-- Footer --}}
        <footer class="px-8 py-4 border-t border-slate-200/60 text-xs text-slate-400 text-center bg-white/40">
            PressManager &copy; {{ date('Y') }} — Logiciel de gestion professionnelle pour pressing
        </footer>
    </div>
</div>

<script>
    // Auto-dismiss flash messages after 5 seconds
    setTimeout(() => {
        ['flash-success', 'flash-error'].forEach(id => {
            const el = document.getElementById(id);
            if (el) { el.style.opacity = '0'; el.style.transition = 'opacity 0.5s'; setTimeout(() => el.remove(), 500); }
        });
    }, 5000);
</script>

</body>
</html>