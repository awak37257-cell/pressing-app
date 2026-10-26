@extends('layouts.app')

@section('title', 'Produits utilisés')
@section('page-title', 'Produits utilisés')
@section('page-subtitle', 'Dépôt ' . $depot->numero_ticket)

@php
    $initial = old('lignes') ?: $propositions->values()->map(fn ($l) => [
        'produit_id' => $l['produit']->id,
        'quantite'   => $l['quantite'],
    ])->all();
@endphp

@section('content')

@if ($errors->any())
    <div role="alert" class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm">
        <p class="font-semibold">Impossible d'enregistrer</p>
        <ul class="mt-1 list-disc list-inside space-y-0.5">
            @foreach ($errors->all() as $erreur)<li>{{ $erreur }}</li>@endforeach
        </ul>
    </div>
@endif

<div class="grid lg:grid-cols-3 gap-6 items-start">
    <form method="POST" action="{{ route('consommations.store', $depot) }}" id="form-conso" class="lg:col-span-2 space-y-6">
        @csrf

        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold text-slate-800">Quels produits avez-vous utilisés ?</h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Les quantités sont pré-remplies d'après les réglages standard. Corrigez-les si vous avez utilisé plus ou moins
                    (ex. 0,5 L sur un bidon de 12 L). Le stock sera diminué à la validation.
                </p>
            </div>
            <div class="card-body space-y-3">
                <div id="lignes" class="space-y-3"></div>
                <button type="button" id="ajouter" class="btn-secondary text-xs px-3 py-1.5">+ Ajouter un autre produit</button>
            </div>
        </div>

        <div class="flex flex-wrap gap-3">
            <button type="submit" id="btn-valider" class="inline-flex items-center gap-2 rounded-xl bg-emerald-500 hover:bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-500/30 transition disabled:opacity-60 disabled:cursor-not-allowed">
                ✔ <span id="btn-valider-label">Valider et marquer prêt</span>
            </button>
            <a href="{{ route('depots.show', $depot) }}" class="btn-secondary">Annuler</a>
        </div>
    </form>

    {{-- Rappel du dépôt --}}
    <aside class="card lg:sticky lg:top-6">
        <div class="card-header"><h3 class="font-semibold text-slate-800">Rappel du dépôt</h3></div>
        <div class="card-body space-y-3 text-sm">
            <p class="text-slate-500">Client : <span class="font-medium text-slate-800">{{ $depot->client?->nom }} {{ $depot->client?->prenom }}</span></p>
            <ul class="space-y-1.5">
                @foreach($depot->articles as $a)
                    <li class="flex justify-between gap-3">
                        <span>{{ $a->quantite }} × {{ $a->typeArticle?->nom }}</span>
                        <span class="text-slate-400">{{ $a->service?->nom }}</span>
                    </li>
                @endforeach
            </ul>
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
        div.className = 'ligne grid grid-cols-12 gap-3 items-end rounded-xl border border-slate-200 bg-slate-50/70 p-3';
        div.innerHTML = `
            <div class="col-span-6"><label class="form-label text-xs">Produit *</label>
                <select name="lignes[${index}][produit_id]" class="form-select produit" required><option value="">— Produit —</option></select></div>
            <div class="col-span-4"><label class="form-label text-xs">Quantité utilisée *</label>
                <div class="flex items-center gap-2">
                    <input type="number" name="lignes[${index}][quantite]" min="0" step="0.001" inputmode="decimal" class="form-input qte" required>
                    <span class="unite text-sm text-slate-500 w-10"></span>
                </div></div>
            <div class="col-span-2 text-right"><button type="button" class="btn-danger px-2 py-1 text-xs sup" aria-label="Retirer cette ligne">✕</button></div>
            <p class="stock col-span-12 text-xs text-slate-400"></p>`;

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
            info.textContent = `Stock actuel : ${fmt(p.stock)} ${p.unite} → après : ${fmt(apres)} ${p.unite}`;
            info.className = 'stock col-span-12 text-xs ' + (apres < 0 ? 'text-red-600 font-semibold' : 'text-slate-400');
            if (apres < 0) info.textContent += ' — stock insuffisant';
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
        document.getElementById('btn-valider').disabled = true;
        document.getElementById('btn-valider-label').textContent = 'Enregistrement…';
    });
    window.addEventListener('pageshow', e => { if (e.persisted) location.reload(); });
})();
</script>
@endsection
