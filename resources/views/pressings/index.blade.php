@extends('layouts.app')

@section('title', 'Gestion des pressings')
@section('page-title', 'Pressings')
@section('page-subtitle', 'Administration de tous les établissements')

@section('content')

<div class="flex items-center justify-between mb-6">
    <div></div>
    <a href="{{ route('pressings.create') }}"
       class="inline-flex items-center gap-2 rounded-xl bg-amber-500 hover:bg-amber-600 px-5 py-2.5 text-sm font-bold text-white shadow-md shadow-amber-500/25 transition">
        + Nouveau pressing
    </a>
</div>

<div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
    <div class="card-header px-6 py-4.5 border-b border-slate-100 bg-slate-50/50">
        <h3 class="font-bold text-slate-800">Liste des pressings ({{ $pressings->count() }})</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="table-base">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nom</th>
                    <th>Adresse</th>
                    <th>Téléphone</th>
                    <th class="text-center">Utilisateurs</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($pressings as $pressing)
                <tr>
                    <td class="text-slate-400 font-mono text-xs">{{ $pressing->id }}</td>
                    <td class="font-semibold text-slate-800">{{ $pressing->nom }}</td>
                    <td class="text-slate-500 text-sm">{{ $pressing->adresse ?? '—' }}</td>
                    <td class="text-slate-500 text-sm">{{ $pressing->telephone ?? '—' }}</td>
                    <td class="text-center">
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-amber-100 text-amber-700 text-xs font-bold">
                            {{ $pressing->users_count }}
                        </span>
                    </td>
                    <td class="text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('pressings.edit', $pressing) }}"
                               class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-semibold text-slate-700 transition">
                                ✎ Modifier
                            </a>
                            <form method="POST" action="{{ route('pressings.destroy', $pressing) }}"
                                  onsubmit="return confirm('Supprimer ce pressing ?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-xs font-semibold text-rose-600 transition">
                                    🗑 Supprimer
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="py-12 text-center">
                        <p class="text-slate-400 text-sm">Aucun pressing enregistré.</p>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
