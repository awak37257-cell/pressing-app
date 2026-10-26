<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reçu R-{{ str_pad($paiement->id, 6, '0', STR_PAD_LEFT) }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: "Courier New", ui-monospace, monospace; font-size: 12px; color: #111; background: #e5e7eb; }
        .ticket { width: 80mm; margin: 16px auto; padding: 5mm 4mm; background: #fff; box-shadow: 0 2px 12px rgba(0,0,0,.15); }
        h1 { font-size: 15px; text-align: center; text-transform: uppercase; letter-spacing: .5px; }
        .centre { text-align: center; }
        .petit { font-size: 10px; color: #444; }
        .sep { border-top: 1px dashed #000; margin: 6px 0; }
        .num { font-size: 15px; font-weight: bold; text-align: center; margin: 4px 0; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 2px 0; vertical-align: top; }
        .d { text-align: right; white-space: nowrap; }
        .montant td { font-weight: bold; font-size: 15px; padding: 4px 0; }
        .barre { display: flex; gap: 8px; justify-content: center; margin: 12px auto; flex-wrap: wrap; }
        .barre a, .barre button { font: 600 13px system-ui, sans-serif; padding: 8px 14px; border-radius: 8px; border: 0; cursor: pointer; text-decoration: none; color: #fff; background: #f59e0b; }
        .barre a.sec { background: #334155; }
        @page { size: 80mm auto; margin: 0; }
        @media print {
            body { background: #fff; }
            .ticket { box-shadow: none; margin: 0; width: 80mm; }
            .barre { display: none; }
        }
    </style>
</head>
<body>

<div class="barre">
    <button type="button" onclick="window.print()">🖨 Imprimer</button>
    <a class="sec" href="{{ route('depots.show', $depot) }}">← Retour au dépôt</a>
</div>

<div class="ticket">
    <h1>{{ $pressing?->nom ?? config('app.name') }}</h1>
    @if($pressing?->adresse)<p class="centre petit">{{ $pressing->adresse }}</p>@endif
    @if($pressing?->telephone)<p class="centre petit">Tél : {{ $pressing->telephone }}</p>@endif

    <div class="sep"></div>
    <p class="centre petit">REÇU DE PAIEMENT</p>
    <p class="num">R-{{ str_pad($paiement->id, 6, '0', STR_PAD_LEFT) }}</p>
    <p class="centre petit">{{ $paiement->date_paiement?->format('d/m/Y à H:i') }}</p>

    <div class="sep"></div>
    <table>
        <tr><td>Ticket</td><td class="d">{{ $depot->numero_ticket }}</td></tr>
        <tr><td>Client</td><td class="d">{{ $depot->client?->nom }} {{ $depot->client?->prenom }}</td></tr>
        <tr><td>Mode</td><td class="d">{{ ucwords(str_replace('_', ' ', $paiement->mode)) }}</td></tr>
        @if($paiement->reference)<tr><td>Référence</td><td class="d">{{ $paiement->reference }}</td></tr>@endif
    </table>

    <div class="sep"></div>
    <table>
        <tr class="montant"><td>MONTANT REÇU</td><td class="d">{{ number_format($paiement->montant, 0, ',', ' ') }} FCFA</td></tr>
        <tr><td>Total du dépôt</td><td class="d">{{ number_format($depot->montant_total, 0, ',', ' ') }}</td></tr>
        <tr><td><strong>Reste à payer</strong></td><td class="d"><strong>{{ number_format($reste, 0, ',', ' ') }}</strong></td></tr>
    </table>

    <div class="sep"></div>
    <p class="centre petit">Encaissé par : {{ $paiement->user?->nom }}</p>
    <p class="centre petit" style="margin-top:4px">Merci de votre confiance !</p>
</div>

@if(request()->boolean('imprimer'))
    <script>window.addEventListener('load', () => setTimeout(() => window.print(), 300));</script>
@endif
</body>
</html>
