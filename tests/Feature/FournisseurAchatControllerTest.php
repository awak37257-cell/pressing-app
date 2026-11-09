<?php

namespace Tests\Feature;

use App\Models\Achat;
use App\Models\CategorieProduit;
use App\Models\Fournisseur;
use App\Models\Pressing;
use App\Models\Produit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FournisseurAchatControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_and_update_a_supplier_with_product_categories(): void
    {
        $user = $this->createUser();
        $lessives = $this->createCategory($user, 'Lessives');
        $detergents = $this->createCategory($user, 'Détachants');

        $this->actingAs($user)->post(route('fournisseurs.store'), [
            'nom' => 'Fournisseur test',
            'categorie_produit_ids' => [$lessives->id],
        ])->assertRedirect(route('fournisseurs.index'));

        $supplier = Fournisseur::firstOrFail();
        $this->assertDatabaseHas('categorie_produit_fournisseur', [
            'fournisseur_id' => $supplier->id,
            'categorie_produit_id' => $lessives->id,
        ]);

        $this->put(route('fournisseurs.update', $supplier), [
            'nom' => 'Fournisseur test',
            'categorie_produit_ids' => [$detergents->id],
        ])->assertRedirect(route('fournisseurs.index'));

        $this->assertDatabaseHas('categorie_produit_fournisseur', [
            'fournisseur_id' => $supplier->id,
            'categorie_produit_id' => $detergents->id,
        ]);
        $this->assertDatabaseMissing('categorie_produit_fournisseur', [
            'fournisseur_id' => $supplier->id,
            'categorie_produit_id' => $lessives->id,
        ]);
    }

    public function test_supplier_cannot_be_assigned_a_category_from_another_pressing(): void
    {
        $user = $this->createUser();
        $otherPressing = Pressing::create(['nom' => 'Autre pressing']);
        $category = CategorieProduit::create([
            'pressing_id' => $otherPressing->id,
            'nom' => 'Lessives',
        ]);

        $this->actingAs($user)
            ->from(route('fournisseurs.create'))
            ->post(route('fournisseurs.store'), [
                'nom' => 'Fournisseur test',
                'categorie_produit_ids' => [$category->id],
            ])
            ->assertRedirect(route('fournisseurs.create'))
            ->assertSessionHasErrors('categorie_produit_ids.0');

        $this->assertDatabaseCount('fournisseurs', 0);
    }

    public function test_supplier_edit_form_preselects_its_product_categories(): void
    {
        $user = $this->createUser();
        $category = $this->createCategory($user, 'Lessives');
        $supplier = Fournisseur::create([
            'pressing_id' => $user->pressing_id,
            'nom' => 'Fournisseur lessives',
        ]);
        $supplier->categories()->attach($category);

        $this->actingAs($user)
            ->get(route('fournisseurs.edit', $supplier))
            ->assertOk()
            ->assertViewHas('categoriesSelectionnees', [$category->id]);
    }

    public function test_purchase_rejects_products_outside_the_selected_supplier_categories(): void
    {
        $user = $this->createUser();
        $supplierCategory = $this->createCategory($user, 'Lessives');
        $otherCategory = $this->createCategory($user, 'Détachants');
        $supplier = Fournisseur::create([
            'pressing_id' => $user->pressing_id,
            'nom' => 'Fournisseur lessives',
        ]);
        $supplier->categories()->attach($supplierCategory);
        $product = Produit::create([
            'pressing_id' => $user->pressing_id,
            'categorie_produit_id' => $otherCategory->id,
            'nom' => 'Détachant',
            'unite' => 'litre',
        ]);

        $this->actingAs($user)
            ->from(route('achats.create'))
            ->post(route('achats.store'), [
                'fournisseur_id' => $supplier->id,
                'date_achat' => now()->toDateString(),
                'mode_paiement' => 'especes',
                'lignes' => [[
                    'produit_id' => $product->id,
                    'quantite' => 2,
                    'prix_unitaire' => 1000,
                ]],
            ])
            ->assertRedirect(route('achats.create'))
            ->assertSessionHasErrors('lignes.0.produit_id');

        $this->assertDatabaseCount('achats', 0);
    }

    public function test_purchase_accepts_a_product_in_the_selected_supplier_categories(): void
    {
        $user = $this->createUser();
        $category = $this->createCategory($user, 'Lessives');
        $supplier = Fournisseur::create([
            'pressing_id' => $user->pressing_id,
            'nom' => 'Fournisseur lessives',
        ]);
        $supplier->categories()->attach($category);
        $product = Produit::create([
            'pressing_id' => $user->pressing_id,
            'categorie_produit_id' => $category->id,
            'nom' => 'Lessive liquide',
            'unite' => 'litre',
        ]);

        $response = $this->actingAs($user)->post(route('achats.store'), [
            'fournisseur_id' => $supplier->id,
            'date_achat' => now()->toDateString(),
            'mode_paiement' => 'especes',
            'lignes' => [[
                'produit_id' => $product->id,
                'quantite' => 2,
                'prix_unitaire' => 1000,
            ]],
        ]);

        $achat = Achat::firstOrFail();
        $response->assertRedirect(route('achats.show', $achat));
        $this->assertDatabaseHas('achats', [
            'id' => $achat->id,
            'fournisseur_id' => $supplier->id,
        ]);
        $this->assertDatabaseHas('lignes_achat', [
            'produit_id' => $product->id,
            'quantite' => 2,
        ]);
        $this->assertDatabaseHas('produits', [
            'id' => $product->id,
            'stock_actuel' => 2,
        ]);
    }

    public function test_purchase_form_includes_supplier_categories_and_product_category_ids(): void
    {
        $user = $this->createUser();
        $category = $this->createCategory($user, 'Lessives');
        $supplier = Fournisseur::create([
            'pressing_id' => $user->pressing_id,
            'nom' => 'Fournisseur lessives',
        ]);
        $supplier->categories()->attach($category);
        Produit::create([
            'pressing_id' => $user->pressing_id,
            'categorie_produit_id' => $category->id,
            'nom' => 'Lessive liquide',
            'unite' => 'litre',
        ]);

        $this->actingAs($user)
            ->get(route('achats.create'))
            ->assertOk()
            ->assertSee('const categoriesFournisseurs =')
            ->assertSee('categorie_id')
            ->assertSee('Lessive liquide')
            ->assertViewHas('fournisseurs', fn ($fournisseurs) => $fournisseurs->first()->categories->contains($category));
    }

    private function createUser(): User
    {
        $pressing = Pressing::create(['nom' => 'Pressing test']);

        return User::create([
            'pressing_id' => $pressing->id,
            'nom' => 'Administrateur test',
            'email' => 'admin@example.com',
            'password' => 'password',
            'role' => 'admin',
            'actif' => true,
        ]);
    }

    private function createCategory(User $user, string $name): CategorieProduit
    {
        return CategorieProduit::create([
            'pressing_id' => $user->pressing_id,
            'nom' => $name,
        ]);
    }
}
