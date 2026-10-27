@extends('layouts.app')

@section('title', 'Services & Tarifs')
@section('page-title', 'Services & Tarifs')
@section('page-subtitle', 'Gérer les prestations et leur tarification par type d\'article')

@section('content')

<div class="grid lg:grid-cols-3 gap-6">

    {{-- ===== COLONNE GAUCHE : Liste des services ===== --}}
    <div class="lg:col-span-2 space-y-6">

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
                    {{-- Modifier le service --}}
                    <button onclick="document.getElementById('edit-service-{{ $service->id }}').classList.toggle('hidden')"
                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-semibold text-slate-700 transition">
                        ✎ Modifier
                    </button>
                    {{-- Supprimer le service --}}
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
                                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition">
                                    <button onclick="document.getElementById('edit-tarif-{{ $tarif->id }}').classList.toggle('hidden')"
                                            class="text-xs font-semibold text-amber-600 hover:underline">Modifier</button>
                                    <form method="POST" action="{{ route('tarifs.destroy', $tarif) }}"
                                          onsubmit="return confirm('Supprimer ce tarif ?')">
                                        @csrf @method('DELETE')
                                        <button class="text-xs font-semibold text-rose-500 hover:underline">Supprimer</button>
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

    {{-- ===== COLONNE DROITE : Ajouter un service ===== --}}
    <div>
        <div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm sticky top-24">
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
@endsection
