<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DepotScannerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('depots', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('pressing_id');
            $table->string('numero_ticket', 50);
            $table->dateTime('date_depot')->nullable();
            $table->string('statut')->default('depose');
            $table->softDeletes();
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
        Schema::dropIfExists('depots');

        parent::tearDown();
    }

    public function test_scanning_a_ticket_redirects_to_its_depot(): void
    {
        DB::table('depots')->insert([
            'id' => 10,
            'pressing_id' => 1,
            'numero_ticket' => 'TK-20261102-0001',
            'statut' => 'pret',
        ]);

        $response = $this->get(route('depots.index', ['scan_ticket' => 'TK-20261102-0001']));

        $response->assertRedirect(route('depots.show', 10));
    }

    public function test_scanning_a_ticket_from_another_pressing_does_not_reveal_it(): void
    {
        DB::table('depots')->insert([
            'id' => 20,
            'pressing_id' => 2,
            'numero_ticket' => 'TK-20261102-0002',
            'statut' => 'pret',
        ]);

        $response = $this->get(route('depots.index', ['scan_ticket' => 'TK-20261102-0002']));

        $response->assertRedirect(route('depots.index'));
        $response->assertSessionHas('error', 'Aucun dépôt ne correspond à ce numéro de ticket.');
    }

    public function test_operator_cannot_use_the_ticket_scanner(): void
    {
        $this->actingAs(new User([
            'pressing_id' => 1,
            'nom' => 'Opérateur test',
            'role' => 'operateur',
            'actif' => true,
        ]));

        $response = $this->get(route('depots.index', ['scan_ticket' => 'TK-20261102-0001']));

        $response->assertForbidden();
    }
}
