@extends('layouts.app')

@section('title', 'Dépôt ' . $depot->numero_ticket)
@section('page-title', 'Dépôt ' . $depot->numero_ticket)
@section('page-subtitle', 'Détail, suivi et historique complet')

@php
    $user         = auth()->user();
    $role         = $user->role;
    $gestionnaire = in_array($role, ['admin', 'gerant'], true);
    $caisse       = in_array($role, ['admin', 'gerant', 'caissier'], true);
    $f            = fn ($n) => number_format($n, 0, ',', ' ');

    $labels = ['depose' => 'Déposé', 'en_traitement' => 'En traitement', 'pret' => 'Prêt à retirer', 'retire' => 'Retiré', 'annule' => 'Annulé'];
    $badges = [
        'depose'        => 'bg-blue-100 text-blue-700 border-blue-200/70',
        'en_traitement' => 'bg-amber-100 text-amber-700 border-amber-200/70',
        'pret'          => 'bg-emerald-100 text-emerald-700 border-emerald-200/70',
        'retire'        => 'bg-slate-100 text-slate-600 border-slate-200/70',
        'annule'        => 'bg-rose-100 text-rose-700 border-rose-200/70',
    ];
    $dots = [
        'depose'        => 'bg-blue-500',
        'en_traitement' => 'bg-amber-500',
        'pret'          => 'bg-emerald-500',
        'retire'        => 'bg-slate-400',
        'annule'        => 'bg-rose-500',
    ];

    $labelsArticle = ['depose' => 'Déposé', 'en_traitement' => 'En traitement', 'pret' => 'Prêt', 'retire' => 'Retiré', 'perdu' => 'Perdu', 'abime' => 'Abîmé'];
    $badgesArticle = ['depose' => 'bg-blue-50 text-blue-700', 'en_traitement' => 'bg-amber-50 text-amber-700', 'pret' => 'bg-emerald-50 text-emerald-700', 'retire' => 'bg-slate-100 text-slate-600', 'perdu' => 'bg-rose-50 text-rose-700', 'abime' => 'bg-rose-50 text-rose-700'];

    $enRetard  = $depot->date_retrait_prevue?->isPast() && in_array($depot->statut, ['depose', 'en_traitement'], true);
    $termine   = in_array($depot->statut, ['retire', 'annule'], true);
    $reste     = (int) $depot->reste_a_payer;
    $coutTotal = $depot->consommations->sum(fn ($c) => $c->quantite * $c->cout_unitaire);
@endphp

@section('content')

