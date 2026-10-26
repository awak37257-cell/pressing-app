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

{{-- ============ BANDEAU ============ --}}
<section class="relative overflow-hidden rounded-2xl px-6 py-6 mb-6 text-white shadow-lg"
         style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 55%, #78350f 130%);">
    <div aria-hidden="true" class="absolute -top-16 -left-12 w-64 h-64 rounded-full bg-amber-500/25 blur-3xl"></div>
    <div aria-hidden="true" class="absolute -bottom-20 right-0 w-72 h-72 rounded-full bg-amber-400/20 blur-3xl"></div>
    <div aria-hidden="true" class="absolute inset-0 opacity-[0.07]"
         style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 22px 22px;"></div>

    <div class="relative flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <span class="w-12 h-12 rounded-2xl bg-amber-500 flex items-center justify-center shadow-lg shadow-amber-500/30">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </span>
            <div>
                <h2 class="text-xl font-bold tracking-tight">Nouveau dépôt</h2>
                <p class="text-sm text-slate-300">Ticket attribué : <span class="font-semibold text-amber-300">{{ $numeroTicket }}</span></p>
            </div>
        </div>
        <a href="{{ route('depots.index') }}"
           class="inline-flex items-center gap-2 rounded-xl bg-white/10 hover:bg-white/20 ring-1 ring-white/20 px-4 py-2 text-sm font-medium transition backdrop-blur-xs">
            ← Retour aux dépôts
        </a>
    </div>
</section>

