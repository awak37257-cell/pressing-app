@extends('layouts.app')

@section('title', 'Services & Tarifs')
@section('page-title', 'Services & Tarifs')
@section('page-subtitle', 'Gérer les prestations et leur tarification par type d\'article')

@php
    // Onglet ouvert d'office si une erreur de validation concerne un de ses formulaires
    $ongletErreur = $errors->has('nom_categorie') ? 'categories'
                  : ($errors->has('nom_type_article') ? 'types'
                  : (($errors->has('nom') || $errors->has('description')) ? 'services' : null));
    $ongletInitial = $ongletErreur ?? 'services';

    $onglets = [
        'categories' => ['label' => 'Catégories',        'count' => $categoriesProduits->count()],
        'types'      => ['label' => 'Types d\'articles', 'count' => $typesArticles->count()],
        'services'   => ['label' => 'Services & tarifs', 'count' => $services->count()],
    ];
@endphp

@section('content')

<style>
    @keyframes tabIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: none; } }

    .tab-btn { display: inline-flex; align-items: center; gap: .5rem; white-space: nowrap;
        padding: .6rem 1rem; border-radius: .75rem; font-size: .875rem; font-weight: 600;
        color: rgb(100 116 139); transition: color .2s, background-color .2s, box-shadow .2s, transform .15s; }
    .tab-btn:hover { color: rgb(30 41 59); background: rgb(248 250 252); }
    .tab-btn:active { transform: scale(.97); }
    .tab-btn:focus-visible { outline: 2px solid #f59e0b; outline-offset: 2px; }
    .tab-btn[aria-selected="true"] { color: #fff; background: linear-gradient(135deg, #f59e0b, #f97316);
        box-shadow: 0 6px 16px -6px rgb(245 158 11 / .6); }
    .tab-count { min-width: 1.5rem; padding: .1rem .45rem; border-radius: 9999px; text-align: center;
        font-size: .7rem; font-weight: 800; background: rgb(241 245 249); color: rgb(71 85 105); transition: background-color .2s, color .2s; }
    .tab-btn[aria-selected="true"] .tab-count { background: rgb(255 255 255 / .25); color: #fff; }

    .tab-panel:not([hidden]) { animation: tabIn .35s cubic-bezier(.2,.7,.2,1); }

    @media (prefers-reduced-motion: reduce) {
        .tab-panel:not([hidden]) { animation: none; }
        .tab-btn { transition: none; }
    }
</style>

{{-- ═══ BARRE D'ONGLETS ═══ --}}
<div class="mb-6 overflow-x-auto pb-1">
    <div role="tablist" aria-label="Sections de configuration"
         class="inline-flex gap-1 rounded-2xl border border-slate-200/80 bg-white p-1.5 shadow-sm">
        @foreach($onglets as $cle => $o)
            <button type="button" role="tab" id="tab-{{ $cle }}" data-tab="{{ $cle }}"
                    aria-controls="panel-{{ $cle }}"
                    aria-selected="{{ $cle === $ongletInitial ? 'true' : 'false' }}"
                    tabindex="{{ $cle === $ongletInitial ? '0' : '-1' }}"
                    class="tab-btn">
                {{ $o['label'] }}
                <span class="tab-count">{{ $o['count'] }}</span>
            </button>
        @endforeach
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     ONGLET 1 : CATÉGORIES DE PRODUITS
     ═══════════════════════════════════════════════════════════════ --}}
<div role="tabpanel" id="panel-categories" aria-labelledby="tab-categories"
     class="tab-panel" @if($ongletInitial !== 'categories') hidden @endif>
    <div class="grid lg:grid-cols-3 gap-6">

        <section id="categories-produits" class="card scroll-mt-24 overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs lg:col-span-2">
            <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/50 px-6 py-4">
                <div>
                    <h3 class="font-bold text-slate-800">Catégories de produits</h3>
                    <p class="mt-0.5 text-xs text-slate-500">Classez les consommables de votre pressing</p>
                </div>
                <span class="rounded-full bg-violet-100 px-2.5 py-1 text-xs font-bold text-violet-700">{{ $categoriesProduits->count() }}</span>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($categoriesProduits as $categorie)
                    <div class="space-y-2 px-6 py-4">
                        <form id="modifier-categorie-{{ $categorie->id }}" method="POST" action="{{ route('categories-produits.update', $categorie) }}" class="flex items-center gap-2">
                            @csrf
                            @method('PUT')
                            <input type="text" name="nom_categorie" value="{{ $categorie->nom }}" maxlength="50" required
                                   aria-label="Nom de la catégorie {{ $categorie->nom }}"
                                   class="form-input min-w-0 flex-1 rounded-lg border-slate-200 px-3 py-2 text-sm font-semibold">
                            <button type="submit" class="whitespace-nowrap rounded-lg bg-amber-50 px-3 py-2 text-xs font-bold text-amber-700 transition hover:bg-amber-100">
                                Modifier
                            </button>
                        </form>
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xs text-slate-500">{{ $categorie->produits_count }} produit(s) associé(s)</span>
                            @if($categorie->produits_count === 0)
                                <form method="POST" action="{{ route('categories-produits.destroy', $categorie) }}"
                                      onsubmit="return confirm('Supprimer cette catégorie ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-semibold text-rose-600 transition hover:text-rose-700 hover:underline">
                                        Supprimer
                                    </button>
                                </form>
                            @else
                                <span class="cursor-not-allowed text-xs font-semibold text-slate-400" title="Cette catégorie est utilisée par des produits">
                                    Suppression impossible
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="px-6 py-10 text-center text-xs text-slate-400">Aucune catégorie de produit configurée. Ajoutez-en une avec le formulaire.</p>
                @endforelse
            </div>
        </section>

        <div class="lg:sticky lg:top-24 lg:self-start">
            <div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
                <div class="h-1.5 bg-gradient-to-r from-violet-500 to-purple-600"></div>
                <div class="border-b border-slate-100 bg-slate-50/50 px-5 py-4">
                    <h3 class="font-bold text-slate-800 text-sm">Nouvelle catégorie</h3>
                    <p class="mt-0.5 text-xs text-slate-500">Ex. : Lessives, Détachants</p>
                </div>
                <form method="POST" action="{{ route('categories-produits.store') }}" class="space-y-4 p-5">
                    @csrf
                    <div>
                        <label for="nom_categorie" class="mb-1.5 block text-xs font-semibold text-slate-700">Nom de la catégorie <span class="text-rose-500">*</span></label>
                        <input type="text" id="nom_categorie" name="nom_categorie" value="{{ old('nom_categorie') }}"
                               maxlength="50" required
                               class="form-input w-full rounded-xl border-slate-200 text-sm @error('nom_categorie') border-rose-400 @enderror"
                               placeholder="Ex. : Lessives">
                        @error('nom_categorie')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="w-full rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-violet-700">
                        + Ajouter la catégorie
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     ONGLET 2 : TYPES D'ARTICLES
     ═══════════════════════════════════════════════════════════════ --}}
