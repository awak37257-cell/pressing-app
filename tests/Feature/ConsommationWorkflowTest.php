<?php

namespace Tests\Feature;

use App\Models\ArticleDepot;
use App\Models\CategorieProduit;
use App\Models\Client;
use App\Models\Depot;
use App\Models\DepotConsommation;
use App\Models\Pressing;
use App\Models\Produit;
use App\Models\Service;
use App\Models\ServiceProduit;
use App\Models\TypeArticle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsommationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_operator_uses_actual_quantities_and_history_shows_validated_consumption(): void
    {
        $context = $this->createWorkflowContext();
        ServiceProduit::create([
            'service_id' => $context['depot']->articles()->value('service_id'),
            'produit_id' => $context['product']->id,
            'quantite_par_article' => 100,
        ]);

        $this->actingAs($context['operator'])
            ->get(route('traitement.index'))
            ->assertOk()
            ->assertSee('✔ Prêt + déclaration');

        $this->post(route('depots.pret', $context['depot']))
            ->assertRedirect(route('consommations.create', $context['depot']));

        $this->get(route('consommations.create', $context['depot']))
            ->assertOk()
            ->assertSee('Déclarez les produits et les quantités réellement utilisés')
            ->assertDontSee('Les quantités sont pré-remplies')
            ->assertSee('Lessive');

        $this->post(route('consommations.store', $context['depot']), [
            'lignes' => [
                ['produit_id' => $context['product']->id, 'quantite' => 0.5],
            ],
        ])->assertRedirect(route('traitement.index'));

        $this->assertDatabaseHas('depots', [
            'id' => $context['depot']->id,
            'statut' => 'pret',
        ]);
        $this->assertNotNull($context['depot']->fresh()->consommation_validee_at);
        $this->assertDatabaseHas('depot_consommations', [
            'depot_id' => $context['depot']->id,
            'produit_id' => $context['product']->id,
            'quantite' => 0.5,
        ]);
        $this->assertDatabaseHas('produits', [
            'id' => $context['product']->id,
            'stock_actuel' => 4.5,
        ]);

        $this->actingAs($context['manager'])
            ->get(route('quantites-standard.index'))
            ->assertOk()
            ->assertSee('Quantités réellement utilisées')
            ->assertSee($context['depot']->numero_ticket)
            ->assertSee('Lessive')
            ->assertSee('0.5')
            ->assertDontSee('Ajouter une règle');

        $this->actingAs($context['operator'])
            ->get(route('traitement.index'))
            ->assertOk();

        $this->get(route('traitement.index'))
            ->assertOk()
            ->assertDontSee($context['depot']->numero_ticket);

        $this->actingAs($context['cashier'])
            ->get(route('depots.prets'))
            ->assertOk()
            ->assertSee($context['depot']->numero_ticket)
            ->assertSee('id="sidebar-depots-prets-count"', false)
            ->assertSee('aria-label="1 dépôt(s) prêt(s) à notifier"', false);
    }

    public function test_operator_can_confirm_no_product_was_used_and_mark_depot_ready(): void
    {
        $context = $this->createWorkflowContext();

        $this->actingAs($context['operator'])
            ->post(route('depots.pret', $context['depot']))
            ->assertRedirect(route('consommations.create', $context['depot']));

        $this->post(route('consommations.store', $context['depot']), [
            'lignes' => [],
        ])->assertRedirect(route('traitement.index'));

        $this->assertDatabaseHas('depots', [
            'id' => $context['depot']->id,
            'statut' => 'pret',
        ]);
        $this->assertNotNull($context['depot']->fresh()->consommation_validee_at);
        $this->assertDatabaseCount('depot_consommations', 0);
        $this->assertDatabaseHas('depot_historique', [
            'depot_id' => $context['depot']->id,
            'statut' => 'pret',
        ]);

        $this->actingAs($context['cashier'])
            ->getJson(route('depots.alertes-prets', ['since_id' => 0]))
            ->assertOk()
            ->assertJsonPath('count', 1)
            ->assertJsonPath('events.0.ticket', $context['depot']->numero_ticket);

        $this->get(route('depots.prets'))
            ->assertOk()
            ->assertSee($context['depot']->numero_ticket)
            ->assertSee('aria-label="1 dépôt(s) prêt(s) à notifier"', false);
    }

    public function test_cashier_cannot_open_the_operator_consumption_form(): void
    {
        $context = $this->createWorkflowContext();

        $this->actingAs($context['cashier'])
            ->get(route('consommations.create', $context['depot']))
            ->assertForbidden();

        $this->post(route('consommations.store', $context['depot']), [
            'lignes' => [],
        ])->assertForbidden();
    }

    public function test_consumption_history_does_not_show_another_pressings_declarations(): void
    {
        $context = $this->createWorkflowContext();
        $foreignPressing = Pressing::create(['nom' => 'Autre pressing']);
        $foreignClient = Client::create([
            'pressing_id' => $foreignPressing->id,
            'nom' => 'Client extérieur',
            'telephone' => '0700000001',
        ]);
        $foreignCategory = CategorieProduit::create([
            'pressing_id' => $foreignPressing->id,
            'nom' => 'Produits extérieurs',
        ]);
        $foreignProduct = Produit::create([
            'pressing_id' => $foreignPressing->id,
            'categorie_produit_id' => $foreignCategory->id,
            'nom' => 'Produit extérieur',
            'unite' => 'litre',
        ]);
        $foreignDepot = Depot::create([
            'pressing_id' => $foreignPressing->id,
            'numero_ticket' => 'TK-AUTRE-PRESSING-001',
            'client_id' => $foreignClient->id,
            'user_id' => $context['manager']->id,
            'date_depot' => now(),
            'date_retrait_prevue' => today()->addDay(),
            'statut' => 'pret',
            'montant_total' => 1000,
            'montant_paye' => 0,
            'reste_a_payer' => 1000,
        ]);
        DepotConsommation::create([
            'depot_id' => $foreignDepot->id,
            'produit_id' => $foreignProduct->id,
            'user_id' => $context['manager']->id,
            'quantite' => 1,
            'cout_unitaire' => 1000,
        ]);

        $this->actingAs($context['manager'])
            ->get(route('quantites-standard.index'))
            ->assertOk()
            ->assertDontSee($foreignDepot->numero_ticket)
            ->assertDontSee('Produit extérieur');
    }

    /**
     * @return array{
     *     operator: User,
     *     cashier: User,
     *     manager: User,
     *     depot: Depot,
     *     product: Produit
     * }
     */
    private function createWorkflowContext(): array
    {
        $pressing = Pressing::create(['nom' => 'Pressing test']);
        $operator = User::create([
            'pressing_id' => $pressing->id,
            'nom' => 'Opérateur test',
            'email' => 'operateur@example.com',
            'password' => 'password',
            'role' => 'operateur',
            'actif' => true,
        ]);
        $cashier = User::create([
            'pressing_id' => $pressing->id,
            'nom' => 'Caissier test',
            'email' => 'caissier@example.com',
            'password' => 'password',
            'role' => 'caissier',
            'actif' => true,
        ]);
        $manager = User::create([
            'pressing_id' => $pressing->id,
            'nom' => 'Gérant test',
            'email' => 'gerant@example.com',
            'password' => 'password',
            'role' => 'gerant',
            'actif' => true,
        ]);
        $client = Client::create([
            'pressing_id' => $pressing->id,
            'nom' => 'Client test',
            'telephone' => '0700000000',
        ]);
        $typeArticle = TypeArticle::create([
            'pressing_id' => $pressing->id,
            'nom' => 'Chemise',
        ]);
        $service = Service::create([
            'pressing_id' => $pressing->id,
            'nom' => 'Lavage',
        ]);
        $category = CategorieProduit::create([
            'pressing_id' => $pressing->id,
            'nom' => 'Lessives',
        ]);
        $product = Produit::create([
            'pressing_id' => $pressing->id,
            'categorie_produit_id' => $category->id,
            'nom' => 'Lessive',
            'unite' => 'litre',
            'stock_actuel' => 5,
            'seuil_alerte' => 1,
            'prix_achat_moyen' => 1000,
        ]);
        $depot = Depot::create([
            'pressing_id' => $pressing->id,
            'numero_ticket' => 'TK-CONSOMMATION-001',
            'client_id' => $client->id,
            'user_id' => $operator->id,
            'date_depot' => now(),
            'date_retrait_prevue' => today()->addDay(),
            'statut' => 'en_traitement',
            'montant_total' => 1000,
            'montant_paye' => 0,
            'reste_a_payer' => 1000,
        ]);
        ArticleDepot::create([
            'depot_id' => $depot->id,
            'type_article_id' => $typeArticle->id,
            'service_id' => $service->id,
            'quantite' => 1,
            'prix_unitaire' => 1000,
            'statut' => 'en_traitement',
        ]);

        return compact('operator', 'cashier', 'manager', 'depot', 'product');
    }
}
