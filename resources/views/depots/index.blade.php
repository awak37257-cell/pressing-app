@extends('layouts.app')

@section('title', 'Dépôts & Retraits')
@section('page-title', 'Dépôts & Retraits')
@section('page-subtitle', 'Suivi intelligent des articles et gestion de la caisse')

@section('content')

<style>
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes pulseRing { 0% { transform: scale(1); opacity: 0.6; } 70% { transform: scale(1.8); opacity: 0; } 100% { transform: scale(1.8); opacity: 0; } }
    .stat-card { animation: fadeInUp 0.5s ease-out backwards; }
    .stat-card:nth-child(1) { animation-delay: 0.03s; }
    .stat-card:nth-child(2) { animation-delay: 0.06s; }
    .stat-card:nth-child(3) { animation-delay: 0.09s; }
    .stat-card:nth-child(4) { animation-delay: 0.12s; }
    .stat-card:nth-child(5) { animation-delay: 0.15s; }
    .table-row { transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
    .table-row:hover { background: linear-gradient(90deg, #fffbeb 0%, #ffffff 40%, #ffffff 100%); }
    .table-row:hover .row-avatar { transform: scale(1.1) rotate(-4deg); }
    .table-row:hover .action-btn { opacity: 1; transform: translateX(0); }
    .action-btn { opacity: 0.5; transform: translateX(4px); transition: all 0.25s ease; }
    .gradient-border { position: relative; background: white; }
    .gradient-border::before {
        content: ''; position: absolute; inset: 0; padding: 1px; border-radius: inherit;
        background: linear-gradient(135deg, rgba(251,191,36,0.3), rgba(245,158,11,0.1), rgba(251,191,36,0.3));
        -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor; mask-composite: exclude; pointer-events: none;
    }
    .ring-pulse::before { content: ''; position: absolute; inset: 0; border-radius: 9999px; background: currentColor; animation: pulseRing 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
</style>

{{-- ══════ HERO ══════ --}}
<div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center shadow-lg shadow-amber-500/30">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
        </div>
        <div>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Registre des dépôts</h1>
            <p class="text-xs text-slate-500 font-medium">Tous les articles déposés et leur suivi</p>
        </div>
    </div>
    <a href="{{ route('depots.create') }}" id="btn-nouveau-depot"
       class="group inline-flex items-center gap-2 bg-gradient-to-r from-amber-500 via-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-bold px-5 py-2.5 rounded-xl shadow-lg shadow-amber-500/30 hover:shadow-xl text-sm transition-all duration-300 hover:-translate-y-0.5 active:scale-95">
        <svg class="w-4 h-4 group-hover:rotate-90 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Nouveau dépôt
    </a>
</div>

{{-- ══════ ALERTE / BANDEAU DÉPÔTS PRÊTS POUR LA CAISSE ══════ --}}
@php
    $roleUtilisateur = strtolower(trim(auth()->user()->role ?? ''));
    $estPersonnelCaisse = in_array($roleUtilisateur, ['secretaire', 'caissiere', 'admin', 'gerant'], true);
    
    // Récupérer les dépôts qui viennent d'être marqués comme 'pret' pour le pressing courant
    $depotsPretsALivrer = \App\Models\Depot::where('pressing_id', auth()->user()->pressing_id)
        ->where('statut', 'pret')
        ->latest()
        ->take(6)
        ->get();
@endphp

@if($estPersonnelCaisse && $depotsPretsALivrer->isNotEmpty())
    <div class="mb-6 p-4 rounded-2xl bg-gradient-to-br from-emerald-50 via-teal-50 to-emerald-100/50 border border-emerald-200/80 text-emerald-900 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-start gap-3">
            <span class="shrink-0 w-9 h-9 rounded-xl bg-emerald-500 text-white flex items-center justify-center font-bold shadow-md shadow-emerald-500/20">
                🔔
            </span>
            <div>
                <h4 class="font-bold text-sm text-emerald-950">Linge prêt à être remis au client (Atelier)</h4>
                <p class="text-xs text-emerald-700 mt-0.5">Ces dépôts ont été traités par l'atelier et attendent l'encaissement ou le retrait.</p>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @foreach($depotsPretsALivrer as $dPret)
                <a href="{{ route('depots.show', $dPret) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white hover:bg-emerald-600 hover:text-white border border-emerald-300 text-xs font-bold text-emerald-900 shadow-2xs transition group">
                    <span>Ticket {{ $dPret->numero_ticket }}</span>
                    <span class="text-[10px] text-slate-400 group-hover:text-emerald-200">({{ $dPret->client?->nom }})</span>
                </a>
            @endforeach
        </div>
    </div>
@endif

{{-- ══════ PILLS STATUTS ══════ --}}
@php
    $statutsConfig = [
        'depose'        => ['label' => 'Déposés',   'bg' => 'from-blue-500 to-indigo-600',    'glow' => 'bg-blue-400/25',    'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
        'en_traitement' => ['label' => 'En cours',  'bg' => 'from-amber-500 to-orange-600',   'glow' => 'bg-yellow-300/25', 'icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15'],
        'pret'          => ['label' => 'Prêts',     'bg' => 'from-emerald-500 to-teal-600',   'glow' => 'bg-teal-300/25',    'icon' => 'M5 13l4 4L19 7'],
        'retire'        => ['label' => 'Retirés',   'bg' => 'from-slate-600 to-slate-700',    'glow' => 'bg-slate-400/20',   'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
        'annule'        => ['label' => 'Annulés',   'bg' => 'from-rose-500 to-pink-600',      'glow' => 'bg-pink-400/25',    'icon' => 'M6 18L18 6M6 6l12 12'],
    ];
    $activeStatut = request('statut');
@endphp

<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 mb-6">
    @foreach($statutsConfig as $key => $cfg)
        @php
            $isActive = $activeStatut === $key;
            $count    = $statutsCount->get($key)?->count ?? 0;
        @endphp
        <a href="{{ route('depots.index', ['statut' => $key]) }}"
           class="stat-card group relative overflow-hidden rounded-2xl bg-gradient-to-br {{ $cfg['bg'] }} p-3.5 text-white shadow-lg shadow-slate-900/10 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl {{ $isActive ? 'ring-2 ring-white ring-offset-2 ring-offset-slate-100' : '' }}">
            <div class="absolute top-0 right-0 w-20 h-20 {{ $cfg['glow'] }} rounded-full blur-2xl -translate-y-1/2 translate-x-1/2"></div>
            <div class="relative">
                <div class="flex items-start justify-between mb-2">
                    <div class="w-7 h-7 rounded-lg bg-white/15 backdrop-blur-sm border border-white/25 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $cfg['icon'] }}"/>
                        </svg>
                    </div>
                    @if($isActive)
                        <span class="text-[9px] font-bold bg-white/25 px-1.5 py-0.5 rounded-md tracking-wider uppercase">Actif</span>
                    @endif
                </div>
                <p class="text-[10px] font-bold text-white/70 uppercase tracking-wider">{{ $cfg['label'] }}</p>
                <p class="text-xl font-extrabold tracking-tight mt-0.5">{{ $count }}</p>
            </div>
        </a>
    @endforeach
</div>

{{-- ══════ CONTENEUR PRINCIPAL ══════ --}}
<div class="gradient-border rounded-2xl bg-white shadow-lg shadow-slate-200/50 overflow-hidden">

    {{-- Filtres --}}
    <div class="px-6 py-4 border-b border-slate-100 bg-white">
        <form method="GET" action="{{ route('depots.index') }}" class="flex flex-col lg:flex-row gap-3 lg:items-center">
            <div class="flex flex-wrap gap-2.5 items-center flex-1">
                <div class="relative flex-1 min-w-[220px] max-w-md">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher par N° ticket ou client…"
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-sm text-slate-700 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all duration-200">
                </div>

                <div class="relative">
                    <select name="statut" class="appearance-none pl-3.5 pr-9 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-sm text-slate-700 focus:bg-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all cursor-pointer">
                        <option value="">Tous les statuts</option>
                        @foreach($statutsConfig as $key => $cfg)
                            <option value="{{ $key }}" @selected(request('statut') === $key)>{{ $cfg['label'] }}</option>
                        @endforeach
                    </select>
                    <svg class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>

                <div class="flex items-center gap-2 bg-slate-50/50 rounded-xl border border-slate-200 px-3 py-1">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <input type="date" name="date_debut" value="{{ request('date_debut') }}" class="bg-transparent border-0 text-sm text-slate-700 focus:ring-0 focus:outline-none p-1">
                    <span class="text-slate-400 text-xs">→</span>
                    <input type="date" name="date_fin" value="{{ request('date_fin') }}" class="bg-transparent border-0 text-sm text-slate-700 focus:ring-0 focus:outline-none p-1">
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="inline-flex items-center gap-1.5 bg-slate-900 hover:bg-slate-800 text-white font-semibold px-4 py-2.5 rounded-xl text-sm transition shadow-sm hover:shadow-md active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    Filtrer
                </button>
                @if(request()->hasAny(['search', 'statut', 'date_debut', 'date_fin']))
                    <a href="{{ route('depots.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 text-sm font-medium transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Réinitialiser
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Tableau --}}
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50/70 border-b border-slate-200/60">
                    <th class="py-3 px-4 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">Ticket</th>
                    <th class="py-3 px-4 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">Client</th>
                    <th class="py-3 px-4 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">Dépôt / Retrait</th>
                    <th class="py-3 px-4 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">Statut</th>
                    <th class="py-3 px-4 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em] text-right">Montant</th>
                    <th class="py-3 px-4 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em] text-right">Reste</th>
                    <th class="py-3 px-4 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em] text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                @forelse($depots as $depot)
                @php
                    $badgeClass = match($depot->statut) {
                        'depose'        => 'bg-blue-100 text-blue-700 border-blue-200/70',
                        'en_traitement' => 'bg-amber-100 text-amber-700 border-amber-200/70',
                        'pret'          => 'bg-emerald-100 text-emerald-700 border-emerald-200/70',
                        'retire'        => 'bg-slate-100 text-slate-600 border-slate-200/70',
                        'annule'        => 'bg-rose-100 text-rose-700 border-rose-200/70',
                        default         => 'bg-slate-100 text-slate-600 border-slate-200/70',
                    };
                    $dotClass = match($depot->statut) {
                        'depose'        => 'bg-blue-500',
                        'en_traitement' => 'bg-amber-500',
                        'pret'          => 'bg-emerald-500',
                        'retire'        => 'bg-slate-400',
                        'annule'        => 'bg-rose-500',
                        default         => 'bg-slate-400',
                    };
                    $labelStatut = match($depot->statut) {
                        'depose'        => 'Déposé',
                        'en_traitement' => 'En cours',
                        'pret'          => 'Prêt',
                        'retire'        => 'Retiré',
                        'annule'        => 'Annulé',
                        default         => $depot->statut,
                    };
                @endphp
                <tr class="table-row group">
                    <td class="py-3.5 px-4">
                        <div class="flex items-center gap-2 flex-wrap">
                            <a href="{{ route('depots.show', $depot) }}" class="inline-flex items-center gap-1.5 font-bold text-amber-600 hover:text-amber-700 group-hover:underline transition">
                                <span class="w-6 h-6 rounded-md bg-amber-100 flex items-center justify-center">
                                    <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                                    </svg>
                                </span>
                                {{ $depot->numero_ticket }}
                            </a>
                            @if($depot->express)
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[9px] font-extrabold bg-gradient-to-r from-rose-500 to-pink-600 text-white uppercase shadow-sm">
                                    <svg class="w-2 h-2 mr-0.5" fill="currentColor" viewBox="0 0 20 20"><path d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z"/></svg>
                                    Express
                                </span>
                            @endif
                        </div>
                    </td>
                    <td class="py-3.5 px-4">
                        <div class="flex items-center gap-2.5">
                            <span class="row-avatar w-8 h-8 shrink-0 rounded-lg bg-gradient-to-br from-amber-100 to-orange-200 text-amber-700 text-xs font-extrabold flex items-center justify-center uppercase ring-2 ring-amber-200/60 transition-all duration-300">
                                {{ mb_substr($depot->client?->nom ?? '?', 0, 1) }}
                            </span>
                            <span class="font-semibold text-slate-800 truncate">{{ $depot->client?->nom ?? '—' }}</span>
                        </div>
                    </td>
                    <td class="py-3.5 px-4">
                        <div class="text-xs text-slate-600 font-semibold">{{ $depot->date_depot->format('d/m/Y') }}</div>
                        <div class="text-[10px] {{ $depot->date_retrait_prevue?->isPast() && in_array($depot->statut, ['depose','en_traitement']) ? 'text-rose-500 font-bold' : 'text-slate-400' }} mt-0.5">
                            → {{ $depot->date_retrait_prevue?->format('d/m/Y') ?? '—' }}
                        </div>
                    </td>
                    <td class="py-3.5 px-4">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $badgeClass }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $dotClass }}"></span>
                            {{ $labelStatut }}
                        </span>
                    </td>
                    <td class="py-3.5 px-4 text-right">
                        <div class="font-extrabold text-slate-900">{{ number_format($depot->montant_total, 0, ',', ' ') }}</div>
                        <div class="text-[10px] text-emerald-600 font-semibold">Payé : {{ number_format($depot->montant_paye, 0, ',', ' ') }}</div>
                    </td>
                    <td class="py-3.5 px-4 text-right">
                        @if($depot->reste_a_payer > 0)
                            <span class="font-extrabold text-rose-600">{{ number_format($depot->reste_a_payer, 0, ',', ' ') }}</span>
                        @else
                            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                Soldé
                            </span>
                        @endif
                    </td>
                    <td class="py-3.5 px-4">
                        <div class="flex items-center justify-center gap-1">
                            <a href="{{ route('depots.show', $depot) }}" title="Voir"
                               class="action-btn p-1.5 rounded-lg bg-slate-100 hover:bg-slate-900 text-slate-600 hover:text-white transition-all duration-200 hover:scale-110">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                           @php
    $user         = auth()->user();
    $role         = strtolower(trim($user->role ?? ''));
    $gestionnaire = in_array($role, ['admin', 'gerant'], true);
@endphp

@if($depot->statut !== 'annule')
    @php
        // Si c'est un admin ou gérant, on pointe vers la modification du dernier paiement s'il existe, sinon vers le dépôt
        $dernierPaiement = $depot->paiements->last();
        $urlModification = ($gestionnaire && $dernierPaiement) 
            ? route('paiements.edit', $dernierPaiement) 
            : route('depots.edit', $depot);
        
        $titreAction = ($gestionnaire && $dernierPaiement) ? 'Modifier le dernier paiement' : 'Modifier le dépôt';
    @endphp

    <a href="{{ $urlModification }}" title="{{ $titreAction }}"
       class="action-btn p-1.5 rounded-lg bg-amber-50 hover:bg-amber-500 text-amber-600 hover:text-white transition-all duration-200 hover:scale-110">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
        </svg>
    </a>
@endif
                            @if($depot->statut === 'pret' && $depot->client?->telephone)
                                @php
                                    $msgWhatsapp = "Bonjour {$depot->client->nom}, votre linge (ticket {$depot->numero_ticket}) est prêt."
                                        . ((int) $depot->reste_a_payer > 0 ? " Reste à payer : " . number_format($depot->reste_a_payer, 0, ',', ' ') . " FCFA." : "")
                                        . " Merci de passer le récupérer.";
                                    $urlWhatsapp = \App\Support\Whatsapp::url($depot->client->telephone, $msgWhatsapp);
                                @endphp
                                <a href="{{ $urlWhatsapp }}" target="_blank" rel="noopener" title="WhatsApp"
                                   class="action-btn p-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-500 text-emerald-600 hover:text-white transition-all duration-200 hover:scale-110">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                                </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-20 text-center">
                        <div class="flex flex-col items-center">
                            <div class="relative mb-5">
                                <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center text-slate-400 rotate-6">
                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                </div>
                                <div class="absolute -bottom-2 -right-2 w-8 h-8 rounded-full bg-amber-100 border-2 border-white flex items-center justify-center">
                                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                    </svg>
                                </div>
                            </div>
                            <p class="font-bold text-slate-700 text-base">Aucun dépôt trouvé</p>
                            <p class="text-xs text-slate-400 mt-1 max-w-xs">Essayez de modifier vos critères de recherche ou créez un nouveau dépôt.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($depots->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 bg-gradient-to-r from-slate-50/40 to-white flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-xs text-slate-500">
                Affichage de <span class="font-bold text-slate-700">{{ $depots->firstItem() }}</span>
                à <span class="font-bold text-slate-700">{{ $depots->lastItem() }}</span>
                sur <span class="font-bold text-slate-700">{{ $depots->total() }}</span> dépôts
            </p>
            <div>{{ $depots->links() }}</div>
        </div>
    @endif
</div>

@endsection