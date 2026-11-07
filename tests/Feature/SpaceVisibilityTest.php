<?php

namespace Tests\Feature;

use App\Models\Space;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SpaceVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_space_index_only_shows_spaces_owned_or_shared_with_user(): void
    {
        [$owner, $space] = $this->createSpace();
        $member = User::factory()->create();
        $outsider = User::factory()->create();

        $space->members()->attach($member->id, [
            'invited_by' => $owner->id,
        ]);

        $this->actingAs($member)
            ->get(route('spaces.index'))
            ->assertOk()
            ->assertSee($space->name);

        $this->actingAs($outsider)
            ->get(route('spaces.index'))
            ->assertOk()
            ->assertDontSee($space->name);
    }

    public function test_space_member_can_view_space_but_unrelated_user_cannot_open_it(): void
    {
        [$owner, $space] = $this->createSpace();
        $member = User::factory()->create();
        $outsider = User::factory()->create();

        $space->members()->attach($member->id, [
            'invited_by' => $owner->id,
        ]);

        $this->actingAs($member)
            ->get(route('spaces.show', $space))
            ->assertRedirect(route('tasks.index', ['space_id' => $space->id]));

        $this->actingAs($outsider)
            ->get(route('spaces.show', $space))
            ->assertNotFound();
    }

    public function test_space_member_can_view_folder_and_list_but_cannot_manage_them(): void
    {
        [$owner, $space, $workspace] = $this->createSpace();
        $member = User::factory()->create();

        $space->members()->attach($member->id, [
            'invited_by' => $owner->id,
        ]);

        $this->actingAs($member)
            ->get(route('folders.index', [$workspace, $space]))
            ->assertOk();

        $this->actingAs($member)
            ->get(route('task-lists.index', $space))
            ->assertOk();

        $this->actingAs($member)
            ->post(route('folders.store', [$workspace, $space]), [
                'name' => 'Folder Baru',
            ])
            ->assertForbidden();

        $this->actingAs($member)
            ->post(route('task-lists.store', $space), [
                'name' => 'List Baru',
            ])
            ->assertForbidden();
    }

    public function test_new_space_creator_is_saved_as_owner_and_initial_member(): void
    {
        $owner = User::factory()->create();
        $this->createWorkspace($owner);

        $this->actingAs($owner)
            ->post(route('spaces.store'), [
                'name' => 'Space Baru',
                'description' => 'Space milik owner',
            ])
            ->assertRedirect();

        $space = Space::query()
            ->where('name', 'Space Baru')
            ->firstOrFail();

        $this->assertSame($owner->id, $space->owner_id);
        $this->assertDatabaseHas('space_members', [
            'space_id' => $space->id,
            'user_id' => $owner->id,
            'invited_by' => $owner->id,
        ]);
    }

    private function createSpace(): array
    {
        $owner = User::factory()->create();
        $workspace = $this->createWorkspace($owner);

        $space = Space::create([
            'uuid' => (string) Str::uuid(),
            'workspace_id' => $workspace->id,
            'owner_id' => $owner->id,
            'name' => 'Space ' . Str::random(5),
            'slug' => 'space-' . Str::lower(Str::random(8)),
            'is_active' => true,
        ]);

        $space->members()->attach($owner->id, [
            'invited_by' => $owner->id,
        ]);

        return [$owner, $space, $workspace];
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
