<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reçu R-{{ str_pad($paiement->id, 6, '0', STR_PAD_LEFT) }} — PressManager</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: "Courier New", ui-monospace, monospace; font-size: 12px; color: #1e293b; background: #f1f5f9; }
        .ticket { width: 80mm; margin: 24px auto; padding: 6mm 5mm; background: #fff; box-shadow: 0 4px 20px -2px rgba(0,0,0,.1); border-radius: 12px; }
        .sep { border-top: 1px dashed #cbd5e1; margin: 8px 0; }
        @page { size: 80mm auto; margin: 0; }
        @media print {
            body { background: #fff; }
            .ticket { box-shadow: none; margin: 0; width: 80mm; border-radius: 0; padding: 4mm; }
            .barre { display: none !important; }
        }
    </style>
</head>
<body class="py-6">

{{-- Barre d'actions (masquée à l'impression) --}}
<div class="barre flex items-center justify-center gap-3 mb-6">
    <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white font-bold px-4 py-2.5 rounded-xl text-sm shadow-md shadow-amber-500/20 transition cursor-pointer">
        🖨 Imprimer le reçu
    </button>
    <a href="{{ route('depots.show', $depot) }}" class="inline-flex items-center gap-2 bg-slate-800 hover:bg-slate-900 text-white font-semibold px-4 py-2.5 rounded-xl text-sm shadow-md transition text-decoration-none">
        ← Retour au dépôt
    </a>
</div>

{{-- Ticket thermique 80mm --}}
<div class="ticket">
    <div class="text-center mb-3">
        <h1 class="text-base font-extrabold uppercase tracking-wide text-slate-900">{{ $pressing?->nom ?? config('app.name') }}</h1>
        @if($pressing?->adresse)<p class="text-[11px] text-slate-500 mt-0.5">{{ $pressing->adresse }}</p>@endif
        @if($pressing?->telephone)<p class="text-[11px] text-slate-500">Tél : {{ $pressing->telephone }}</p>@endif
    </div>

    <div class="sep"></div>

    <div class="text-center my-2">
        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Reçu de paiement</p>
        <p class="text-base font-black text-slate-900 tracking-tight my-0.5">R-{{ str_pad($paiement->id, 6, '0', STR_PAD_LEFT) }}</p>
        <p class="text-[11px] text-slate-500">{{ $paiement->date_paiement?->format('d/m/Y à H:i') }}</p>
    </div>

    <div class="sep"></div>

    <table class="w-full text-xs space-y-1">
        <tr>
            <td class="py-1 text-slate-500 font-medium">Ticket</td>
            <td class="py-1 text-right font-bold text-amber-600">{{ $depot->numero_ticket }}</td>
        </tr>
        <tr>
            <td class="py-1 text-slate-500 font-medium">Client</td>
            <td class="py-1 text-right font-bold text-slate-800 truncate max-w-[120px]">{{ $depot->client?->nom }} {{ $depot->client?->prenom }}</td>
        </tr>
        <tr>
            <td class="py-1 text-slate-500 font-medium">Mode de paiement</td>
            <td class="py-1 text-right font-semibold text-slate-700">{{ ucwords(str_replace('_', ' ', $paiement->mode)) }}</td>
        </tr>
        @if($paiement->reference)
        <tr>
            <td class="py-1 text-slate-500 font-medium">Référence</td>
            <td class="py-1 text-right font-mono text-slate-700">{{ $paiement->reference }}</td>
        </tr>
        @endif
    </table>

    <div class="sep"></div>

    <table class="w-full text-xs">
        <tr class="text-sm bg-slate-50">
            <td class="py-2 px-2 font-extrabold text-slate-900">MONTANT REÇU</td>
            <td class="py-2 px-2 text-right font-black text-emerald-600">{{ number_format($paiement->montant, 0, ',', ' ') }} FCFA</td>
        </tr>
        <tr>
            <td class="py-1.5 px-2 text-slate-500">Total du dépôt</td>
            <td class="py-1.5 px-2 text-right font-semibold text-slate-700">{{ number_format($depot->montant_total, 0, ',', ' ') }} FCFA</td>
        </tr>
        <tr class="border-t border-slate-100">
            <td class="py-2 px-2 font-bold text-slate-900">Reste à payer</td>
            <td class="py-2 px-2 text-right font-extrabold {{ $reste > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                {{ number_format($reste, 0, ',', ' ') }} FCFA
            </td>
        </tr>
    </table>

    <div class="sep"></div>

    <div class="text-center text-[11px] text-slate-500 space-y-1 pt-1">
        <p>Encaissé par : <span class="font-bold text-slate-700">{{ $paiement->user?->nom ?? '—' }}</span></p>
        <p class="font-semibold text-slate-800 pt-1">Merci de votre confiance !</p>
    </div>
</div>

@if(request()->boolean('imprimer'))
    <script>window.addEventListener('load', () => setTimeout(() => window.print(), 300));</script>
@endif
</body>
</html>