<div role="tabpanel" id="panel-types" aria-labelledby="tab-types"
     class="tab-panel" @if($ongletInitial !== 'types') hidden @endif>
    <div class="grid lg:grid-cols-3 gap-6">

        <div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs lg:col-span-2">
            <div class="card-header flex items-center justify-between border-b border-slate-100 bg-slate-50/50 px-6 py-4">
                <div>
                    <h3 class="font-bold text-slate-800">Types d’articles</h3>
                    <p class="mt-0.5 text-xs text-slate-500">Vêtements et pièces proposés dans les formulaires de dépôt</p>
                </div>
                <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-700">{{ $typesArticles->count() }}</span>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($typesArticles as $typeArticle)
                    <div class="px-6 py-4">
                        <div class="flex items-center justify-between gap-3">
                            <span class="font-semibold text-slate-800">{{ $typeArticle->nom }}</span>
                            <div class="flex items-center gap-3">
                                <button type="button" onclick="document.getElementById('edit-type-article-{{ $typeArticle->id }}').classList.toggle('hidden')"
                                        class="text-xs font-semibold text-amber-600 hover:underline">Modifier</button>
                                <form method="POST" action="{{ route('types-articles.destroy', $typeArticle) }}"
                                      onsubmit="return confirm('Supprimer ce type d’article ? Les tarifs associés seront également supprimés.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs font-semibold text-rose-500 hover:underline">Supprimer</button>
                                </form>
                            </div>
                        </div>
                        <form id="edit-type-article-{{ $typeArticle->id }}" method="POST" action="{{ route('types-articles.update', $typeArticle) }}"
                              class="hidden mt-3 flex flex-wrap items-end gap-3 rounded-xl bg-amber-50/50 p-4">
                            @csrf @method('PUT')
                            <div class="min-w-48 flex-1">
                                <label for="nom-type-article-{{ $typeArticle->id }}" class="mb-1 block text-xs font-semibold text-slate-600">Nom du type d’article</label>
                                <input id="nom-type-article-{{ $typeArticle->id }}" type="text" name="nom_type_article" value="{{ $typeArticle->nom }}"
                                       maxlength="50" required class="form-input w-full rounded-xl border-slate-200 text-sm">
                            </div>
                            <button type="submit" class="whitespace-nowrap rounded-xl bg-amber-500 px-4 py-2 text-xs font-bold text-white transition hover:bg-amber-600">Enregistrer</button>
                        </form>
                    </div>
                @empty
                    <p class="px-6 py-10 text-center text-xs text-slate-400">Aucun type d’article configuré. Ajoutez-en un avec le formulaire.</p>
                @endforelse
            </div>
        </div>

        <div class="lg:sticky lg:top-24 lg:self-start">
            <div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
                <div class="h-1.5 bg-gradient-to-r from-blue-400 to-indigo-500"></div>
                <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="font-bold text-slate-800 text-sm">Nouveau type d’article</h3>
                    <p class="mt-0.5 text-xs text-slate-500">Ex. : Chemise, pantalon, robe…</p>
                </div>
                <form method="POST" action="{{ route('types-articles.store') }}" class="space-y-4 p-5">
                    @csrf
                    <div>
                        <label for="nom_type_article" class="mb-1.5 block text-xs font-semibold text-slate-700">Nom de l’article <span class="text-rose-500">*</span></label>
                        <input type="text" id="nom_type_article" name="nom_type_article" value="{{ old('nom_type_article') }}"
                               maxlength="50" required
                               class="form-input w-full rounded-xl border-slate-200 text-sm @error('nom_type_article') border-rose-400 @enderror"
                               placeholder="Ex. : Chemise">
                        @error('nom_type_article')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-500 px-5 py-2.5 text-sm font-bold text-white shadow-md shadow-blue-500/25 transition hover:bg-blue-600">
                        + Ajouter le type d’article
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     ONGLET 3 : SERVICES & TARIFS
     ═══════════════════════════════════════════════════════════════ --}}
