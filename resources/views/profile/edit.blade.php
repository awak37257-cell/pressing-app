@extends('layouts.app')

@section('title', 'Mon profil')
@section('page-title', 'Mon profil')
@section('page-subtitle', 'Modifier votre email et mot de passe')

@section('content')

<div class="max-w-lg mx-auto">
    <div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <div class="h-1.5 bg-gradient-to-r from-amber-400 to-amber-500"></div>

        <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50 flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-500/20 flex items-center justify-center text-amber-600 font-extrabold text-lg uppercase shadow-sm">
                {{ mb_substr(auth()->user()->nom ?? 'U', 0, 2) }}
            </div>
            <div>
                <p class="font-bold text-slate-800">{{ auth()->user()->nom }}</p>
                <p class="text-xs text-slate-500">{{ ucfirst(auth()->user()->role ?? '') }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('profile.update') }}" class="p-6 space-y-5">
            @csrf
            @method('PUT')

            {{-- Email --}}
            <div>
                <label for="email" class="block text-sm font-semibold text-slate-700 mb-1.5">Adresse email</label>
                <input type="email" id="email" name="email"
                       value="{{ old('email', $user->email) }}"
                       class="form-input w-full rounded-xl border-slate-200 text-sm @error('email') border-rose-400 @enderror"
                       required>
                @error('email')
                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <hr class="border-slate-100">

            <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Changer le mot de passe (optionnel)</p>

            {{-- Nouveau mot de passe --}}
            <div>
                <label for="password" class="block text-sm font-semibold text-slate-700 mb-1.5">Nouveau mot de passe</label>
                <input type="password" id="password" name="password"
                       class="form-input w-full rounded-xl border-slate-200 text-sm @error('password') border-rose-400 @enderror"
                       placeholder="Laisser vide pour ne pas changer"
                       autocomplete="new-password">
                @error('password')
                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Confirmer mot de passe --}}
            <div>
                <label for="password_confirmation" class="block text-sm font-semibold text-slate-700 mb-1.5">Confirmer le nouveau mot de passe</label>
                <input type="password" id="password_confirmation" name="password_confirmation"
                       class="form-input w-full rounded-xl border-slate-200 text-sm"
                       placeholder="Répétez le nouveau mot de passe"
                       autocomplete="new-password">
            </div>

            <hr class="border-slate-100">

            {{-- Mot de passe actuel (obligatoire) --}}
            <div>
                <label for="current_password" class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Mot de passe actuel <span class="text-rose-500">*</span>
                </label>
                <input type="password" id="current_password" name="current_password"
                       class="form-input w-full rounded-xl border-slate-200 text-sm @error('current_password') border-rose-400 @enderror"
                       required autocomplete="current-password">
                @error('current_password')
                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                @enderror
                <p class="text-xs text-slate-400 mt-1">Requis pour confirmer toute modification.</p>
            </div>

            <button type="submit"
                    class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-amber-500 hover:bg-amber-600 px-5 py-3 text-sm font-bold text-white shadow-md shadow-amber-500/25 transition">
                💾 Enregistrer les modifications
            </button>
        </form>
    </div>
</div>

@endsection
