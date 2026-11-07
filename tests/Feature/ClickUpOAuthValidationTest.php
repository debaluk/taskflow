<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ClickUpOAuthValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_callback_rejects_non_string_authorization_code_without_calling_provider(): void
    {
        $user = User::factory()->create();
        Http::fake();

        $this->actingAs($user)
            ->get(route('settings.clickup.callback', [
                'code' => ['invalid'],
            ]))
            ->assertRedirect(route('settings'))
            ->assertSessionHas('error', 'Authorization code ClickUp tidak valid.');

        Http::assertNothingSent();
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
            ->get(route('settings.clickup.callback', [
                'code' => 'valid-looking-code',
            ]))
            ->assertRedirect(route('settings'))
            ->assertSessionHas('error', 'Respons koneksi ClickUp tidak valid.')
            ->assertSessionMissing('clickup_access_token');

        $this->assertFalse(session()->has('clickup_connected'));
    }
}
