@extends('layouts.app')
@section('title', 'Nouvel achat')
@section('page-title', 'Nouvel achat')
@section('page-subtitle', 'Le stock est mis à jour automatiquement')

@section('content')
<form method="POST" action="{{ route('achats.store') }}" class="max-w-4xl space-y-6">
    @csrf
    <div class="card"><div class="card-body grid grid-cols-3 gap-4">
        <div>
            <label for="fournisseur_id" class="form-label">Fournisseur *</label>
            <select id="fournisseur_id" name="fournisseur_id" class="form-select" required>
                <option value="">— Choisir —</option>
                @foreach($fournisseurs as $f)
                    <option value="{{ $f->id }}" @selected(old('fournisseur_id') == $f->id)>{{ $f->nom }}</option>
                @endforeach
            </select>
            @error('fournisseur_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <x-field name="date_achat" label="Date" type="date" :value="now()->format('Y-m-d')" max="{{ now()->format('Y-m-d') }}" required />
        <div>
            <label for="mode_paiement" class="form-label">Mode de paiement *</label>
            <select id="mode_paiement" name="mode_paiement" class="form-select">
                <option value="especes">Espèces</option>
                <option value="orange_money">Orange Money</option>
                <option value="mtn_money">MTN Money</option>
                <option value="wave">Wave</option>
                <option value="autre">Autre</option>
            </select>
        </div>
    </div></div>

    <div class="card">
        <div class="card-header flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-slate-800">Produits achetés</h2>
                <p class="text-xs text-slate-500 mt-0.5">Pour un produit avec conditionnement (ex. bidon de 12 L), vous pouvez saisir le nombre de bidons.</p>
            </div>
            <button type="button" id="ajouter" class="btn-secondary">+ Ajouter une ligne</button>
        </div>
        <div class="card-body space-y-3" id="lignes"></div>
        @error('lignes')<p class="text-red-500 text-xs px-6 pb-2">{{ $message }}</p>@enderror
        @foreach($errors->get('lignes.*') as $messages)
            @foreach($messages as $m)<p class="text-red-500 text-xs px-6 pb-1">{{ $m }}</p>@endforeach
        @endforeach
        <div class="px-6 py-3 border-t border-slate-100 text-right font-bold text-slate-800">
            Total : <span id="total">0</span> FCFA
        </div>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="btn-primary">Enregistrer l'achat</button>
        <a href="{{ route('achats.index') }}" class="btn-secondary">Annuler</a>
    </div>
</form>

<script>
const produits = @json($produits->map(fn ($p) => [
    'id' => $p->id, 'nom' => $p->nom, 'unite' => $p->unite,
    'contenance' => $p->contenance, 'libelle' => $p->libelle_conditionnement,
]));
let index = 0;

function ajouterLigne() {
    const div = document.createElement('div');
    div.className = 'ligne rounded-xl border border-slate-200 bg-slate-50/70 p-3';
    div.innerHTML = `
        <div class="grid grid-cols-12 gap-3 items-end">
            <div class="col-span-5"><label class="form-label text-xs">Produit *</label>
                <select name="lignes[${index}][produit_id]" class="form-select produit" required><option value="">— Produit —</option></select></div>
            <div class="col-span-2"><label class="form-label text-xs qte-label">Quantité *</label>
                <input type="number" name="lignes[${index}][quantite]" min="0.001" step="0.001" class="form-input qte" required></div>
            <div class="col-span-2"><label class="form-label text-xs pu-label">Prix unitaire *</label>
                <input type="number" name="lignes[${index}][prix_unitaire]" min="0" step="0.01" class="form-input pu" required></div>
            <div class="col-span-2 text-sm font-semibold text-slate-700 pb-2"><span class="st">0</span> FCFA</div>
            <div class="col-span-1"><button type="button" class="btn-danger px-2 py-1 sup">✕</button></div>
        </div>
        <label class="cond-wrap hidden mt-2 flex items-center gap-2 text-xs text-slate-600 cursor-pointer">
            <input type="checkbox" name="lignes[${index}][conditionnement]" value="1" class="cond rounded border-slate-300 text-amber-500">
            <span class="cond-texte"></span>
        </label>`;

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
