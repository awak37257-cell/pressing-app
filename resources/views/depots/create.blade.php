@extends('layouts.app')

@section('title', 'Nouveau dépôt')
@section('page-title', 'Nouveau dépôt')
@section('page-subtitle', 'Enregistrer un dépôt client')

@php
    $initial = old('articles') ?: [['type_article_id' => '', 'service_id' => '', 'quantite' => 1]];
    $erreursLignes = collect($errors->getMessages())
        ->filter(fn ($messages, $cle) => str_starts_with($cle, 'articles.'))
        ->map(fn ($messages) => $messages[0]);
    $catalogueVide = $typesArticles->isEmpty() || $services->isEmpty();
@endphp

@section('content')

<style>
    @keyframes slideIn { from { opacity: 0; transform: translateY(-8px) scale(0.98); } to { opacity: 1; transform: translateY(0) scale(1); } }
    @keyframes pulseRing { 0% { transform: scale(1); opacity: 0.6; } 70% { transform: scale(1.8); opacity: 0; } 100% { transform: scale(1.8); opacity: 0; } }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
    .line-item { animation: slideIn 0.3s ease-out; }
    .line-item:hover .line-number { background: linear-gradient(135deg, #f59e0b, #ea580c); color: white; }
    .gradient-border { position: relative; background: white; }
    .gradient-border::before {
        content: ''; position: absolute; inset: 0; padding: 1px; border-radius: inherit;
        background: linear-gradient(135deg, rgba(251,191,36,0.3), rgba(245,158,11,0.1), rgba(251,191,36,0.3));
        -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor; mask-composite: exclude; pointer-events: none;
    }
    .ring-pulse::before { content: ''; position: absolute; inset: 0; border-radius: 9999px; background: currentColor; animation: pulseRing 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
    .summary-sticky { position: sticky; top: 1.5rem; }
</style>

{{-- ══════ HERO ══════ --}}
<section class="relative overflow-hidden rounded-2xl p-6 sm:p-8 mb-6 text-white shadow-2xl shadow-slate-900/30"
         style="background: linear-gradient(135deg, #0a0f1e 0%, #1e293b 45%, #7c2d12 120%);">
    <div aria-hidden="true" class="absolute -top-20 -left-16 w-72 h-72 rounded-full bg-amber-500/25 blur-3xl"></div>
    <div aria-hidden="true" class="absolute -bottom-24 right-0 w-80 h-80 rounded-full bg-orange-500/20 blur-3xl"></div>
    <div aria-hidden="true" class="absolute inset-0 opacity-[0.07]" style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 22px 22px;"></div>

    <div class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <span class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center shadow-lg shadow-amber-500/40 ring-1 ring-white/20">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </span>
            <div>
                <div class="inline-flex items-center gap-2 text-[10px] font-bold tracking-wider text-amber-300 bg-amber-500/15 border border-amber-400/30 px-3 py-1 rounded-full uppercase mb-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 ring-pulse relative"></span>
                    Nouveau dépôt
                </div>
                <h2 class="text-2xl font-extrabold tracking-tight">Ticket attribué : <span class="bg-gradient-to-r from-amber-300 to-orange-400 bg-clip-text text-transparent">{{ $numeroTicket }}</span></h2>
            </div>
        </div>
        <a href="{{ route('depots.index') }}"
           class="inline-flex items-center gap-1.5 text-xs font-semibold text-white bg-white/10 hover:bg-white/20 ring-1 ring-white/20 px-3.5 py-2 rounded-xl transition backdrop-blur-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Retour aux dépôts
        </a>
    </div>
</section>

{{-- ══════ ERREURS ══════ --}}
@if ($errors->any())
    <div role="alert" class="mb-6 p-4 rounded-2xl bg-gradient-to-br from-rose-50 to-pink-50 border border-rose-200 text-rose-700 text-sm flex items-start gap-3 shadow-sm">
        <span class="shrink-0 w-8 h-8 rounded-lg bg-rose-100 flex items-center justify-center">
            <svg class="h-4 w-4 text-rose-600" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
        </span>
        <div>
            <p class="font-bold">Le dépôt n'a pas pu être enregistré</p>
            <ul class="mt-1 space-y-0.5 text-rose-600 text-xs">
                @foreach ($errors->all() as $erreur)<li>· {{ $erreur }}</li>@endforeach
            </ul>
        </div>
    </div>
@endif

@if ($catalogueVide)
    <div class="mb-6 p-4 rounded-2xl bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-200 text-amber-800 text-sm shadow-sm">
        <p class="font-bold">Aucun article ou service n'est configuré.</p>
        <p class="mt-0.5 text-amber-700 text-xs">Ajoutez des types d'articles, des services et des tarifs avant de créer un dépôt.</p>
    </div>
@endif

<form method="POST" action="{{ route('depots.store') }}" id="form-depot" class="grid lg:grid-cols-3 gap-6 items-start">
    @csrf

    {{-- ══════ COLONNE GAUCHE ══════ --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Section 1 : Client & retrait --}}
        <div class="gradient-border rounded-2xl bg-white shadow-lg shadow-slate-200/50 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-white flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-blue-500/25">
                    <span class="text-xs font-extrabold">01</span>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 text-sm">Client et retrait</h3>
                    <p class="text-[11px] text-slate-500">Informations du client et date de récupération</p>
                </div>
            </div>
            <div class="p-6 space-y-5">
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="client_id" class="text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5 block">Client <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <select id="client_id" name="client_id" required
                                    class="appearance-none w-full pl-3.5 pr-9 py-2.5 rounded-xl border {{ $errors->has('client_id') ? 'border-rose-400' : 'border-slate-200' }} bg-slate-50/50 text-sm text-slate-700 focus:bg-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all cursor-pointer">
                                <option value="">— Sélectionner un client —</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}" @selected(old('client_id') == $client->id)>
                                        {{ $client->nom }} {{ $client->prenom }}{{ $client->telephone ? ' — ' . $client->telephone : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <svg class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                        @error('client_id')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                        <a href="{{ route('clients.create') }}" class="inline-flex items-center gap-1 mt-2 text-xs font-semibold text-amber-600 hover:text-amber-700 hover:underline">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            Créer un nouveau client
                        </a>
                    </div>
                    <div>
                        <label for="date_retrait_prevue" class="text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5 block">Date de retrait prévue <span class="text-rose-500">*</span></label>
                        <input type="date" id="date_retrait_prevue" name="date_retrait_prevue" required
                               value="{{ old('date_retrait_prevue', now()->addDays(2)->format('Y-m-d')) }}"
                               min="{{ now()->format('Y-m-d') }}"
                               class="w-full px-3.5 py-2.5 rounded-xl border {{ $errors->has('date_retrait_prevue') ? 'border-rose-400' : 'border-slate-200' }} bg-slate-50/50 text-sm text-slate-700 focus:bg-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all">
                        @error('date_retrait_prevue')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <label for="express"
                       class="group flex items-center gap-4 rounded-xl border-2 border-dashed border-slate-200 p-4 cursor-pointer transition-all hover:border-amber-400 hover:bg-amber-50/30
                              has-[:checked]:border-solid has-[:checked]:border-amber-500 has-[:checked]:bg-gradient-to-br has-[:checked]:from-amber-50 has-[:checked]:to-orange-50 has-[:checked]:shadow-md has-[:checked]:shadow-amber-500/10">
                    <input type="checkbox" id="express" name="express" value="1" @checked(old('express'))
                           class="w-5 h-5 rounded border-slate-300 text-amber-500 focus:ring-2 focus:ring-amber-400/40">
                    <div class="flex-1">
                        <span class="flex items-center gap-2 text-sm font-bold text-slate-800 group-has-[:checked]:text-amber-700">
                            <svg class="w-4 h-4 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z"/></svg>
                            Service express
                        </span>
                        <span class="block text-xs text-slate-500 mt-0.5">Un supplément tarifaire sera appliqué automatiquement à chaque article.</span>
                    </div>
                </label>
            </div>
        </div>

        {{-- Section 2 : Articles --}}
        <div class="gradient-border rounded-2xl bg-white shadow-lg shadow-slate-200/50 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-white flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-white shadow-md shadow-amber-500/25">
                        <span class="text-xs font-extrabold">02</span>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800 text-sm">Articles déposés</h3>
                        <p class="text-[11px] text-slate-500">Prix calculés en temps réel selon la grille tarifaire</p>
                    </div>
                </div>
                <button type="button" id="btn-ajouter-article" class="inline-flex items-center gap-1.5 bg-slate-900 hover:bg-slate-800 text-white font-semibold px-3.5 py-2 rounded-xl text-xs transition shadow-sm hover:shadow-md active:scale-95">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Ajouter un article
                </button>
            </div>
            <div class="p-6">
                <div id="articles-container" class="space-y-3"></div>
                <p class="mt-4 text-xs text-slate-400 italic flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Les prix proviennent de la grille tarifaire et sont calculés en temps réel.
                </p>
            </div>
        </div>

        {{-- Section 3 : Observations --}}
        <div class="gradient-border rounded-2xl bg-white shadow-lg shadow-slate-200/50 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-white flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center text-white shadow-md shadow-violet-500/25">
                    <span class="text-xs font-extrabold">03</span>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 text-sm">Observations</h3>
                    <p class="text-[11px] text-slate-500">Taches, défauts, instructions particulières (optionnel)</p>
                </div>
            </div>
            <div class="p-6">
                <textarea id="observations" name="observations" rows="3"
                          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-sm text-slate-700 resize-none placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all"
                          placeholder="Taches spécifiques, défauts constatés, instructions particulières…">{{ old('observations') }}</textarea>
            </div>
        </div>
    </div>

    {{-- ══════ RÉCAPITULATIF STICKY ══════ --}}
    <aside class="summary-sticky space-y-6">
        <div class="gradient-border rounded-2xl bg-white shadow-lg shadow-slate-200/50 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-amber-50 via-orange-50 to-white flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-white shadow-md shadow-amber-500/25">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m-6 4h6m-6 4h4m-7 5h12a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 text-sm">Récapitulatif</h3>
                    <p class="text-[11px] text-slate-500">Total et encaissement</p>
                </div>
            </div>
            <div class="p-6 space-y-5">

                {{-- Récap montants --}}
                <dl class="space-y-3 text-sm">
                    <div class="flex items-center justify-between text-slate-600">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total articles</dt>
                        <dd class="font-extrabold text-slate-900"><span id="sum-total">0</span> <span class="text-[10px] text-slate-400">FCFA</span></dd>
                    </div>
                    <div class="flex items-center justify-between text-slate-600">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Remise</dt>
                        <dd class="font-bold text-rose-600"><span id="sum-remise">0</span></dd>
                    </div>
                    <div class="flex items-center justify-between border-t border-slate-100 pt-3">
                        <dt class="font-bold text-slate-800 text-sm">Total à payer</dt>
                        <dd class="text-xl font-extrabold text-slate-900"><span id="sum-net">0</span> <span class="text-[10px] font-semibold text-slate-400">FCFA</span></dd>
                    </div>
                </dl>

                {{-- Formulaire paiement --}}
                <div class="space-y-4 rounded-2xl bg-gradient-to-br from-amber-50/80 to-orange-50/50 border border-amber-200/70 p-4">
                    <div>
                        <label for="remise" class="text-[11px] font-bold text-amber-800 uppercase tracking-wider mb-1 block">Remise (FCFA)</label>
                        <input type="number" id="remise" name="remise" min="0" step="1" inputmode="numeric"
                               value="{{ old('remise', 0) }}"
                               class="w-full px-3 py-2 rounded-lg border {{ $errors->has('remise') ? 'border-rose-400' : 'border-amber-200' }} bg-white text-sm font-semibold text-slate-800 focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all">
                        <p id="warn-remise" class="hidden text-xs text-rose-600 font-bold mt-1">La remise dépasse le total des articles.</p>
                    </div>
                    <div>
                        <label for="montant_paye" class="text-[11px] font-bold text-amber-800 uppercase tracking-wider mb-1 block">Acompte reçu (FCFA)</label>
                        <input type="number" id="montant_paye" name="montant_paye" min="0" step="1" inputmode="numeric"
                               value="{{ old('montant_paye', 0) }}"
                               class="w-full px-3 py-2 rounded-lg border {{ $errors->has('montant_paye') ? 'border-rose-400' : 'border-amber-200' }} bg-white text-sm font-semibold text-slate-800 focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all">
                        <p id="warn-acompte" class="hidden text-xs text-rose-600 font-bold mt-1">L'acompte dépasse le total à payer.</p>
                    </div>
                    <div>
                        <label for="mode_paiement" class="text-[11px] font-bold text-amber-800 uppercase tracking-wider mb-1 block">Mode de paiement</label>
                        <select id="mode_paiement" name="mode_paiement"
                                class="w-full px-3 py-2 rounded-lg border border-amber-200 bg-white text-sm font-semibold text-slate-800 focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all">
                            <option value="especes" @selected(old('mode_paiement', 'especes') === 'especes')>💵 Espèces</option>
                            <option value="orange_money" @selected(old('mode_paiement') === 'orange_money')>📱 Orange Money</option>
                            <option value="mtn_money" @selected(old('mode_paiement') === 'mtn_money')>📱 MTN Money</option>
                            <option value="wave" @selected(old('mode_paiement') === 'wave')>🌊 Wave</option>
                        </select>
                    </div>
                    <div id="ref-wrap" class="hidden">
                        <label for="reference" class="text-[11px] font-bold text-amber-800 uppercase tracking-wider mb-1 block">Référence transaction <span class="text-rose-500">*</span></label>
                        <input type="text" id="reference" name="reference" maxlength="100" value="{{ old('reference') }}"
                               placeholder="Ex. : MP240101.1234.A56789"
                               class="w-full px-3 py-2 rounded-lg border {{ $errors->has('reference') ? 'border-rose-400' : 'border-amber-200' }} bg-white text-sm font-mono text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all">
                        @error('reference')<p class="text-rose-500 text-xs font-bold mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Reste à payer --}}
                <div class="flex items-center justify-between rounded-xl bg-gradient-to-r from-slate-900 to-slate-800 px-4 py-3.5 shadow-md">
                    <span class="text-xs font-bold text-slate-300 uppercase tracking-wider">Reste à payer</span>
                    <span class="text-xl font-extrabold text-amber-400"><span id="sum-reste">0</span> <span class="text-[10px] font-semibold text-amber-300">FCFA</span></span>
                </div>

                {{-- Actions --}}
                <div class="space-y-2.5 pt-2">
                    <button type="submit" id="btn-submit"
                            class="group inline-flex items-center justify-center gap-2 w-full bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-bold py-3 px-4 rounded-xl shadow-lg shadow-emerald-500/30 hover:shadow-xl text-sm transition-all duration-200 hover:-translate-y-0.5 active:scale-95 disabled:opacity-60 disabled:cursor-not-allowed disabled:hover:translate-y-0"
                            @disabled($catalogueVide)>
                        <svg id="spinner" class="hidden w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"/><path d="M4 12a8 8 0 018-8" stroke="currentColor" stroke-width="4" stroke-linecap="round" class="opacity-90"/></svg>
                        <svg id="check-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span id="btn-submit-label">Enregistrer le dépôt</span>
                    </button>
                    <a href="{{ route('depots.index') }}"
                       class="inline-flex items-center justify-center w-full bg-white hover:bg-slate-50 text-slate-600 border border-slate-200 font-semibold py-2.5 px-4 rounded-xl text-sm transition">
                        Annuler
                    </a>
                </div>
            </div>
        </div>
    </aside>
</form>

{{-- ══════ GABARIT LIGNE ══════ --}}
<template id="tpl-ligne">
    <div class="line-item group relative rounded-xl border border-slate-200/80 bg-gradient-to-br from-slate-50/70 to-white p-4 transition-all duration-200 hover:border-amber-300 hover:shadow-md">
        <div class="flex items-center gap-3 mb-3">
            <div class="line-number w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center text-xs font-extrabold text-slate-500 transition-all duration-200">1</div>
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Article</span>
        </div>
        <div class="grid md:grid-cols-12 gap-3">
            <div class="md:col-span-5">
                <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1 block">Type d'article <span class="text-rose-500">*</span></label>
                <div class="relative">
                    <select name="articles[__I__][type_article_id]" class="appearance-none w-full pl-3 pr-8 py-2.5 rounded-lg border border-slate-200 bg-white text-sm text-slate-700 focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all cursor-pointer" required>
                        <option value="">— Sélectionner —</option>
                        @foreach($typesArticles as $type)
                            <option value="{{ $type->id }}">{{ $type->nom }}</option>
                        @endforeach
                    </select>
                    <svg class="w-3.5 h-3.5 text-slate-400 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </div>
            </div>
            <div class="md:col-span-4">
                <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1 block">Service <span class="text-rose-500">*</span></label>
                <div class="relative">
                    <select name="articles[__I__][service_id]" class="appearance-none w-full pl-3 pr-8 py-2.5 rounded-lg border border-slate-200 bg-white text-sm text-slate-700 focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all cursor-pointer" required>
                        <option value="">— Sélectionner —</option>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}">{{ $service->nom }}</option>
                        @endforeach
                    </select>
                    <svg class="w-3.5 h-3.5 text-slate-400 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </div>
            </div>
            <div class="md:col-span-3">
                <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1 block">Quantité <span class="text-rose-500">*</span></label>
                <input type="number" name="articles[__I__][quantite]" min="1" max="999" value="1" inputmode="numeric"
                       class="w-full px-3 py-2.5 rounded-lg border border-slate-200 bg-white text-sm text-slate-700 focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all" required>
            </div>
        </div>
        <div class="mt-3 pt-3 flex items-center justify-between gap-3 text-xs border-t border-slate-200/60">
            <p class="text-slate-500 font-medium">Prix : <span class="prix font-extrabold text-slate-800">—</span> <span class="text-[10px] text-slate-400">FCFA</span></p>
            <div class="flex items-center gap-3">
                <p class="text-slate-500 font-medium">Sous-total : <span class="st font-extrabold text-slate-900 text-sm">0</span> <span class="text-[10px] text-slate-400">FCFA</span></p>
                <button type="button" class="sup inline-flex items-center justify-center w-7 h-7 rounded-lg bg-rose-50 hover:bg-rose-500 text-rose-600 hover:text-white font-bold transition-all duration-200" aria-label="Supprimer">✕</button>
            </div>
        </div>
        <p class="ligne-hint hidden mt-2 text-[11px] font-bold text-amber-700 bg-amber-50 border border-amber-200/60 p-2 rounded-lg">⚠ Aucun tarif défini pour cette combinaison article / service.</p>
        <p class="ligne-erreur hidden mt-2 text-[11px] font-bold text-rose-700 bg-rose-50 border border-rose-200/60 p-2 rounded-lg"></p>
    </div>
</template>

<script>
(() => {
    const tarifs   = @json($tarifs);
    const initial  = @json($initial);
    const erreurs  = @json($erreursLignes);

    const form      = document.getElementById('form-depot');
    const container = document.getElementById('articles-container');
    const gabarit   = document.getElementById('tpl-ligne').innerHTML;
    const fmt       = n => Math.round(n).toLocaleString('fr-FR');
    const $         = id => document.getElementById(id);
    let prochain    = 0;

    function majNumero() {
        container.querySelectorAll('.line-item').forEach((l, i) => {
            l.querySelector('.line-number').textContent = i + 1;
        });
    }

    function majBoutonsSupprimer() {
        const lignes = container.querySelectorAll('.line-item');
        lignes.forEach(l => l.querySelector('.sup').classList.toggle('hidden', lignes.length <= 1));
    }

    function ajouterLigne(index, data = {}) {
        const div = document.createElement('div');
        div.innerHTML = gabarit.replaceAll('__I__', index);
        const ligne = div.firstElementChild;

        ligne.querySelector('[name$="[type_article_id]"]').value = data.type_article_id ?? '';
        ligne.querySelector('[name$="[service_id]"]').value      = data.service_id ?? '';
        ligne.querySelector('[name$="[quantite]"]').value        = data.quantite ?? 1;

        const message = ['type_article_id', 'service_id', 'quantite']
            .map(champ => erreurs[`articles.${index}.${champ}`])
            .find(Boolean);
        if (message) {
            const p = ligne.querySelector('.ligne-erreur');
            p.textContent = message;
            p.classList.remove('hidden');
        }

        ligne.querySelector('.sup').addEventListener('click', () => {
            ligne.style.opacity = '0';
            ligne.style.transform = 'translateX(-20px)';
            setTimeout(() => { ligne.remove(); majNumero(); majBoutonsSupprimer(); calculer(); }, 200);
        });

        container.appendChild(ligne);
    }

    function calculer() {
        const express = $('express').checked;
        let total = 0;

        container.querySelectorAll('.line-item').forEach(ligne => {
            const type    = ligne.querySelector('[name$="[type_article_id]"]').value;
            const service = ligne.querySelector('[name$="[service_id]"]').value;
            const qte     = parseInt(ligne.querySelector('[name$="[quantite]"]').value, 10) || 0;
            const tarif   = tarifs[type + '-' + service];
            const prix    = tarif ? tarif.prix + (express ? tarif.express : 0) : null;

            ligne.querySelector('.prix').textContent = prix === null ? '—' : fmt(prix);
            ligne.querySelector('.ligne-hint').classList.toggle('hidden', !(type && service && !tarif));

            const sousTotal = (prix || 0) * qte;
            ligne.querySelector('.st').textContent = fmt(sousTotal);
            total += sousTotal;
        });

        const remise  = Math.max(0, parseInt($('remise').value, 10) || 0);
        const acompte = Math.max(0, parseInt($('montant_paye').value, 10) || 0);
        const net     = Math.max(0, total - remise);
        const reste   = net - acompte;

        $('sum-total').textContent  = fmt(total);
        $('sum-remise').textContent = remise > 0 ? '− ' + fmt(remise) : '0';
        $('sum-net').textContent    = fmt(net);
        $('sum-reste').textContent  = fmt(Math.max(0, reste));

        $('warn-remise').classList.toggle('hidden', remise <= total);
        $('warn-acompte').classList.toggle('hidden', acompte <= net);
        $('ref-wrap').classList.toggle('hidden', $('mode_paiement').value === 'especes');
    }

    Object.entries(initial).forEach(([cle, donnees]) => {
        ajouterLigne(cle, donnees);
        prochain = Math.max(prochain, parseInt(cle, 10) + 1);
    });

    $('btn-ajouter-article').addEventListener('click', () => {
        ajouterLigne(prochain++);
        majNumero();
        majBoutonsSupprimer();
        calculer();
    });

    form.addEventListener('input', calculer);
    form.addEventListener('change', calculer);

    form.addEventListener('submit', () => {
        const btn = $('btn-submit');
        btn.disabled = true;
        $('spinner').classList.remove('hidden');
        $('check-icon').classList.add('hidden');
        $('btn-submit-label').textContent = 'Enregistrement en cours…';
    });
    window.addEventListener('pageshow', e => { if (e.persisted) location.reload(); });

    majBoutonsSupprimer();
    calculer();
})();
</script>
@endsection