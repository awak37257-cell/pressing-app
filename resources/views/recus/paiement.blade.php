<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reçu R-{{ str_pad($paiement->id, 6, '0', STR_PAD_LEFT) }} — PressManager</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: "Courier New", ui-monospace, monospace; font-size: 12px; color: #0f172a; background: #f1f5f9; }
        .ticket {
            width: 80mm; margin: 24px auto; padding: 6mm 5mm;
            background: #fff;
            box-shadow: 0 20px 40px -12px rgba(15,23,42,.15), 0 0 0 1px rgba(15,23,42,.04);
            border-radius: 12px;
        }
        .sep { border-top: 1px dashed #cbd5e1; margin: 10px 0; }
        .sep-thick { border-top: 2px dashed #0f172a; margin: 10px 0; }
        @page { size: 80mm auto; margin: 0; }
        @media print {
            body { background: #fff; }
            .ticket { box-shadow: none; margin: 0; width: 80mm; border-radius: 0; padding: 4mm; }
            .no-print { display: none !important; }
        }
        .barcode {
            font-family: "Libre Barcode 39", "Courier New", monospace;
            letter-spacing: 2px;
            font-size: 22px;
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Barcode+39&display=swap" rel="stylesheet">
</head>
<body class="py-6">

{{-- ══════ BARRE D'ACTIONS ══════ --}}
<div class="no-print flex flex-col sm:flex-row items-center justify-center gap-2.5 mb-6 px-4">
    <button type="button" onclick="window.print()"
            class="inline-flex items-center gap-2 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-bold px-5 py-2.5 rounded-xl text-sm shadow-lg shadow-amber-500/30 hover:shadow-xl transition-all duration-200 hover:-translate-y-0.5 active:scale-95">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
        </svg>
        Imprimer le reçu
    </button>
    <a href="{{ route('depots.show', $depot) }}"
       class="inline-flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white font-semibold px-5 py-2.5 rounded-xl text-sm shadow-lg shadow-slate-900/20 transition-all duration-200 hover:-translate-y-0.5 active:scale-95">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Retour au dépôt
    </a>
</div>

{{-- ══════ TICKET THERMIQUE 80mm ══════ --}}
<div class="ticket">

    {{-- En-tête établissement --}}
    <div class="text-center mb-3">
        <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 shadow-lg shadow-amber-500/30 mb-2">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
            </svg>
        </div>
        <h1 class="text-sm font-extrabold uppercase tracking-wider text-slate-900 leading-tight">{{ $pressing?->nom ?? config('app.name') }}</h1>
        @if($pressing?->adresse)
            <p class="text-[10px] text-slate-500 mt-0.5 leading-tight">{{ $pressing->adresse }}</p>
        @endif
        @if($pressing?->telephone)
            <p class="text-[10px] text-slate-500">Tél : {{ $pressing->telephone }}</p>
        @endif
    </div>

    <div class="sep-thick"></div>

    {{-- Titre reçu --}}
    <div class="text-center my-2">
        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-[0.15em]">Reçu de paiement</p>
        <p class="text-base font-black text-slate-900 tracking-tight my-0.5">R-{{ str_pad($paiement->id, 6, '0', STR_PAD_LEFT) }}</p>
        <p class="text-[10px] text-slate-500">{{ $paiement->date_paiement?->format('d/m/Y à H:i') }}</p>
    </div>

    <div class="sep"></div>

    {{-- Infos --}}
    <table class="w-full text-xs">
        <tr>
            <td class="py-1 text-slate-500 font-medium">Ticket</td>
            <td class="py-1 text-right font-bold text-amber-600">{{ $depot->numero_ticket }}</td>
        </tr>
        <tr>
            <td class="py-1 text-slate-500 font-medium">Client</td>
            <td class="py-1 text-right font-bold text-slate-800 truncate max-w-[120px]">{{ $depot->client?->nom }} {{ $depot->client?->prenom }}</td>
        </tr>
        <tr>
            <td class="py-1 text-slate-500 font-medium">Mode</td>
            <td class="py-1 text-right font-semibold text-slate-700 capitalize">{{ str_replace('_', ' ', $paiement->mode) }}</td>
        </tr>
        @if($paiement->reference)
        <tr>
            <td class="py-1 text-slate-500 font-medium">Réf.</td>
            <td class="py-1 text-right font-mono text-[10px] text-slate-700 truncate max-w-[120px]">{{ $paiement->reference }}</td>
        </tr>
        @endif
    </table>

    <div class="sep"></div>

    {{-- Montant principal (hero) --}}
    <div class="rounded-lg bg-gradient-to-r from-emerald-500 to-teal-600 text-white p-3 my-2 shadow-lg shadow-emerald-500/30">
        <div class="flex items-center justify-between gap-2">
            <div>
                <p class="text-[9px] font-bold text-white/80 uppercase tracking-[0.15em] leading-tight">Montant reçu</p>
                <p class="text-lg font-black tracking-tight">{{ number_format($paiement->montant, 0, ',', ' ') }}</p>
            </div>
            <span class="text-[10px] font-extrabold bg-white/20 px-2 py-1 rounded-md">FCFA</span>
        </div>
    </div>

    {{-- Détail financier --}}
    <table class="w-full text-xs">
        <tr>
            <td class="py-1 text-slate-500 font-medium">Total du dépôt</td>
            <td class="py-1 text-right font-semibold text-slate-700">{{ number_format($depot->montant_total, 0, ',', ' ') }} FCFA</td>
        </tr>
        <tr class="border-t border-slate-200">
            <td class="py-1.5 font-bold text-slate-900 text-[11px]">RESTE À PAYER</td>
            <td class="py-1.5 text-right font-extrabold text-[12px] {{ $reste > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                {{ number_format($reste, 0, ',', ' ') }} FCFA
            </td>
        </tr>
    </table>

    <div class="sep"></div>

    {{-- Barcode visuel --}}
    <div class="text-center my-2">
        <p class="barcode text-slate-800 tracking-wider">R{{ str_pad($paiement->id, 8, '0', STR_PAD_LEFT) }}</p>
        <p class="text-[8px] text-slate-400 tracking-widest uppercase mt-0.5">Scan · Réf. paiement</p>
    </div>

    <div class="sep"></div>

    {{-- Footer --}}
    <div class="text-center text-[10px] text-slate-500 space-y-0.5 pt-1">
        <p>Encaissé par : <span class="font-bold text-slate-700">{{ $paiement->user?->nom ?? '—' }}</span></p>
        <p class="font-bold text-slate-900 pt-1.5 text-xs">Merci de votre confiance !</p>
        <p class="text-[9px] text-slate-400 pt-0.5">Document non contractuel</p>
    </div>
</div>

@if(request()->boolean('imprimer'))
    <script>window.addEventListener('load', () => setTimeout(() => window.print(), 300));</script>
@endif
</body>
</html>