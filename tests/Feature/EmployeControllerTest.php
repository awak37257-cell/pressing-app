<?php

namespace Tests\Feature;

use App\Models\Pressing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_form_uses_operateur_as_the_role_value(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('employes.create'));

        $response->assertOk()
            ->assertSee('value="operateur"', false)
            ->assertSee('Opérateur');
    }

    public function test_edit_form_selects_the_operateur_role(): void
    {
        $admin = $this->createAdmin();
        $employee = User::create([
            'pressing_id' => $admin->pressing_id,
            'nom' => 'Employé opérateur',
            'email' => 'operateur@example.com',
            'password' => 'password',
            'role' => 'operateur',
            'actif' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('employes.edit', $employee));

        $response->assertOk()
            ->assertSee('value="operateur" selected', false)
            ->assertSee('Opérateur');
    }

    public function test_admin_can_create_an_employee_with_the_operateur_role(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('employes.store'), [
            'nom' => 'Employé opérateur',
            'email' => 'operateur@example.com',
            'role' => 'operateur',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertRedirect(route('employes.index'));
        $this->assertDatabaseHas('users', [
            'pressing_id' => $admin->pressing_id,
            'email' => 'operateur@example.com',
            'role' => 'operateur',
        ]);
    }

    public function test_admin_can_update_an_employee_to_the_operateur_role(): void
    {
        $admin = $this->createAdmin();
        $employee = User::create([
            'pressing_id' => $admin->pressing_id,
            'nom' => 'Employé gérant',
            'email' => 'employe@example.com',
            'password' => 'password',
            'role' => 'gerant',
            'actif' => true,
        ]);

        $response = $this->actingAs($admin)->put(route('employes.update', $employee), [
            'nom' => 'Employé opérateur',
            'email' => 'employe@example.com',
            'role' => 'operateur',
        ]);

        $response->assertRedirect(route('employes.index'));
        $this->assertDatabaseHas('users', [
            'id' => $employee->id,
            'role' => 'operateur',
        ]);
    }

    public function test_admin_cannot_create_an_employee_with_the_legacy_atelier_role(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)
            ->from(route('employes.create'))
            ->post(route('employes.store'), [
                'nom' => 'Employé atelier',
                'email' => 'atelier@example.com',
                'role' => 'atelier',
                'password' => 'secret123',
                'password_confirmation' => 'secret123',
            ]);

        $response->assertRedirect(route('employes.create'))
            ->assertSessionHasErrors('role');
        $this->assertDatabaseMissing('users', [
            'email' => 'atelier@example.com',
        ]);
    }

    private function createAdmin(): User
    {
        $pressing = Pressing::create(['nom' => 'Pressing test']);

        return User::create([
            'pressing_id' => $pressing->id,
            'nom' => 'Admin test',
            'email' => 'admin@example.com',
            'password' => 'password',
            'role' => 'admin',
            'actif' => true,
        ]);
    }
}
