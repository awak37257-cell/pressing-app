@extends('layouts.app')

@section('title', 'À traiter')
@section('page-title', 'À traiter')
@section('page-subtitle', 'Dépôts à commencer ou en cours de traitement')

@php
    $nbADemarrer = (int) ($compteurs['depose'] ?? 0);
    $nbEnCours   = (int) ($compteurs['en_traitement'] ?? 0);
    $nbPrets     = (int) ($compteurs['pret'] ?? 0);
@endphp

@section('content')

@if ($errors->any())
    <div role="alert" class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm">
        @foreach ($errors->all() as $erreur)<p>{{ $erreur }}</p>@endforeach
    </div>
@endif

{{-- Compteurs / filtres --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <a href="{{ route('traitement.index', ['statut' => 'depose']) }}" class="stat-card relative overflow-hidden transition hover:-translate-y-0.5 hover:shadow-md {{ $filtre === 'depose' ? 'ring-2 ring-blue-400' : '' }}">
        <span aria-hidden="true" class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-blue-400 to-blue-500"></span>
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">À commencer</p>
        <p class="text-3xl font-bold text-slate-800 mt-1">{{ $nbADemarrer }}</p>
    </a>
    <a href="{{ route('traitement.index', ['statut' => 'en_traitement']) }}" class="stat-card relative overflow-hidden transition hover:-translate-y-0.5 hover:shadow-md {{ $filtre === 'en_traitement' ? 'ring-2 ring-amber-400' : '' }}">
        <span aria-hidden="true" class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-amber-400 to-amber-500"></span>
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">En traitement</p>
        <p class="text-3xl font-bold text-slate-800 mt-1">{{ $nbEnCours }}</p>
    </a>
    <div class="stat-card relative overflow-hidden">
        <span aria-hidden="true" class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-emerald-400 to-emerald-500"></span>
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Prêts à retirer</p>
        <p class="text-3xl font-bold text-emerald-600 mt-1">{{ $nbPrets }}</p>
    </div>
    <div class="stat-card relative overflow-hidden">
        <span aria-hidden="true" class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-red-400 to-rose-500"></span>
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">En retard</p>
        <p class="text-3xl font-bold {{ $enRetard > 0 ? 'text-red-500' : 'text-slate-800' }} mt-1">{{ $enRetard }}</p>
    </div>
</div>

<div class="card">
    <div class="card-header flex items-center justify-between">
        <div>
            <h3 class="font-semibold text-slate-800">Ordre de travail</h3>
            <p class="text-xs text-slate-500 mt-0.5">Les dépôts express d'abord, puis par date de retrait prévue.</p>
        </div>
        @if($filtre)
            <a href="{{ route('traitement.index') }}" class="text-xs font-medium text-amber-600 hover:underline">Voir tout</a>
        @endif
    </div>

    <div class="overflow-x-auto">
        <table class="table-base">
            <thead>
                <tr><th>Ticket</th><th>Client</th><th>Articles à traiter</th><th>Retrait prévu</th><th>Statut</th><th class="text-right">Action</th></tr>
            </thead>
            <tbody>
            @forelse($depots as $depot)
                @php $enRetardLigne = $depot->date_retrait_prevue?->isPast(); @endphp
                <tr>
                    <td>
                        <a href="{{ route('depots.show', $depot) }}" class="font-semibold text-amber-600 hover:text-amber-700">{{ $depot->numero_ticket }}</a>
                        @if($depot->express)<span class="badge-danger ml-1">⚡ Express</span>@endif
                    </td>
                    <td>{{ $depot->client?->nom }} {{ $depot->client?->prenom }}</td>
                    
                    {{-- Colonne Articles avec détails et observations visibles pour l'opérateur --}}
                    <td class="text-sm py-3">
                        @foreach($depot->articles->where('statut', '!=', 'perdu')->take(3) as $a)
                            <div class="mb-1.5 last:mb-0">
                                <span class="font-bold text-slate-800">{{ $a->quantite }} × {{ $a->typeArticle?->nom }}</span> 
                                <span class="text-slate-500">— {{ $a->service?->nom }}</span>
                                
                                @if($a->observation)
                                    <div class="mt-0.5 inline-flex items-center gap-1 text-[11px] font-semibold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-md border border-amber-200/60">
                                        <svg class="w-3 h-3 text-amber-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                        <span>{{ $a->observation }}</span>
                                    </div>
                                @endif
                            </div>
                        @endforeach

                        @if($depot->articles->count() > 3)
                            <span class="text-xs text-slate-400 font-medium block mt-1">+ {{ $depot->articles->count() - 3 }} autre(s) article(s)</span>
                        @endif

                        @if($depot->observations)
                            <div class="mt-2 text-[11px] text-slate-600 bg-slate-100/80 px-2.5 py-1 rounded-lg border border-slate-200/60">
                                <span class="font-bold text-slate-700">Note globale :</span> {{ $depot->observations }}
                            </div>
                        @endif
                    </td>

                    <td class="{{ $enRetardLigne ? 'text-red-600 font-semibold' : 'text-slate-500' }}">
                        {{ $depot->date_retrait_prevue?->format('d/m/Y') }}
                        @if($enRetardLigne)<span class="block text-[11px]">En retard</span>@endif
                    </td>
                    <td>
                        @if($depot->statut === 'depose')
                            <span class="badge-info">À commencer</span>
                        @else
                            <span class="badge-warning">En traitement</span>
                        @endif
                    </td>
                    <td class="text-right">
                        @php 
                            $roleUser = strtolower(trim(auth()->user()->role ?? ''));
                            $estPersonnelAtelier = in_array($roleUser, ['admin', 'gerant', 'operateur', 'atelier'], true);
                        @endphp

                        @if($estPersonnelAtelier)
                            @if($depot->statut === 'depose')
                                <form method="POST" action="{{ route('depots.commencer', $depot) }}" class="inline">
                                    @csrf
                                    <button class="btn-primary text-xs px-3 py-1.5">▶ Commencer</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('depots.pret', $depot) }}" class="inline">
                                    @csrf
                                    <button class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-500 hover:bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white transition">
                                        ✔ Prêt{{ ($produitsRequis[$depot->id] ?? false) ? ' + produits' : '' }}
                                    </button>
                                </form>
                            @endif
                        @else
                            <span class="text-xs text-slate-400 italic">Consultation</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="py-12">
                        <div class="flex flex-col items-center text-center">
                            <span class="w-12 h-12 rounded-full bg-emerald-50 flex items-center justify-center mb-3">
                                <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            <p class="text-sm font-medium text-slate-600">Rien à traiter pour le moment</p>
                            <p class="text-xs text-slate-400 mt-0.5">Tous les dépôts sont prêts ou remis aux clients.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-6 py-3">{{ $depots->links() }}</div>
</div>
@endsection