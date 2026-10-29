@extends('layouts.app')

@section('title', 'Produits utilisés')
@section('page-title', 'Produits utilisés')
@section('page-subtitle', 'Déclaration des consommations pour le dépôt ' . $depot->numero_ticket)

@php
    $initial = old('lignes') ?: $propositions->values()->map(fn ($l) => [
        'produit_id' => $l['produit']->id,
        'quantite'   => $l['quantite'],
    ])->all();
@endphp

@section('content')

<style>
    @keyframes slideIn {
        from { opacity: 0; transform: translateY(-8px) scale(0.98); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }
    @keyframes pulseRing {
        0%   { transform: scale(1); opacity: 0.6; }
        70%  { transform: scale(1.8); opacity: 0; }
        100% { transform: scale(1.8); opacity: 0; }
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(8px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .line-item { animation: slideIn 0.3s ease-out; }
    .line-item:hover .line-number { background: linear-gradient(135deg, #f59e0b, #ea580c); color: white; }
    .line-item:hover .line-remove { opacity: 1; transform: scale(1); }
    .line-remove { opacity: 0.6; transform: scale(0.95); transition: all 0.2s ease; }
    .side-card { animation: fadeInUp 0.5s ease-out backwards; animation-delay: 0.15s; }
    .form-card { animation: fadeInUp 0.5s ease-out backwards; }
    .gradient-border { position: relative; background: white; }
    .gradient-border::before {
        content: ''; position: absolute; inset: 0; padding: 1px; border-radius: inherit;
        background: linear-gradient(135deg, rgba(251,191,36,0.3), rgba(245,158,11,0.1), rgba(251,191,36,0.3));
        -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor; mask-composite: exclude; pointer-events: none;
    }
    .ring-pulse::before {
        content: ''; position: absolute; inset: 0; border-radius: 9999px;
        background: currentColor; animation: pulseRing 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
    .stock-bar { transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1), background 0.3s ease; }
</style>

{{-- ══════ HERO ══════ --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg shadow-emerald-500/30">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
            </svg>
        </div>
        <div>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Déclarer les consommations</h1>
            <p class="text-xs text-slate-500 font-medium">Dépôt <span class="font-bold text-amber-600">{{ $depot->numero_ticket }}</span> · Les stocks seront déduits automatiquement</p>
        </div>
    </div>
    <a href="{{ route('depots.show', $depot) }}"
       class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 hover:border-slate-300 px-3.5 py-2 rounded-xl transition shadow-sm hover:shadow">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Retour au dépôt
    </a>
</div>

{{-- ══════ ERREURS ══════ --}}
@if ($errors->any())
    <div role="alert" class="mb-6 p-4 rounded-2xl bg-gradient-to-br from-rose-50 to-pink-50 border border-rose-200 text-rose-700 text-sm flex items-start gap-3 shadow-sm form-card">
        <span class="shrink-0 w-8 h-8 rounded-lg bg-rose-100 flex items-center justify-center">
            <svg class="h-4 w-4 text-rose-600" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
        </span>
        <div>
            <p class="font-bold">Impossible d'enregistrer la consommation</p>
            <ul class="mt-1 space-y-0.5 text-xs text-rose-600">
                @foreach ($errors->all() as $erreur)<li>· {{ $erreur }}</li>@endforeach
            </ul>
        </div>
    </div>
@endif

<div class="grid lg:grid-cols-3 gap-6 items-start">

    {{-- ═══════════════════════════════════════════════════════════════
         FORMULAIRE PRINCIPAL
         ═══════════════════════════════════════════════════════════════ --}}
    <form method="POST" action="{{ route('consommations.store', $depot) }}" id="form-conso" class="lg:col-span-2 space-y-6">
        @csrf

        <div class="form-card gradient-border rounded-2xl bg-white shadow-lg shadow-slate-200/50 overflow-hidden">
            {{-- Header gradient --}}
            <div class="px-6 py-5 border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-white">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-white shadow-md shadow-amber-500/25">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800 text-sm">Produits consommés</h3>
                        <p class="text-[11px] text-slate-500">Les quantités sont pré-remplies d'après les réglages standard — ajustez si nécessaire.</p>
                    </div>
                </div>
            </div>

            {{-- Lignes --}}
            <div class="p-6 space-y-3" id="lignes"></div>

            {{-- Bouton ajouter --}}
            <div class="px-6 pb-6">
                <button type="button" id="ajouter"
                        class="inline-flex items-center gap-1.5 bg-slate-900 hover:bg-slate-800 text-white font-semibold px-3.5 py-2 rounded-xl text-xs transition shadow-sm hover:shadow-md active:scale-95">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Ajouter un autre produit
                </button>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex flex-wrap items-center gap-3 pt-2">
            <button type="submit" id="btn-valider"
                    class="group inline-flex items-center gap-2 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-bold py-3 px-5 rounded-xl shadow-lg shadow-emerald-500/30 hover:shadow-xl text-sm transition-all duration-200 hover:-translate-y-0.5 active:scale-95 disabled:opacity-60 disabled:cursor-not-allowed disabled:hover:translate-y-0">
                <svg id="spinner" class="hidden w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"/>
                    <path d="M4 12a8 8 0 018-8" stroke="currentColor" stroke-width="4" stroke-linecap="round" class="opacity-90"/>
                </svg>
                <svg id="check-icon" class="w-4 h-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                <span id="btn-valider-label">Valider et marquer prêt</span>
            </button>
            <a href="{{ route('depots.show', $depot) }}"
               class="inline-flex items-center justify-center bg-white hover:bg-slate-50 text-slate-600 border border-slate-200 font-semibold py-3 px-4 rounded-xl text-sm transition">
                Annuler
            </a>
        </div>
    </form>

    {{-- ═══════════════════════════════════════════════════════════════
         SIDEBAR — Rappel du dépôt
         ═══════════════════════════════════════════════════════════════ --}}
    <aside class="side-card gradient-border rounded-2xl bg-white shadow-lg shadow-slate-200/50 overflow-hidden lg:sticky lg:top-6">

        {{-- Header gradient --}}
        <div class="relative px-6 py-5 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-amber-500/20 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
            <div class="relative flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-white/15 backdrop-blur-sm border border-white/25 flex items-center justify-center">
                    <svg class="w-4.5 h-4.5 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-amber-300 uppercase tracking-wider">Rappel</p>
                    <p class="font-bold text-sm">Détails du dépôt</p>
                </div>
            </div>
        </div>

        <div class="p-6 space-y-4 text-sm">

            {{-- Client --}}
            <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-200/60">
                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-amber-100 to-orange-200 flex items-center justify-center text-xs font-extrabold text-amber-700 ring-2 ring-amber-200/60">
                    {{ strtoupper(substr($depot->client?->nom ?? '?', 0, 1)) }}{{ strtoupper(substr($depot->client?->prenom ?? '', 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Client</p>
                    <p class="font-bold text-slate-800 truncate">{{ $depot->client?->nom }} {{ $depot->client?->prenom }}</p>
                </div>
            </div>

            {{-- Ticket --}}
            <div class="flex items-center justify-between p-3 rounded-xl bg-amber-50/60 border border-amber-200/60">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-md bg-amber-100 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                        </svg>
                    </span>
                    <span class="text-[10px] font-bold text-amber-700 uppercase tracking-wider">Ticket</span>
                </div>
                <span class="font-extrabold text-amber-600 text-sm">{{ $depot->numero_ticket }}</span>
            </div>

            {{-- Articles --}}
            <div class="border-t border-slate-100 pt-4">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Articles concernés</p>
                    <span class="text-[10px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-md">{{ $depot->articles->count() }}</span>
                </div>
                <ul class="space-y-2">
                    @foreach($depot->articles as $a)
                        <li class="flex items-center justify-between gap-3 text-xs bg-white p-2.5 rounded-lg border border-slate-200/60 hover:border-amber-300/60 hover:bg-amber-50/30 transition-colors">
                            <span class="flex items-center gap-2 min-w-0">
                                <span class="w-5 h-5 rounded-md bg-slate-100 flex items-center justify-center text-[10px] font-extrabold text-slate-600 shrink-0">{{ $a->quantite }}</span>
                                <span class="font-semibold text-slate-800 truncate">{{ $a->typeArticle?->nom }}</span>
                            </span>
                            <span class="text-slate-500 font-medium whitespace-nowrap">{{ $a->service?->nom }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Avertissement --}}
            <div class="flex items-start gap-2.5 p-3 rounded-xl bg-blue-50 border border-blue-200/60">
                <svg class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-[11px] text-blue-700 leading-relaxed">
                    Le stock sera <span class="font-bold">automatiquement déduit</span> après validation.
                </p>
            </div>
        </div>
    </aside>
</div>

<script>
(() => {
    const produits = @json($produits);
    const initial  = @json($initial);
    const conteneur = document.getElementById('lignes');
    let index = 0;

    const fmt = n => (Math.round(n * 1000) / 1000).toLocaleString('fr-FR');

    function ajouter(data = {}) {
        const num = index + 1;
        const div = document.createElement('div');
        div.className = 'line-item group relative rounded-xl border border-slate-200/80 bg-gradient-to-br from-slate-50/70 to-white p-4 transition-all duration-200 hover:border-amber-300 hover:shadow-md';

        div.innerHTML = `
            <div class="flex items-center gap-3 mb-3">
                <div class="line-number w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center text-xs font-extrabold text-slate-500 transition-all duration-200">
                    ${num}
                </div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Produit consommé</span>
            </div>

            <div class="grid grid-cols-12 gap-3 items-end">
                <div class="col-span-12 sm:col-span-6">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1 block">Produit <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <select name="lignes[${index}][produit_id]" class="produit appearance-none w-full pl-3 pr-8 py-2.5 rounded-lg border border-slate-200 bg-white text-sm text-slate-700 focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all cursor-pointer" required>
                            <option value="">— Sélectionner —</option>
                        </select>
                        <svg class="w-3.5 h-3.5 text-slate-400 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>

                <div class="col-span-10 sm:col-span-5">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1 block">Quantité utilisée <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <input type="number" name="lignes[${index}][quantite]" min="0" step="0.001" inputmode="decimal"
                               class="qte w-full pl-3 pr-14 py-2.5 rounded-lg border border-slate-200 bg-white text-sm text-slate-700 focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all" required>
                        <span class="unite absolute inset-y-0 right-3 flex items-center text-[10px] font-bold text-slate-500 pointer-events-none"></span>
                    </div>
                </div>

                <div class="col-span-2 sm:col-span-1 text-right">
                    <button type="button" class="line-remove w-9 h-9 rounded-lg bg-rose-50 hover:bg-rose-500 text-rose-600 hover:text-white font-bold transition-all duration-200 sup flex items-center justify-center" title="Retirer cette ligne" aria-label="Retirer cette ligne">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="stock-wrap mt-3 pt-3 border-t border-slate-200/60">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="stock-label text-[10px] font-bold text-slate-500 uppercase tracking-wider">Stock</span>
                    <span class="stock-delta text-[10px] font-bold"></span>
                </div>
                <div class="w-full h-1.5 rounded-full bg-slate-100 overflow-hidden">
                    <div class="stock-bar h-full rounded-full bg-slate-300" style="width: 100%"></div>
                </div>
                <p class="stock text-[11px] font-medium mt-1.5 text-slate-500"></p>
            </div>`;

        const select = div.querySelector('.produit');
        produits.forEach(p => select.add(new Option(p.nom, p.id)));
        select.value = data.produit_id ?? '';
        div.querySelector('.qte').value = data.quantite ?? '';

        const maj = () => {
            const p = produits.find(x => String(x.id) === select.value);
            const q = parseFloat(div.querySelector('.qte').value) || 0;
            const info  = div.querySelector('.stock');
            const bar   = div.querySelector('.stock-bar');
            const delta = div.querySelector('.stock-delta');
            const label = div.querySelector('.stock-label');

            div.querySelector('.unite').textContent = p ? p.unite : '';

            if (!p) {
                info.textContent = '';
                delta.textContent = '';
                label.textContent = 'Stock';
                bar.style.width = '100%';
                bar.className = 'stock-bar h-full rounded-full bg-slate-300';
                return;
            }

            const apres = p.stock - q;
            const ratio = p.stock > 0 ? Math.min(100, Math.max(0, (apres / p.stock) * 100)) : 0;

            // Barre
            bar.style.width = ratio + '%';
            bar.className = 'stock-bar h-full rounded-full ' + (apres < 0
                ? 'bg-gradient-to-r from-rose-400 to-rose-600'
                : apres === 0
                    ? 'bg-gradient-to-r from-amber-400 to-amber-500'
                    : apres < p.stock * 0.2
                        ? 'bg-gradient-to-r from-amber-400 to-orange-500'
                        : 'bg-gradient-to-r from-emerald-400 to-emerald-500');

            // Delta
            if (q > 0) {
                delta.textContent = `− ${fmt(q)} ${p.unite}`;
                delta.className = 'stock-delta text-[10px] font-bold ' + (apres < 0 ? 'text-rose-600' : 'text-slate-500');
            } else {
                delta.textContent = '';
            }

            // Texte
            if (apres < 0) {
                info.textContent = `⚠ Stock insuffisant — actuel ${fmt(p.stock)} ${p.unite}, manque ${fmt(Math.abs(apres))} ${p.unite}`;
                info.className = 'stock text-[11px] font-bold mt-1.5 text-rose-600';
            } else {
                info.textContent = `Stock actuel : ${fmt(p.stock)} ${p.unite} → Après validation : ${fmt(apres)} ${p.unite}`;
                info.className = 'stock text-[11px] font-medium mt-1.5 text-slate-500';
            }
        };

        select.addEventListener('change', maj);
        div.addEventListener('input', maj);
        div.querySelector('.sup').addEventListener('click', () => {
            div.style.opacity = '0';
            div.style.transform = 'translateX(-20px)';
            setTimeout(() => { div.remove(); reindexer(); }, 200);
        });

        conteneur.appendChild(div);
        index++;
        maj();
    }

    function reindexer() {
        conteneur.querySelectorAll('.line-item').forEach((l, i) => {
            l.querySelector('.line-number').textContent = i + 1;
        });
    }

    initial.forEach(l => ajouter(l));
    document.getElementById('ajouter').addEventListener('click', () => ajouter());

    // État de chargement
    document.getElementById('form-conso').addEventListener('submit', () => {
        const btn = document.getElementById('btn-valider');
        btn.disabled = true;
        document.getElementById('spinner').classList.remove('hidden');
        document.getElementById('check-icon').classList.add('hidden');
        document.getElementById('btn-valider-label').textContent = 'Validation en cours…';
    });
    window.addEventListener('pageshow', e => { if (e.persisted) location.reload(); });
})();
</script>
@endsection