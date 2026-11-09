<?php

namespace Tests\Feature;

use App\Models\CategorieDepense;
use App\Models\Pressing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepenseControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_form_shows_only_categories_for_the_users_pressing(): void
    {
        $user = $this->createAdmin();
        $ownCategory = CategorieDepense::create([
            'pressing_id' => $user->pressing_id,
            'nom' => 'Électricité',
        ]);
        $otherPressing = Pressing::create(['nom' => 'Autre pressing']);
        CategorieDepense::create([
            'pressing_id' => $otherPressing->id,
            'nom' => 'Catégorie privée',
        ]);

        $this->actingAs($user)
            ->get(route('depenses.create'))
            ->assertOk()
            ->assertSee($ownCategory->nom)
            ->assertDontSee('Catégorie privée');
    }

    public function test_new_pressing_receives_default_expense_categories(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->post(route('pressings.store'), [
                'nom' => 'Nouveau pressing',
            ])
            ->assertRedirect(route('pressings.index'));

        $pressing = Pressing::where('nom', 'Nouveau pressing')->firstOrFail();
        CategorieDepense::initialiserPourPressing($pressing);

        foreach (CategorieDepense::NOMS_PAR_DEFAUT as $nom) {
            $this->assertDatabaseHas('categories_depenses', [
                'pressing_id' => $pressing->id,
                'nom' => $nom,
            ]);
        }
    }

    private function createAdmin(): User
    {
        $pressing = Pressing::create(['nom' => 'Pressing test']);

        return User::create([
            'pressing_id' => $pressing->id,
            'nom' => 'Administrateur',
            'email' => 'admin@example.com',
            'password' => 'password',
            'role' => 'admin',
            'actif' => true,
        ]);
    }
}
