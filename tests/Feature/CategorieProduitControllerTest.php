<?php

namespace Tests\Feature;

use App\Models\CategorieProduit;
use App\Models\Pressing;
use App\Models\Produit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategorieProduitControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_management_is_integrated_into_services_and_tariffs(): void
    {
        $user = $this->createUser();
        CategorieProduit::create([
            'pressing_id' => $user->pressing_id,
            'nom' => 'Lessives',
        ]);

        $this->actingAs($user)
            ->get(route('categories-produits.index'))
            ->assertRedirect(route('services.index').'#categories-produits');

        $this->get(route('services.index'))
            ->assertOk()
            ->assertSee('Services & Tarifs')
            ->assertSee('Catégories de produits')
            ->assertSee('Lessives');

        $this->get(route('produits.index'))
            ->assertOk()
            ->assertSee(route('services.index').'#categories-produits');
    }

    public function test_admin_can_create_a_category_for_their_pressing(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->post(route('categories-produits.store'), [
            'nom_categorie' => 'Détachants',
        ]);

        $response->assertRedirect(route('services.index').'#categories-produits');
        $this->assertDatabaseHas('categories_produits', [
            'pressing_id' => $user->pressing_id,
            'nom' => 'Détachants',
        ]);
    }

    public function test_category_names_must_be_unique_within_the_same_pressing(): void
    {
        $user = $this->createUser();
        CategorieProduit::create([
            'pressing_id' => $user->pressing_id,
            'nom' => 'Lessives',
        ]);

        $response = $this->actingAs($user)
            ->from(route('services.index').'#categories-produits')
            ->post(route('categories-produits.store'), [
                'nom_categorie' => 'Lessives',
            ]);

        $response->assertRedirect(route('services.index').'#categories-produits')
            ->assertSessionHasErrors('nom_categorie');
        $this->assertDatabaseCount('categories_produits', 1);
    }

    public function test_admin_can_update_and_delete_an_unused_category(): void
    {
        $user = $this->createUser();
        $category = CategorieProduit::create([
            'pressing_id' => $user->pressing_id,
            'nom' => 'Lessives',
        ]);

        $this->actingAs($user)->put(route('categories-produits.update', $category), [
            'nom_categorie' => 'Produits de lavage',
        ])->assertRedirect(route('services.index').'#categories-produits');

        $this->assertDatabaseHas('categories_produits', [
            'id' => $category->id,
            'nom' => 'Produits de lavage',
        ]);

        $this->delete(route('categories-produits.destroy', $category))
            ->assertRedirect(route('services.index').'#categories-produits');

        $this->assertDatabaseMissing('categories_produits', ['id' => $category->id]);
    }

    public function test_category_in_use_by_a_product_cannot_be_deleted(): void
    {
        $user = $this->createUser();
        $category = CategorieProduit::create([
            'pressing_id' => $user->pressing_id,
            'nom' => 'Lessives',
        ]);
        Produit::create([
            'pressing_id' => $user->pressing_id,
            'categorie_produit_id' => $category->id,
            'nom' => 'Lessive liquide',
            'unite' => 'litre',
            'stock_actuel' => 5,
            'seuil_alerte' => 1,
            'prix_achat_moyen' => 1000,
        ]);

        $response = $this->actingAs($user)
            ->from(route('services.index').'#categories-produits')
            ->delete(route('categories-produits.destroy', $category));

        $response->assertRedirect(route('services.index').'#categories-produits')
            ->assertSessionHas('error', 'Cette catégorie ne peut pas être supprimée car elle est utilisée par un ou plusieurs produits.');
        $this->assertDatabaseHas('categories_produits', ['id' => $category->id]);
    }

    public function test_category_used_by_a_soft_deleted_product_cannot_be_deleted(): void
    {
        $user = $this->createUser();
        $category = CategorieProduit::create([
            'pressing_id' => $user->pressing_id,
            'nom' => 'Lessives',
        ]);
        $product = Produit::create([
            'pressing_id' => $user->pressing_id,
            'categorie_produit_id' => $category->id,
            'nom' => 'Lessive liquide',
            'unite' => 'litre',
            'stock_actuel' => 5,
            'seuil_alerte' => 1,
            'prix_achat_moyen' => 1000,
        ]);
        $product->delete();

        $response = $this->actingAs($user)
            ->from(route('services.index').'#categories-produits')
            ->delete(route('categories-produits.destroy', $category));

        $response->assertRedirect(route('services.index').'#categories-produits')
            ->assertSessionHas('error');
        $this->assertDatabaseHas('categories_produits', ['id' => $category->id]);
    }

    public function test_caissier_cannot_open_product_categories(): void
    {
        $user = $this->createUser(role: 'caissier');

        $this->actingAs($user)->get(route('categories-produits.index'))->assertForbidden();
    }

    public function test_admin_cannot_update_a_category_from_another_pressing(): void
    {
        $user = $this->createUser();
        $otherPressing = Pressing::create(['nom' => 'Autre pressing']);
        $category = CategorieProduit::create([
            'pressing_id' => $otherPressing->id,
            'nom' => 'Lessives',
        ]);

        $response = $this->actingAs($user)->put(route('categories-produits.update', $category), [
            'nom_categorie' => 'Produits de lavage',
        ]);

        $response->assertNotFound();
        $this->assertDatabaseHas('categories_produits', [
            'id' => $category->id,
            'nom' => 'Lessives',
        ]);
    }

    private function createUser(string $role = 'admin'): User
    {
        $pressing = Pressing::create(['nom' => 'Pressing test']);

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
