<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Connectez-vous à votre espace PressManager — Solution de gestion professionnelle pour pressings.">
    <meta name="theme-color" content="#0f172a">
    <meta name="robots" content="noindex, nofollow">

    <title>Connexion — {{ config('app.name', 'PressManager') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @keyframes rise       { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes fadeScale  { from { opacity: 0; transform: scale(0.96); }    to { opacity: 1; transform: scale(1); } }
        @keyframes float      { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-12px); } }
        @keyframes pulseRing  { 0% { transform: scale(1); opacity: 0.5; } 70% { transform: scale(1.8); opacity: 0; } 100% { transform: scale(1.8); opacity: 0; } }
        @keyframes shimmer    { 0% { background-position: -200% 0; } 100% { background-position: 200% 0; } }
        @keyframes orbit      { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

        .rise       { animation: rise .6s cubic-bezier(.2,.7,.2,1) both; }
        .rise-2     { animation-delay: .1s; }
        .rise-3     { animation-delay: .2s; }
        .rise-4     { animation-delay: .3s; }
        .fade-scale { animation: fadeScale .5s cubic-bezier(.2,.7,.2,1) both; }
        .float      { animation: float 8s ease-in-out infinite; }
        .float-2    { animation: float 10s ease-in-out infinite; animation-delay: 2s; }

        .ring-pulse::before {
            content: ''; position: absolute; inset: 0; border-radius: 9999px;
            background: currentColor; animation: pulseRing 2.2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        .btn-shine { position: relative; overflow: hidden; }
        .btn-shine::after {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(110deg, transparent 30%, rgba(255,255,255,.35) 50%, transparent 70%);
            background-size: 200% 100%;
            animation: shimmer 3s ease-in-out infinite;
            pointer-events: none;
        }

        .input-focus:focus-within span.left-icon { color: #f59e0b; transform: scale(1.05); }
        .input-focus span.left-icon { transition: all .25s ease; }

        /* Champ invalide */
        .field-error input { border-color: #f43f5e !important; }

        @media (prefers-reduced-motion: reduce) {
            .rise, .fade-scale, .float, .btn-shine::after, .ring-pulse::before { animation: none !important; }
        }
    </style>
</head>
<body class="h-full bg-white antialiased text-slate-800">

<div class="min-h-full grid lg:grid-cols-2">

    {{-- ═══════════════════════════════════════════════════════════════
         PANNEAU DE MARQUE (desktop)
         ═══════════════════════════════════════════════════════════════ --}}
    <aside class="relative hidden lg:flex flex-col justify-between overflow-hidden p-12 xl:p-16 text-white"
           style="background: linear-gradient(135deg, #0a0f1e 0%, #1e293b 45%, #7c2d12 120%);">

        {{-- Décors de fond --}}
        <div aria-hidden="true" class="float absolute -top-24 -left-24 w-[28rem] h-[28rem] rounded-full bg-amber-500/25 blur-3xl"></div>
        <div aria-hidden="true" class="float-2 absolute -bottom-32 -right-20 w-[32rem] h-[32rem] rounded-full bg-orange-500/20 blur-3xl"></div>
        <div aria-hidden="true" class="absolute inset-0 opacity-[0.06]"
             style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 24px 24px;"></div>
        <div aria-hidden="true" class="absolute inset-0 opacity-[0.03]"
             style="background-image: linear-gradient(#fff 1px, transparent 1px), linear-gradient(90deg, #fff 1px, transparent 1px); background-size: 80px 80px;"></div>

        {{-- Logo --}}
        <div class="relative flex items-center gap-3 rise">
            <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center shadow-lg shadow-amber-500/40 ring-1 ring-white/20">
                <svg class="w-6 h-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M20.38 3.46 16 2a4 4 0 0 1-8 0L3.62 3.46a2 2 0 0 0-1.34 2.23l.58 3.47a1 1 0 0 0 .99.84H6v10c0 1.1.9 2 2 2h8a2 2 0 0 0 2-2V10h2.15a1 1 0 0 0 .99-.84l.58-3.47a2 2 0 0 0-1.34-2.23z"/>
                </svg>
            </div>
            <span class="text-xl font-extrabold tracking-tight">{{ config('app.name', 'PressManager') }}</span>
        </div>

        {{-- Contenu central --}}
        <div class="relative max-w-md rise rise-2">
            <span class="inline-flex items-center gap-2 text-[10px] font-bold text-amber-300 bg-amber-500/15 border border-amber-400/30 px-3 py-1.5 rounded-full tracking-wider uppercase mb-6">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-400 ring-pulse relative"></span>
                Solution pressing · Côte d'Ivoire
            </span>

            <h2 class="text-4xl xl:text-5xl font-extrabold leading-[1.1] tracking-tight">
                Pilotez votre pressing<br>
                <span class="bg-gradient-to-r from-amber-300 via-amber-400 to-orange-400 bg-clip-text text-transparent">en toute sérénité.</span>
            </h2>
            <p class="mt-5 text-slate-300 leading-relaxed text-[15px]">
                Dépôts, paiements, stocks et rapports réunis dans un seul outil, pensé pour les pressings ivoiriens.
            </p>

            <ul class="mt-10 space-y-5">
                @foreach ([
                    ['Dépôts et tickets', 'Suivez chaque vêtement, du dépôt jusqu’au retrait.', 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                    ['Caisse et Mobile Money', 'Espèces, Orange Money, MTN Money et Wave.', 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['Stocks et achats', 'Alertes de rupture et prix d’achat moyen automatiques.', 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
                ] as [$titre, $texte, $icone])
                    <li class="flex items-start gap-4 group">
                        <span class="shrink-0 w-10 h-10 rounded-xl bg-white/[0.08] ring-1 ring-white/15 flex items-center justify-center backdrop-blur-sm group-hover:bg-amber-500/20 group-hover:ring-amber-400/40 transition-all duration-300">
                            <svg class="w-5 h-5 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $icone }}"/></svg>
                        </span>
                        <div>
                            <p class="font-semibold text-white">{{ $titre }}</p>
                            <p class="text-sm text-slate-400 leading-relaxed">{{ $texte }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- Footer --}}
        <div class="relative flex items-center justify-between text-xs text-slate-500 rise rise-3">
            <p>&copy; {{ date('Y') }} {{ config('app.name', 'PressManager') }} · Côte d'Ivoire</p>
            <div class="flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                <span class="text-emerald-400/80 font-medium">Serveur opérationnel</span>
            </div>
        </div>
    </aside>

    {{-- ═══════════════════════════════════════════════════════════════
         FORMULAIRE
         ═══════════════════════════════════════════════════════════════ --}}
    <main class="relative flex items-center justify-center px-6 py-12 sm:px-10 bg-slate-50 lg:bg-white overflow-hidden">

        {{-- Décor subtil côté formulaire --}}
        <div aria-hidden="true" class="absolute top-0 right-0 w-72 h-72 rounded-full bg-amber-100/50 blur-3xl -translate-y-1/2 translate-x-1/2"></div>
        <div aria-hidden="true" class="absolute bottom-0 left-0 w-72 h-72 rounded-full bg-slate-100 blur-3xl translate-y-1/2 -translate-x-1/2"></div>

        <div class="relative w-full max-w-sm">

            {{-- Logo mobile --}}
            <div class="lg:hidden flex items-center gap-3 mb-10 rise">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center shadow-lg shadow-amber-500/40">
                    <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M20.38 3.46 16 2a4 4 0 0 1-8 0L3.62 3.46a2 2 0 0 0-1.34 2.23l.58 3.47a1 1 0 0 0 .99.84H6v10c0 1.1.9 2 2 2h8a2 2 0 0 0 2-2V10h2.15a1 1 0 0 0 .99-.84l.58-3.47a2 2 0 0 0-1.34-2.23z"/>
                    </svg>
                </div>
                <span class="text-lg font-extrabold tracking-tight">{{ config('app.name', 'PressManager') }}</span>
            </div>

            {{-- Titre --}}
            <div class="rise">
                <div class="inline-flex items-center gap-2 text-[10px] font-bold text-amber-700 bg-amber-100/60 border border-amber-200 px-2.5 py-1 rounded-full tracking-wider uppercase mb-3">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    Espace sécurisé
                </div>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 leading-tight">Content de vous revoir</h1>
                <p class="mt-2 text-sm text-slate-500">Connectez-vous pour accéder à votre espace pressing.</p>
            </div>

            {{-- Erreurs --}}
            @if ($errors->any())
                <div role="alert" aria-live="polite"
                     class="mt-6 p-4 rounded-xl bg-gradient-to-br from-rose-50 to-pink-50 border border-rose-200 text-rose-700 text-sm flex items-start gap-3 shadow-sm fade-scale">
                    <span class="shrink-0 w-8 h-8 rounded-lg bg-rose-100 flex items-center justify-center">
                        <svg class="h-4 w-4 text-rose-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </span>
                    <div class="flex-1">
                        <p class="font-bold">Connexion impossible</p>
                        <ul class="mt-1 space-y-0.5 text-rose-600 text-xs">
                            @foreach ($errors->all() as $error)
                                <li>· {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            {{-- Formulaire --}}
            <form method="POST" action="{{ route('login.store') }}" id="login-form" class="mt-8 space-y-5 rise rise-2" novalidate>
                @csrf

                {{-- Email --}}
                <div class="{{ $errors->has('email') ? 'field-error' : '' }}">
                    <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Adresse email</label>
                    <div class="input-focus relative">
                        <span class="left-icon pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </span>
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                               required autofocus autocomplete="username" inputmode="email"
                               placeholder="vous@exemple.com"
                               class="block w-full rounded-xl border bg-white pl-11 pr-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 shadow-sm transition-all duration-200
                                      focus:outline-none focus:ring-4 focus:ring-amber-400/20 focus:border-amber-500
                                      {{ $errors->has('email') ? 'border-rose-400' : 'border-slate-300' }}">
                    </div>
                </div>

                {{-- Mot de passe --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Mot de passe</label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-xs font-semibold text-amber-600 hover:text-amber-700 hover:underline transition-colors">Mot de passe oublié ?</a>
                        @endif
                    </div>
                    <div class="input-focus relative">
                        <span class="left-icon pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </span>
                        <input type="password" id="password" name="password"
                               required autocomplete="current-password" placeholder="••••••••"
                               class="block w-full rounded-xl border border-slate-300 bg-white pl-11 pr-12 py-3 text-sm text-slate-900 placeholder:text-slate-400 shadow-sm transition-all duration-200
                                      focus:outline-none focus:ring-4 focus:ring-amber-400/20 focus:border-amber-500">
                        <button type="button" id="toggle-password" aria-label="Afficher le mot de passe" aria-pressed="false"
                                class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-amber-600 transition-colors focus:outline-none focus-visible:text-amber-600">
                            <svg id="icon-eye" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg id="icon-eye-off" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 3l18 18M10.584 10.587a2 2 0 002.828 2.83M9.363 5.365A9.466 9.466 0 0112 5c4.478 0 8.268 2.943 9.542 7a9.97 9.97 0 01-1.563 3.029M6.228 6.228A9.97 9.97 0 002.458 12c1.274 4.057 5.064 7 9.542 7a9.5 9.5 0 004.043-.898"/></svg>
                        </button>
                    </div>
                </div>

                {{-- Remember --}}
                <label for="remember" class="group inline-flex items-center gap-2.5 text-sm text-slate-600 cursor-pointer select-none">
                    <input type="checkbox" id="remember" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}
                           class="w-4 h-4 rounded border-slate-300 text-amber-500 focus:ring-2 focus:ring-amber-400/40 focus:ring-offset-0 transition">
                    <span class="group-hover:text-slate-900 transition-colors">Rester connecté</span>
                </label>

                {{-- Bouton --}}
                <button type="submit" id="submit-btn"
                        class="btn-shine group w-full inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 px-4 py-3.5 text-sm font-bold text-white
                               shadow-lg shadow-amber-500/30 hover:shadow-xl hover:shadow-amber-500/40 transition-all duration-200 active:scale-[0.98] hover:-translate-y-0.5
                               focus:outline-none focus-visible:ring-4 focus-visible:ring-amber-400/40 disabled:opacity-70 disabled:cursor-not-allowed disabled:hover:translate-y-0">
                    <svg id="spinner" class="hidden w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"/>
                        <path d="M4 12a8 8 0 018-8" stroke="currentColor" stroke-width="4" stroke-linecap="round" class="opacity-90"/>
                    </svg>
                    <span id="submit-label">Se connecter</span>
                    <svg id="arrow" class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14m-6-6l6 6-6 6"/></svg>
                </button>
            </form>

            {{-- Footer --}}
            <div class="mt-10 rise rise-3">
                <div class="flex items-center gap-3">
                    <div class="flex-1 h-px bg-slate-200"></div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Accès sécurisé</span>
                    <div class="flex-1 h-px bg-slate-200"></div>
                </div>
                <p class="mt-4 text-center text-xs text-slate-400 leading-relaxed">
                    Accès réservé au personnel autorisé du pressing.<br>
                    <span class="inline-flex items-center gap-1 mt-1.5 text-[11px]">
                        <svg class="w-3 h-3 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        Connexion chiffrée · SSL
                    </span>
                </p>
            </div>
        </div>
    </main>
</div>

<script>
    // ─── Toggle visibilité mot de passe ───
    const pwd    = document.getElementById('password');
    const toggle = document.getElementById('toggle-password');
    toggle.addEventListener('click', () => {
        const visible = pwd.type === 'text';
        pwd.type = visible ? 'password' : 'text';
        toggle.setAttribute('aria-pressed', String(!visible));
        toggle.setAttribute('aria-label', visible ? 'Afficher le mot de passe' : 'Masquer le mot de passe');
        document.getElementById('icon-eye').classList.toggle('hidden', !visible);
        document.getElementById('icon-eye-off').classList.toggle('hidden', visible);
    });

    // ─── État de chargement du bouton ───
    document.getElementById('login-form').addEventListener('submit', () => {
        const btn = document.getElementById('submit-btn');
        btn.disabled = true;
        document.getElementById('spinner').classList.remove('hidden');
        document.getElementById('arrow').classList.add('hidden');
        document.getElementById('submit-label').textContent = 'Connexion…';
    });

    // ─── Retour arrière : recharger l'état du bouton ───
    window.addEventListener('pageshow', e => { if (e.persisted) location.reload(); });
</script>
</body>
</html>