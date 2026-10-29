@extends('layouts.app')

@section('title', 'Nouvel achat')
@section('page-title', 'Nouvel achat')
@section('page-subtitle', 'Enregistrez vos approvisionnements et mettez à jour le stock automatiquement')

@section('content')

<style>
    @keyframes slideIn {
        from { opacity: 0; transform: translateY(-8px) scale(0.98); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }
    .line-item { animation: slideIn 0.3s ease-out; }
    .line-item:hover .line-number { background: linear-gradient(135deg, #f59e0b, #ea580c); color: white; }
    .line-item:hover .line-remove { opacity: 1; transform: scale(1); }
    .line-remove { opacity: 0.6; transform: scale(0.95); transition: all 0.2s ease; }
    .input-focus:focus-within { box-shadow: 0 0 0 3px rgba(251, 191, 36, 0.15); }
    .sticky-actions {
        position: sticky; bottom: 0; z-index: 10;
        backdrop-filter: blur(12px); background: rgba(255,255,255,0.85);
    }
</style>

<form method="POST" action="{{ route('achats.store') }}" class="max-w-5xl mx-auto space-y-6 pb-28">
    @csrf

    {{-- ══════ HERO ══════ --}}
    <div class="flex items-center gap-3 mb-2">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center shadow-lg shadow-amber-500/30">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
        </div>
        <div>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Créer un bon de commande</h1>
            <p class="text-xs text-slate-500 font-medium">Renseignez le fournisseur, les produits et validez l'approvisionnement</p>
        </div>
    </div>

    {{-- ══════ SECTION 1 : INFOS GÉNÉRALES ══════ --}}
    <div class="gradient-border rounded-2xl bg-white shadow-lg shadow-slate-200/50 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-white flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-blue-500/25">
                <span class="text-xs font-extrabold">01</span>
            </div>
            <div>
                <h3 class="font-bold text-slate-800 text-sm">Informations générales</h3>
                <p class="text-[11px] text-slate-500">Fournisseur, date et mode de paiement</p>
            </div>
        </div>

        <div class="p-6 grid sm:grid-cols-3 gap-5">
            {{-- Fournisseur --}}
            <div>
                <label for="fournisseur_id" class="text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5 block">Fournisseur <span class="text-rose-500">*</span></label>
                <div class="relative">
                    <select id="fournisseur_id" name="fournisseur_id" class="appearance-none w-full pl-3.5 pr-9 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-sm text-slate-700 focus:bg-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all cursor-pointer" required>
                        <option value="">— Choisir un fournisseur —</option>
                        @foreach($fournisseurs as $f)
                            <option value="{{ $f->id }}" @selected(old('fournisseur_id') == $f->id)>{{ $f->nom }}</option>
                        @endforeach
                    </select>
                    <svg class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
                @error('fournisseur_id')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Date --}}
            <div>
                <label class="text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5 block">Date d'achat <span class="text-rose-500">*</span></label>
                <input type="date" name="date_achat" value="{{ old('date_achat', now()->format('Y-m-d')) }}" max="{{ now()->format('Y-m-d') }}" required
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-sm text-slate-700 focus:bg-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all">
            </div>

            {{-- Mode paiement --}}
            <div>
                <label for="mode_paiement" class="text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5 block">Mode de paiement <span class="text-rose-500">*</span></label>
                <div class="relative">
                    <select id="mode_paiement" name="mode_paiement" class="appearance-none w-full pl-3.5 pr-9 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-sm text-slate-700 focus:bg-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all cursor-pointer">
                        <option value="especes">💵 Espèces</option>
                        <option value="orange_money">📱 Orange Money</option>
                        <option value="mtn_money">📱 MTN Money</option>
                        <option value="wave">🌊 Wave</option>
                        <option value="autre">💳 Autre</option>
                    </select>
                    <svg class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════ SECTION 2 : LIGNES DE PRODUITS ══════ --}}
    <div class="gradient-border rounded-2xl bg-white shadow-lg shadow-slate-200/50 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-white flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-white shadow-md shadow-amber-500/25">
                    <span class="text-xs font-extrabold">02</span>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 text-sm">Produits achetés</h3>
                    <p class="text-[11px] text-slate-500">Pour un produit conditionné (bidon de 12L), activez le mode conditionnement.</p>
                </div>
            </div>
            <button type="button" id="ajouter"
                    class="inline-flex items-center gap-1.5 bg-slate-900 hover:bg-slate-800 text-white font-semibold px-3.5 py-2 rounded-xl text-xs transition shadow-sm hover:shadow-md active:scale-95">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Ajouter une ligne
            </button>
        </div>

        <div class="p-6 space-y-3" id="lignes"></div>

        @error('lignes')<p class="text-rose-500 text-xs px-6 pb-2">{{ $message }}</p>@enderror
        @foreach($errors->get('lignes.*') as $messages)
            @foreach($messages as $m)<p class="text-rose-500 text-xs px-6 pb-1">{{ $m }}</p>@endforeach
        @endforeach
    </div>

    {{-- ══════ BARRE STICKY : TOTAL + ACTIONS ══════ --}}
    <div class="sticky-actions rounded-2xl border border-slate-200/80 shadow-2xl shadow-slate-900/10">
        <div class="px-6 py-4 flex flex-col sm:flex-row items-center justify-between gap-4">
            {{-- Total --}}
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white shadow-lg shadow-amber-500/30">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Montant total de l'achat</p>
                    <p class="text-2xl font-extrabold text-slate-900 tracking-tight">
                        <span id="total">0</span>
                        <span class="text-xs font-semibold text-slate-400 ml-1">FCFA</span>
                    </p>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <a href="{{ route('achats.index') }}"
                   class="flex-1 sm:flex-none inline-flex items-center justify-center bg-white hover:bg-slate-50 text-slate-600 border border-slate-200 font-semibold py-2.5 px-4 rounded-xl text-sm transition">
                    Annuler
                </a>
                <button type="submit"
                        class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-bold py-2.5 px-5 rounded-xl shadow-lg shadow-amber-500/30 hover:shadow-xl text-sm transition-all duration-200 active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Enregistrer l'achat
                </button>
            </div>
        </div>
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
    const num = index + 1;
    const div = document.createElement('div');
    div.className = 'line-item group relative rounded-xl border border-slate-200/80 bg-gradient-to-br from-slate-50/70 to-white p-4 transition-all duration-200 hover:border-amber-300 hover:shadow-md';

    div.innerHTML = `
        <div class="flex items-center gap-3 mb-3">
            <div class="line-number w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center text-xs font-extrabold text-slate-500 transition-all duration-200">
                ${num}
            </div>
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Ligne de commande</span>
        </div>

        <div class="grid grid-cols-12 gap-3 items-end">
            <div class="col-span-12 md:col-span-5">
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
            <div class="col-span-4 md:col-span-3">
                <label class="qte-label text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1 block">Quantité <span class="text-rose-500">*</span></label>
                <input type="number" name="lignes[${index}][quantite]" min="0.001" step="0.001"
                       class="qte w-full px-3 py-2.5 rounded-lg border border-slate-200 bg-white text-sm text-slate-700 focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all" required>
            </div>
            <div class="col-span-4 md:col-span-3">
                <label class="pu-label text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1 block">Prix unitaire <span class="text-rose-500">*</span></label>
                <div class="relative">
                    <input type="number" name="lignes[${index}][prix_unitaire]" min="0" step="0.01"
                           class="pu w-full pl-3 pr-12 py-2.5 rounded-lg border border-slate-200 bg-white text-sm text-slate-700 focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all" required>
                    <span class="absolute inset-y-0 right-3 flex items-center text-[10px] font-bold text-slate-400 pointer-events-none">FCFA</span>
                </div>
            </div>
            <div class="col-span-4 md:col-span-1 text-right">
                <button type="button" class="line-remove w-9 h-9 rounded-lg bg-rose-50 hover:bg-rose-500 text-rose-600 hover:text-white font-bold transition-all duration-200 sup flex items-center justify-center" title="Supprimer la ligne">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="mt-3 pt-3 border-t border-slate-200/60 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
            <label class="cond-wrap hidden inline-flex items-center gap-2 text-xs text-slate-600 cursor-pointer font-medium bg-amber-50 border border-amber-200 px-3 py-1.5 rounded-lg hover:bg-amber-100 transition-colors">
                <input type="checkbox" name="lignes[${index}][conditionnement]" value="1" class="cond rounded border-slate-300 text-amber-500 focus:ring-amber-400">
                <span class="cond-texte text-amber-700 font-semibold"></span>
            </label>
            <div class="ml-auto text-xs text-slate-500 font-semibold">
                Sous-total : <span class="st text-slate-900 font-extrabold text-sm">0</span> <span class="text-slate-400">FCFA</span>
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
                `Saisir en ${(p.libelle || 'conditionnement').toLowerCase()}s de ${p.contenance} ${p.unite} → stock converti en ${p.unite}`;
        } else {
            wrap.classList.add('hidden');
            coche.checked = false;
        }

        const enCond = coche.checked && p && p.contenance;
        div.querySelector('.qte-label').innerHTML = enCond ? `Nombre de ${(p.libelle || 'conditionnement').toLowerCase()}s <span class="text-rose-500">*</span>` : 'Quantité <span class="text-rose-500">*</span>';
        div.querySelector('.pu-label').innerHTML  = enCond ? `Prix du ${(p.libelle || 'conditionnement').toLowerCase()} <span class="text-rose-500">*</span>` : 'Prix unitaire <span class="text-rose-500">*</span>';
        calculer();
    };

    select.addEventListener('change', majConditionnement);
    div.querySelector('.cond').addEventListener('change', majConditionnement);
    div.addEventListener('input', calculer);
    div.querySelector('.sup').addEventListener('click', () => {
        div.style.opacity = '0';
        div.style.transform = 'translateX(-20px)';
        setTimeout(() => { div.remove(); calculer(); reindexer(); }, 200);
    });
    document.getElementById('lignes').appendChild(div);
    index++;
}

function reindexer() {
    document.querySelectorAll('.line-item').forEach((l, i) => {
        l.querySelector('.line-number').textContent = i + 1;
    });
}

function calculer() {
    let total = 0;
    document.querySelectorAll('.line-item').forEach(l => {
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