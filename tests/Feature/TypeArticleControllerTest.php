<?php

namespace Tests\Feature;

use App\Models\ArticleDepot;
use App\Models\Client;
use App\Models\Depot;
use App\Models\Pressing;
use App\Models\Service;
use App\Models\TypeArticle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TypeArticleControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_creates_article_type_for_the_current_pressing(): void
    {
        $pressing = Pressing::create(['nom' => 'Pressing test']);
        $user = $this->createUser($pressing);

        $response = $this->actingAs($user)->post(route('types-articles.store'), [
            'nom_type_article' => 'Chemise',
        ]);

        $response->assertRedirect(route('services.index'));
        $this->assertDatabaseHas('types_articles', [
            'pressing_id' => $pressing->id,
            'nom' => 'Chemise',
        ]);
        $typeArticle = TypeArticle::where('nom', 'Chemise')->firstOrFail();
        $this->get(route('services.index'))->assertOk()->assertSee('Chemise');
        $this->get(route('depots.create'))
            ->assertOk()
            ->assertSee('value="'.$typeArticle->id.'"', false)
            ->assertSee('>Chemise</option>', false);
    }

    public function test_admin_cannot_create_duplicate_article_types_in_the_same_pressing(): void
    {
        $pressing = Pressing::create(['nom' => 'Pressing test']);
        $user = $this->createUser($pressing);
        TypeArticle::create(['pressing_id' => $pressing->id, 'nom' => 'Chemise']);

        $response = $this->actingAs($user)->post(route('types-articles.store'), [
            'nom_type_article' => 'Chemise',
        ]);

        $response->assertSessionHasErrors('nom_type_article');
        $this->assertDatabaseCount('types_articles', 1);
    }

    public function test_admin_updates_an_article_type(): void
    {
        $pressing = Pressing::create(['nom' => 'Pressing test']);
        $user = $this->createUser($pressing);
        $typeArticle = TypeArticle::create(['pressing_id' => $pressing->id, 'nom' => 'Chemise']);

        $response = $this->actingAs($user)->put(route('types-articles.update', $typeArticle), [
            'nom_type_article' => 'Chemise homme',
        ]);

        $response->assertRedirect(route('services.index'));
        $this->assertDatabaseHas('types_articles', [
            'id' => $typeArticle->id,
            'nom' => 'Chemise homme',
        ]);
    }

    public function test_admin_cannot_delete_an_article_type_used_in_a_depot(): void
    {
        $pressing = Pressing::create(['nom' => 'Pressing test']);
        $user = $this->createUser($pressing);
        $client = Client::create([
            'pressing_id' => $pressing->id,
            'nom' => 'Client test',
            'telephone' => '0102030405',
        ]);
        $service = Service::create([
            'pressing_id' => $pressing->id,
            'nom' => 'Lavage',
        ]);
        $typeArticle = TypeArticle::create(['pressing_id' => $pressing->id, 'nom' => 'Chemise']);
        $depot = Depot::create([
            'pressing_id' => $pressing->id,
            'numero_ticket' => 'TK-TEST-001',
            'client_id' => $client->id,
            'user_id' => $user->id,
            'date_depot' => now(),
            'date_retrait_prevue' => now()->addDay(),
        ]);
        ArticleDepot::create([
            'depot_id' => $depot->id,
            'type_article_id' => $typeArticle->id,
            'service_id' => $service->id,
            'quantite' => 1,
            'prix_unitaire' => 1500,
        ]);

        $response = $this->actingAs($user)->delete(route('types-articles.destroy', $typeArticle));

        $response->assertRedirect(route('services.index'));
        $response->assertSessionHas('error', 'Ce type d’article est utilisé dans un dépôt et ne peut pas être supprimé.');
        $this->assertModelExists($typeArticle);
    }

    public function test_user_cannot_update_an_article_type_from_another_pressing(): void
    {
        $pressing = Pressing::create(['nom' => 'Pressing utilisateur']);
        $autrePressing = Pressing::create(['nom' => 'Autre pressing']);
        $user = $this->createUser($pressing);
        $typeArticle = TypeArticle::create(['pressing_id' => $autrePressing->id, 'nom' => 'Chemise']);

        $response = $this->actingAs($user)->put(route('types-articles.update', $typeArticle), [
            'nom_type_article' => 'Chemise modifiée',
        ]);

        $response->assertNotFound();
        $this->assertDatabaseHas('types_articles', [
            'id' => $typeArticle->id,
            'pressing_id' => $autrePressing->id,
            'nom' => 'Chemise',
        ]);
    }

    public function test_caissier_cannot_manage_article_types(): void
    {
        $pressing = Pressing::create(['nom' => 'Pressing test']);
        $caissier = $this->createUser($pressing, 'caissier');

        $response = $this->actingAs($caissier)->post(route('types-articles.store'), [
            'nom_type_article' => 'Chemise',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('types_articles', 0);
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
