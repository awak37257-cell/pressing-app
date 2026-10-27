@extends('layouts.app')

@section('title', 'Clients')
@section('page-title', 'Clients')
@section('page-subtitle', 'Fichier clients et historique du pressing')

@section('content')
<div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
    {{-- En-tête avec recherche et bouton d'action --}}
    <div class="card-header px-6 py-4.5 border-b border-slate-100 flex flex-wrap items-center justify-between gap-4 bg-slate-50/50">
        <form method="GET" action="{{ route('clients.index') }}" class="flex flex-wrap gap-3 items-center flex-1 max-w-md">
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher par nom ou téléphone…"
                       class="form-input w-full pl-9 rounded-xl border-slate-200 text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="inline-flex items-center gap-1.5 bg-slate-800 hover:bg-slate-900 text-white font-medium px-4 py-2.5 rounded-xl text-sm transition shadow-xs">
                    Rechercher
                </button>
                @if(request('search'))
                    <a href="{{ route('clients.index') }}" class="inline-flex items-center px-3 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-slate-600 text-sm font-medium transition">
                        Réinitialiser
                    </a>
                @endif
            </div>
        </form>

        <a href="{{ route('clients.create') }}" class="inline-flex items-center gap-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-semibold px-4 py-2.5 rounded-xl shadow-md shadow-amber-500/20 text-sm transition-all duration-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nouveau client
        </a>
    </div>

    {{-- Tableau élégant --}}
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50/80 text-slate-400 uppercase text-[11px] font-bold tracking-wider border-b border-slate-200/60">
                    <th class="py-3.5 px-6">Client</th>
                    <th class="py-3.5 px-6">Téléphone</th>
                    <th class="py-3.5 px-6">Dépôts</th>
                    <th class="py-3.5 px-6 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
            @forelse($clients as $client)
                <tr class="hover:bg-slate-50/80 transition-colors">
                    <td class="py-4 px-6">
                        <a href="{{ route('clients.show', $client) }}" class="font-bold text-slate-800 hover:text-amber-600 transition">
                            {{ $client->nom }} {{ $client->prenom }}
                        </a>
                    </td>
                    <td class="py-4 px-6 text-slate-600 font-mono text-xs">{{ $client->telephone }}</td>
                    <td class="py-4 px-6">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200/60">
                            {{ $client->depots_count }} dépôt(s)
                        </span>
                    </td>
                    <td class="py-4 px-6 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('clients.edit', $client) }}" class="p-2 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-600 hover:text-amber-800 transition shadow-2xs" title="Modifier">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <form method="POST" action="{{ route('clients.destroy', $client) }}" class="inline" onsubmit="return confirm('Supprimer ce client ?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 hover:text-rose-800 transition shadow-2xs" title="Supprimer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center py-16 text-slate-400 bg-slate-50/30">
                        <div class="flex flex-col items-center justify-center">
                            <div class="w-14 h-14 bg-slate-100 rounded-full flex items-center justify-center mb-3 text-slate-400">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            </div>
                            <p class="font-medium text-slate-600 text-base">Aucun client trouvé</p>
                            <p class="text-xs text-slate-400 mt-0.5">Commencez par ajouter votre premier client pour enregistrer des dépôts.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($clients->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/40">
            {{ $clients->links() }}
        </div>
    @endif
</div>
@endsection