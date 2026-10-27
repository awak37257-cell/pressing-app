@extends('layouts.app')

@section('title', 'Nouvel achat')
@section('page-title', 'Nouvel achat')
@section('page-subtitle', 'Enregistrez vos approvisionnements et mettez à jour le stock automatiquement')

@section('content')
<form method="POST" action="{{ route('achats.store') }}" class="max-w-4xl mx-auto space-y-6">
    @csrf

    {{-- En-tête / Informations générales --}}
    <div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
        <div class="h-1.5 bg-gradient-to-r from-amber-400 to-amber-500"></div>
        <div class="card-header px-6 py-4.5 border-b border-slate-100 bg-slate-50/50">
            <h3 class="font-bold text-slate-800 text-base">Informations de l'achat</h3>
        </div>
        <div class="card-body p-6 grid sm:grid-cols-3 gap-5">
            <div>
                <label for="fournisseur_id" class="form-label font-semibold text-xs text-slate-700 mb-1.5 block">Fournisseur *</label>
                <select id="fournisseur_id" name="fournisseur_id" class="form-select w-full rounded-xl border-slate-200 text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500" required>
                    <option value="">— Choisir un fournisseur —</option>
                    @foreach($fournisseurs as $f)
                        <option value="{{ $f->id }}" @selected(old('fournisseur_id') == $f->id)>{{ $f->nom }}</option>
                    @endforeach
                </select>
                @error('fournisseur_id')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <x-field name="date_achat" label="Date d'achat *" type="date" :value="now()->format('Y-m-d')" max="{{ now()->format('Y-m-d') }}" required />
            </div>

            <div>
                <label for="mode_paiement" class="form-label font-semibold text-xs text-slate-700 mb-1.5 block">Mode de paiement *</label>
                <select id="mode_paiement" name="mode_paiement" class="form-select w-full rounded-xl border-slate-200 text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                    <option value="especes">Espèces</option>
                    <option value="orange_money">Orange Money</option>
                    <option value="mtn_money">MTN Money</option>
                    <option value="wave">Wave</option>
                    <option value="autre">Autre</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Lignes de produits --}}
    <div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
        <div class="card-header px-6 py-4.5 border-b border-slate-100 flex flex-wrap items-center justify-between gap-4 bg-slate-50/50">
            <div>
                <h2 class="font-bold text-slate-800 text-base">Produits achetés</h2>
                <p class="text-xs text-slate-500 mt-0.5">Pour un produit conditionné (ex. bidon de 12L), vous pouvez activer le mode conditionnement.</p>
            </div>
            <button type="button" id="ajouter" class="inline-flex items-center gap-1.5 bg-slate-800 hover:bg-slate-900 text-white font-medium px-3.5 py-2 rounded-xl text-xs transition shadow-2xs">
                + Ajouter une ligne
            </button>
        </div>

        <div class="card-body p-6 space-y-4" id="lignes"></div>

        @error('lignes')<p class="text-rose-500 text-xs px-6 pb-2">{{ $message }}</p>@enderror
        @foreach($errors->get('lignes.*') as $messages)
            @foreach($messages as $m)<p class="text-rose-500 text-xs px-6 pb-1">{{ $m }}</p>@endforeach
        @endforeach

        <div class="px-6 py-4 bg-slate-50/80 border-t border-slate-200/60 flex items-center justify-between">
            <span class="text-sm font-bold text-slate-700">Montant total de l'achat</span>
            <span class="text-xl font-extrabold text-amber-600"><span id="total">0</span> <span class="text-xs font-semibold">FCFA</span></span>
        </div>
    </div>

    {{-- Boutons d'action --}}
    <div class="flex items-center gap-3 pt-2">
        <button type="submit" class="inline-flex items-center gap-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-bold py-2.5 px-5 rounded-xl shadow-md shadow-amber-500/20 text-sm transition">
            Enregistrer l'achat
        </button>
        <a href="{{ route('achats.index') }}" class="inline-flex items-center justify-center bg-white hover:bg-slate-50 text-slate-600 border border-slate-200 font-semibold py-2.5 px-4 rounded-xl text-sm transition">
            Annuler
        </a>
    </div>
</form>

<script>
const produits = [
    @foreach($produits as $p)
    {
        id: {{ $p->id }},
        nom: "{{ addslashes($p->nom) }}",
        unite: "{{ addslashes($p->unite) }}",
        contenance: {{ $p->contenance ?? 'null' }},
        libelle: "{{ addslashes($p->libelle_conditionnement ?? '') }}"
    },
    @endforeach
];
let index = 0;

function ajouterLigne() {
    const div = document.createElement('div');
    div.className = 'ligne rounded-2xl border border-slate-200/80 bg-slate-50/50 p-4.5 shadow-2xs transition space-y-3';
    div.innerHTML = `
        <div class="grid grid-cols-12 gap-3 items-end">
            <div class="col-span-5">
                <label class="form-label font-medium text-xs text-slate-600 mb-1 block">Produit *</label>
                <select name="lignes[${index}][produit_id]" class="form-select w-full rounded-xl border-slate-200 text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 produit" required>
                    <option value="">— Sélectionner —</option>
                </select>
            </div>
            <div class="col-span-3">
                <label class="form-label font-medium text-xs text-slate-600 mb-1 block qte-label">Quantité *</label>
                <input type="number" name="lignes[${index}][quantite]" min="0.001" step="0.001" class="form-input w-full rounded-xl border-slate-200 text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 qte" required>
            </div>
            <div class="col-span-3">
                <label class="form-label font-medium text-xs text-slate-600 mb-1 block pu-label">Prix unitaire *</label>
                <input type="number" name="lignes[${index}][prix_unitaire]" min="0" step="0.01" class="form-input w-full rounded-xl border-slate-200 text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 pu" required>
            </div>
            <div class="col-span-1 text-right pb-1">
                <button type="button" class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold transition shadow-2xs sup" title="Supprimer la ligne">✕</button>
            </div>
        </div>
        <div class="flex items-center justify-between border-t border-slate-200/60 pt-2 text-xs">
            <label class="cond-wrap hidden flex items-center gap-2 text-slate-600 cursor-pointer font-medium">
                <input type="checkbox" name="lignes[${index}][conditionnement]" value="1" class="cond rounded border-slate-300 text-amber-500 focus:ring-amber-400">
                <span class="cond-texte text-amber-700"></span>
            </label>
            <div class="ml-auto text-slate-700 font-bold">
                Sous-total : <span class="st text-slate-900 font-extrabold text-sm">0</span> FCFA
            </div>
        </div>`;

    const select = div.querySelector('.produit');
    produits.forEach(p => select.add(new Option(`${p.nom} (${p.unite})`, p.id)));

    const majConditionnement = () => {
        const p = produits.find(x => String(x.id) === select.value);
        const wrap = div.querySelector('.cond-wrap');
        const coche = div.querySelector('.cond');

        if (p && p.contenance) {
            wrap.classList.remove('hidden');
            div.querySelector('.cond-texte').textContent =
                `Saisir en ${(p.libelle || 'conditionnement').toLowerCase()}s de ${p.contenance} ${p.unite} (le stock sera converti en ${p.unite})`;
        } else {
            wrap.classList.add('hidden');
            coche.checked = false;
        }

        const enCond = coche.checked && p && p.contenance;
        div.querySelector('.qte-label').textContent = enCond ? `Nombre de ${(p.libelle || 'conditionnement').toLowerCase()}s *` : 'Quantité *';
        div.querySelector('.pu-label').textContent  = enCond ? `Prix du ${(p.libelle || 'conditionnement').toLowerCase()} *` : 'Prix unitaire *';
        calculer();
    };

    select.addEventListener('change', majConditionnement);
    div.querySelector('.cond').addEventListener('change', majConditionnement);
    div.addEventListener('input', calculer);
    div.querySelector('.sup').addEventListener('click', () => { div.remove(); calculer(); });
    document.getElementById('lignes').appendChild(div);
    index++;
}

function calculer() {
    let total = 0;
    document.querySelectorAll('.ligne').forEach(l => {
        const s = (parseFloat(l.querySelector('.qte').value) || 0) * (parseFloat(l.querySelector('.pu').value) || 0);
        l.querySelector('.st').textContent = Math.round(s).toLocaleString('fr-FR');
        total += s;
    });
    document.getElementById('total').textContent = Math.round(total).toLocaleString('fr-FR');
}

document.getElementById('ajouter').addEventListener('click', ajouterLigne);
ajouterLigne();
</script>
@endsection