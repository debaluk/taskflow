<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_user_and_authenticates_them(): void
    {
        $this->post(route('register'), [
            'name' => 'TaskFlow User',
            'email' => 'taskflow-user@example.test',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', [
            'name' => 'TaskFlow User',
            'email' => 'taskflow-user@example.test',
        ]);

        $this->assertAuthenticated();
    }

    public function test_user_can_log_in_and_log_out(): void
    {
        $user = User::factory()->create([
            'email' => 'login-user@example.test',
            'password' => Hash::make('secret123'),
        ]);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'secret123',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);

        $this->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_invalid_login_is_rejected(): void
    {
        $user = User::factory()->create([
            'email' => 'invalid-login@example.test',
            'password' => Hash::make('correct-secret'),
        ]);

        $this->from(route('login'))
            ->post(route('login'), [
                'email' => $user->email,
                'password' => 'wrong-secret',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}
