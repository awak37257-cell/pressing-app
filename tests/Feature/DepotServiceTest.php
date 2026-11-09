<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Depot;
use App\Models\User;
use App\Services\DepotService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class DepotServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('clients', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('pressing_id');
            $table->string('nom');
            $table->string('telephone');
            $table->unsignedBigInteger('points_fidelite')->default(0);
            $table->unsignedSmallInteger('reste_fidelite_fcfa')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('depots', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('pressing_id');
            $table->string('numero_ticket');
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('user_id');
            $table->dateTime('date_depot');
            $table->date('date_retrait_prevue');
            $table->dateTime('date_retrait_reelle')->nullable();
            $table->dateTime('consommation_validee_at')->nullable();
            $table->string('statut')->default('depose');
            $table->boolean('express')->default(false);
            $table->decimal('montant_total')->default(0);
            $table->decimal('remise')->default(0);
            $table->unsignedBigInteger('points_fidelite_utilises')->default(0);
            $table->decimal('montant_paye')->default(0);
            $table->decimal('reste_a_payer')->default(0);
            $table->text('observations')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('tarifs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('type_article_id');
            $table->unsignedBigInteger('service_id');
            $table->decimal('prix');
            $table->decimal('prix_express')->default(0);
            $table->timestamps();
        });

        Schema::create('articles_depot', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('depot_id');
            $table->unsignedBigInteger('type_article_id');
            $table->unsignedBigInteger('service_id');
            $table->unsignedInteger('quantite')->default(1);
            $table->decimal('prix_unitaire');
            $table->string('statut')->default('depose');
            $table->timestamps();
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
        foreach (['paiements', 'depot_historique', 'articles_depot', 'tarifs', 'depots', 'clients'] as $table) {
            Schema::dropIfExists($table);
        }

        parent::tearDown();
    }

    public function test_deposit_redemption_reduces_the_balance_and_applies_the_discount(): void
    {
        $client = $this->createClient(points: 200);
        $this->createTarif(prix: 5000);
        $user = $this->cashier();

        $depot = app(DepotService::class)->creer([
            'client_id' => $client->id,
            'date_retrait_prevue' => '2026-11-05',
            'articles' => [['type_article_id' => 1, 'service_id' => 1, 'quantite' => 1]],
            'remise' => 500,
            'points_fidelite_utilises' => 100,
            'montant_paye' => 0,
        ], $user);

        $this->assertSame(3500, (int) $depot->montant_total);
        $this->assertSame(1500, (int) $depot->remise);
        $this->assertSame(100, $depot->points_fidelite_utilises);
        $this->assertSame(100, $client->refresh()->points_fidelite);
        $this->assertDatabaseHas('depots', [
            'id' => $depot->id,
            'montant_total' => 3500,
            'points_fidelite_utilises' => 100,
        ]);
    }

    public function test_partial_payments_accumulate_toward_loyalty_points(): void
    {
        $client = $this->createClient();
        $depot = $this->createDepot($client, total: 2500);
        $user = $this->cashier();
        $service = app(DepotService::class);

        $service->encaisser($depot, 1500, 'especes', $user);
        $service->encaisser($depot->refresh(), 700, 'especes', $user);

        $this->assertSame(2, $client->refresh()->points_fidelite);
        $this->assertSame(200, $client->reste_fidelite_fcfa);
        $this->assertDatabaseCount('paiements', 2);
    }

    public function test_deposit_rejects_redemption_above_the_client_balance(): void
    {
        $client = $this->createClient(points: 200);
        $user = $this->cashier();

        try {
            app(DepotService::class)->creer([
                'client_id' => $client->id,
                'date_retrait_prevue' => '2026-11-05',
                'articles' => [['type_article_id' => 1, 'service_id' => 1, 'quantite' => 1]],
                'points_fidelite_utilises' => 300,
                'montant_paye' => 0,
            ], $user);

            $this->fail('A client must not redeem more loyalty points than their balance.');
        } catch (ValidationException $exception) {
            $this->assertSame(
                'Le client ne possède pas assez de points de fidélité.',
                $exception->errors()['points_fidelite_utilises'][0],
            );
        }

        $this->assertSame(200, $client->refresh()->points_fidelite);
        $this->assertDatabaseCount('depots', 0);
    }

    public function test_deposit_rejects_a_redemption_that_exceeds_the_article_total_without_spending_points(): void
    {
        $client = $this->createClient(points: 200);
        $this->createTarif(prix: 1500);
        $user = $this->cashier();

        try {
            app(DepotService::class)->creer([
                'client_id' => $client->id,
                'date_retrait_prevue' => '2026-11-05',
                'articles' => [['type_article_id' => 1, 'service_id' => 1, 'quantite' => 1]],
                'points_fidelite_utilises' => 200,
                'montant_paye' => 0,
            ], $user);

            $this->fail('A loyalty discount must not exceed the article total.');
        } catch (ValidationException $exception) {
            $this->assertSame(
                'Le total des remises dépasse le montant des articles.',
                $exception->errors()['points_fidelite_utilises'][0],
            );
        }

        $this->assertSame(200, $client->refresh()->points_fidelite);
        $this->assertDatabaseCount('depots', 0);
    }

    public function test_deposit_rejects_points_that_are_not_a_multiple_of_one_hundred(): void
    {
        $client = $this->createClient(points: 200);
        $user = $this->cashier();

        try {
            app(DepotService::class)->creer([
                'client_id' => $client->id,
                'date_retrait_prevue' => '2026-11-05',
                'articles' => [['type_article_id' => 1, 'service_id' => 1, 'quantite' => 1]],
                'points_fidelite_utilises' => 50,
                'montant_paye' => 0,
            ], $user);

            $this->fail('Loyalty points must be redeemed in units of one hundred.');
        } catch (ValidationException $exception) {
            $this->assertSame(
                'Les points utilisés doivent être un multiple de 100.',
                $exception->errors()['points_fidelite_utilises'][0],
            );
        }

        $this->assertSame(200, $client->refresh()->points_fidelite);
        $this->assertDatabaseCount('depots', 0);
    }

    public function test_cancelling_a_deposit_restores_redeemed_points_only_once(): void
    {
        $client = $this->createClient(points: 100);
        $depot = $this->createDepot($client, total: 0);
        $depot->update(['points_fidelite_utilises' => 100]);
        $client->decrement('points_fidelite', 100);
        $manager = $this->cashier();
        $manager->role = 'gerant';
        $service = app(DepotService::class);

        $service->changerStatut($depot, 'annule', $manager);
        $service->changerStatut($depot->refresh(), 'annule', $manager);

        $this->assertSame(100, $client->refresh()->points_fidelite);
        $this->assertDatabaseHas('depots', [
            'id' => $depot->id,
            'statut' => 'annule',
            'points_fidelite_utilises' => 100,
        ]);
    }

    private function createClient(int $points = 0): Client
    {
        $client = Client::create([
            'pressing_id' => 1,
            'nom' => 'Client test',
            'telephone' => '0700000000',
            'points_fidelite' => $points,
        ]);

        $this->actingAs($this->cashier());

        return $client;
    }

    private function createTarif(int $prix): void
    {
        DB::table('tarifs')->insert([
            'type_article_id' => 1,
            'service_id' => 1,
            'prix' => $prix,
            'prix_express' => 0,
        ]);
    }

    private function createDepot(Client $client, int $total): Depot
    {
        return Depot::create([
            'pressing_id' => 1,
            'numero_ticket' => 'TK-TEST-0001',
            'client_id' => $client->id,
            'user_id' => 1,
            'date_depot' => now(),
            'date_retrait_prevue' => '2026-11-05',
            'statut' => 'depose',
            'montant_total' => $total,
            'montant_paye' => 0,
            'reste_a_payer' => $total,
        ]);
    }

    private function cashier(): User
    {
        $user = new User([
            'id' => 1,
            'pressing_id' => 1,
            'nom' => 'Caissier test',
            'role' => 'caissier',
            'actif' => true,
        ]);
        $user->forceFill(['id' => 1]);

        return $user;
    }
}
