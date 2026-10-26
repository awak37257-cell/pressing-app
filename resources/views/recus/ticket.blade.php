<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket {{ $depot->numero_ticket }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: "Courier New", ui-monospace, monospace; font-size: 12px; color: #111; background: #e5e7eb; }
        .ticket { width: 80mm; margin: 16px auto; padding: 5mm 4mm; background: #fff; box-shadow: 0 2px 12px rgba(0,0,0,.15); }
        h1 { font-size: 15px; text-align: center; text-transform: uppercase; letter-spacing: .5px; }
        .centre { text-align: center; }
        .petit { font-size: 10px; color: #444; }
        .sep { border-top: 1px dashed #000; margin: 6px 0; }
        .num { font-size: 16px; font-weight: bold; text-align: center; margin: 4px 0; letter-spacing: 1px; }
        table { width: 100%; border-collapse: collapse; }
        td, th { padding: 1.5px 0; vertical-align: top; }
        th { text-align: left; font-size: 10px; border-bottom: 1px solid #000; }
        .d { text-align: right; white-space: nowrap; }
        .total td { font-weight: bold; font-size: 13px; padding-top: 3px; }
        .reste td { font-weight: bold; font-size: 14px; }
        .barre { display: flex; gap: 8px; justify-content: center; margin: 12px auto; flex-wrap: wrap; }
        .barre a, .barre button { font: 600 13px system-ui, sans-serif; padding: 8px 14px; border-radius: 8px; border: 0; cursor: pointer; text-decoration: none; color: #fff; background: #f59e0b; }
        .barre a.sec { background: #334155; }
        .barre a.wa { background: #16a34a; }
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
    @if($whatsapp)<a class="wa" href="{{ $whatsapp }}" target="_blank" rel="noopener">💬 Envoyer par WhatsApp</a>@endif
    <a class="sec" href="{{ route('depots.show', $depot) }}">← Retour au dépôt</a>
</div>

<div class="ticket">
    <h1>{{ $pressing?->nom ?? config('app.name') }}</h1>
    @if($pressing?->adresse)<p class="centre petit">{{ $pressing->adresse }}</p>@endif
    @if($pressing?->telephone)<p class="centre petit">Tél : {{ $pressing->telephone }}</p>@endif

    <div class="sep"></div>
    <p class="centre petit">TICKET DE DÉPÔT</p>
    <p class="num">{{ $depot->numero_ticket }}</p>
    <p class="centre petit">{{ $depot->date_depot?->format('d/m/Y à H:i') }}@if($depot->express) · EXPRESS @endif</p>

    <div class="sep"></div>
    <p><strong>Client :</strong> {{ $depot->client?->nom }} {{ $depot->client?->prenom }}</p>
    @if($depot->client?->telephone)<p><strong>Tél :</strong> {{ $depot->client->telephone }}</p>@endif

    <div class="sep"></div>
    <table>
        <thead><tr><th>Article</th><th class="d">Qté</th><th class="d">Montant</th></tr></thead>
        <tbody>
        @foreach($depot->articles as $a)
            <tr>
                <td>{{ $a->typeArticle?->nom }}<br><span class="petit">{{ $a->service?->nom }} · {{ number_format($a->prix_unitaire, 0, ',', ' ') }}</span></td>
                <td class="d">{{ $a->quantite }}</td>
                <td class="d">{{ number_format($a->prix_unitaire * $a->quantite, 0, ',', ' ') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="sep"></div>
    <table>
        @if($depot->remise > 0)
            <tr><td>Sous-total</td><td class="d">{{ number_format($depot->montant_total + $depot->remise, 0, ',', ' ') }}</td></tr>
            <tr><td>Remise</td><td class="d">− {{ number_format($depot->remise, 0, ',', ' ') }}</td></tr>
        @endif
        <tr class="total"><td>TOTAL (FCFA)</td><td class="d">{{ number_format($depot->montant_total, 0, ',', ' ') }}</td></tr>
        <tr><td>Payé</td><td class="d">{{ number_format($depot->montant_paye, 0, ',', ' ') }}</td></tr>
        <tr class="reste"><td>RESTE À PAYER</td><td class="d">{{ number_format($depot->reste_a_payer, 0, ',', ' ') }}</td></tr>
    </table>

    <div class="sep"></div>
    <p class="centre"><strong>Retrait prévu le {{ $depot->date_retrait_prevue?->format('d/m/Y') }}</strong></p>
    <p class="centre petit">Reçu par : {{ $depot->user?->nom }}</p>

    <div class="sep"></div>
    {{-- Conditions de garde : à adapter à la politique de votre pressing --}}
    <p class="centre petit">Présentez ce ticket pour retirer vos articles.</p>
    <p class="centre petit">Articles non réclamés sous 30 jours : le pressing décline toute responsabilité.</p>
    <p class="centre petit" style="margin-top:4px">Merci de votre confiance !</p>
</div>

@if(request()->boolean('imprimer'))
    <script>window.addEventListener('load', () => setTimeout(() => window.print(), 300));</script>
@endif
</body>
</html>
