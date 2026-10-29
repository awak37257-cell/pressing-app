@extends('layouts.app')

@section('title', 'Nouveau compte employé')
@section('page-title', 'Nouveau compte employé')
@section('page-subtitle', 'Créer un accès pour un nouveau collaborateur')

@section('content')

<style>
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
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
</style>

<div class="max-w-3xl mx-auto space-y-6">

    {{-- Hero --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center shadow-lg shadow-violet-500/30">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Créer un compte employé</h1>
                <p class="text-xs text-slate-500 font-medium">Nouvel accès pour un collaborateur</p>
            </div>
        </div>
        <a href="{{ route('employes.index') }}"
           class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 hover:border-slate-300 px-3.5 py-2 rounded-xl transition shadow-sm hover:shadow">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Retour
        </a>
    </div>

    {{-- Formulaire --}}
    <form method="POST" action="{{ route('employes.store') }}" class="space-y-6">
        @csrf

        {{-- Section 1 : Identité --}}
        <div class="form-card gradient-border rounded-2xl bg-white shadow-lg shadow-slate-200/50 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-white flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-blue-500/25">
                    <span class="text-xs font-extrabold">01</span>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 text-sm">Identité du collaborateur</h3>
                    <p class="text-[11px] text-slate-500">Nom complet et coordonnées</p>
                </div>
            </div>
            <div class="p-6 space-y-5">
                <div>
                    <label for="nom" class="text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5 block">Nom complet <span class="text-rose-500">*</span></label>
                    <input type="text" id="nom" name="nom" value="{{ old('nom') }}"
                           class="w-full px-3.5 py-2.5 rounded-xl border {{ $errors->has('nom') ? 'border-rose-400' : 'border-slate-200' }} bg-slate-50/50 text-sm text-slate-700 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all"
                           required placeholder="Ex : Konan Yao">
                    @error('nom')<p class="text-rose-500 text-xs font-bold mt-1.5">{{ $message }}</p>@enderror
                </div>

                <div class="grid sm:grid-cols-2 gap-5">
                    <div>
                        <label for="email" class="text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5 block">Adresse email <span class="text-rose-500">*</span></label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                               class="w-full px-3.5 py-2.5 rounded-xl border {{ $errors->has('email') ? 'border-rose-400' : 'border-slate-200' }} bg-slate-50/50 text-sm text-slate-700 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all"
                               required placeholder="employe@pressmanager.ci">
                        @error('email')<p class="text-rose-500 text-xs font-bold mt-1.5">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="telephone" class="text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5 block">Téléphone</label>
                        <input type="text" id="telephone" name="telephone" value="{{ old('telephone') }}"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-sm text-slate-700 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all"
                               placeholder="Ex : 07 00 00 00 00">
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 2 : Rôle --}}
        <div class="form-card gradient-border rounded-2xl bg-white shadow-lg shadow-slate-200/50 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-white flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center text-white shadow-md shadow-violet-500/25">
                    <span class="text-xs font-extrabold">02</span>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 text-sm">Rôle & permissions</h3>
                    <p class="text-[11px] text-slate-500">Niveau d'accès au système</p>
                </div>
            </div>
            <div class="p-6">
                <label for="role" class="text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5 block">Rôle <span class="text-rose-500">*</span></label>
                <div class="relative">
                    <select id="role" name="role" required
                            class="appearance-none w-full pl-3.5 pr-9 py-2.5 rounded-xl border {{ $errors->has('role') ? 'border-rose-400' : 'border-slate-200' }} bg-slate-50/50 text-sm text-slate-700 focus:bg-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all cursor-pointer">
                        <option value="gerant" @selected(old('role') === 'gerant')>Gérant</option>
                        <option value="caissier" @selected(old('role') === 'caissier')>Caissière</option>
                        <option value="atelier" @selected(old('role') === 'atelier')>Operateur</option>
                    </select>
                    <svg class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
                @error('role')<p class="text-rose-500 text-xs font-bold mt-1.5">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- Section 3 : Mot de passe --}}
        <div class="form-card gradient-border rounded-2xl bg-white shadow-lg shadow-slate-200/50 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-white flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-rose-500 to-pink-600 flex items-center justify-center text-white shadow-md shadow-rose-500/25">
                    <span class="text-xs font-extrabold">03</span>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 text-sm">Mot de passe</h3>
                    <p class="text-[11px] text-slate-500">Identifiants de connexion</p>
                </div>
            </div>
            <div class="p-6 grid sm:grid-cols-2 gap-5">
                <div>
                    <label for="password" class="text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5 block">Mot de passe <span class="text-rose-500">*</span></label>
                    <input type="password" id="password" name="password"
                           class="w-full px-3.5 py-2.5 rounded-xl border {{ $errors->has('password') ? 'border-rose-400' : 'border-slate-200' }} bg-slate-50/50 text-sm text-slate-700 focus:bg-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all"
                           required autocomplete="new-password" minlength="8">
                    @error('password')<p class="text-rose-500 text-xs font-bold mt-1.5">{{ $message }}</p>@enderror
                    <p class="text-[10px] text-slate-400 mt-1">8 caractères minimum</p>
                </div>
                <div>
                    <label for="password_confirmation" class="text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5 block">Confirmer <span class="text-rose-500">*</span></label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-sm text-slate-700 focus:bg-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all"
                           required autocomplete="new-password" minlength="8">
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex flex-col sm:flex-row items-center justify-end gap-2 pt-2">
            <a href="{{ route('employes.index') }}"
               class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 bg-white hover:bg-slate-50 text-slate-600 border border-slate-200 font-semibold py-2.5 px-5 rounded-xl text-sm transition">
                Annuler
            </a>
            <button type="submit"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-bold py-2.5 px-5 rounded-xl shadow-lg shadow-amber-500/30 hover:shadow-xl text-sm transition-all duration-200 active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Créer le compte
            </button>
        </div>
    </form>
</div>

@endsection