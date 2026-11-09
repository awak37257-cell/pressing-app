<?php

namespace Tests\Feature;

use App\Models\Pressing;
use App\Models\Service;
use App\Models\Tarif;
use App\Models\TypeArticle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TarifControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_prefills_test_prices_for_each_article_and_service_and_replaces_existing_prices(): void
    {
        $pressing = Pressing::create(['nom' => 'Pressing test']);
        $user = $this->createUser($pressing);
        $chemise = TypeArticle::create(['pressing_id' => $pressing->id, 'nom' => 'Chemise']);
        $pantalon = TypeArticle::create(['pressing_id' => $pressing->id, 'nom' => 'Pantalon']);
        $lavage = Service::create(['pressing_id' => $pressing->id, 'nom' => 'Lavage']);
        $repassage = Service::create(['pressing_id' => $pressing->id, 'nom' => 'Repassage']);
        Tarif::create([
            'type_article_id' => $chemise->id,
            'service_id' => $lavage->id,
            'prix' => 9999,
            'prix_express' => 0,
        ]);

        $response = $this->actingAs($user)->post(route('tarifs.pre-remplir'));

        $response->assertRedirect(route('services.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseCount('tarifs', 4);
        $this->assertDatabaseHas('tarifs', [
            'type_article_id' => $chemise->id,
            'service_id' => $lavage->id,
            'prix' => 1500,
            'prix_express' => 400,
        ]);
        $this->assertDatabaseHas('tarifs', [
            'type_article_id' => $pantalon->id,
            'service_id' => $repassage->id,
            'prix' => 900,
            'prix_express' => 300,
        ]);
    }

    public function test_admin_prefills_only_the_current_pressings_articles_and_services(): void
    {
        $pressing = Pressing::create(['nom' => 'Pressing utilisateur']);
        $autrePressing = Pressing::create(['nom' => 'Autre pressing']);
        $user = $this->createUser($pressing);
        $typeArticle = TypeArticle::create(['pressing_id' => $pressing->id, 'nom' => 'Chemise']);
        $service = Service::create(['pressing_id' => $pressing->id, 'nom' => 'Lavage']);
        $autreTypeArticle = TypeArticle::create(['pressing_id' => $autrePressing->id, 'nom' => 'Robe']);
        $autreService = Service::create(['pressing_id' => $autrePressing->id, 'nom' => 'Repassage']);

        $this->actingAs($user)->post(route('tarifs.pre-remplir'))->assertRedirect(route('services.index'));

        $this->assertDatabaseCount('tarifs', 1);
        $this->assertDatabaseHas('tarifs', [
            'type_article_id' => $typeArticle->id,
            'service_id' => $service->id,
            'prix' => 1500,
        ]);
        $this->assertDatabaseMissing('tarifs', [
            'type_article_id' => $autreTypeArticle->id,
            'service_id' => $autreService->id,
        ]);
    }

    public function test_caissier_cannot_prefill_service_prices(): void
    {
        $pressing = Pressing::create(['nom' => 'Pressing test']);
        $caissier = $this->createUser($pressing, 'caissier');
        TypeArticle::create(['pressing_id' => $pressing->id, 'nom' => 'Chemise']);
        Service::create(['pressing_id' => $pressing->id, 'nom' => 'Lavage']);

        $response = $this->actingAs($caissier)->post(route('tarifs.pre-remplir'));

        $response->assertForbidden();
        $this->assertDatabaseCount('tarifs', 0);
    }

    public function test_service_page_shows_tariff_actions_without_hovering(): void
    {
        $pressing = Pressing::create(['nom' => 'Pressing test']);
        $user = $this->createUser($pressing);
        $typeArticle = TypeArticle::create(['pressing_id' => $pressing->id, 'nom' => 'Chemise']);
        $service = Service::create(['pressing_id' => $pressing->id, 'nom' => 'Lavage']);
        $tarif = Tarif::create([
            'type_article_id' => $typeArticle->id,
            'service_id' => $service->id,
            'prix' => 1500,
            'prix_express' => 400,
        ]);

        $response = $this->actingAs($user)->get(route('services.index'));

        $response->assertOk()
            ->assertSee('Modifier')
            ->assertSee('Supprimer')
            ->assertSee('edit-tarif-'.$tarif->id, false)
            ->assertDontSee('opacity-0 group-hover:opacity-100', false);
    }

    public function test_admin_can_update_and_delete_tariff_for_their_pressing(): void
    {
        $pressing = Pressing::create(['nom' => 'Pressing test']);
        $user = $this->createUser($pressing);
        $typeArticle = TypeArticle::create(['pressing_id' => $pressing->id, 'nom' => 'Chemise']);
        $service = Service::create(['pressing_id' => $pressing->id, 'nom' => 'Lavage']);
        $tarif = Tarif::create([
            'type_article_id' => $typeArticle->id,
            'service_id' => $service->id,
            'prix' => 1500,
            'prix_express' => 400,
        ]);

        $this->actingAs($user)->put(route('tarifs.update', $tarif), [
            'prix' => 1800,
            'prix_express' => 500,
        ])->assertRedirect(route('services.index'));

        $this->assertDatabaseHas('tarifs', [
            'id' => $tarif->id,
            'prix' => 1800,
            'prix_express' => 500,
        ]);

        $this->delete(route('tarifs.destroy', $tarif))
            ->assertRedirect(route('services.index'));

        $this->assertDatabaseMissing('tarifs', ['id' => $tarif->id]);
    }

    public function test_admin_cannot_update_tariff_from_another_pressing(): void
    {
        $pressing = Pressing::create(['nom' => 'Pressing utilisateur']);
        $autrePressing = Pressing::create(['nom' => 'Autre pressing']);
        $user = $this->createUser($pressing);
        $typeArticle = TypeArticle::create(['pressing_id' => $autrePressing->id, 'nom' => 'Chemise']);
        $service = Service::create(['pressing_id' => $autrePressing->id, 'nom' => 'Lavage']);
        $tarif = Tarif::create([
            'type_article_id' => $typeArticle->id,
            'service_id' => $service->id,
            'prix' => 1500,
            'prix_express' => 400,
        ]);

        $response = $this->actingAs($user)->put(route('tarifs.update', $tarif), [
            'prix' => 1,
            'prix_express' => 0,
        ]);

        $response->assertNotFound();
        $this->assertDatabaseHas('tarifs', [
            'id' => $tarif->id,
            'prix' => 1500,
            'prix_express' => 400,
        ]);
    }

    private function createUser(Pressing $pressing, string $role = 'admin'): User
    {
        return User::create([
            'pressing_id' => $pressing->id,
            'nom' => 'Utilisateur test',
            'email' => $role.'@example.com',
            'password' => 'password',
            'role' => $role,
            'actif' => true,
        ]);
    }
}
