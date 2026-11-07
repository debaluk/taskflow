<?php

namespace Tests\Feature;

use App\Models\Space;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SpaceMemberManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_space_owner_can_view_and_add_members(): void
    {
        [$owner, $space] = $this->createOwnedSpace();
        $member = User::factory()->create();

        $this->actingAs($owner)
            ->get(route('spaces.members.index', $space))
            ->assertOk();

        $this->actingAs($owner)
            ->post(route('spaces.members.store', $space), [
                'user_id' => $member->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('space_members', [
            'space_id' => $space->id,
            'user_id' => $member->id,
            'invited_by' => $owner->id,
        ]);
    }

    public function test_non_owner_cannot_manage_space_members(): void
    {
        [$owner, $space] = $this->createOwnedSpace();
        $member = User::factory()->create();
        $anotherUser = User::factory()->create();

        $this->actingAs($member)
            ->get(route('spaces.members.index', $space))
            ->assertForbidden();

        $this->actingAs($member)
            ->post(route('spaces.members.store', $space), [
                'user_id' => $anotherUser->id,
            ])
            ->assertForbidden();

        $this->actingAs($owner)
            ->post(route('spaces.members.store', $space), [
                'user_id' => $member->id,
            ])
            ->assertRedirect();

        $this->actingAs($member)
            ->delete(route('spaces.members.destroy', [$space, $member]))
            ->assertForbidden();

        $this->assertDatabaseHas('space_members', [
            'space_id' => $space->id,
            'user_id' => $member->id,
        ]);
    }

    public function test_space_owner_cannot_remove_own_initial_membership(): void
    {
        [$owner, $space] = $this->createOwnedSpace();

        $this->actingAs($owner)
            ->delete(route('spaces.members.destroy', [$space, $owner]))
            ->assertForbidden();

        $this->assertDatabaseHas('space_members', [
            'space_id' => $space->id,
            'user_id' => $owner->id,
        ]);
    }

    public function test_space_owner_can_remove_member(): void
    {
        [$owner, $space] = $this->createOwnedSpace();
        $member = User::factory()->create();

        $space->members()->attach($member->id, [
            'invited_by' => $owner->id,
        ]);

        $this->actingAs($owner)
            ->delete(route('spaces.members.destroy', [$space, $member]))
            ->assertRedirect();

        $this->assertDatabaseMissing('space_members', [
            'space_id' => $space->id,
            'user_id' => $member->id,
        ]);
    }

    public function test_database_requires_an_owner_for_every_space(): void
    {
        $owner = User::factory()->create();
        $workspace = $this->createWorkspace($owner);

        $this->expectException(QueryException::class);

        Space::create([
            'uuid' => (string) Str::uuid(),
            'workspace_id' => $workspace->id,
            'owner_id' => null,
            'name' => 'Space Tanpa Owner',
            'slug' => 'space-tanpa-owner',
            'is_active' => true,
        ]);
    }

    private function createOwnedSpace(): array
    {
        $owner = User::factory()->create();
        $workspace = $this->createWorkspace($owner);

        $space = Space::create([
            'uuid' => (string) Str::uuid(),
            'workspace_id' => $workspace->id,
            'owner_id' => $owner->id,
            'name' => 'Space Test',
            'slug' => 'space-test-' . Str::lower(Str::random(6)),
            'is_active' => true,
        ]);

        return [$owner, $space];
    }

    private function createWorkspace(User $owner): Workspace
    {
        return Workspace::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Workspace Internal',
            'slug' => 'workspace-internal-' . Str::lower(Str::random(6)),
            'owner_id' => $owner->id,
            'is_active' => true,
        ]);
    }
}
