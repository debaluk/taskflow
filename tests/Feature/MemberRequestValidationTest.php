<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberRequestValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_search_rejects_non_string_query_input(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson(route('members.search-users', [
                'q' => ['invalid'],
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('q');
    }

    public function test_member_creation_rejects_non_integer_user_id(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('members.store'), [
                'user_id' => ['invalid'],
            ])
            ->assertSessionHasErrors('user_id');

        $this->assertDatabaseCount('members', 0);
    }
}
