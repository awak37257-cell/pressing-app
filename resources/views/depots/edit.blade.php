@extends('layouts.app')

@php
    $modes = ['especes' => 'Espèces', 'orange_money' => 'Orange Money', 'mtn_money' => 'MTN Money', 'wave' => 'Wave'];
    $reste = (int) $depot->reste_a_payer;
@endphp

@section('title', 'Dépôt ' . $depot->numero_ticket)
@section('page-title', 'Dépôt ' . $depot->numero_ticket)
@section('page-subtitle', 'Encaissement du solde et modification des informations')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <div class="h-1.5 bg-gradient-to-r from-amber-400 to-amber-500"></div>
        <div class="card-header px-6 py-4.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div>
                <h3 class="font-bold text-slate-800 text-base">Modifier le dépôt</h3>
                <p class="text-xs text-slate-500 mt-0.5">Mettez à jour la date, les observations ou encaissez le reliquat.</p>
            </div>
            <a href="{{ route('depots.show', $depot) }}"
               class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 px-3 py-2 rounded-xl transition shadow-2xs">
                ← Retour à la fiche
            </a>
        </div>

        <div class="card-body p-6">
            <form method="POST" action="{{ route('depots.update', $depot) }}" class="space-y-6">
                @csrf @method('PUT')

                @if($errors->any())
                    <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-sm flex items-start gap-3 shadow-2xs">
                        <svg class="h-5 w-5 shrink-0 text-rose-500 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <p class="font-semibold">Veuillez corriger les erreurs ci-dessous</p>
                            <ul class="mt-1 list-disc list-inside space-y-0.5 text-rose-600 text-xs">
                                @foreach($errors->all() as $e)
                                    <li>{{ $e }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/60 text-xs text-slate-600 flex items-center justify-between gap-4">
                    <span>💡 Pour changer le statut du dépôt (commencer, marquer prêt, remettre au client), utilisez directement les actions situées sur la fiche du dépôt.</span>
                    <a href="{{ route('depots.show', $depot) }}" class="font-bold text-amber-600 hover:underline shrink-0">Voir la fiche →</a>
                </div>

                <div class="grid sm:grid-cols-2 gap-5">
                    <div>
                        <x-field name="date_retrait_prevue" label="Retrait prévu" type="date" :value="$depot->date_retrait_prevue?->format('Y-m-d')" />
                    </div>
                </div>

                @if($reste > 0 && $depot->statut !== 'annule')
                    <div class="p-5 bg-amber-50/60 rounded-2xl border border-amber-200/80 space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-bold text-amber-900">Reste à payer actuel</span>
                            <span class="text-lg font-extrabold text-amber-700">{{ number_format($reste, 0, ',', ' ') }} FCFA</span>
                        </div>
                        
                        <div class="grid sm:grid-cols-3 gap-4">
                            <div>
                                <x-field name="montant_paye_ajout" label="Montant encaissé" type="number" min="0" max="{{ $reste }}" />
                            </div>
                            <div>
                                <label for="mode_paiement" class="form-label font-semibold text-xs text-slate-700 mb-1 block">Mode de paiement</label>
                                <select id="mode_paiement" name="mode_paiement" class="form-select w-full rounded-xl border-amber-200 bg-white text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                                    @foreach($modes as $k => $v)
                                        <option value="{{ $k }}" @selected(old('mode_paiement') === $k)>{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-field name="reference" label="Référence (mobile)" placeholder="Ex: MP24..." />
                            </div>
                        </div>
                    </div>
                @endif

                <div>
                    <label for="observations" class="form-label font-semibold text-xs text-slate-700 mb-1.5 block">Observations</label>
                    <textarea id="observations" name="observations" rows="3" class="form-input w-full rounded-xl border-slate-200 text-sm resize-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500" placeholder="Notes particulières...">{{ old('observations', $depot->observations) }}</textarea>
                </div>

                <div class="flex items-center gap-3 pt-4 border-t border-slate-100">
                    <button type="submit" class="inline-flex items-center gap-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-bold py-2.5 px-5 rounded-xl shadow-md shadow-amber-500/20 text-sm transition">
                        Enregistrer les modifications
                    </button>
                    <a href="{{ route('depots.show', $depot) }}" class="inline-flex items-center justify-center bg-white hover:bg-slate-50 text-slate-600 border border-slate-200 font-semibold py-2.5 px-4 rounded-xl text-sm transition">
                        Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection