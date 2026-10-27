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

@if ($errors->any())
    <div role="alert" class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-sm flex items-start gap-3 shadow-2xs">
        <svg class="h-5 w-5 shrink-0 text-rose-500 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
        </svg>
        <div>
            <p class="font-semibold">Impossible d'enregistrer la consommation</p>
            <ul class="mt-1 list-disc list-inside space-y-0.5 text-xs">
                @foreach ($errors->all() as $erreur)<li>{{ $erreur }}</li>@endforeach
            </ul>
        </div>
    </div>
@endif

<div class="grid lg:grid-cols-3 gap-6 items-start">
    <form method="POST" action="{{ route('consommations.store', $depot) }}" id="form-conso" class="lg:col-span-2 space-y-6">
        @csrf

        <div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
            <div class="h-1.5 bg-gradient-to-r from-amber-400 to-amber-500"></div>
            <div class="card-header px-6 py-4.5 border-b border-slate-100 bg-slate-50/50">
                <h3 class="font-bold text-slate-800 text-base">Quels produits avez-vous utilisés ?</h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Les quantités sont pré-remplies d'après les réglages standard. Ajustez-les si nécessaire. Le stock sera automatiquement déduit à la validation.
                </p>
            </div>
            <div class="card-body p-6 space-y-4">
                <div id="lignes" class="space-y-3"></div>
                <button type="button" id="ajouter" class="inline-flex items-center gap-1.5 bg-slate-800 hover:bg-slate-900 text-white font-medium px-3.5 py-2 rounded-xl text-xs transition shadow-2xs">
                    + Ajouter un autre produit
                </button>
            </div>
        </div>

        <div class="flex flex-wrap gap-3">
            <button type="submit" id="btn-valider" class="inline-flex items-center gap-2 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white font-bold py-2.5 px-5 rounded-xl shadow-md shadow-emerald-500/20 text-sm transition disabled:opacity-60 disabled:cursor-not-allowed">
                ✔ <span id="btn-valider-label">Valider et marquer prêt</span>
            </button>
            <a href="{{ route('depots.show', $depot) }}" class="inline-flex items-center justify-center bg-white hover:bg-slate-50 text-slate-600 border border-slate-200 font-semibold py-2.5 px-4 rounded-xl text-sm transition">
                Annuler
            </a>
        </div>
    </form>

    {{-- Rappel du dépôt --}}
    <aside class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs lg:sticky lg:top-6">
        <div class="card-header px-6 py-4.5 border-b border-slate-100 bg-slate-50/50">
            <h3 class="font-bold text-slate-800 text-base">Rappel du dépôt</h3>
        </div>
        <div class="card-body p-6 space-y-4 text-sm">
            <p class="text-slate-600">Client : <span class="font-bold text-slate-800">{{ $depot->client?->nom }} {{ $depot->client?->prenom }}</span></p>
            <div class="border-t border-slate-100 pt-3">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Articles concernés</p>
                <ul class="space-y-2">
                    @foreach($depot->articles as $a)
                        <li class="flex items-center justify-between gap-3 text-xs bg-slate-50 p-2.5 rounded-xl border border-slate-200/60">
                            <span class="font-semibold text-slate-800">{{ $a->quantite }} × {{ $a->typeArticle?->nom }}</span>
                            <span class="text-slate-500 font-medium">{{ $a->service?->nom }}</span>
                        </li>
                    @endforeach
                </ul>
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
        const div = document.createElement('div');
        div.className = 'ligne grid grid-cols-12 gap-3 items-end rounded-2xl border border-slate-200/80 bg-slate-50/50 p-4.5 shadow-2xs transition';
        div.innerHTML = `
            <div class="col-span-6">
                <label class="form-label font-medium text-xs text-slate-600 mb-1 block">Produit *</label>
                <select name="lignes[${index}][produit_id]" class="form-select w-full rounded-xl border-slate-200 text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 produit" required>
                    <option value="">— Sélectionner —</option>
                </select>
            </div>
            <div class="col-span-5">
                <label class="form-label font-medium text-xs text-slate-600 mb-1 block">Quantité utilisée *</label>
                <div class="flex items-center gap-2">
                    <input type="number" name="lignes[${index}][quantite]" min="0" step="0.001" inputmode="decimal" class="form-input w-full rounded-xl border-slate-200 text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 qte" required>
                    <span class="unite text-xs font-bold text-slate-600 w-12"></span>
                </div>
            </div>
            <div class="col-span-1 text-right pb-1">
                <button type="button" class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold transition shadow-2xs sup" aria-label="Retirer cette ligne" title="Supprimer">✕</button>
            </div>
            <p class="stock col-span-12 text-xs pt-1"></p>`;

        const select = div.querySelector('.produit');
        produits.forEach(p => select.add(new Option(p.nom, p.id)));
        select.value = data.produit_id ?? '';
        div.querySelector('.qte').value = data.quantite ?? '';

        const maj = () => {
            const p = produits.find(x => String(x.id) === select.value);
            const q = parseFloat(div.querySelector('.qte').value) || 0;
            const info = div.querySelector('.stock');
            div.querySelector('.unite').textContent = p ? p.unite : '';
            if (!p) { info.textContent = ''; return; }
            const apres = p.stock - q;
            info.textContent = `Stock actuel : ${fmt(p.stock)} ${p.unite} → Après validation : ${fmt(apres)} ${p.unite}`;
            info.className = 'stock col-span-12 text-xs font-medium pt-1 ' + (apres < 0 ? 'text-rose-600 font-bold' : 'text-slate-500');
            if (apres < 0) info.textContent += ' — ⚠ Stock insuffisant !';
        };

        select.addEventListener('change', maj);
        div.addEventListener('input', maj);
        div.querySelector('.sup').addEventListener('click', () => div.remove());

        conteneur.appendChild(div);
        index++;
        maj();
    }

    initial.forEach(l => ajouter(l));
    document.getElementById('ajouter').addEventListener('click', () => ajouter());

    document.getElementById('form-conso').addEventListener('submit', () => {
        const btn = document.getElementById('btn-valider');
        btn.disabled = true;
        document.getElementById('btn-valider-label').textContent = 'Validation en cours...';
    });
    window.addEventListener('pageshow', e => { if (e.persisted) location.reload(); });
})();
</script>
@endsection