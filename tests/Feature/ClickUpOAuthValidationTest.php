<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ClickUpOAuthValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_connect_generates_and_sends_a_session_bound_state(): void
    {
        $user = User::factory()->create();

        config([
            'services.clickup.client_id' => 'test-client',
            'services.clickup.redirect_uri' => 'https://example.test/callback',
        ]);

        $response = $this->actingAs($user)
            ->get(route('settings.clickup.connect'));

        $response->assertRedirect();

        $redirect = $response->headers->get('Location');
        parse_str((string) parse_url($redirect, PHP_URL_QUERY), $query);

        $this->assertSame('test-client', $query['client_id'] ?? null);
        $this->assertSame('https://example.test/callback', $query['redirect_uri'] ?? null);
        $this->assertIsString($query['state'] ?? null);
        $this->assertSame(64, strlen($query['state']));
        $this->assertSame($query['state'], session('clickup_oauth_state'));
    }

    public function test_callback_rejects_missing_or_mismatched_state_without_calling_provider(): void
    {
        $user = User::factory()->create();
        Http::fake();

        $this->actingAs($user)
            ->withSession(['clickup_oauth_state' => 'expected-state'])
            ->get(route('settings.clickup.callback', [
                'state' => 'attacker-state',
                'code' => 'authorization-code',
            ]))
            ->assertRedirect(route('settings'))
            ->assertSessionHas('error', 'Validasi keamanan koneksi ClickUp gagal. Silakan coba hubungkan kembali.');

        Http::assertNothingSent();
        $this->assertSame('expected-state', session('clickup_oauth_state'));
    }

    public function test_callback_rejects_non_string_authorization_code_without_calling_provider(): void
    {
        $user = User::factory()->create();
        Http::fake();

        $this->actingAs($user)
            ->withSession(['clickup_oauth_state' => 'test-state'])
            ->get(route('settings.clickup.callback', [
                'state' => 'test-state',
                'code' => ['invalid'],
            ]))
            ->assertRedirect(route('settings'))
            ->assertSessionHas('error', 'Authorization code ClickUp tidak valid.');

        Http::assertNothingSent();
    }

    public function test_callback_handles_provider_connection_failure_without_server_error(): void
    {
        $user = User::factory()->create();

        config([
            'services.clickup.client_id' => 'test-client',
            'services.clickup.client_secret' => 'test-secret',
        ]);

        Http::fake(function () {
            throw new \\Illuminate\\Http\\Client\\ConnectionException('Connection failed.');
        });

        $this->actingAs($user)
            ->withSession(['clickup_oauth_state' => 'test-state'])
            ->get(route('settings.clickup.callback', [
                'state' => 'test-state',
                'code' => 'valid-looking-code',
            ]))
            ->assertRedirect(route('settings'))
            ->assertSessionHas('error', 'Tidak dapat terhubung ke ClickUp. Silakan coba lagi.')
            ->assertSessionMissing('clickup_access_token');

        $this->assertFalse(session()->has('clickup_connected'));
    }

    public function test_callback_does_not_store_an_invalid_access_token_response(): void
    {
        $user = User::factory()->create();

        config([
            'services.clickup.client_id' => 'test-client',
            'services.clickup.client_secret' => 'test-secret',
        ]);

        Http::fake([
            'https://api.clickup.com/api/v2/oauth/token' => Http::response([
                'unexpected' => 'payload',
            ], 200),
        ]);

        $this->actingAs($user)
            ->withSession(['clickup_oauth_state' => 'test-state'])
            ->get(route('settings.clickup.callback', [
                'state' => 'test-state',
                'code' => 'valid-looking-code',
            ]))
            ->assertRedirect(route('settings'))
            ->assertSessionHas('error', 'Respons koneksi ClickUp tidak valid.')
            ->assertSessionMissing('clickup_access_token');

        $this->assertFalse(session()->has('clickup_connected'));
    }
}
