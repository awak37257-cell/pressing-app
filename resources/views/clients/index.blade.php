@extends('layouts.app')
@section('title', 'Clients')
@section('page-title', 'Clients')
@section('page-subtitle', 'Fichier clients du pressing')

@section('content')
<div class="card">
    <div class="card-header flex flex-wrap items-center justify-between gap-4">
        <form method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Nom ou téléphone…" class="form-input">
            <button class="btn-secondary">Rechercher</button>
        </form>
        <a href="{{ route('clients.create') }}" class="btn-primary">+ Nouveau client</a>
    </div>
    <div class="overflow-x-auto">
        <table class="table-base">
            <thead><tr><th>Client</th><th>Téléphone</th><th>Dépôts</th><th class="text-right">Actions</th></tr></thead>
            <tbody>
            @forelse($clients as $client)
                <tr>
                    <td><a href="{{ route('clients.show', $client) }}" class="font-medium text-slate-800 hover:text-amber-600">{{ $client->nom }} {{ $client->prenom }}</a></td>
                    <td>{{ $client->telephone }}</td>
                    <td>{{ $client->depots_count }}</td>
                    <td class="text-right">
                        <a href="{{ route('clients.edit', $client) }}" class="text-amber-600 hover:underline text-sm">Modifier</a>
                        <form method="POST" action="{{ route('clients.destroy', $client) }}" class="inline" onsubmit="return confirm('Supprimer ce client ?')">
                            @csrf @method('DELETE')
                            <button class="text-red-500 hover:underline text-sm ml-3">Supprimer</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-slate-400 py-8">Aucun client.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-6 py-3">{{ $clients->links() }}</div>
</div>
@endsection
