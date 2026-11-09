<?php

namespace Tests\Feature;

use App\Http\Controllers\DepotController;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DepotRetraitTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('clients', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('pressing_id');
            $table->string('nom');
            $table->string('telephone')->nullable();
            $table->unsignedBigInteger('points_fidelite')->default(0);
            $table->unsignedSmallInteger('reste_fidelite_fcfa')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('pressings', function (Blueprint $table): void {
            $table->id();
            $table->string('nom');
        });

        Schema::create('types_articles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('pressing_id');
            $table->string('nom');
        });

        Schema::create('articles_depot', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('depot_id');
            $table->unsignedBigInteger('type_article_id');
            $table->unsignedBigInteger('service_id');
            $table->unsignedInteger('quantite')->default(1);
            $table->string('statut')->default('pret');
            $table->string('observation')->nullable();
            $table->timestamps();
        });

        Schema::create('services', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('pressing_id');
            $table->string('nom');
            $table->timestamps();
        });

        Schema::create('produits', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('pressing_id');
            $table->unsignedBigInteger('categorie_produit_id')->nullable();
            $table->string('nom');
            $table->string('unite');
            $table->decimal('stock_actuel')->default(0);
            $table->decimal('seuil_alerte')->default(0);
            $table->decimal('prix_achat_moyen')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('service_produit', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('service_id');
            $table->unsignedBigInteger('type_article_id')->nullable();
            $table->unsignedBigInteger('produit_id');
            $table->decimal('quantite_par_article');
            $table->timestamps();
        });

        Schema::create('depots', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('pressing_id');
            $table->string('numero_ticket');
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('user_id');
            $table->dateTime('date_depot');
            $table->date('date_retrait_prevue')->nullable();
            $table->dateTime('date_retrait_reelle')->nullable();
            $table->dateTime('consommation_validee_at')->nullable();
            $table->string('statut');
            $table->decimal('montant_total')->default(0);
            $table->decimal('montant_paye')->default(0);
            $table->decimal('reste_a_payer')->default(0);
            $table->decimal('remise')->default(0);
            $table->unsignedBigInteger('points_fidelite_utilises')->default(0);
            $table->boolean('express')->default(false);
            $table->text('observations')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('depot_historique', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('depot_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('statut');
            $table->string('note')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('paiements', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('depot_id');
            $table->unsignedBigInteger('user_id');
            $table->decimal('montant');
            $table->string('mode');
            $table->string('reference')->nullable();
            $table->dateTime('date_paiement');
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('paiements');
        Schema::dropIfExists('depot_historique');
        Schema::dropIfExists('service_produit');
        Schema::dropIfExists('produits');
        Schema::dropIfExists('services');
        Schema::dropIfExists('articles_depot');
        Schema::dropIfExists('depots');
        Schema::dropIfExists('clients');
        Schema::dropIfExists('types_articles');
        Schema::dropIfExists('pressings');

        parent::tearDown();
    }

    public function test_ready_depot_search_returns_only_the_matching_ticket_for_the_cashiers_pressing(): void
    {
        $cashier = $this->cashier();
        $this->actingAs($cashier);
        DB::table('pressings')->insert([
            ['id' => 1, 'nom' => 'Pressing test'],
            ['id' => 2, 'nom' => 'Autre pressing'],
        ]);
        $matchingDepotId = $this->createDepot('TK-READY-001');
        $this->createDepot('TK-READY-OTHER', pressingId: 2);
        $this->createDepot('TK-IN-PROGRESS', status: 'en_traitement');
        $request = Request::create('/depots/prets?scan_ticket=TK-READY-001');
        $request->setUserResolver(fn (): User => $cashier);

        $view = app(DepotController::class)->prets($request);

        $this->assertSame(
            [$matchingDepotId],
            $view->getData()['depots']->pluck('id')->all(),
        );
    }

    public function test_operator_cannot_open_the_ready_depot_cashier_page(): void
    {
        $this->actingAs(new User([
            'pressing_id' => 1,
            'nom' => 'Opérateur test',
            'role' => 'operateur',
            'actif' => true,
        ]));

        $this->get(route('depots.prets'))->assertForbidden();
    }

    public function test_operator_is_redirected_to_the_active_queue_after_starting_a_deposit(): void
    {
        DB::table('pressings')->insert(['id' => 1, 'nom' => 'Pressing test']);
        $operator = new User([
            'pressing_id' => 1,
            'nom' => 'Opérateur test',
            'role' => 'operateur',
            'actif' => true,
        ]);
        $operator->forceFill(['id' => 1]);
        $this->actingAs($operator);
        $depotId = $this->createDepot('TK-START-001', status: 'depose');

        $this->get(route('traitement.index'))
            ->assertOk()
            ->assertSee('▶ Commencer')
            ->assertDontSee('Consultation');

        $typeArticleId = DB::table('types_articles')->insertGetId([
            'pressing_id' => 1,
            'nom' => 'Chemise',
        ]);
        $serviceId = DB::table('services')->insertGetId([
            'pressing_id' => 1,
            'nom' => 'Lavage',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $productId = DB::table('produits')->insertGetId([
            'pressing_id' => 1,
            'nom' => 'Lessive',
            'unite' => 'litre',
            'stock_actuel' => 10,
            'seuil_alerte' => 1,
            'prix_achat_moyen' => 1000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('service_produit')->insert([
            'service_id' => $serviceId,
            'type_article_id' => $typeArticleId,
            'produit_id' => $productId,
            'quantite_par_article' => 0.1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('articles_depot')->insert([
            'depot_id' => $depotId,
            'type_article_id' => $typeArticleId,
            'service_id' => $serviceId,
            'quantite' => 1,
            'statut' => 'depose',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->from(route('traitement.index', ['statut' => 'depose']))
            ->followingRedirects()
            ->post(route('depots.commencer', $depotId));

        $response->assertOk()
            ->assertSee('✔ Prêt + déclaration');
        $this->assertDatabaseHas('depots', [
            'id' => $depotId,
            'statut' => 'en_traitement',
        ]);
        $this->assertDatabaseHas('depot_historique', [
            'depot_id' => $depotId,
            'statut' => 'en_traitement',
            'user_id' => $operator->id,
        ]);

        $this->post(route('depots.pret', $depotId))
            ->assertRedirect(route('consommations.create', $depotId));
    }

    public function test_partial_payment_keeps_the_ready_depot_in_the_scan_result(): void
    {
        $this->actingAs($this->cashier());
        $depotId = $this->createDepot('TK-PARTIAL-001', total: 5000, paid: 2500);

        $response = $this->post(route('depots.prets.encaisser', $depotId), [
            'montant_paye_ajout' => 1000,
            'mode_paiement' => 'especes',
        ]);

        $response->assertRedirect(route('depots.prets', ['scan_ticket' => 'TK-PARTIAL-001']));
        $this->assertDatabaseHas('depots', [
            'id' => $depotId,
            'montant_paye' => 3500,
            'reste_a_payer' => 1500,
            'statut' => 'pret',
        ]);
        $this->assertDatabaseHas('paiements', [
            'depot_id' => $depotId,
            'montant' => 1000,
            'mode' => 'especes',
        ]);
    }

    public function test_final_payment_redirects_to_the_printable_final_receipt(): void
    {
        $this->actingAs($this->cashier());
        $depotId = $this->createDepot('TK-FINAL-001', total: 5000, paid: 2500);

        $response = $this->post(route('depots.prets.encaisser', $depotId), [
            'montant_paye_ajout' => 2500,
            'mode_paiement' => 'wave',
            'reference' => 'WV-12345',
        ]);

        $paymentId = DB::table('paiements')->where('depot_id', $depotId)->max('id');
        $response->assertRedirect(route('paiements.recu', ['paiement' => $paymentId, 'imprimer' => 1]));
        $this->assertDatabaseHas('depots', [
            'id' => $depotId,
            'montant_paye' => 5000,
            'reste_a_payer' => 0,
            'statut' => 'pret',
        ]);
        $this->assertDatabaseHas('paiements', [
            'id' => $paymentId,
            'depot_id' => $depotId,
            'montant' => 2500,
            'mode' => 'wave',
            'reference' => 'WV-12345',
        ]);
    }

    public function test_cashier_cannot_encaisser_a_ticket_from_another_pressing(): void
    {
        $this->actingAs($this->cashier());
        $depotId = $this->createDepot('TK-OTHER-PRESSING', pressingId: 2);

        $this->post(route('depots.prets.encaisser', $depotId), [
            'montant_paye_ajout' => 5000,
            'mode_paiement' => 'especes',
        ])->assertNotFound();

        $this->assertDatabaseCount('paiements', 0);
    }

    public function test_ready_depot_payment_rejects_an_amount_above_the_remaining_balance(): void
    {
        $this->actingAs($this->cashier());
        $depotId = $this->createDepot('TK-OVERPAY-001', total: 5000, paid: 2500);

        $this->from(route('depots.prets', ['scan_ticket' => 'TK-OVERPAY-001']))
            ->post(route('depots.prets.encaisser', $depotId), [
                'montant_paye_ajout' => 2501,
                'mode_paiement' => 'especes',
            ])
            ->assertSessionHasErrors('montant_paye_ajout');

        $this->assertDatabaseHas('depots', [
            'id' => $depotId,
            'montant_paye' => 2500,
            'reste_a_payer' => 2500,
        ]);
        $this->assertDatabaseCount('paiements', 1);
    }

    public function test_mobile_money_payment_requires_a_reference(): void
    {
        $this->actingAs($this->cashier());
        $depotId = $this->createDepot('TK-MOBILE-001');

        $this->from(route('depots.prets', ['scan_ticket' => 'TK-MOBILE-001']))
            ->post(route('depots.prets.encaisser', $depotId), [
                'montant_paye_ajout' => 5000,
                'mode_paiement' => 'wave',
            ])
            ->assertSessionHasErrors('reference');

        $this->assertDatabaseCount('paiements', 0);
    }

    public function test_only_ready_depots_can_be_paid_from_the_ready_page(): void
    {
        $this->actingAs($this->cashier());
        $depotId = $this->createDepot('TK-NOT-READY-001', status: 'en_traitement');

        $this->from(route('depots.prets', ['scan_ticket' => 'TK-NOT-READY-001']))
            ->post(route('depots.prets.encaisser', $depotId), [
                'montant_paye_ajout' => 5000,
                'mode_paiement' => 'especes',
            ])
            ->assertRedirect(route('depots.prets', ['scan_ticket' => 'TK-NOT-READY-001']))
            ->assertSessionHas('error', 'Ce dépôt n’est plus prêt à être remis au client.');

        $this->assertDatabaseCount('paiements', 0);
    }

    private function cashier(): User
    {
        $cashier = new User([
            'pressing_id' => 1,
            'nom' => 'Caissière test',
            'role' => 'caissier',
            'actif' => true,
        ]);
        $cashier->forceFill(['id' => 1]);

        return $cashier;
    }

    private function createDepot(
        string $ticket,
        int $pressingId = 1,
        int $total = 5000,
        int $paid = 0,
        string $status = 'pret',
    ): int {
        $clientId = DB::table('clients')->insertGetId([
            'pressing_id' => $pressingId,
            'nom' => 'Client test',
            'telephone' => '0102030405',
            'points_fidelite' => 0,
            'reste_fidelite_fcfa' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $depotId = DB::table('depots')->insertGetId([
            'pressing_id' => $pressingId,
            'numero_ticket' => $ticket,
            'client_id' => $clientId,
            'user_id' => 1,
            'date_depot' => now(),
            'date_retrait_prevue' => now()->toDateString(),
            'statut' => $status,
            'montant_total' => $total,
            'montant_paye' => $paid,
            'reste_a_payer' => $total - $paid,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($paid > 0) {
            DB::table('paiements')->insert([
                'depot_id' => $depotId,
                'user_id' => 1,
                'montant' => $paid,
                'mode' => 'especes',
                'date_paiement' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $depotId;
    }
}
