<?php

namespace Tests\Feature;

use App\Http\Controllers\RecuController;
use App\Models\ArticleDepot;
use App\Models\Client;
use App\Models\Depot;
use App\Models\Paiement;
use App\Models\Pressing;
use App\Models\Service;
use App\Models\TypeArticle;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Picqer\Barcode\BarcodeGeneratorSVG;
use Tests\TestCase;

class RecuControllerTest extends TestCase
{
    public function test_ticket_receipt_embeds_barcode_and_whatsapp_includes_article_incidents(): void
    {
        $ticketNumber = 'TK-20261102-0001';
        $pressing = new Pressing(['nom' => 'Pressing test']);
        $user = new User(['nom' => 'Agent test']);
        $user->setRelation('pressing', $pressing);
        $this->actingAs($user);

        $depot = new class extends Depot
        {
            public function load($relations): static
            {
                return $this;
            }
        };
        $depot->setRawAttributes([
            'id' => 1,
            'numero_ticket' => $ticketNumber,
            'date_depot' => '2026-11-02 10:00:00',
            'date_retrait_prevue' => '2026-11-05',
            'montant_total' => 0,
            'montant_paye' => 0,
            'reste_a_payer' => 0,
            'remise' => 0,
            'express' => false,
        ]);
        $depot->setRelation('client', new Client(['nom' => 'Client test', 'telephone' => '0102030405']));
        $articlePerdu = new ArticleDepot([
            'quantite' => 1,
            'statut' => 'perdu',
            'observation' => 'Tache indelebile',
        ]);
        $articlePerdu->setRelation('typeArticle', new TypeArticle(['nom' => 'Veste']));
        $articleAbime = new ArticleDepot([
            'quantite' => 1,
            'statut' => 'abime',
            'observation' => 'Bouton arrache',
        ]);
        $articleAbime->setRelation('typeArticle', new TypeArticle(['nom' => 'Chemise']));
        $depot->setRelation('articles', new Collection([$articlePerdu, $articleAbime]));
        $depot->setRelation('user', $user);

        $html = app(RecuController::class)->ticket($depot)->render();
        $expectedSvg = (new BarcodeGeneratorSVG)->getBarcode(
            $ticketNumber,
            BarcodeGeneratorSVG::TYPE_CODE_128,
            1.5,
            42,
        );
        $expectedBarcode = 'data:image/svg+xml;base64,'.base64_encode($expectedSvg);

        $this->assertStringContainsString('src="'.$expectedBarcode.'"', $html);
        $this->assertStringContainsString('>'.$ticketNumber.'</p>', $html);
        $this->assertStringContainsString('Veste%20%E2%80%94%20perdu%20%3A%20Tache%20indelebile', $html);
        $this->assertStringContainsString('Chemise%20%E2%80%94%20ab%C3%AEm%C3%A9%20%3A%20Bouton%20arrache', $html);
    }

    public function test_payment_receipt_is_labeled_as_final_when_no_balance_remains(): void
    {
        $depot = new Depot;
        $depot->setRawAttributes([
            'id' => 10,
            'numero_ticket' => 'TK-FINAL-001',
            'statut' => 'pret',
            'montant_total' => 5000,
        ]);
        $depot->setRelation('client', new Client(['nom' => 'Client test']));
        $article = new ArticleDepot([
            'quantite' => 2,
            'prix_unitaire' => 1500,
        ]);
        $article->setRelation('typeArticle', new TypeArticle(['nom' => 'Chemise']));
        $article->setRelation('service', new Service(['nom' => 'Repassage']));
        $depot->setRelation('articles', new Collection([$article]));

        $paiement = new Paiement;
        $paiement->setRawAttributes([
            'id' => 20,
            'montant' => 2500,
            'mode' => 'especes',
            'date_paiement' => '2026-11-04 11:00:00',
        ]);

        $html = view('recus.paiement', [
            'paiement' => $paiement,
            'depot' => $depot,
            'pressing' => new Pressing(['nom' => 'Pressing test']),
            'reste' => 0,
        ])->render();

        $this->assertStringContainsString('Reçu final de paiement', $html);
        $this->assertStringContainsString('Retour pour confirmer la remise', $html);
        $this->assertStringContainsString('Chemise', $html);
        $this->assertStringContainsString('Repassage', $html);
        $this->assertStringContainsString('3 000', $html);
    }
}
