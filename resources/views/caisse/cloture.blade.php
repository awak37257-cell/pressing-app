@extends('layouts.app')

@section('title', 'Journal de Caisse & Clôture')
@section('page-title', 'Journal de Caisse')
@section('page-subtitle', 'Clôture journalière, suivi des flux et vérification des espèces')

@php
    $user         = auth()->user();
    $role         = strtolower(trim($user->role ?? ''));
    $gestionnaire = in_array($role, ['admin', 'gerant'], true);
    $f            = fn ($n) => number_format($n, 0, ',', ' ');
@endphp

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    {{-- Sélecteur de date et en-tête --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <h2 class="text-lg font-extrabold text-slate-900">Rapport de Caisse</h2>
            <p class="text-xs text-slate-500">Visualisez et clôturez les transactions financières d'une journée spécifique.</p>
        </div>
        <form method="GET" action="{{ route('caisse.cloture') }}" class="flex items-center gap-2">
            <input type="date" name="date" value="{{ $dateCible }}" max="{{ date('Y-m-d') }}"
                   class="px-3.5 py-2 rounded-xl border border-slate-200 text-sm font-semibold text-slate-700 focus:ring-amber-500">
            <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition">
                Filtrer
            </button>
        </form>
    </div>

    @if($clotureExistante)
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center font-bold">✓</span>
                <div>
                    <p class="text-xs font-bold uppercase tracking-wide">Caisse clôturée pour cette date</p>
                    <p class="text-xs text-emerald-700">Clôture effectuée par <span class="font-semibold">{{ $clotureExistante->user?->nom }}</span>. Écart enregistré : <strong class="{{ $clotureExistante->ecart < 0 ? 'text-rose-600' : 'text-emerald-700' }}">{{ $f($clotureExistante->ecart) }} FCFA</strong></p>
                </div>
            </div>
            <span class="text-xs font-mono bg-white/60 px-3 py-1 rounded-lg border border-emerald-300">
                Déclaré : {{ $f($clotureExistante->montant_physique_declare) }} FCFA
            </span>
        </div>
    @endif

    {{-- Grille des totaux par mode de paiement --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-1">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">💵 Espèces</span>
            <div class="text-2xl font-black text-slate-900">{{ $f($totauxParMode['especes']) }} <span class="text-xs font-normal text-slate-500">FCFA</span></div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-1">
            <span class="text-[11px] font-bold text-blue-500 uppercase tracking-wider">🌊 Wave</span>
            <div class="text-2xl font-black text-slate-900">{{ $f($totauxParMode['wave']) }} <span class="text-xs font-normal text-slate-500">FCFA</span></div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-1">
            <span class="text-[11px] font-bold text-orange-500 uppercase tracking-wider">📱 Orange Money</span>
            <div class="text-2xl font-black text-slate-900">{{ $f($totauxParMode['orange_money']) }} <span class="text-xs font-normal text-slate-500">FCFA</span></div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-1">
            <span class="text-[11px] font-bold text-amber-600 uppercase tracking-wider">📱 MTN Money</span>
            <div class="text-2xl font-black text-slate-900">{{ $f($totauxParMode['mtn_money']) }} <span class="text-xs font-normal text-slate-500">FCFA</span></div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-1 sm:col-span-2 lg:col-span-1 bg-gradient-to-br from-slate-900 to-slate-800 text-white">
            <span class="text-[11px] font-bold text-amber-400 uppercase tracking-wider">Total Général</span>
            <div class="text-2xl font-black">{{ $f($totalGeneral) }} <span class="text-xs font-normal text-slate-300">FCFA</span></div>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        
        {{-- Formulaire de clôture / Déclaration physique --}}
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
            <div>
                <h3 class="font-bold text-slate-900 text-sm">Effectuer / Modifier la Clôture</h3>
                <p class="text-xs text-slate-500">Comptez l'argent physique dans le tiroir-caisse (Espèces uniquement) et validez.</p>
            </div>

            <form method="POST" action="{{ route('caisse.cloture.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="date_cloture" value="{{ $dateCible }}">

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Montant espèces théorique attendu</label>
                    <input type="text" disabled value="{{ $f($totauxParMode['especes']) }} FCFA" 
                           class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm font-bold text-slate-600 cursor-not-allowed">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Montant physique compté en caisse (FCFA) *</label>
                    <input type="number" step="any" name="montant_physique_declare" required
                           value="{{ old('montant_physique_declare', $clotureExistante?->montant_physique_declare ?? $totauxParMode['especes']) }}"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm font-black text-emerald-700 focus:ring-amber-500"
                           placeholder="Ex: 45000">
                    <p class="text-[10px] text-slate-400 mt-1">Saisissez l'argent total présent physiquement dans le tiroir.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Commentaire ou observation (optionnel)</label>
                    <textarea name="commentaire" rows="2" placeholder="Ex: Manquant justifié par..."
                              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs resize-none">{{ old('commentaire', $clotureExistante?->commentaire) }}</textarea>
                </div>

                <button type="submit" class="w-full py-3 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-bold text-sm shadow-md transition-all active:scale-95">
                    Enregistrer la clôture
                </button>
            </form>
        </div>

        {{-- Liste détaillée des paiements du jour --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <h3 class="font-bold text-slate-900 text-sm">Transactions encaissées le {{ \Carbon\Carbon::parse($dateCible)->format('d/m/Y') }}</h3>
                <span class="text-xs font-semibold text-slate-500">{{ $paiementsJour->count() }} paiement(s)</span>
            </div>
            
            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-[10.5px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50/30">
                            <th class="py-3 px-6">Heure</th>
                            <th class="py-3 px-6">Ticket / Client</th>
                            <th class="py-3 px-6">Mode</th>
                            <th class="py-3 px-6">Caissier(ère)</th>
                            <th class="py-3 px-6 text-right">Montant</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse($paiementsJour as $p)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="py-3 px-6 text-xs text-slate-500 font-mono">{{ $p->date_paiement?->format('H:i') }}</td>
                                <td class="py-3 px-6">
                                    <a href="{{ route('depots.show', $p->depot_id) }}" class="font-bold text-amber-600 hover:underline">
                                        {{ $p->depot?->numero_ticket }}
                                    </a>
                                    <div class="text-[11px] text-slate-500">{{ $p->depot?->client?->nom }}</div>
                                </td>
                                <td class="py-3 px-6">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase bg-slate-100 text-slate-700">
                                        {{ str_replace('_', ' ', $p->mode) }}
                                    </span>
                                </td>
                                <td class="py-3 px-6 text-xs text-slate-600">{{ $p->user?->nom ?? '—' }}</td>
                                <td class="py-3 px-6 text-right font-extrabold text-emerald-600">{{ $f($p->montant) }} FCFA</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center text-slate-400 text-xs italic">Aucune transaction enregistrée à cette date.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>
@endsection