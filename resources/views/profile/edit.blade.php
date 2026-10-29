@extends('layouts.app')

@section('title', 'Mon profil')
@section('page-title', 'Mon profil')
@section('page-subtitle', 'Modifier votre email et mot de passe')

@section('content')

<style>
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes pulseRing { 0% { transform: scale(1); opacity: 0.6; } 70% { transform: scale(1.8); opacity: 0; } 100% { transform: scale(1.8); opacity: 0; } }
    .form-card { animation: fadeInUp 0.5s ease-out backwards; }
    .form-card:nth-child(1) { animation-delay: 0s; }
    .form-card:nth-child(2) { animation-delay: 0.08s; }
    .form-card:nth-child(3) { animation-delay: 0.16s; }
    .gradient-border { position: relative; background: white; }
    .gradient-border::before {
        content: ''; position: absolute; inset: 0; padding: 1px; border-radius: inherit;
        background: linear-gradient(135deg, rgba(251,191,36,0.3), rgba(245,158,11,0.1), rgba(251,191,36,0.3));
        -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor; mask-composite: exclude; pointer-events: none;
    }
    .ring-pulse::before { content: ''; position: absolute; inset: 0; border-radius: 9999px; background: currentColor; animation: pulseRing 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
    .pwd-strength { transition: width 0.3s ease, background 0.3s ease; }
</style>

<div class="max-w-3xl mx-auto space-y-6">

    {{-- ══════ CARTE IDENTITÉ PREMIUM ══════ --}}
    <div class="form-card relative overflow-hidden rounded-2xl text-white shadow-2xl shadow-slate-900/30"
         style="background: linear-gradient(135deg, #0a0f1e 0%, #1e293b 45%, #7c2d12 120%);">

        <div aria-hidden="true" class="absolute -top-20 -left-16 w-72 h-72 rounded-full bg-amber-500/25 blur-3xl"></div>
        <div aria-hidden="true" class="absolute -bottom-24 right-0 w-80 h-80 rounded-full bg-orange-500/20 blur-3xl"></div>
        <div aria-hidden="true" class="absolute inset-0 opacity-[0.07]"
             style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 22px 22px;"></div>

        <div class="relative p-6 sm:p-8 flex flex-col sm:flex-row sm:items-center gap-5">
            <div class="relative">
                <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white font-extrabold text-2xl uppercase shadow-2xl shadow-amber-500/40 ring-2 ring-white/20">
                    {{ mb_substr(auth()->user()->nom ?? 'U', 0, 2) }}
                </div>
                <span class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full bg-emerald-500 border-2 border-slate-900 shadow-md"></span>
            </div>

            <div class="flex-1 min-w-0">
                <div class="inline-flex items-center gap-2 text-[10px] font-bold tracking-wider text-amber-300 bg-amber-500/15 border border-amber-400/30 px-2.5 py-1 rounded-full uppercase mb-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 ring-pulse relative"></span>
                    Compte actif
                </div>
                <h1 class="text-2xl font-extrabold tracking-tight truncate">{{ auth()->user()->nom }}</h1>
                <div class="flex flex-wrap items-center gap-3 mt-2 text-sm text-slate-300">
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <span class="font-mono text-xs">{{ auth()->user()->email }}</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-white/10 border border-white/15 text-xs font-bold">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        {{ ucfirst(auth()->user()->role ?? '') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════ FORMULAIRE ══════ --}}
    <form method="POST" action="{{ route('profile.update') }}" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Section 1 : Email --}}
        <div class="form-card gradient-border rounded-2xl bg-white shadow-lg shadow-slate-200/50 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-white flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-blue-500/25">
                    <span class="text-xs font-extrabold">01</span>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 text-sm">Adresse email</h3>
                    <p class="text-[11px] text-slate-500">Utilisée pour la connexion</p>
                </div>
            </div>
            <div class="p-6">
                <label for="email" class="text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5 block">
                    Email <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 pointer-events-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </span>
                    <input type="email" id="email" name="email"
                           value="{{ old('email', $user->email) }}"
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl border {{ $errors->has('email') ? 'border-rose-400' : 'border-slate-200' }} bg-slate-50/50 text-sm text-slate-700 focus:bg-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all"
                           required>
                </div>
                @error('email')<p class="text-rose-500 text-xs font-bold mt-1.5">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- Section 2 : Nouveau mot de passe --}}
        <div class="form-card gradient-border rounded-2xl bg-white shadow-lg shadow-slate-200/50 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-white flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-white shadow-md shadow-amber-500/25">
                    <span class="text-xs font-extrabold">02</span>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 text-sm">Changer le mot de passe</h3>
                    <p class="text-[11px] text-slate-500">Laissez vide pour conserver l'actuel</p>
                </div>
            </div>
            <div class="p-6 space-y-5">
                {{-- Nouveau mot de passe --}}
                <div>
                    <label for="password" class="text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5 block">Nouveau mot de passe</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 pointer-events-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </span>
                        <input type="password" id="password" name="password"
                               class="pwd-field w-full pl-10 pr-12 py-2.5 rounded-xl border {{ $errors->has('password') ? 'border-rose-400' : 'border-slate-200' }} bg-slate-50/50 text-sm text-slate-700 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all"
                               placeholder="Laisser vide pour ne pas changer"
                               autocomplete="new-password">
                        <button type="button"
                                class="pwd-toggle absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-amber-600 transition-colors"
                                aria-label="Afficher le mot de passe">
                            <svg class="w-4 h-4 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg class="w-4 h-4 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.584 10.587a2 2 0 002.828 2.83M9.363 5.365A9.466 9.466 0 0112 5c4.478 0 8.268 2.943 9.542 7a9.97 9.97 0 01-1.563 3.029M6.228 6.228A9.97 9.97 0 002.458 12c1.274 4.057 5.064 7 9.542 7a9.5 9.5 0 004.043-.898"/>
                            </svg>
                        </button>
                    </div>
                    {{-- Barre de force --}}
                    <div id="pwd-strength-wrap" class="hidden mt-2">
                        <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                            <div id="pwd-strength" class="pwd-strength h-full rounded-full" style="width: 0%; background: #cbd5e1;"></div>
                        </div>
                        <p id="pwd-strength-label" class="text-[10px] font-bold mt-1"></p>
                    </div>
                    @error('password')<p class="text-rose-500 text-xs font-bold mt-1.5">{{ $message }}</p>@enderror
                </div>

                {{-- Confirmation --}}
                <div>
                    <label for="password_confirmation" class="text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5 block">Confirmer le mot de passe</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 pointer-events-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </span>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               class="pwd-field w-full pl-10 pr-12 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-sm text-slate-700 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all"
                               placeholder="Répétez le nouveau mot de passe"
                               autocomplete="new-password">
                        <button type="button"
                                class="pwd-toggle absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-amber-600 transition-colors"
                                aria-label="Afficher le mot de passe">
                            <svg class="w-4 h-4 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg class="w-4 h-4 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.584 10.587a2 2 0 002.828 2.83M9.363 5.365A9.466 9.466 0 0112 5c4.478 0 8.268 2.943 9.542 7a9.97 9.97 0 01-1.563 3.029M6.228 6.228A9.97 9.97 0 002.458 12c1.274 4.057 5.064 7 9.542 7a9.5 9.5 0 004.043-.898"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 3 : Mot de passe actuel --}}
        <div class="form-card gradient-border rounded-2xl bg-white shadow-lg shadow-slate-200/50 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-white flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-rose-500 to-pink-600 flex items-center justify-center text-white shadow-md shadow-rose-500/25">
                    <span class="text-xs font-extrabold">03</span>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 text-sm">Confirmation de sécurité</h3>
                    <p class="text-[11px] text-slate-500">Requis pour toute modification</p>
                </div>
            </div>
            <div class="p-6">
                <label for="current_password" class="text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5 block">
                    Mot de passe actuel <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 pointer-events-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </span>
                    <input type="password" id="current_password" name="current_password"
                           class="pwd-field w-full pl-10 pr-12 py-2.5 rounded-xl border {{ $errors->has('current_password') ? 'border-rose-400' : 'border-slate-200' }} bg-slate-50/50 text-sm text-slate-700 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all"
                           required autocomplete="current-password">
                    <button type="button"
                            class="pwd-toggle absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-amber-600 transition-colors"
                            aria-label="Afficher le mot de passe">
                        <svg class="w-4 h-4 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg class="w-4 h-4 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.584 10.587a2 2 0 002.828 2.83M9.363 5.365A9.466 9.466 0 0112 5c4.478 0 8.268 2.943 9.542 7a9.97 9.97 0 01-1.563 3.029M6.228 6.228A9.97 9.97 0 002.458 12c1.274 4.057 5.064 7 9.542 7a9.5 9.5 0 004.043-.898"/>
                        </svg>
                    </button>
                </div>
                @error('current_password')<p class="text-rose-500 text-xs font-bold mt-1.5">{{ $message }}</p>@enderror
                <p class="text-[11px] text-slate-400 mt-2 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Requis pour confirmer la modification de votre profil.
                </p>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex flex-col sm:flex-row items-center justify-end gap-2 pt-2">
            <a href="{{ route('dashboard') }}"
               class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 bg-white hover:bg-slate-50 text-slate-600 border border-slate-200 font-semibold py-2.5 px-5 rounded-xl text-sm transition">
                Annuler
            </a>
            <button type="submit"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-bold py-2.5 px-5 rounded-xl shadow-lg shadow-amber-500/30 hover:shadow-xl text-sm transition-all duration-200 active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                Enregistrer les modifications
            </button>
        </div>
    </form>

    {{-- Footer sécurité --}}
    <div class="flex flex-col sm:flex-row items-center justify-center gap-3 text-[11px] text-slate-400">
        <span class="inline-flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            Connexion chiffrée SSL
        </span>
        <span class="hidden sm:inline text-slate-300">·</span>
        <span>Dernière modification : {{ auth()->user()->updated_at?->format('d/m/Y à H:i') }}</span>
    </div>
</div>

<script>
(() => {
    // ─── Toggle visibilité des mots de passe ───
    document.querySelectorAll('.pwd-toggle').forEach(btn => {
        btn.addEventListener('click', () => {
            const input = btn.parentElement.querySelector('.pwd-field');
            const eyeOpen = btn.querySelector('.eye-open');
            const eyeClosed = btn.querySelector('.eye-closed');
            const isHidden = input.type === 'password';

            input.type = isHidden ? 'text' : 'password';
            eyeOpen.classList.toggle('hidden', isHidden);
            eyeClosed.classList.toggle('hidden', !isHidden);
            btn.setAttribute('aria-label', isHidden ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
        });
    });

    // ─── Barre de force du mot de passe ───
    const pwdInput = document.getElementById('password');
    const pwdWrap = document.getElementById('pwd-strength-wrap');
    const pwdBar = document.getElementById('pwd-strength');
    const pwdLabel = document.getElementById('pwd-strength-label');

    if (pwdInput) {
        pwdInput.addEventListener('input', () => {
            const val = pwdInput.value;
            if (!val) {
                pwdWrap.classList.add('hidden');
                return;
            }
            pwdWrap.classList.remove('hidden');

            let score = 0;
            if (val.length >= 8) score++;
            if (val.length >= 12) score++;
            if (/[a-z]/.test(val) && /[A-Z]/.test(val)) score++;
            if (/\d/.test(val)) score++;
            if (/[^a-zA-Z0-9]/.test(val)) score++;

            const config = [
                { w: '20%', bg: '#ef4444', label: 'Très faible', color: '#dc2626' },
                { w: '40%', bg: '#f97316', label: 'Faible', color: '#ea580c' },
                { w: '60%', bg: '#eab308', label: 'Moyen', color: '#ca8a04' },
                { w: '80%', bg: '#22c55e', label: 'Bon', color: '#16a34a' },
                { w: '100%', bg: '#10b981', label: 'Excellent', color: '#059669' },
            ];
            const idx = Math.min(score, 4);
            const cfg = config[idx];

            pwdBar.style.width = cfg.w;
            pwdBar.style.background = cfg.bg;
            pwdLabel.textContent = cfg.label;
            pwdLabel.style.color = cfg.color;
        });
    }

    // ─── Feedback temps réel : confirmation ───
    const confirmInput = document.getElementById('password_confirmation');
    if (confirmInput && pwdInput) {
        confirmInput.addEventListener('input', () => {
            if (!confirmInput.value || !pwdInput.value) return;
            if (confirmInput.value === pwdInput.value) {
                confirmInput.classList.remove('border-rose-400');
                confirmInput.classList.add('border-emerald-400');
            } else {
                confirmInput.classList.remove('border-emerald-400');
                confirmInput.classList.add('border-rose-400');
            }
        });
    }
})();
</script>

@endsection