{{-- ============ ERREURS ============ --}}
@if ($errors->any())
    <div role="alert" class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-sm flex items-start gap-3 shadow-xs">
        <svg class="h-5 w-5 shrink-0 text-rose-500 mt-0.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
        </svg>
        <div>
            <p class="font-semibold">Le dépôt n'a pas pu être enregistré</p>
            <ul class="mt-1 list-disc list-inside space-y-0.5 text-rose-600 text-xs">
                @foreach ($errors->all() as $erreur)
                    <li>{{ $erreur }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

@if ($catalogueVide)
    <div class="mb-6 p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 text-sm shadow-xs">
        <p class="font-semibold">Aucun article ou service n'est configuré.</p>
        <p class="mt-0.5 text-amber-700 text-xs">Ajoutez des types d'articles, des services et des tarifs avant de créer un dépôt.</p>
    </div>
@endif

<form method="POST" action="{{ route('depots.store') }}" id="form-depot" class="grid lg:grid-cols-3 gap-6 items-start">
    @csrf

    {{-- ================= COLONNE GAUCHE ================= --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- 1. Client et retrait --}}
        <div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
            <div class="card-header px-6 py-4.5 border-b border-slate-100 flex items-center gap-3 bg-slate-50/50">
                <span class="w-7 h-7 rounded-xl bg-amber-500 text-white text-xs font-bold flex items-center justify-center shadow-xs">1</span>
                <h3 class="font-bold text-slate-800 text-base">Client et retrait</h3>
            </div>
            <div class="card-body p-6 space-y-5">
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="client_id" class="form-label font-semibold text-xs text-slate-700 mb-1.5 block">Client *</label>
                        <select id="client_id" name="client_id" required
                                class="form-select w-full rounded-xl border-slate-200 text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 @error('client_id') border-red-400 @enderror">
                            <option value="">— Sélectionner un client —</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" @selected(old('client_id') == $client->id)>
                                    {{ $client->nom }} {{ $client->prenom }}{{ $client->telephone ? ' — ' . $client->telephone : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('client_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        <a href="{{ route('clients.create') }}" class="inline-block mt-2 text-xs font-semibold text-amber-600 hover:text-amber-700 hover:underline">+ Créer un nouveau client</a>
                    </div>
                    <div>
                        <label for="date_retrait_prevue" class="form-label font-semibold text-xs text-slate-700 mb-1.5 block">Date de retrait prévue *</label>
                        <input type="date" id="date_retrait_prevue" name="date_retrait_prevue" required
                               value="{{ old('date_retrait_prevue', now()->addDays(2)->format('Y-m-d')) }}"
                               min="{{ now()->format('Y-m-d') }}"
                               class="form-input w-full rounded-xl border-slate-200 text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 @error('date_retrait_prevue') border-red-400 @enderror">
                        @error('date_retrait_prevue')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <label for="express"
                       class="flex items-center gap-4 rounded-xl border border-slate-200/80 p-4 cursor-pointer transition hover:border-amber-400 hover:bg-amber-50/20
                              has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50/50 shadow-2xs">
                    <input type="checkbox" id="express" name="express" value="1" @checked(old('express'))
                           class="w-5 h-5 rounded border-slate-300 text-amber-500 focus:ring-2 focus:ring-amber-400/40">
                    <div>
                        <span class="block text-sm font-bold text-slate-800">⚡ Service express</span>
                        <span class="block text-xs text-slate-500">Un supplément tarifaire sera appliqué automatiquement à chaque article.</span>
                    </div>
                </label>
            </div>
        </div>

        {{-- 2. Articles --}}
        <div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
            <div class="card-header px-6 py-4.5 border-b border-slate-100 flex items-center justify-between gap-3 bg-slate-50/50">
                <div class="flex items-center gap-3">
                    <span class="w-7 h-7 rounded-xl bg-amber-500 text-white text-xs font-bold flex items-center justify-center shadow-xs">2</span>
                    <h3 class="font-bold text-slate-800 text-base">Articles déposés</h3>
                </div>
                <button type="button" id="btn-ajouter-article" class="inline-flex items-center gap-1.5 bg-slate-800 hover:bg-slate-900 text-white font-medium px-3.5 py-2 rounded-xl text-xs transition shadow-2xs">
                    + Ajouter un article
                </button>
            </div>
            <div class="card-body p-6">
                <div id="articles-container" class="space-y-4"></div>
                <p class="mt-4 text-xs text-slate-400 italic">💡 Les prix proviennent de la grille tarifaire et sont calculés en temps réel.</p>
            </div>
        </div>

        {{-- 3. Observations --}}
        <div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
            <div class="card-header px-6 py-4.5 border-b border-slate-100 flex items-center gap-3 bg-slate-50/50">
                <span class="w-7 h-7 rounded-xl bg-amber-500 text-white text-xs font-bold flex items-center justify-center shadow-xs">3</span>
                <h3 class="font-bold text-slate-800 text-base">Observations</h3>
            </div>
            <div class="card-body p-6">
                <label for="observations" class="sr-only">Observations</label>
                <textarea id="observations" name="observations" rows="3" class="form-input w-full rounded-xl border-slate-200 text-sm resize-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500"
                          placeholder="Taches spécifiques, défauts constatés, instructions particulières…">{{ old('observations') }}</textarea>
            </div>
        </div>
    </div>

    {{-- ================= RÉCAPITULATIF ================= --}}
    <aside class="lg:sticky lg:top-6 space-y-6">
        <div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
            <div class="h-1.5 bg-gradient-to-r from-amber-400 to-amber-500"></div>
            <div class="card-header px-6 py-4.5 border-b border-slate-100 bg-slate-50/50">
                <h3 class="font-bold text-slate-800 text-base">Récapitulatif</h3>
            </div>
            <div class="card-body p-6 space-y-5">

                <dl class="space-y-3 text-sm">
                    <div class="flex items-center justify-between text-slate-600">
                        <dt>Total articles</dt>
                        <dd class="font-bold text-slate-800"><span id="sum-total">0</span> FCFA</dd>
                    </div>
                    <div class="flex items-center justify-between text-slate-600">
                        <dt>Remise</dt>
                        <dd class="font-bold text-slate-800"><span id="sum-remise">0</span></dd>
                    </div>
                    <div class="flex items-center justify-between border-t border-slate-100 pt-3">
                        <dt class="font-bold text-slate-800 text-base">Total à payer</dt>
                        <dd class="text-xl font-extrabold text-slate-900"><span id="sum-net">0</span> <span class="text-xs font-semibold">FCFA</span></dd>
                    </div>
                </dl>

                <div class="space-y-4 rounded-2xl bg-amber-50/60 border border-amber-200/60 p-4">
                    <div>
                        <label for="remise" class="form-label font-semibold text-xs text-slate-700 mb-1 block">Remise (FCFA)</label>
                        <input type="number" id="remise" name="remise" min="0" step="1" inputmode="numeric"
                               value="{{ old('remise', 0) }}" class="form-input w-full rounded-xl border-amber-200/80 bg-white text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 @error('remise') border-red-400 @enderror">
                        <p id="warn-remise" class="hidden text-xs text-red-600 mt-1">La remise dépasse le total des articles.</p>
                    </div>
                    <div>
                        <label for="montant_paye" class="form-label font-semibold text-xs text-slate-700 mb-1 block">Acompte reçu (FCFA)</label>
                        <input type="number" id="montant_paye" name="montant_paye" min="0" step="1" inputmode="numeric"
                               value="{{ old('montant_paye', 0) }}" class="form-input w-full rounded-xl border-amber-200/80 bg-white text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 @error('montant_paye') border-red-400 @enderror">
                        <p id="warn-acompte" class="hidden text-xs text-red-600 mt-1">L'acompte dépasse le total à payer.</p>
                    </div>
                    <div>
                        <label for="mode_paiement" class="form-label font-semibold text-xs text-slate-700 mb-1 block">Mode de paiement</label>
                        <select id="mode_paiement" name="mode_paiement" class="form-select w-full rounded-xl border-amber-200/80 bg-white text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                            <option value="especes" @selected(old('mode_paiement', 'especes') === 'especes')>Espèces</option>
                            <option value="orange_money" @selected(old('mode_paiement') === 'orange_money')>Orange Money</option>
                            <option value="mtn_money" @selected(old('mode_paiement') === 'mtn_money')>MTN Money</option>
                            <option value="wave" @selected(old('mode_paiement') === 'wave')>Wave</option>
                        </select>
                    </div>
                    <div id="ref-wrap" class="hidden">
                        <label for="reference" class="form-label font-semibold text-xs text-slate-700 mb-1 block">Référence transaction *</label>
                        <input type="text" id="reference" name="reference" maxlength="100" value="{{ old('reference') }}"
                               placeholder="Ex. : MP240101.1234.A56789"
                               class="form-input w-full rounded-xl border-amber-200/80 bg-white text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 @error('reference') border-red-400 @enderror">
                        @error('reference')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="flex items-center justify-between rounded-xl bg-slate-100/80 border border-slate-200 px-4 py-3.5">
                    <span class="text-sm font-bold text-slate-700">Reste à payer</span>
                    <span class="text-xl font-extrabold text-amber-600"><span id="sum-reste">0</span> <span class="text-xs font-semibold">FCFA</span></span>
                </div>

                <div class="space-y-2.5 pt-2">
                    <button type="submit" id="btn-submit"
                            class="inline-flex items-center justify-center gap-2 w-full bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-bold py-3 px-4 rounded-xl shadow-md shadow-amber-500/20 text-sm transition disabled:opacity-70 disabled:cursor-not-allowed" @disabled($catalogueVide)>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span id="btn-submit-label">Enregistrer le dépôt</span>
                    </button>
                    <a href="{{ route('depots.index') }}" class="inline-flex items-center justify-center w-full bg-white hover:bg-slate-50 text-slate-600 border border-slate-200 font-semibold py-2.5 px-4 rounded-xl text-sm transition">
                        Annuler
                    </a>
                </div>
            </div>
        </div>
    </aside>
</form>

{{-- ============ GABARIT D'UNE LIGNE ARTICLE ============ --}}
<template id="tpl-ligne">
    <div class="grid md:grid-cols-12 gap-3">
        <div class="md:col-span-5">
            <label class="form-label font-medium text-xs text-slate-600 mb-1 block">Type d'article *</label>
            <select name="articles[__I__][type_article_id]" class="form-select w-full rounded-xl border-slate-200 text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500" required>
                <option value="">— Sélectionner —</option>
                @foreach($typesArticles as $type)
                    <option value="{{ $type->id }}">{{ $type->nom }}</option>
                @endforeach
            </select>
        </div>
        <div class="md:col-span-4">
            <label class="form-label font-medium text-xs text-slate-600 mb-1 block">Service *</label>
            <select name="articles[__I__][service_id]" class="form-select w-full rounded-xl border-slate-200 text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500" required>
                <option value="">— Sélectionner —</option>
                @foreach($services as $service)
                    <option value="{{ $service->id }}">{{ $service->nom }}</option>
                @endforeach
            </select>
        </div>
        <div class="md:col-span-3">
            <label class="form-label font-medium text-xs text-slate-600 mb-1 block">Quantité *</label>
            <input type="number" name="articles[__I__][quantite]" min="1" max="999" value="1" inputmode="numeric" class="form-input w-full rounded-xl border-slate-200 text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500" required>
        </div>
    </div>
    <div class="mt-3.5 flex items-center justify-between gap-3 text-xs border-t border-slate-200/60 pt-3">
        <p class="text-slate-500">Prix unitaire : <span class="prix font-bold text-slate-700">—</span> FCFA</p>
        <div class="flex items-center gap-4">
            <p class="text-slate-500">Sous-total : <span class="st font-extrabold text-slate-900 text-sm">0</span> FCFA</p>
            <button type="button" class="sup inline-flex items-center justify-center w-7 h-7 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold transition shadow-2xs" aria-label="Supprimer cet article">✕</button>
        </div>
    </div>
    <p class="ligne-hint hidden mt-2 text-xs font-semibold text-amber-600 bg-amber-50 p-2 rounded-lg">⚠ Aucun tarif défini pour cette combinaison article / service.</p>
    <p class="ligne-erreur hidden mt-2 text-xs font-semibold text-rose-600 bg-rose-50 p-2 rounded-lg"></p>
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

    let prochain = 0;

    function majBoutonsSupprimer() {
        const lignes = container.querySelectorAll('.ligne');
        lignes.forEach(l => l.querySelector('.sup').classList.toggle('hidden', lignes.length <= 1));
    }

    function ajouterLigne(index, data = {}) {
        const div = document.createElement('div');
        div.className = 'ligne rounded-2xl border border-slate-200/80 bg-slate-50/50 p-4.5 shadow-2xs transition';
        div.innerHTML = gabarit.replaceAll('__I__', index);

        div.querySelector('[name$="[type_article_id]"]').value = data.type_article_id ?? '';
        div.querySelector('[name$="[service_id]"]').value      = data.service_id ?? '';
        div.querySelector('[name$="[quantite]"]').value        = data.quantite ?? 1;

        const message = ['type_article_id', 'service_id', 'quantite']
            .map(champ => erreurs[`articles.${index}.${champ}`])
            .find(Boolean);
        if (message) {
            const p = div.querySelector('.ligne-erreur');
            p.textContent = message;
            p.classList.remove('hidden');
        }

        div.querySelector('.sup').addEventListener('click', () => {
            div.remove();
            majBoutonsSupprimer();
            calculer();
        });

        container.appendChild(div);
    }

    function calculer() {
        const express = $('express').checked;
        let total = 0;

        container.querySelectorAll('.ligne').forEach(ligne => {
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
        $('sum-reste').parentElement.className = 'text-xl font-extrabold ' + (reste > 0 ? 'text-amber-600' : 'text-emerald-600');

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
        majBoutonsSupprimer();
        calculer();
    });

    form.addEventListener('input', calculer);
    form.addEventListener('change', calculer);

    form.addEventListener('submit', () => {
        const bouton = $('btn-submit');
        bouton.disabled = true;
        $('btn-submit-label').textContent = 'Enregistrement en cours...';
    });
    window.addEventListener('pageshow', e => { if (e.persisted) location.reload(); });

    majBoutonsSupprimer();
    calculer();
})();
</script>
@endsection