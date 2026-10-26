@extends('layouts.app')

@section('title', 'Dépôts & Retraits')
@section('page-title', 'Dépôts & Retraits')
@section('page-subtitle', 'Suivi intelligent des articles et gestion de la caisse')

@section('content')

{{-- Compteurs par statut (Pills interactifs modernes) --}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 mb-6">
    @php
        $statutsConfig = [
            'depose'        => ['label' => 'Déposés',       'class' => 'bg-blue-50/80 text-blue-700 border-blue-200 hover:bg-blue-100', 'dot' => 'bg-blue-500'],
            'en_traitement' => ['label' => 'En cours',      'class' => 'bg-amber-50/80 text-amber-700 border-amber-200 hover:bg-amber-100', 'dot' => 'bg-amber-500'],
            'pret'          => ['label' => 'Prêts',         'class' => 'bg-emerald-50/80 text-emerald-700 border-emerald-200 hover:bg-emerald-100', 'dot' => 'bg-emerald-500'],
            'retire'        => ['label' => 'Retirés',       'class' => 'bg-slate-100/80 text-slate-700 border-slate-200 hover:bg-slate-200', 'dot' => 'bg-slate-400'],
            'annule'        => ['label' => 'Annulés',       'class' => 'bg-rose-50/80 text-rose-700 border-rose-200 hover:bg-rose-100', 'dot' => 'bg-rose-500'],
        ];
        $activeStatut = request('statut');
    @endphp

    @foreach($statutsConfig as $key => $cfg)
    @php $isActive = $activeStatut === $key; @endphp
    <a href="{{ route('depots.index', ['statut' => $key]) }}"
       class="group relative flex items-center justify-between p-3.5 rounded-2xl border {{ $cfg['class'] }} {{ $isActive ? 'ring-2 ring-amber-500 shadow-sm font-bold' : 'shadow-sm' }} transition-all duration-200">
        <div class="flex items-center gap-2.5">
            <span class="w-2.5 h-2.5 rounded-full {{ $cfg['dot'] }} shadow-sm"></span>
            <span class="text-xs font-semibold tracking-wide">{{ $cfg['label'] }}</span>
        </div>
        <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full bg-white text-xs font-bold shadow-xs text-slate-800">
            {{ $statutsCount->get($key)?->count ?? 0 }}
        </span>
    </a>
    @endforeach
</div>

<div class="card overflow-hidden shadow-sm border border-slate-200/80 rounded-2xl bg-white">
    {{-- En-tête du tableau --}}
    <div class="px-6 py-4.5 border-b border-slate-100 flex flex-wrap items-center justify-between gap-4 bg-gradient-to-r from-slate-50/50 to-white">
        <div>
            <h2 class="font-bold text-slate-800 text-lg">Liste des dépôts</h2>
            <p class="text-xs text-slate-500 mt-0.5">Gérez, filtrez et suivez l'état de chaque vêtement en temps réel.</p>
        </div>
        <a href="{{ route('depots.create') }}" id="btn-nouveau-depot" class="inline-flex items-center gap-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-semibold px-4 py-2.5 rounded-xl shadow-md shadow-amber-500/20 text-sm transition-all duration-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nouveau dépôt
        </a>
    </div>

    {{-- Filtres modernes --}}
    <div class="px-6 py-4 bg-slate-50/60 border-b border-slate-200/60">
        <form method="GET" action="{{ route('depots.index') }}" class="flex flex-wrap gap-3 items-center">
            <div class="relative flex-1 min-w-[220px]">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Rechercher par N° ticket ou client…"
                       class="form-input w-full pl-9 rounded-xl border-slate-200 text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
            </div>

            <select name="statut" class="form-select w-full sm:w-44 rounded-xl border-slate-200 text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                <option value="">Tous les statuts</option>
                @foreach($statutsConfig as $key => $cfg)
                <option value="{{ $key }}" {{ request('statut') === $key ? 'selected' : '' }}>{{ $cfg['label'] }}</option>
                @endforeach
            </select>

            <input type="date" name="date_debut" value="{{ request('date_debut') }}" class="form-input w-full sm:w-40 rounded-xl border-slate-200 text-sm">
            <input type="date" name="date_fin" value="{{ request('date_fin') }}" class="form-input w-full sm:w-40 rounded-xl border-slate-200 text-sm">

            <div class="flex items-center gap-2">
                <button type="submit" class="inline-flex items-center gap-1.5 bg-slate-800 hover:bg-slate-900 text-white font-medium px-4 py-2.5 rounded-xl text-sm transition shadow-xs">
                    Filtrer
                </button>
                @if(request()->hasAny(['search', 'statut', 'date_debut', 'date_fin']))
                <a href="{{ route('depots.index') }}" class="inline-flex items-center px-3 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-slate-600 text-sm font-medium transition">
                    Réinitialiser
                </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Tableau élégant --}}
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50/80 text-slate-400 uppercase text-[11px] font-bold tracking-wider border-b border-slate-200/60">
                    <th class="py-3.5 px-6">N° Ticket</th>
                    <th class="py-3.5 px-6">Client</th>
                    <th class="py-3.5 px-6">Date dépôt</th>
                    <th class="py-3.5 px-6">Retrait prévu</th>
                    <th class="py-3.5 px-6">Statut</th>
                    <th class="py-3.5 px-6 text-right">Montant</th>
                    <th class="py-3.5 px-6 text-right">Payé</th>
                    <th class="py-3.5 px-6 text-right">Reste</th>
                    <th class="py-3.5 px-6 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                @forelse($depots as $depot)
                <tr class="hover:bg-slate-50/80 transition-colors group">
                    <td class="py-4 px-6 font-semibold">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('depots.show', $depot) }}"
                               class="text-amber-600 hover:text-amber-700 hover:underline font-bold">
                                {{ $depot->numero_ticket }}
                            </a>
                            @if($depot->express)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-700 tracking-wide uppercase shadow-2xs">
                                ⚡ Express
                            </span>
                            @endif
                        </div>
                    </td>
                    <td class="py-4 px-6 font-medium text-slate-800">
                        {{ $depot->client?->nom ?? '—' }}
                    </td>
                    <td class="py-4 px-6 text-slate-500 text-xs">
                        {{ $depot->date_depot->format('d/m/Y') }}
                    </td>
                    <td class="py-4 px-6 text-slate-500 text-xs">
                        {{ $depot->date_retrait_prevue?->format('d/m/Y') ?? '—' }}
                    </td>
                    <td class="py-4 px-6">
                        @php
                            $badgeClass = match($depot->statut) {
                                'depose'        => 'bg-blue-50 text-blue-700 border border-blue-200/60',
                                'en_traitement' => 'bg-amber-50 text-amber-700 border border-amber-200/60',
                                'pret'          => 'bg-emerald-50 text-emerald-700 border border-emerald-200/60',
                                'retire'        => 'bg-slate-100 text-slate-600 border border-slate-200/60',
                                'annule'        => 'bg-rose-50 text-rose-600 border border-rose-200/60',
                                default         => 'bg-slate-100 text-slate-600',
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
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold {{ $badgeClass }}">
                            {{ $labelStatut }}
                        </span>
                    </td>
                    <td class="py-4 px-6 text-right font-bold text-slate-800">
                        {{ number_format($depot->montant_total, 0, ',', ' ') }}
                    </td>
                    <td class="py-4 px-6 text-right font-semibold text-emerald-600">
                        {{ number_format($depot->montant_paye, 0, ',', ' ') }}
                    </td>
                    <td class="py-4 px-6 text-right font-bold {{ $depot->reste_a_payer > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                        {{ number_format($depot->reste_a_payer, 0, ',', ' ') }}
                    </td>
                    <td class="py-4 px-6">
                        <div class="flex items-center justify-center gap-1.5">
                            {{-- Bouton Voir (Œil) --}}
                            <a href="{{ route('depots.show', $depot) }}" title="Voir les détails"
                               class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 hover:text-slate-900 transition-colors shadow-2xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>

                            {{-- Bouton Modifier / Encaisser --}}
                            @if($depot->statut !== 'annule')
                                <a href="{{ route('depots.edit', $depot) }}" title="Modifier / Encaisser"
                                   class="p-2 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-600 hover:text-amber-800 transition-colors shadow-2xs">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                            @endif

                            {{-- Bouton WhatsApp direct si prêt et téléphone dispo --}}
                            @if($depot->statut === 'pret' && $depot->client?->telephone)
                                @php
                                    $msgWhatsapp = "Bonjour {$depot->client->nom}, votre linge (ticket {$depot->numero_ticket}) est prêt."
                                        . ((int) $depot->reste_a_payer > 0 ? " Reste à payer : " . number_format($depot->reste_a_payer, 0, ',', ' ') . " FCFA." : "")
                                        . " Merci de passer le récupérer.";
                                    $urlWhatsapp = \App\Support\Whatsapp::url($depot->client->telephone, $msgWhatsapp);
                                @endphp
                                <a href="{{ $urlWhatsapp }}" target="_blank" rel="noopener" title="Notifier sur WhatsApp"
                                   class="p-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-600 hover:text-emerald-800 transition-colors shadow-2xs text-xs font-bold">
                                    💬
                                </a>
                            @endif

                            {{-- Bouton Action Rapide : Retirer directement si prêt et soldé --}}
                            @if($depot->statut === 'pret' && (float)$depot->reste_a_payer <= 0)
                                <form method="POST" action="{{ route('depots.retirer', $depot) }}" onsubmit="return confirm('Confirmer le retrait du dépôt {{ $depot->numero_ticket }} ?');" class="inline">
                                    @csrf
                                    <button type="submit" title="Valider le retrait" class="p-2 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-600 hover:text-blue-800 transition-colors shadow-2xs font-bold">
                                        ✅
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-16 text-slate-400 bg-slate-50/30">
                        <div class="flex flex-col items-center justify-center">
                            <div class="w-14 h-14 bg-slate-100 rounded-full flex items-center justify-center mb-3 text-slate-400">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                            <p class="font-medium text-slate-600 text-base">Aucun dépôt trouvé</p>
                            <p class="text-xs text-slate-400 mt-0.5">Essayez de modifier vos critères de recherche ou créez un nouveau dépôt.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($depots->hasPages())
    <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/40">
        {{ $depots->links() }}
    </div>
    @endif
</div>
@endsection