<style>
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes pulseRing { 0% { transform: scale(1); opacity: 0.6; } 70% { transform: scale(1.8); opacity: 0; } 100% { transform: scale(1.8); opacity: 0; } }
    .gradient-border { position: relative; background: white; }
    .gradient-border::before {
        content: ''; position: absolute; inset: 0; padding: 1px; border-radius: inherit;
        background: linear-gradient(135deg, rgba(251,191,36,0.3), rgba(245,158,11,0.1), rgba(251,191,36,0.3));
        -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor; mask-composite: exclude; pointer-events: none;
    }
    .ring-pulse::before { content: ''; position: absolute; inset: 0; border-radius: 9999px; background: currentColor; animation: pulseRing 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
    .timeline-dot-active { box-shadow: 0 0 0 4px rgba(245,158,11,0.2); }
</style>

@if ($errors->any())
    <div role="alert" class="mb-6 p-4 rounded-2xl bg-gradient-to-br from-rose-50 to-pink-50 border border-rose-200 text-rose-700 text-sm flex items-start gap-3 shadow-sm">
        <span class="shrink-0 w-8 h-8 rounded-lg bg-rose-100 flex items-center justify-center">
            <svg class="h-4 w-4 text-rose-600" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
        </span>
        <div>
            <p class="font-bold">Attention</p>
            <ul class="mt-1 space-y-0.5 text-rose-600 text-xs">
                @foreach ($errors->all() as $erreur)<li>· {{ $erreur }}</li>@endforeach
            </ul>
        </div>
    </div>
@endif

{{-- ══════ HERO ══════ --}}
<section class="relative overflow-hidden rounded-2xl p-6 sm:p-8 mb-6 text-white shadow-2xl shadow-slate-900/30"
         style="background: linear-gradient(135deg, #0a0f1e 0%, #1e293b 45%, #7c2d12 120%);">
    <div aria-hidden="true" class="absolute -top-20 -left-16 w-72 h-72 rounded-full bg-amber-500/25 blur-3xl"></div>
    <div aria-hidden="true" class="absolute -bottom-24 right-0 w-80 h-80 rounded-full bg-orange-500/20 blur-3xl"></div>
    <div aria-hidden="true" class="absolute inset-0 opacity-[0.07]" style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 22px 22px;"></div>

    <div class="relative flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
        <div class="flex-1">
            {{-- Bouton Retour à la liste ajouté ici --}}
            <div class="mb-4">
                <a href="{{ route('depots.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white/10 hover:bg-white/20 text-xs font-semibold text-white ring-1 ring-white/20 transition backdrop-blur-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Retour aux dépôts
                </a>
            </div>

            <div class="flex flex-wrap items-center gap-2.5 mb-3">
                <h2 class="text-3xl font-extrabold tracking-tight">{{ $depot->numero_ticket }}</h2>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold {{ $badges[$depot->statut] ?? 'bg-slate-100 text-slate-700' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $dots[$depot->statut] ?? 'bg-slate-400' }}"></span>
                    {{ $labels[$depot->statut] ?? $depot->statut }}
                </span>
                @if($depot->express)
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-extrabold bg-gradient-to-r from-rose-500 to-pink-600 text-white shadow-md">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z"/></svg>
                        Express
                    </span>
                @endif
            </div>
            <p class="text-slate-300 text-sm flex flex-wrap items-center gap-2">
                <span class="font-bold text-white">{{ $depot->client?->nom }} {{ $depot->client?->prenom }}</span>
                @if($caisse && $depot->client?->telephone)
                    <span class="text-slate-500">·</span>
                    <span class="font-mono text-xs">{{ $depot->client->telephone }}</span>
                @endif
            </p>
            <p class="mt-2 text-xs flex flex-wrap items-center gap-2 {{ $enRetard ? 'text-rose-300 font-bold' : 'text-slate-400' }}">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Déposé le {{ $depot->date_depot?->format('d/m/Y à H:i') }} · Retrait prévu le {{ $depot->date_retrait_prevue?->format('d/m/Y') }}
                @if($enRetard)
                    <span class="inline-flex items-center gap-1 bg-rose-500/30 border border-rose-400/40 px-2 py-0.5 rounded text-rose-200 font-bold">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-400 ring-pulse relative"></span>
                        En retard
                    </span>
                @endif
            </p>
        </div>

       {{-- Actions workflow dans la vue show.blade.php des dépôts --}}
<div class="flex flex-wrap gap-2 lg:max-w-md lg:justify-end">

    @php 
        $roleUser = strtolower(trim(auth()->user()->role ?? ''));
        $estPersonnelAtelier = in_array($roleUser, ['admin', 'gerant', 'operateur', 'atelier'], true);
    @endphp

    {{-- Bouton Commencer : Visible uniquement pour l'atelier/gérance --}}
    @if($depot->statut === 'depose' && $estPersonnelAtelier)
        <form method="POST" action="{{ route('depots.commencer', $depot) }}">
            @csrf
            <button class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-amber-500/30 transition-all duration-200 hover:-translate-y-0.5 active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3l14 9-14 9V3z"/></svg>
                Commencer
            </button>
        </form>
    @endif

    {{-- Bouton Marquer prêt : Visible uniquement pour l'atelier/gérance --}}
    @if($depot->statut === 'en_traitement' && $estPersonnelAtelier)
        <form method="POST" action="{{ route('depots.pret', $depot) }}">
            @csrf
            <button class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-emerald-500/30 transition-all duration-200 hover:-translate-y-0.5 active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                Marquer prêt{{ $consommationRequise ? ' + déclarer' : '' }}
            </button>
        </form>
    @endif

            @if($depot->statut === 'pret' && $caisse)
                <form method="POST" action="{{ route('depots.retirer', $depot) }}" class="flex flex-wrap items-center gap-2">@csrf
                    @if($reste > 0 && $gestionnaire)
                        <label class="flex items-center gap-2 text-xs text-slate-200 cursor-pointer bg-black/20 px-3 py-2 rounded-xl border border-white/10 hover:bg-black/30 transition">
                            <input type="checkbox" name="derogation" value="1" class="rounded border-slate-300 text-amber-500 focus:ring-amber-400">
                            Remettre malgré solde ({{ $f($reste) }} FCFA)
                        </label>
                    @endif
                    <button @disabled($reste > 0 && ! $gestionnaire)
                            class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-emerald-500/30 transition disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Remettre
                    </button>
                </form>
                @if($reste > 0 && ! $gestionnaire)
                    <p class="w-full text-xs text-amber-300 font-medium">Encaissez d'abord le solde de {{ $f($reste) }} FCFA.</p>
                @endif
            @endif

            @if($caisse)
                <a href="{{ route('depots.ticket', ['depot' => $depot, 'imprimer' => 1]) }}" target="_blank"
                   class="inline-flex items-center gap-1.5 rounded-xl bg-white/10 hover:bg-white/20 ring-1 ring-white/20 px-3.5 py-2.5 text-sm font-semibold transition backdrop-blur-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Ticket
                </a>
                @php $lienWhatsapp = $depot->statut === 'pret' ? $whatsappPret : $whatsappTicket; @endphp
                @if($lienWhatsapp)
                    <a href="{{ $lienWhatsapp }}" target="_blank" rel="noopener"
                       class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-500/30 hover:bg-emerald-500/50 ring-1 ring-emerald-400/40 px-3.5 py-2.5 text-sm font-semibold transition backdrop-blur-sm">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                        WhatsApp
                    </a>
                @endif
                @if($depot->statut !== 'annule')
                    <a href="{{ route('depots.edit', $depot) }}"
                       class="inline-flex items-center gap-1.5 rounded-xl bg-white/10 hover:bg-white/20 ring-1 ring-white/20 px-3.5 py-2.5 text-sm font-semibold transition backdrop-blur-sm">
                        @if($reste > 0)
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Encaisser
                        @else
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            Modifier
                        @endif
                    </a>
                @endif
            @endif

            @if($depot->statut === 'pret' && $gestionnaire)
                <form method="POST" action="{{ route('depots.rouvrir', $depot) }}" onsubmit="return confirm('Remettre ce dépôt « en traitement » ?')">@csrf
                    <button class="inline-flex items-center gap-1.5 rounded-xl bg-white/10 hover:bg-white/20 ring-1 ring-white/20 px-3.5 py-2.5 text-sm font-semibold transition backdrop-blur-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Rouvrir
                    </button>
                </form>
            @endif

            @if($gestionnaire && ! $termine)
                <form method="POST" action="{{ route('depots.destroy', $depot) }}" onsubmit="return confirm('Annuler ce dépôt ?')">@csrf @method('DELETE')
                    <button class="inline-flex items-center gap-1.5 rounded-xl bg-rose-600/80 hover:bg-rose-600 px-3.5 py-2.5 text-sm font-semibold transition shadow-md">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        Annuler
                    </button>
                </form>
            @endif
        </div>
    </div>
</section>

<div class="grid lg:grid-cols-3 gap-6">

    {{-- Colonne gauche --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Articles --}}
        <div class="gradient-border rounded-2xl bg-white shadow-lg shadow-slate-200/50 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-white shadow-md shadow-amber-500/25">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800 text-sm">Articles déposés</h3>
                        <p class="text-[11px] text-slate-500">{{ $depot->articles->sum('quantite') }} article(s) au total</p>
                    </div>
                </div>
                @if($depot->observations)
                    <span class="text-[11px] text-slate-500 truncate max-w-xs bg-slate-100 px-2.5 py-1 rounded-lg" title="{{ $depot->observations }}">
                        📝 {{ \Illuminate\Support\Str::limit($depot->observations, 40) }}
                    </span>
                @endif
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/70 border-b border-slate-200/60">
                            <th class="py-3 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">Article</th>
                            <th class="py-3 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">Service</th>
                            <th class="py-3 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">Qté</th>
                            @if($caisse)
                                <th class="py-3 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em] text-right">Prix unit.</th>
                                <th class="py-3 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em] text-right">Sous-total</th>
                            @endif
                            <th class="py-3 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">État</th>
                            <th class="py-3 px-6"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                    @foreach($depot->articles as $article)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="py-3.5 px-6 font-bold text-slate-800">{{ $article->typeArticle?->nom }}</td>
                            <td class="py-3.5 px-6 text-slate-600">{{ $article->service?->nom }}</td>
                            <td class="py-3.5 px-6 font-extrabold text-slate-800">{{ $article->quantite }}</td>
                            @if($caisse)
                                <td class="py-3.5 px-6 text-right text-slate-600">{{ $f($article->prix_unitaire) }}</td>
                                <td class="py-3.5 px-6 text-right font-extrabold text-slate-900">{{ $f($article->prix_unitaire * $article->quantite) }}</td>
                            @endif
                            <td class="py-3.5 px-6">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold {{ $badgesArticle[$article->statut] ?? 'bg-slate-100 text-slate-600' }}">
                                    {{ $labelsArticle[$article->statut] ?? $article->statut }}
                                </span>
                                @if($article->observation)
                                    <p class="text-[10px] text-slate-400 mt-1 max-w-[12rem]">{{ $article->observation }}</p>
                                @endif
                            </td>
                            <td class="py-3.5 px-6 text-right">
                                @if(! $termine && ! in_array($article->statut, ['perdu', 'abime'], true))
                                    <details class="relative inline-block text-left">
                                        <summary class="cursor-pointer list-none text-[11px] font-bold text-rose-600 hover:text-rose-700 transition inline-flex items-center gap-1 bg-rose-50 hover:bg-rose-100 px-2 py-1 rounded-lg">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                            Signaler
                                        </summary>
                                        <form method="POST" action="{{ route('depots.signaler', [$depot, $article]) }}"
                                              class="absolute right-0 z-10 mt-2 w-64 space-y-2.5 rounded-2xl border border-slate-200 bg-white p-4 shadow-2xl">
                                            @csrf
                                            <select name="statut" class="w-full px-3 py-2 rounded-lg border border-slate-200 text-xs" required>
                                                <option value="abime">Article abîmé</option>
                                                <option value="perdu">Article perdu</option>
                                            </select>
                                            <textarea name="note" rows="2" required minlength="3" maxlength="200"
                                                      class="w-full px-3 py-2 rounded-lg border border-slate-200 text-xs resize-none"
                                                      placeholder="Décrivez le problème (obligatoire)"></textarea>
                                            <button class="w-full bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold py-2 rounded-lg transition shadow-sm">Enregistrer</button>
                                        </form>
                                    </details>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Consommations --}}
        @if($depot->consommations->isNotEmpty())
            <div class="gradient-border rounded-2xl bg-white shadow-lg shadow-slate-200/50 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-white flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-blue-500/25">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-800 text-sm">Produits utilisés</h3>
                            <p class="text-[11px] text-slate-500">Déclarés le {{ $depot->consommation_validee_at?->format('d/m/Y à H:i') }}</p>
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/70 border-b border-slate-200/60">
                                <th class="py-3 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">Produit</th>
                                <th class="py-3 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">Quantité</th>
                                <th class="py-3 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">Par</th>
                                @if($gestionnaire)
                                    <th class="py-3 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em] text-right">Coût</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                        @foreach($depot->consommations as $c)
                            <tr class="hover:bg-slate-50/50">
                                <td class="py-3.5 px-6 font-bold text-slate-800">{{ $c->produit?->nom }}</td>
                                <td class="py-3.5 px-6 text-slate-600">{{ $c->quantite + 0 }} <span class="text-[10px] text-slate-400">{{ $c->produit?->unite }}</span></td>
                                <td class="py-3.5 px-6 text-slate-500">{{ $c->user?->nom }}</td>
                                @if($gestionnaire)
                                    <td class="py-3.5 px-6 text-right font-extrabold text-slate-700">{{ $f($c->quantite * $c->cout_unitaire) }}</td>
                                @endif
                            </tr>
                        @endforeach
                        </tbody>
                        @if($gestionnaire)
                            <tfoot class="bg-gradient-to-r from-amber-50/60 to-white border-t-2 border-amber-200/60">
                                <tr>
                                    <td colspan="3" class="px-6 py-3 text-right font-bold text-slate-700 text-xs uppercase">Coût total des produits</td>
                                    <td class="px-6 py-3 text-right font-extrabold text-slate-900">{{ $f($coutTotal) }} <span class="text-[10px] text-slate-400">FCFA</span></td>
                                </tr>
                                @if($caisse)
                                    <tr>
                                        <td colspan="3" class="px-6 py-2 text-right text-[11px] font-semibold text-emerald-600 uppercase">Marge brute estimée</td>
                                        <td class="px-6 py-2 text-right font-extrabold text-emerald-600">{{ $f($depot->montant_total - $coutTotal) }} <span class="text-[10px]">FCFA</span></td>
                                    </tr>
                                @endif
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        @elseif($depot->statut === 'en_traitement' && $consommationRequise)
            <div class="flex items-start gap-3 p-4 rounded-2xl bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200/70 text-blue-800 text-sm">
                <span class="shrink-0 w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
                <span>Les produits utilisés seront à déclarer lorsque vous cliquerez sur <strong>« Marquer prêt »</strong>.</span>
            </div>
        @endif

        {{-- Paiements --}}
        @if($caisse)
            <div class="gradient-border rounded-2xl bg-white shadow-lg shadow-slate-200/50 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-white flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white shadow-md shadow-emerald-500/25">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800 text-sm">Historique des paiements</h3>
                        <p class="text-[11px] text-slate-500">{{ $depot->paiements->count() }} paiement(s) enregistré(s)</p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/70 border-b border-slate-200/60">
                                <th class="py-3 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">Date</th>
                                <th class="py-3 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">Mode</th>
                                <th class="py-3 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">Référence</th>
                                <th class="py-3 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">Reçu par</th>
                                <th class="py-3 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em] text-right">Montant</th>
                                <th class="py-3 px-6"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse($depot->paiements as $p)
                            <tr class="hover:bg-slate-50/50">
                                <td class="py-3.5 px-6 text-slate-500 text-xs">{{ $p->date_paiement?->format('d/m/Y H:i') }}</td>
                                <td class="py-3.5 px-6 font-semibold text-slate-800">{{ ucwords(str_replace('_', ' ', $p->mode)) }}</td>
                                <td class="py-3.5 px-6 text-slate-500 font-mono text-xs">{{ $p->reference ?? '—' }}</td>
                                <td class="py-3.5 px-6 text-slate-500">{{ $p->user?->nom }}</td>
                                <td class="py-3.5 px-6 text-right font-extrabold text-emerald-600">{{ $f($p->montant) }} <span class="text-[10px]">FCFA</span></td>
                                <td class="py-3.5 px-6 text-right">
                                    <a href="{{ route('paiements.recu', ['paiement' => $p, 'imprimer' => 1]) }}" target="_blank"
                                       class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-600 hover:text-amber-700 bg-amber-50 hover:bg-amber-100 px-2 py-1 rounded-lg transition">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                        Reçu
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-slate-400 py-8 text-xs italic">Aucun paiement enregistré pour le moment.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    {{-- Colonne droite --}}
    <div class="space-y-6">

        @if($caisse)
            <div class="gradient-border rounded-2xl bg-white shadow-lg shadow-slate-200/50 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-amber-50 via-orange-50 to-white flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-white shadow-md shadow-amber-500/25">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m-6 4h6m-6 4h4m-7 5h12a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800 text-sm">Récapitulatif financier</h3>
                        <p class="text-[11px] text-slate-500">Détail des montants</p>
                    </div>
                </div>
                <div class="p-6">
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between text-slate-600"><dt class="text-xs font-semibold">Articles</dt><dd class="font-bold text-slate-800">{{ $f($depot->montant_total + $depot->remise) }}</dd></div>
                        @if($depot->remise > 0)
                            <div class="flex justify-between text-slate-600"><dt class="text-xs font-semibold">Remise</dt><dd class="font-bold text-rose-600">− {{ $f($depot->remise) }}</dd></div>
                        @endif
                        <div class="flex justify-between border-t border-slate-100 pt-3"><dt class="font-bold text-slate-800 text-xs uppercase">Total net</dt><dd class="font-extrabold text-slate-900 text-base">{{ $f($depot->montant_total) }} <span class="text-[10px]">FCFA</span></dd></div>
                        <div class="flex justify-between text-slate-600"><dt class="text-xs font-semibold">Déjà payé</dt><dd class="font-bold text-emerald-600">{{ $f($depot->montant_paye) }}</dd></div>
                    </dl>
                    <div class="mt-4 flex items-center justify-between rounded-xl px-4 py-3.5 shadow-md {{ $reste > 0 ? 'bg-gradient-to-r from-rose-600 to-pink-700' : 'bg-gradient-to-r from-emerald-600 to-teal-700' }}">
                        <dt class="text-xs font-bold text-white/90 uppercase tracking-wider">Reste à payer</dt>
                        <dd class="text-xl font-extrabold text-white">{{ $f($reste) }} <span class="text-[10px] text-white/80">FCFA</span></dd>
                    </div>
                </div>
            </div>
        @endif

        {{-- Historique statuts --}}
        <div class="gradient-border rounded-2xl bg-white shadow-lg shadow-slate-200/50 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-white flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center text-white shadow-md shadow-violet-500/25">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 text-sm">Historique des statuts</h3>
                    <p class="text-[11px] text-slate-500">Chronologie complète</p>
                </div>
            </div>
            <div class="p-6">
                <ol class="relative space-y-5 border-l-2 border-slate-200 pl-5 ml-2">
                    @foreach($depot->historique->reverse() as $h)
                        <li class="relative">
                            <span class="absolute -left-[27px] top-1 h-3.5 w-3.5 rounded-full border-2 border-white {{ $loop->first ? 'bg-amber-500 timeline-dot-active' : 'bg-slate-300' }}"></span>
                            <p class="text-sm font-bold text-slate-800">{{ $labels[$h->statut] ?? $h->statut }}</p>
                            @if($h->note)<p class="text-xs text-slate-500 mt-0.5">{{ $h->note }}</p>@endif
                            <p class="text-[11px] text-slate-400 mt-1">{{ $h->created_at?->format('d/m/Y à H:i') }} · <span class="font-semibold text-slate-600">{{ $h->user?->nom ?? 'Système' }}</span></p>
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>
    </div>
</div>

@endsection