<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket {{ $depot->numero_ticket }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: "Courier New", ui-monospace, monospace; font-size: 12px; color: #0f172a; background: #e5e7eb; }

        .ticket {
            width: 80mm; margin: 16px auto; padding: 5mm 4mm;
            background: #fff;
            box-shadow: 0 20px 40px -12px rgba(15,23,42,.15), 0 0 0 1px rgba(15,23,42,.04);
            border-radius: 10px;
        }

        h1 {
            font-size: 14px; text-align: center; text-transform: uppercase;
            letter-spacing: .6px; font-weight: 800;
        }

        .centre { text-align: center; }
        .petit { font-size: 10px; color: #475569; line-height: 1.4; }
        .sep { border-top: 1px dashed #94a3b8; margin: 8px 0; }
        .sep-thick { border-top: 2px dashed #0f172a; margin: 8px 0; }

        .num {
            font-size: 18px; font-weight: 900; text-align: center;
            margin: 4px 0; letter-spacing: 1.5px;
            color: #0f172a;
        }

        .badge {
            display: inline-block; padding: 2px 6px; border-radius: 4px;
            font-size: 9px; font-weight: 800; text-transform: uppercase;
            letter-spacing: .5px;
        }
        .badge-express { background: #fee2e2; color: #b91c1c; }
        .badge-normal  { background: #e0f2fe; color: #0369a1; }

        table { width: 100%; border-collapse: collapse; }
        td, th { padding: 2px 0; vertical-align: top; }
        th {
            text-align: left; font-size: 9px;
            border-bottom: 1px solid #0f172a;
            padding-bottom: 3px; text-transform: uppercase; letter-spacing: .5px;
        }
        .d { text-align: right; white-space: nowrap; }

        .article-nom { font-weight: 700; font-size: 11px; }
        .article-detail { font-size: 9px; color: #64748b; }

        .total td {
            font-weight: 900; font-size: 13px;
            padding-top: 4px; border-top: 1px solid #0f172a;
        }
        .reste td {
            font-weight: 900; font-size: 13px;
            padding-top: 3px;
        }

        .totaux-box {
            background: #f8fafc; border-radius: 6px; padding: 6px 8px;
            margin: 4px 0;
        }

        /* Barre d'actions */
        .barre {
            display: flex; gap: 8px; justify-content: center;
            margin: 16px auto; flex-wrap: wrap;
            max-width: 90%; padding: 0 12px;
        }
        .barre button, .barre a {
            font: 700 13px system-ui, -apple-system, sans-serif;
            padding: 10px 16px; border-radius: 10px; border: 0;
            cursor: pointer; text-decoration: none; color: #fff;
            display: inline-flex; align-items: center; gap: 6px;
            transition: all .2s ease;
            box-shadow: 0 4px 12px rgba(0,0,0,.08);
        }
        .barre button { background: linear-gradient(135deg, #f59e0b, #ea580c); }
        .barre button:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(245,158,11,.3); }
        .barre a.sec { background: #1e293b; }
        .barre a.sec:hover { background: #0f172a; transform: translateY(-1px); }
        .barre a.wa { background: linear-gradient(135deg, #16a34a, #15803d); }
        .barre a.wa:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(22,163,74,.3); }

        .barcode {
            font-family: "Libre Barcode 39", "Courier New", monospace;
            letter-spacing: 2px; font-size: 24px;
        }

        @page { size: 80mm auto; margin: 0; }

        @media print {
            body { background: #fff; }
            .ticket { box-shadow: none; margin: 0; width: 80mm; border-radius: 0; }
            .barre { display: none !important; }
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Barcode+39&display=swap" rel="stylesheet">
</head>
<body>

{{-- ══════ BARRE D'ACTIONS ══════ --}}
<div class="barre">
    <button type="button" onclick="window.print()">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="6 9 6 2 18 2 18 9"/>
            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
            <rect x="6" y="14" width="12" height="8"/>
        </svg>
        Imprimer
    </button>
    @if($whatsapp)
        <a class="wa" href="{{ $whatsapp }}" target="_blank" rel="noopener">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
            </svg>
            WhatsApp
        </a>
    @endif
    <a class="sec" href="{{ route('depots.show', $depot) }}">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="19" y1="12" x2="5" y2="12"/>
            <polyline points="12 19 5 12 12 5"/>
        </svg>
        Retour
    </a>
</div>

{{-- ══════ TICKET 80mm ══════ --}}
<div class="ticket">

    {{-- En-tête --}}
    <div class="centre mb-2">
        <h1>{{ $pressing?->nom ?? config('app.name') }}</h1>
        @if($pressing?->adresse)<p class="centre petit">{{ $pressing->adresse }}</p>@endif
        @if($pressing?->telephone)<p class="centre petit">Tél : {{ $pressing->telephone }}</p>@endif
    </div>

    <div class="sep-thick"></div>

    {{-- Titre + numéro --}}
    <div class="centre my-2">
        <p class="petit" style="text-transform:uppercase; letter-spacing:.15em; font-weight:800; font-size:9px; color:#64748b;">Ticket de dépôt</p>
        <p class="num">{{ $depot->numero_ticket }}</p>
        <div style="display:flex; justify-content:center; gap:6px; margin-top:2px; flex-wrap:wrap;">
            @if($depot->express)
                <span class="badge badge-express">⚡ Express</span>
            @else
                <span class="badge badge-normal">Normal</span>
            @endif
            <span class="petit">{{ $depot->date_depot?->format('d/m/Y à H:i') }}</span>
        </div>
    </div>

    <div class="sep"></div>

    {{-- Client --}}
    <p class="petit"><strong style="color:#0f172a;">Client :</strong> {{ $depot->client?->nom }} {{ $depot->client?->prenom }}</p>
    @if($depot->client?->telephone)
        <p class="petit"><strong style="color:#0f172a;">Tél :</strong> {{ $depot->client->telephone }}</p>
    @endif

    <div class="sep"></div>

    {{-- Articles --}}
    <table>
        <thead>
            <tr>
                <th>Article</th>
                <th class="d">Qté</th>
                <th class="d">Montant</th>
            </tr>
        </thead>
        <tbody>
        @foreach($depot->articles as $a)
            <tr>
                <td>
                    <div class="article-nom">{{ $a->typeArticle?->nom }}</div>
                    <div class="article-detail">{{ $a->service?->nom }} · {{ number_format($a->prix_unitaire, 0, ',', ' ') }}</div>
                </td>
                <td class="d">{{ $a->quantite }}</td>
                <td class="d">{{ number_format($a->prix_unitaire * $a->quantite, 0, ',', ' ') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="sep"></div>

    {{-- Totaux --}}
    <div class="totaux-box">
        <table>
            @if($depot->remise > 0)
                <tr>
                    <td class="petit">Sous-total</td>
                    <td class="d petit">{{ number_format($depot->montant_total + $depot->remise, 0, ',', ' ') }}</td>
                </tr>
                <tr>
                    <td class="petit">Remise</td>
                    <td class="d petit" style="color:#b91c1c;">− {{ number_format($depot->remise, 0, ',', ' ') }}</td>
                </tr>
            @endif
            <tr class="total">
                <td>TOTAL</td>
                <td class="d">{{ number_format($depot->montant_total, 0, ',', ' ') }} F</td>
            </tr>
        </table>
        <table style="margin-top:3px;">
            <tr>
                <td class="petit">Payé</td>
                <td class="d petit" style="color:#059669; font-weight:700;">{{ number_format($depot->montant_paye, 0, ',', ' ') }}</td>
            </tr>
            <tr class="reste">
                <td style="color:{{ $depot->reste_a_payer > 0 ? '#dc2626' : '#059669' }};">RESTE</td>
                <td class="d" style="color:{{ $depot->reste_a_payer > 0 ? '#dc2626' : '#059669' }};">{{ number_format($depot->reste_a_payer, 0, ',', ' ') }} F</td>
            </tr>
        </table>
    </div>

    <div class="sep"></div>

    {{-- Retrait --}}
    <div class="centre">
        <p class="petit" style="font-weight:800; color:#0f172a; font-size:11px;">Retrait prévu</p>
        <p style="font-size:13px; font-weight:900; color:#0f172a; letter-spacing:0.5px;">{{ $depot->date_retrait_prevue?->format('d/m/Y') }}</p>
    </div>

    <div class="sep"></div>

    {{-- Barcode --}}
    <div class="centre my-1">
        <p class="barcode" style="color:#0f172a;">{{ $depot->numero_ticket }}</p>
        <p class="petit" style="font-size:8px; letter-spacing:.15em; text-transform:uppercase; color:#94a3b8;">Scan · Ticket dépôt</p>
    </div>

    <div class="sep"></div>

    {{-- Footer --}}
    <div class="centre petit" style="line-height:1.5;">
        <p>Reçu par : <strong style="color:#0f172a;">{{ $depot->user?->nom }}</strong></p>
        <p style="margin-top:4px;">Présentez ce ticket pour retirer vos articles.</p>
        <p>Articles non réclamés sous 30 jours : le pressing décline toute responsabilité.</p>
        <p style="margin-top:6px; font-weight:800; color:#0f172a; font-size:11px;">Merci de votre confiance !</p>
    </div>
</div>

@if(request()->boolean('imprimer'))
    <script>window.addEventListener('load', () => setTimeout(() => window.print(), 300));</script>
@endif
</body>
</html>