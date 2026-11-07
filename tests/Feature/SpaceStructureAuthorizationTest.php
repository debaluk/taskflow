<?php

namespace Tests\Feature;

use App\Models\Folder;
use App\Models\Space;
use App\Models\TaskList;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SpaceStructureAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_folder_and_list_in_owned_space(): void
    {
        [$owner, $space, $workspace] = $this->createSpace();

        $this->actingAs($owner)
            ->post(route('folders.store', [$workspace, $space]), [
                'name' => 'Folder Owner',
                'description' => 'Folder yang dibuat Owner',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('folders', [
            'workspace_id' => $workspace->id,
            'space_id' => $space->id,
            'name' => 'Folder Owner',
        ]);

        $this->actingAs($owner)
            ->post(route('task-lists.store', $space), [
                'name' => 'List Owner',
                'description' => 'List yang dibuat Owner',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('task_lists', [
            'space_id' => $space->id,
            'name' => 'List Owner',
        ]);
    }

    public function test_folder_slug_collisions_are_resolved_without_database_errors(): void
    {
        [$owner, $space, $workspace] = $this->createSpace();

        $this->actingAs($owner)
            ->post(route('folders.store', [$workspace, $space]), [
                'name' => 'Folder Riset',
            ])
            ->assertRedirect();

        $this->actingAs($owner)
            ->post(route('folders.store', [$workspace, $space]), [
                'name' => 'Folder Riset',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('folders', [
            'space_id' => $space->id,
            'slug' => 'folder-riset',
        ]);

        $this->assertDatabaseHas('folders', [
            'space_id' => $space->id,
            'slug' => 'folder-riset-2',
        ]);

        $folder = Folder::create([
            'uuid' => (string) Str::uuid(),
            'workspace_id' => $workspace->id,
            'space_id' => $space->id,
            'name' => 'Folder Lain',
            'slug' => 'folder-lain',
            'is_active' => true,
        ]);

        $this->actingAs($owner)
            ->put(route('folders.update', [$workspace, $space, $folder]), [
                'name' => 'Folder Riset',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('folders', [
            'id' => $folder->id,
            'slug' => 'folder-riset-3',
        ]);
    }

    public function test_member_cannot_update_toggle_or_delete_folder_and_list(): void
    {
        [$owner, $space, $workspace] = $this->createSpace();
        $member = User::factory()->create();

        $space->members()->attach($member->id, [
            'invited_by' => $owner->id,
        ]);

        $folder = Folder::create([
            'uuid' => (string) Str::uuid(),
            'workspace_id' => $workspace->id,
            'space_id' => $space->id,
            'name' => 'Folder Existing',
            'slug' => 'folder-existing',
            'is_active' => true,
        ]);

        $list = TaskList::create([
            'uuid' => (string) Str::uuid(),
            'space_id' => $space->id,
            'name' => 'List Existing',
            'slug' => 'list-existing',
            'is_active' => true,
        ]);

        $this->actingAs($member)
            ->put(route('folders.update', [$workspace, $space, $folder]), [
                'name' => 'Folder Diubah',
            ])
            ->assertForbidden();

        $this->actingAs($member)
            ->patch(route('folders.toggle', [$workspace, $space, $folder]))
            ->assertForbidden();

        $this->actingAs($member)
            ->delete(route('folders.destroy', [$workspace, $space, $folder]))
            ->assertForbidden();

        $this->actingAs($member)
            ->put(route('task-lists.update', [$space, $list]), [
                'name' => 'List Diubah',
            ])
            ->assertForbidden();

        $this->actingAs($member)
            ->patch(route('task-lists.toggle', [$space, $list]))
            ->assertForbidden();

        $this->actingAs($member)
            ->delete(route('task-lists.destroy', [$space, $list]))
            ->assertForbidden();
    }

    private function createSpace(): array
    {
        $owner = User::factory()->create();

        $workspace = Workspace::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Workspace Structure',
            'slug' => 'workspace-structure-' . Str::lower(Str::random(6)),
            'owner_id' => $owner->id,
            'is_active' => true,
        ]);

        $space = Space::create([
            'uuid' => (string) Str::uuid(),
            'workspace_id' => $workspace->id,
            'owner_id' => $owner->id,
            'name' => 'Space Structure',
            'slug' => 'space-structure-' . Str::lower(Str::random(6)),
            'is_active' => true,
        ]);

        $space->members()->attach($owner->id, [
            'invited_by' => $owner->id,
        ]);

        return [$owner, $space, $workspace];
    }
}
