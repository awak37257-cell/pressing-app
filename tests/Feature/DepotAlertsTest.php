<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DepotAlertsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('clients', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('pressing_id');
            $table->string('nom');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('depots', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('pressing_id');
            $table->unsignedBigInteger('client_id');
            $table->string('numero_ticket');
            $table->string('statut');
            $table->unsignedInteger('reste_a_payer')->default(0);
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

        $this->actingAs(new User([
            'pressing_id' => 1,
            'nom' => 'Caissier test',
            'role' => 'caissier',
            'actif' => true,
        ]));
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('depot_historique');
        Schema::dropIfExists('depots');
        Schema::dropIfExists('clients');

        parent::tearDown();
    }

    public function test_cashier_receives_new_ready_depot_alert_with_balance_and_ticket_details(): void
    {
        DB::table('clients')->insert([
            'id' => 4,
            'pressing_id' => 1,
            'nom' => 'Awa',
        ]);
        DB::table('depots')->insert([
            'id' => 10,
            'pressing_id' => 1,
            'client_id' => 4,
            'numero_ticket' => 'TK-20261104-0010',
            'statut' => 'pret',
            'reste_a_payer' => 2500,
        ]);
        DB::table('depot_historique')->insert([
            'id' => 15,
            'depot_id' => 10,
            'statut' => 'pret',
            'created_at' => now(),
        ]);

        $response = $this->getJson(route('depots.alertes-prets', ['since_id' => 14]));

        $response->assertOk()
            ->assertJsonPath('count', 1)
            ->assertJsonPath('events.0.id', 15)
            ->assertJsonPath('events.0.ticket', 'TK-20261104-0010')
            ->assertJsonPath('events.0.client', 'Awa')
            ->assertJsonPath('events.0.reste', 2500)
            ->assertJsonPath('events.0.url', route('depots.prets', ['scan_ticket' => 'TK-20261104-0010']));
    }

    public function test_cashier_alerts_exclude_other_pressings_old_events_and_depots_no_longer_ready(): void
    {
        DB::table('clients')->insert([
            ['id' => 4, 'pressing_id' => 1, 'nom' => 'Awa'],
            ['id' => 5, 'pressing_id' => 2, 'nom' => 'Binta'],
        ]);
        DB::table('depots')->insert([
            ['id' => 10, 'pressing_id' => 1, 'client_id' => 4, 'numero_ticket' => 'TK-OLD', 'statut' => 'pret', 'reste_a_payer' => 0],
            ['id' => 11, 'pressing_id' => 2, 'client_id' => 5, 'numero_ticket' => 'TK-OTHER', 'statut' => 'pret', 'reste_a_payer' => 0],
            ['id' => 12, 'pressing_id' => 1, 'client_id' => 4, 'numero_ticket' => 'TK-REOPENED', 'statut' => 'en_traitement', 'reste_a_payer' => 0],
        ]);
        DB::table('depot_historique')->insert([
            ['id' => 14, 'depot_id' => 10, 'statut' => 'pret', 'created_at' => now()],
            ['id' => 15, 'depot_id' => 11, 'statut' => 'pret', 'created_at' => now()],
            ['id' => 16, 'depot_id' => 12, 'statut' => 'pret', 'created_at' => now()],
        ]);

        $response = $this->getJson(route('depots.alertes-prets', ['since_id' => 14]));

        $response->assertOk()
            ->assertJsonPath('count', 1)
            ->assertJsonPath('events', []);
    }

    public function test_operator_cannot_access_cashier_ready_depot_alerts(): void
    {
        $this->actingAs(new User([
            'pressing_id' => 1,
            'nom' => 'Opérateur test',
            'role' => 'operateur',
            'actif' => true,
        ]));

        $response = $this->getJson(route('depots.alertes-prets', ['since_id' => 0]));

        $response->assertForbidden();
    }

    public function test_ready_depot_alerts_reject_a_missing_history_cursor(): void
    {
        $response = $this->getJson(route('depots.alertes-prets'));

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('since_id');
    }
}
