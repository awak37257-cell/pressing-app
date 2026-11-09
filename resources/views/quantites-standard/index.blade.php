@extends('layouts.app')

@section('title', 'Consommations de produits')
@section('page-title', 'Consommations de produits')
@section('page-subtitle', 'Historique des produits et quantités réellement déclarés par les opérateurs')

@section('content')

<div class="space-y-6">
    <div class="rounded-2xl border border-violet-200 bg-gradient-to-r from-violet-50 to-white p-5 shadow-sm">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-100 text-violet-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2m-4 0a2 2 0 002 2h2m-4 0a2 2 0 00-2-2h-2m-1 9h6m-6 4h6"/>
                </svg>
            </div>
            <div>
                <h2 class="font-bold text-slate-900">Quantités réellement utilisées</h2>
                <p class="mt-1 text-sm leading-relaxed text-slate-600">
                    Les lignes apparaissent ici dès qu’un opérateur valide les produits consommés pour un dépôt.
                    Les quantités sont saisies au moment de l’opération : aucune dose standard n’est à définir à l’avance.
                </p>
            </div>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Déclarations enregistrées</p>
            <p class="mt-2 text-3xl font-extrabold text-slate-900">{{ $statistiques->nombre_lignes }}</p>
            <p class="mt-1 text-xs text-slate-500">Lignes de consommation validées</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Produits utilisés</p>
            <p class="mt-2 text-3xl font-extrabold text-violet-700">{{ $statistiques->nombre_produits }}</p>
            <p class="mt-1 text-xs text-slate-500">Produits distincts dans l’historique</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Coût total estimé</p>
            <p class="mt-2 text-3xl font-extrabold text-emerald-700">{{ number_format($statistiques->cout_total, 0, ',', ' ') }}</p>
            <p class="mt-1 text-xs text-slate-500">FCFA, calculé au coût enregistré lors de la déclaration</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="font-bold text-slate-900">Dernières consommations</h2>
            <p class="mt-1 text-xs text-slate-500">Les opérations les plus récentes apparaissent en premier.</p>
        </div>

        @if($consommations->isEmpty())
            <div class="flex flex-col items-center px-6 py-14 text-center">
                <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2m-4 0a2 2 0 012-2m-3 9h6m-6 4h6"/>
                    </svg>
                </span>
                <p class="mt-4 font-semibold text-slate-700">Aucune consommation enregistrée</p>
                <p class="mt-1 max-w-md text-sm text-slate-500">Les quantités validées par les opérateurs seront affichées ici, avec le dépôt et le produit concernés.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-left">
                    <thead>
                        <tr class="bg-slate-50 text-[10px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="px-5 py-3">Date</th>
                            <th class="px-5 py-3">Dépôt / client</th>
                            <th class="px-5 py-3">Produit</th>
                            <th class="px-5 py-3 text-right">Quantité utilisée</th>
                            <th class="px-5 py-3 text-right">Coût estimé</th>
                            <th class="px-5 py-3">Déclaré par</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @foreach($consommations as $consommation)
                            <tr class="transition hover:bg-violet-50/40">
                                <td class="whitespace-nowrap px-5 py-4 text-slate-600">
                                    {{ $consommation->created_at?->format('d/m/Y H:i') ?? '—' }}
                                </td>
                                <td class="px-5 py-4">
                                    <a href="{{ route('depots.show', $consommation->depot) }}" class="font-bold text-amber-700 hover:text-amber-800">
                                        {{ $consommation->depot?->numero_ticket ?? 'Dépôt indisponible' }}
                                    </a>
                                    <p class="mt-0.5 text-xs text-slate-500">
                                        {{ $consommation->depot?->client?->nom ?? 'Client inconnu' }}
                                        {{ $consommation->depot?->client?->prenom }}
                                    </p>
                                </td>
                                <td class="px-5 py-4 font-semibold text-slate-800">
                                    {{ $consommation->produit?->nom ?? 'Produit supprimé' }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-right font-bold text-violet-700">
                                    {{ $consommation->quantite + 0 }}
                                    <span class="text-xs font-medium text-slate-500">{{ $consommation->produit?->unite }}</span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-right font-semibold text-slate-700">
                                    {{ number_format($consommation->cout, 0, ',', ' ') }} FCFA
                                </td>
                                <td class="px-5 py-4 text-slate-600">{{ $consommation->user?->nom ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($consommations->hasPages())
                <div class="border-t border-slate-100 px-5 py-4">
                    {{ $consommations->links() }}
                </div>
            @endif
        @endif
    </div>
</div>

@endsection