<div role="tabpanel" id="panel-services" aria-labelledby="tab-services"
     class="tab-panel" @if($ongletInitial !== 'services') hidden @endif>
    <div class="grid lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2 space-y-6">

            <div class="flex flex-col gap-3 rounded-2xl border border-blue-200 bg-blue-50/70 p-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-sm font-bold text-blue-900">Tarifs de test</h3>
                    <p class="mt-1 text-xs text-blue-800">Préremplit les tarifs de chaque article pour tous les services et remplace les tarifs actuels.</p>
                </div>
                <form method="POST" action="{{ route('tarifs.pre-remplir') }}"
                      onsubmit="return confirm('Remplacer tous les tarifs existants par des tarifs de test ? Vous pourrez ensuite les modifier un par un.')">
                    @csrf
                    <button type="submit" @disabled($typesArticles->isEmpty() || $services->isEmpty())
                            class="w-full whitespace-nowrap rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-bold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto">
                        Préremplir les tarifs
                    </button>
                </form>
            </div>

            @foreach($services as $service)
            <div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
                {{-- En-tête du service --}}
                <div class="card-header px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-slate-800">{{ $service->nom }}</h3>
                        @if($service->description)
                            <p class="text-xs text-slate-500 mt-0.5">{{ $service->description }}</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        <button onclick="document.getElementById('edit-service-{{ $service->id }}').classList.toggle('hidden')"
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-semibold text-slate-700 transition">
                            ✎ Modifier
                        </button>
                        <form method="POST" action="{{ route('services.destroy', $service) }}"
                              onsubmit="return confirm('Supprimer ce service et tous ses tarifs ?')">
                            @csrf @method('DELETE')
                            <button class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-xs font-semibold text-rose-600 transition">
                                🗑
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Formulaire inline de modification du service --}}
                <div id="edit-service-{{ $service->id }}" class="hidden px-6 py-4 bg-amber-50/50 border-b border-amber-100">
                    <form method="POST" action="{{ route('services.update', $service) }}" class="flex items-end gap-3">
                        @csrf @method('PUT')
                        <div class="flex-1">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Nom du service</label>
                            <input type="text" name="nom" value="{{ $service->nom }}"
                                   class="form-input w-full rounded-xl border-slate-200 text-sm" required>
                        </div>
                        <div class="flex-1">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Description</label>
                            <input type="text" name="description" value="{{ $service->description }}"
                                   class="form-input w-full rounded-xl border-slate-200 text-sm" placeholder="Optionnel">
                        </div>
                        <button type="submit"
                                class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-xs font-bold text-white transition whitespace-nowrap">
                            Enregistrer
                        </button>
                    </form>
                </div>

                {{-- Tableau des tarifs --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 text-slate-400 uppercase text-[11px] font-bold tracking-wider border-b border-slate-200/60">
                                <th class="py-3 px-6">Type d'article</th>
                                <th class="py-3 px-6 text-right">Prix normal</th>
                                <th class="py-3 px-6 text-right">Prix express</th>
                                <th class="py-3 px-6 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse($service->tarifs as $tarif)
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="py-3 px-6 font-medium text-slate-800">{{ $tarif->typeArticle?->nom }}</td>
                                <td class="py-3 px-6 text-right font-semibold text-slate-700">{{ number_format((int)$tarif->prix, 0, ',', ' ') }} FCFA</td>
                                <td class="py-3 px-6 text-right text-slate-500">{{ number_format((int)$tarif->prix_express, 0, ',', ' ') }} FCFA</td>
                                <td class="py-3 px-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button" onclick="document.getElementById('edit-tarif-{{ $tarif->id }}').classList.toggle('hidden')"
                                                class="inline-flex items-center rounded-lg bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700 transition hover:bg-amber-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-600">
                                            Modifier
                                        </button>
                                        <form method="POST" action="{{ route('tarifs.destroy', $tarif) }}"
                                              onsubmit="return confirm('Supprimer ce tarif ?')">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center rounded-lg bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-600 transition hover:bg-rose-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-rose-600">
                                                Supprimer
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            {{-- Formulaire inline de modification de tarif --}}
                            <tr id="edit-tarif-{{ $tarif->id }}" class="hidden bg-amber-50/30">
                                <td class="px-6 py-3 text-xs text-slate-500 font-medium">{{ $tarif->typeArticle?->nom }}</td>
                                <td class="px-6 py-3" colspan="3">
                                    <form method="POST" action="{{ route('tarifs.update', $tarif) }}" class="flex items-end gap-3">
                                        @csrf @method('PUT')
                                        <div>
                                            <label class="block text-[11px] font-semibold text-slate-500 mb-1">Prix normal (FCFA)</label>
                                            <input type="number" name="prix" value="{{ (int)$tarif->prix }}" min="0"
                                                   class="form-input rounded-xl border-slate-200 text-sm w-32" required>
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-slate-500 mb-1">Prix express (FCFA)</label>
                                            <input type="number" name="prix_express" value="{{ (int)$tarif->prix_express }}" min="0"
                                                   class="form-input rounded-xl border-slate-200 text-sm w-32">
                                        </div>
                                        <button type="submit"
                                                class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-xs font-bold text-white transition">
                                            ✔ Sauver
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-6 text-center text-xs text-slate-400">Aucun tarif configuré pour ce service.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Ajouter un tarif --}}
                <div class="px-6 py-4 bg-slate-50/40 border-t border-slate-100">
                    <details class="group">
                        <summary class="cursor-pointer text-xs font-semibold text-amber-600 hover:text-amber-700 list-none flex items-center gap-1">
                            <span class="group-open:hidden">+ Ajouter un tarif pour ce service</span>
                            <span class="hidden group-open:inline">− Fermer</span>
                        </summary>
                        <form method="POST" action="{{ route('tarifs.store') }}" class="mt-4 flex flex-wrap items-end gap-3">
                            @csrf
                            <input type="hidden" name="service_id" value="{{ $service->id }}">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Type d'article</label>
                                <select name="type_article_id" class="form-select rounded-xl border-slate-200 text-sm" required>
                                    <option value="">-- Choisir --</option>
                                    @foreach($typesArticles as $ta)
                                        <option value="{{ $ta->id }}">{{ $ta->nom }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Prix normal (FCFA)</label>
                                <input type="number" name="prix" min="0" placeholder="0"
                                       class="form-input rounded-xl border-slate-200 text-sm w-28" required>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Prix express (FCFA)</label>
                                <input type="number" name="prix_express" min="0" placeholder="0"
                                       class="form-input rounded-xl border-slate-200 text-sm w-28">
                            </div>
                            <button type="submit"
                                    class="px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-xs font-bold text-white transition shadow-xs">
                                Enregistrer
                            </button>
                        </form>
                    </details>
                </div>
            </div>
            @endforeach

            @if($services->isEmpty())
                <div class="card p-12 rounded-2xl border border-slate-200/80 bg-white shadow-xs text-center">
                    <p class="text-slate-400 text-sm mb-1">Aucun service configuré.</p>
                    <p class="text-xs text-slate-400">Utilisez le panneau à droite pour créer votre premier service.</p>
                </div>
            @endif
        </div>

        {{-- Formulaire : nouveau service --}}
        <div class="lg:sticky lg:top-24 lg:self-start">
            <div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
                <div class="h-1.5 bg-gradient-to-r from-amber-400 to-amber-500"></div>
                <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="font-bold text-slate-800 text-sm">Nouveau service</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Ex: Lavage, Repassage, Nettoyage à sec…</p>
                </div>
                <form method="POST" action="{{ route('services.store') }}" class="p-5 space-y-4">
                    @csrf
                    <div>
                        <label for="nom" class="block text-xs font-semibold text-slate-700 mb-1.5">Nom du service <span class="text-rose-500">*</span></label>
                        <input type="text" id="nom" name="nom" value="{{ old('nom') }}"
                               class="form-input w-full rounded-xl border-slate-200 text-sm @error('nom') border-rose-400 @enderror"
                               required placeholder="Ex: Lavage simple">
                        @error('nom')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="description" class="block text-xs font-semibold text-slate-700 mb-1.5">Description</label>
                        <textarea id="description" name="description" rows="2"
                                  class="form-input w-full rounded-xl border-slate-200 text-sm resize-none"
                                  placeholder="Optionnel">{{ old('description') }}</textarea>
                    </div>
                    <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-amber-500 hover:bg-amber-600 px-5 py-2.5 text-sm font-bold text-white shadow-md shadow-amber-500/25 transition">
                        + Créer le service
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ═══ Logique des onglets ═══ --}}
<script>
(() => {
    const tabs   = [...document.querySelectorAll('[role="tab"]')];
    const panels = Object.fromEntries(tabs.map(t => [t.dataset.tab, document.getElementById(t.getAttribute('aria-controls'))]));
    const KEY    = 'services-tarifs-onglet';
    const alias  = { 'categories-produits': 'categories' };   // anciens liens #categories-produits
    const erreurServeur = @json($ongletErreur);

    function activer(nom, { focus = false, memoriser = true } = {}) {
        if (!panels[nom]) return;
        tabs.forEach(t => {
            const actif = t.dataset.tab === nom;
            t.setAttribute('aria-selected', String(actif));
            t.tabIndex = actif ? 0 : -1;
            panels[t.dataset.tab].hidden = !actif;
            if (actif && focus) t.focus();
        });
        if (memoriser) {
            try { sessionStorage.setItem(KEY, nom); } catch (e) {}
            history.replaceState(null, '', '#' + nom);
        }
    }

    tabs.forEach((tab, i) => {
        tab.addEventListener('click', () => activer(tab.dataset.tab));
        tab.addEventListener('keydown', e => {
            const cible = { ArrowRight: (i + 1) % tabs.length, ArrowLeft: (i - 1 + tabs.length) % tabs.length, Home: 0, End: tabs.length - 1 }[e.key];
            if (cible !== undefined) { e.preventDefault(); activer(tabs[cible].dataset.tab, { focus: true }); }
        });
    });

    // Priorité : erreur de validation > lien (#hash) > dernier onglet utilisé > défaut
    const hash = alias[location.hash.slice(1)] ?? location.hash.slice(1);
    let memo = null;
    try { memo = sessionStorage.getItem(KEY); } catch (e) {}
    const depart = erreurServeur ?? (panels[hash] ? hash : (panels[memo] ? memo : 'services'));
    activer(depart, { memoriser: !erreurServeur });

    // Lien interne vers #categories-produits clic alors qu'on est déjà sur la page
    window.addEventListener('hashchange', () => {
        const h = alias[location.hash.slice(1)] ?? location.hash.slice(1);
        if (panels[h]) activer(h, { memoriser: false });
    });
})();
</script>

@endsection