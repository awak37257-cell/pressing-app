@extends('layouts.app')
@section('title', 'Fournisseurs')
@section('page-title', 'Fournisseurs')
@section('page-subtitle', 'Vos fournisseurs de produits')

@section('content')
<div class="card">
    <div class="card-header flex flex-wrap items-center justify-between gap-4">
        <form method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher…" class="form-input">
            <button class="btn-secondary">Rechercher</button>
        </form>
        <a href="{{ route('fournisseurs.create') }}" class="btn-primary">+ Nouveau fournisseur</a>
    </div>
    <div class="overflow-x-auto">
        <table class="table-base">
            <thead><tr><th>Nom</th><th>Téléphone</th><th>Adresse</th><th>Achats</th><th class="text-right">Actions</th></tr></thead>
            <tbody>
            @forelse($fournisseurs as $f)
                <tr>
                    <td class="font-medium text-slate-800">{{ $f->nom }}</td>
                    <td>{{ $f->telephone ?? '—' }}</td>
                    <td>{{ $f->adresse ?? '—' }}</td>
                    <td>{{ $f->achats_count }}</td>
                    <td class="text-right">
                        <a href="{{ route('fournisseurs.edit', $f) }}" class="text-amber-600 hover:underline text-sm">Modifier</a>
                        <form method="POST" action="{{ route('fournisseurs.destroy', $f) }}" class="inline" onsubmit="return confirm('Supprimer ce fournisseur ?')">
                            @csrf @method('DELETE')
                            <button class="text-red-500 hover:underline text-sm ml-3">Supprimer</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-slate-400 py-8">Aucun fournisseur.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-6 py-3">{{ $fournisseurs->links() }}</div>
</div>
@endsection
