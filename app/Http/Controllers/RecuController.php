<?php

namespace App\Http\Controllers;

use App\Models\Depot;
use App\Models\Paiement;
use App\Support\Whatsapp;
use Illuminate\View\View;
use Picqer\Barcode\BarcodeGeneratorSVG;

class RecuController extends Controller
{
    public function ticket(Depot $depot): View
    {
        $depot->load(['client', 'articles.typeArticle', 'articles.service', 'user', 'paiements']);
        $pressing = auth()->user()->pressing;
        $barcodeSvg = (new BarcodeGeneratorSVG)->getBarcode(
            $depot->numero_ticket,
            BarcodeGeneratorSVG::TYPE_CODE_128,
            1.5,
            42,
        );
        $barcodeDataUri = 'data:image/svg+xml;base64,'.base64_encode($barcodeSvg);

        $whatsapp = Whatsapp::url(
            $depot->client?->telephone,
            "Bonjour {$depot->client?->nom}, voici votre dépôt {$depot->numero_ticket} chez ".($pressing?->nom ?? 'notre pressing')
            .'. Total : '.number_format((int) $depot->montant_total, 0, ',', ' ').' FCFA, payé : '
            .number_format((int) $depot->montant_paye, 0, ',', ' ').' FCFA, reste : '
            .number_format((int) $depot->reste_a_payer, 0, ',', ' ').' FCFA. Retrait prévu le '
            .$depot->date_retrait_prevue?->format('d/m/Y').'.'
            .Whatsapp::articleIncidentNotice($depot->articles)
        );

        return view('recus.ticket', compact('depot', 'pressing', 'whatsapp', 'barcodeDataUri'));
    }

    public function paiement(Paiement $paiement): View
    {
        $paiement->load(['depot.client', 'depot.articles.typeArticle', 'depot.articles.service', 'user']);
        abort_unless($paiement->depot, 404);

        $depot = $paiement->depot;
        $pressing = auth()->user()->pressing;

        // Reste à payer juste après ce paiement
        $cumul = (int) $depot->paiements()->where('id', '<=', $paiement->id)->sum('montant');
        $reste = max(0, (int) $depot->montant_total - $cumul);

        return view('recus.paiement', compact('paiement', 'depot', 'pressing', 'reste'));
    }
}
