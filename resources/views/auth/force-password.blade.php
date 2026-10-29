@extends('layouts.app')

@section('title', 'Sécurité — Nouveau mot de passe')

@section('content')
<div class="max-w-md mx-auto mt-12 space-y-6">
    <div class="p-6 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 space-y-2">
        <h2 class="font-bold text-base">Première connexion requise</h2>
        <p class="text-xs text-amber-700 leading-relaxed">
            Pour des raisons de sécurité, veuillez définir un nouveau mot de passe personnel et secret avant d'accéder au logiciel.
        </p>
    </div>

    <form method="POST" action="{{ route('password.force.update') }}" class="p-6 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-4">
        @csrf @method('PUT')

        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Nouveau mot de passe *</label>
            <input type="password" name="password" required 
                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-amber-500">
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Confirmer le mot de passe *</label>
            <input type="password" name="password_confirmation" required 
                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-amber-500">
        </div>

        <button type="submit" class="w-full py-3 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-sm shadow-md transition">
            Valider et accéder à l'application
        </button>
    </form>
</div>
@endsection