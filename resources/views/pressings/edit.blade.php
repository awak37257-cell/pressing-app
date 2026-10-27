@extends('layouts.app')

@section('title', 'Modifier le pressing')
@section('page-title', 'Modifier : ' . $pressing->nom)
@section('page-subtitle', 'Mettre à jour les informations de l\'établissement')

@section('content')

<div class="max-w-lg mx-auto">
    <div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <div class="h-1.5 bg-gradient-to-r from-amber-400 to-amber-500"></div>
        <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50">
            <h3 class="font-bold text-slate-800">Informations du pressing</h3>
        </div>
        <form method="POST" action="{{ route('pressings.update', $pressing) }}" class="p-6 space-y-5">
            @csrf @method('PUT')

            <div>
                <label for="nom" class="block text-sm font-semibold text-slate-700 mb-1.5">Nom <span class="text-rose-500">*</span></label>
                <input type="text" id="nom" name="nom" value="{{ old('nom', $pressing->nom) }}"
                       class="form-input w-full rounded-xl border-slate-200 text-sm @error('nom') border-rose-400 @enderror"
                       required>
                @error('nom')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="adresse" class="block text-sm font-semibold text-slate-700 mb-1.5">Adresse</label>
                <textarea id="adresse" name="adresse" rows="2"
                          class="form-input w-full rounded-xl border-slate-200 text-sm resize-none @error('adresse') border-rose-400 @enderror">{{ old('adresse', $pressing->adresse) }}</textarea>
                @error('adresse')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="telephone" class="block text-sm font-semibold text-slate-700 mb-1.5">Téléphone</label>
                <input type="text" id="telephone" name="telephone" value="{{ old('telephone', $pressing->telephone) }}"
                       class="form-input w-full rounded-xl border-slate-200 text-sm @error('telephone') border-rose-400 @enderror">
                @error('telephone')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="flex-1 inline-flex items-center justify-center gap-2 rounded-xl bg-amber-500 hover:bg-amber-600 px-5 py-2.5 text-sm font-bold text-white shadow-md shadow-amber-500/25 transition">
                    💾 Enregistrer
                </button>
                <a href="{{ route('pressings.index') }}"
                   class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-sm font-semibold text-slate-700 transition">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>

@endsection
