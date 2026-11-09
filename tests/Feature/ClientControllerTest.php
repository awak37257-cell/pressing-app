<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Pressing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_created_from_depot_form_is_returned_for_selection_without_leaving_depot(): void
    {
        $pressing = Pressing::create(['nom' => 'Pressing test']);
        $user = User::create([
            'pressing_id' => $pressing->id,
            'nom' => 'Caissier test',
            'email' => 'caissier@example.com',
            'password' => 'password',
            'role' => 'caissier',
            'actif' => true,
        ]);

        $response = $this->actingAs($user)->postJson(route('clients.store'), [
            'nom' => 'Awa',
            'prenom' => 'Diop',
            'telephone' => '0102030405',
            'email' => 'awa@example.com',
        ]);

        $response->assertOk()->assertJsonPath('client.nom', 'Awa');
        $response->assertJsonPath('client.prenom', 'Diop');
        $response->assertJsonPath('client.telephone', '0102030405');

        $client = Client::where('telephone', '0102030405')->firstOrFail();
        $this->assertSame($pressing->id, $client->pressing_id);
        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'pressing_id' => $pressing->id,
            'nom' => 'Awa',
        ]);
    }

    public function test_depot_form_renders_first_article_row_without_javascript(): void
    {
        $pressing = Pressing::create(['nom' => 'Pressing test']);
        $user = User::create([
            'pressing_id' => $pressing->id,
            'nom' => 'Caissier test',
            'email' => 'caissier@example.com',
            'password' => 'password',
            'role' => 'caissier',
            'actif' => true,
        ]);

        $response = $this->actingAs($user)->get(route('depots.create'));

        $response->assertOk()
            ->assertSee('id="modal-client"', false)
            ->assertSee('id="form-nouveau-client"', false)
            ->assertSee('articles[0][type_article_id]', false)
            ->assertSee('articles[0][service_id]', false)
            ->assertSee('articles[0][quantite]', false)
            ->assertSee('Créer et continuer');
    }
}
