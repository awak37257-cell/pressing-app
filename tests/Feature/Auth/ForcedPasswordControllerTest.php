<?php

namespace Tests\Feature\Auth;

use App\Models\Pressing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ForcedPasswordControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_changed_password_replaces_temporary_password_and_can_be_used_to_log_in(): void
    {
        $pressing = Pressing::create(['nom' => 'Pressing test']);
        $user = User::create([
            'pressing_id' => $pressing->id,
            'nom' => 'Employé test',
            'email' => 'employe@example.com',
            'password' => 'temporary-password',
            'role' => 'gerant',
            'actif' => true,
            'doit_changer_mot_de_passe' => true,
        ]);

        $response = $this->actingAs($user)->get(route('password.force.change'));

        $response->assertOk()->assertSee('autocomplete="new-password"', false);
        $this->assertSame(
            2,
            substr_count($response->getContent(), 'autocomplete="new-password"'),
        );

        $this->put(route('password.force.update'), [
            'password' => 'new-personal-password',
            'password_confirmation' => 'new-personal-password',
        ])->assertRedirect(route('dashboard'));

        $user = $user->fresh();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'doit_changer_mot_de_passe' => false,
        ]);
        $this->assertTrue(Hash::check('new-personal-password', $user->password));

        $this->post(route('logout'))->assertRedirect(route('login'));

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'temporary-password',
        ])->assertRedirectBackWithErrors('email');
        $this->assertGuest();

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'new-personal-password',
        ])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_operator_is_sent_to_the_work_queue_after_login_and_forced_password_change(): void
    {
        $pressing = Pressing::create(['nom' => 'Pressing test']);
        $operator = User::create([
            'pressing_id' => $pressing->id,
            'nom' => 'Opérateur test',
            'email' => 'operateur@example.com',
            'password' => 'temporary-password',
            'role' => 'operateur',
            'actif' => true,
            'doit_changer_mot_de_passe' => true,
        ]);

        $this->post(route('login.store'), [
            'email' => $operator->email,
            'password' => 'temporary-password',
        ])->assertRedirect(route('password.force.change'));

        $this->put(route('password.force.update'), [
            'password' => 'new-personal-password',
            'password_confirmation' => 'new-personal-password',
        ])->assertRedirect(route('traitement.index'));

        $this->post(route('logout'));

        $this->post(route('login.store'), [
            'email' => $operator->email,
            'password' => 'new-personal-password',
        ])->assertRedirect(route('traitement.index'));
    }
}
