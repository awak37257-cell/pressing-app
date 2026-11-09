@extends('layouts.app')

@section('title', 'Articles prêts')
@section('page-title', 'Articles prêts')
@section('page-subtitle', 'Retrouver un ticket, encaisser le solde et confirmer la remise')

@section('content')
@php
    $modes = [
        'especes'      => 'Espèces',
        'orange_money' => 'Orange Money',
        'mtn_money'    => 'MTN Money',
        'wave'         => 'Wave',
    ];
    $f = fn ($n) => number_format((int) $n, 0, ',', ' ');
@endphp

<style>
    @keyframes rise { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: none; } }
    .reveal { opacity: 0; animation: rise .5s cubic-bezier(.2,.7,.2,1) forwards; animation-delay: calc(var(--i, 0) * 70ms); }
    .pret-card { transition: background-color .2s; }
    .pret-card:hover { background-color: rgb(248 250 252 / .7); }
    .btn-loading { opacity: .7; pointer-events: none; }
    @media (prefers-reduced-motion: reduce) { .reveal { animation: none; opacity: 1; } }
</style>

<div class="mx-auto max-w-6xl space-y-6">

    {{-- ═══ RECHERCHE / SCAN ═══ --}}
    <section class="reveal rounded-2xl border border-emerald-200 bg-gradient-to-br from-emerald-50 via-white to-teal-50 p-5 shadow-sm sm:p-6" style="--i:0">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-start gap-3">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-md shadow-emerald-600/25">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                    </svg>
                </span>
                <div>
                    <h2 class="text-lg font-extrabold text-slate-900">Retrait client</h2>
                    <p class="mt-0.5 text-sm text-slate-600">Scannez ou saisissez le numéro du ticket pour retrouver les articles prêts.</p>
                </div>
            </div>

            <form method="GET" action="{{ route('depots.prets') }}" class="flex w-full gap-2 lg:max-w-md">
                <label for="scan-ticket-pret" class="sr-only">Numéro du ticket à retirer</label>
                <input id="scan-ticket-pret" type="text" name="scan_ticket" value="{{ $numeroTicket }}"
                       placeholder="Ex. TK-20261104-0010" autocomplete="off" autofocus required maxlength="50"
                       class="min-w-0 flex-1 rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-800 placeholder:font-normal placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                <button type="submit" class="inline-flex shrink-0 items-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700 hover:-translate-y-px active:translate-y-0">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m20 20-4-4"/>
                    </svg>
                    <span class="hidden sm:inline">Rechercher</span>
                </button>
                @if($numeroTicket !== null)
                    <a href="{{ route('depots.prets') }}" title="Afficher tous les dépôts prêts"
                       class="inline-flex shrink-0 items-center rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                        ✕
                    </a>
                @endif
            </form>
        </div>

        <p class="mt-4 flex items-start gap-2 rounded-xl bg-white/80 px-4 py-3 text-xs leading-relaxed text-slate-600">
            <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Le reçu de paiement final s’ouvre automatiquement lorsque le solde est réglé. La remise des articles reste à confirmer séparément après leur remise physique au client.</span>
        </p>
    </section>

    {{-- ═══ ERREURS DE PAIEMENT ═══ --}}
    @if($errors->any())
        <div role="alert" class="reveal rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800" style="--i:1">
            <p class="font-bold">Vérifiez les informations de paiement.</p>
            <ul class="mt-1 list-inside list-disc">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- (Le message de succès est déjà affiché par le layout) --}}

    @if($numeroTicket !== null && $depots->isEmpty())
        <div role="status" class="reveal rounded-2xl border border-amber-200 bg-amber-50 p-8 text-center" style="--i:1">
            <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 8v3m0 3h.01M19 11a8 8 0 11-16 0 8 8 0 0116 0z"/>
                </svg>
            </span>
            <p class="mt-3 font-bold text-amber-900">Aucun dépôt prêt ne correspond à ce ticket.</p>
            <p class="mt-1 text-sm text-amber-800">Vérifiez le numéro ou assurez-vous que l’opérateur a marqué le dépôt comme prêt.</p>
            <a href="{{ route('depots.prets') }}" class="mt-4 inline-block text-sm font-semibold text-amber-700 hover:underline">Voir tous les dépôts prêts →</a>
        </div>
    @else

        {{-- ═══ LISTE ═══ --}}
        <section class="reveal overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" style="--i:1">
            <div class="flex flex-col gap-1 border-b border-slate-100 bg-slate-50/60 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="font-bold text-slate-900">
                        {{ $numeroTicket !== null ? 'Résultat de la recherche' : 'En attente de remise' }}
                    </h3>
                    <p class="text-xs text-slate-500">Encaissez le solde, puis confirmez la remise au client.</p>
                </div>
                <span class="inline-flex w-fit items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                    {{ $depots->total() }} dépôt(s) prêt(s)
                </span>
            </div>

            @forelse($depots as $depot)
                @php
                    $reste  = (int) $depot->reste_a_payer;
                    $solde  = $reste <= 0;
                    $trouve = $numeroTicket !== null;
                @endphp
                <article class="pret-card relative border-b border-slate-100 p-5 last:border-b-0 {{ $trouve ? 'bg-emerald-50/40' : '' }}">
                    <span class="absolute inset-y-0 left-0 w-1 {{ $solde ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>

                    <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">

                        {{-- Client + ticket --}}
                        <div class="flex min-w-0 items-start gap-3 lg:w-1/3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-amber-100 to-orange-200 text-sm font-extrabold uppercase text-amber-700">
                                {{ mb_substr($depot->client?->nom ?? '?', 0, 1) }}
                            </span>
                            <div class="min-w-0">
                                <a href="{{ route('depots.show', $depot) }}" class="font-mono text-sm font-extrabold text-emerald-700 hover:underline">
                                    {{ $depot->numero_ticket }}
                                </a>
                                <p class="mt-0.5 truncate font-semibold text-slate-800">{{ $depot->client?->nom ?? 'Client inconnu' }}</p>
                                @if($depot->client?->telephone)
                                    <p class="mt-0.5 text-xs text-slate-500">{{ $depot->client->telephone }}</p>
                                    @php
                                        $messagePret = "Bonjour {$depot->client->nom}, votre linge (ticket {$depot->numero_ticket}) est prêt à être retiré chez "
                                            .($depot->pressing?->nom ?? 'notre pressing').'. '
                                            .($reste > 0
                                                ? 'Il vous reste à payer : '.number_format($reste, 0, ',', ' ').' FCFA. '
                                                : 'Votre solde est réglé. ')
                                            .'Merci de passer le récupérer.'
                                            .\App\Support\Whatsapp::articleIncidentNotice($depot->articles);
                                        $whatsappPret = \App\Support\Whatsapp::url($depot->client->telephone, $messagePret);
                                    @endphp
                                    @if($whatsappPret)
                                        <a href="{{ $whatsappPret }}" target="_blank" rel="noopener"
                                           class="mt-2 inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 ring-1 ring-inset ring-emerald-600/20 transition hover:bg-emerald-100">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 01-4-.8L3 20l1.3-3.9A7.5 7.5 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                            </svg>
                                            Prévenir par WhatsApp
                                        </a>
                                    @endif
                                @endif
                            </div>
                        </div>

                        {{-- Solde --}}
                        <div class="rounded-xl px-4 py-3 lg:min-w-44 lg:text-right {{ $solde ? 'bg-emerald-50' : 'bg-slate-50' }}">
                            <p class="text-[11px] font-bold uppercase tracking-wide text-slate-500">Reste à payer</p>
                            <p class="mt-0.5 text-lg font-extrabold tabular-nums {{ $solde ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $f($reste) }} FCFA
                            </p>
                            @if($solde)
                                <p class="text-[11px] font-bold text-emerald-600">✓ Soldé</p>
                            @endif
                        </div>

                        {{-- Action : encaisser OU confirmer la remise --}}
                        @if(! $solde)
                            <form method="POST" action="{{ route('depots.prets.encaisser', $depot) }}"
                                  class="form-encaisser grid w-full gap-3 sm:grid-cols-2 lg:max-w-xl lg:grid-cols-[1fr_1fr_auto]">
                                @csrf
                                <div>
                                    <label for="montant-{{ $depot->id }}" class="mb-1 block text-xs font-semibold text-slate-600">Montant reçu</label>
                                    <input id="montant-{{ $depot->id }}" type="number" name="montant_paye_ajout" min="1"
                                           max="{{ $reste }}" value="{{ $reste }}" required inputmode="numeric"
                                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm tabular-nums focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                                </div>
                                <div>
                                    <label for="mode-{{ $depot->id }}" class="mb-1 block text-xs font-semibold text-slate-600">Mode</label>
                                    <select id="mode-{{ $depot->id }}" name="mode_paiement" required data-mode-select
                                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                                        @foreach($modes as $mode => $label)
                                            <option value="{{ $mode }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" data-label="Encaisser"
                                        class="inline-flex items-center justify-center gap-2 self-end rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700 sm:col-span-2 lg:col-span-1">
                                    Encaisser
                                </button>

                                {{-- Référence : visible seulement pour les paiements mobiles --}}
                                <div data-reference-wrap hidden class="sm:col-span-2 lg:col-span-3">
                                    <label for="reference-{{ $depot->id }}" class="mb-1 block text-xs font-semibold text-slate-600">Référence de la transaction mobile</label>
                                    <input id="reference-{{ $depot->id }}" type="text" name="reference" maxlength="100" placeholder="Ex. numéro de transaction" disabled
                                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                                </div>
                            </form>
                        @else
                            <form method="POST" action="{{ route('depots.retirer', $depot) }}" class="form-remise w-full lg:w-auto"
                                  data-confirm="Confirmer que les articles du ticket {{ $depot->numero_ticket }} ont bien été remis au client ?">
                                @csrf
                                <button type="submit" data-label="Confirmer la remise"
                                        class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-slate-700 hover:-translate-y-px active:translate-y-0 lg:w-auto">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Confirmer la remise
                                </button>
                            </form>
                        @endif
                    </div>
                </article>
            @empty
                <div class="px-5 py-14 text-center">
                    <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </span>
                    <p class="mt-3 text-sm font-bold text-slate-700">Rien à remettre pour le moment</p>
                    <p class="mt-0.5 text-xs text-slate-500">Aucun dépôt n’attend actuellement d’être remis.</p>
                </div>
            @endforelse
        </section>

        <div>{{ $depots->withQueryString()->links() }}</div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {

    // Référence mobile : affichée (et envoyée) seulement si le mode n'est pas « espèces »
    document.querySelectorAll('.form-encaisser').forEach(form => {
        const select = form.querySelector('[data-mode-select]');
        const wrap   = form.querySelector('[data-reference-wrap]');
        const input  = wrap.querySelector('input');
        const maj = () => {
            const mobile = select.value !== 'especes';
            wrap.hidden    = !mobile;
            input.disabled = !mobile;
        };
        select.addEventListener('change', maj);
        maj();
    });

    // Anti double-clic + confirmation avant la remise
    document.querySelectorAll('.form-encaisser, .form-remise').forEach(form => {
        form.addEventListener('submit', e => {
            const message = form.dataset.confirm;
            if (message && !confirm(message)) { e.preventDefault(); return; }
            const btn = form.querySelector('button[type="submit"]');
            btn.classList.add('btn-loading');
            btn.textContent = 'Traitement…';
        });
    });
});
</script>
@endsection