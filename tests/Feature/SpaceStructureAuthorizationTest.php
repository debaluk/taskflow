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

    public function test_folder_slug_is_not_reused_while_soft_deleted_record_exists(): void
    {
        [$owner, $space, $workspace] = $this->createSpace();

        $this->actingAs($owner)
            ->post(route('folders.store', [$workspace, $space]), [
                'name' => 'Folder Arsip',
            ])
            ->assertRedirect();

        $folder = Folder::query()
            ->where('space_id', $space->id)
            ->where('slug', 'folder-arsip')
            ->firstOrFail();

        $this->actingAs($owner)
            ->delete(route('folders.destroy', [$workspace, $space, $folder]))
            ->assertRedirect();

        $this->actingAs($owner)
            ->post(route('folders.store', [$workspace, $space]), [
                'name' => 'Folder Arsip',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('folders', [
            'id' => $folder->id,
            'slug' => 'folder-arsip',
        ]);

        $this->assertDatabaseHas('folders', [
            'space_id' => $space->id,
            'slug' => 'folder-arsip-2',
            'deleted_at' => null,
        ]);
    }

    public function test_task_list_slug_is_not_reused_while_soft_deleted_record_exists(): void
    {
        [$owner, $space] = $this->createSpace();

        $this->actingAs($owner)
            ->post(route('task-lists.store', $space), [
                'name' => 'List Arsip',
            ])
            ->assertRedirect();

        $list = TaskList::query()
            ->where('space_id', $space->id)
            ->where('slug', 'list-arsip')
            ->firstOrFail();

        $this->actingAs($owner)
            ->delete(route('task-lists.destroy', [$space, $list]))
            ->assertRedirect();

        $this->actingAs($owner)
            ->post(route('task-lists.store', $space), [
                'name' => 'List Arsip',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('task_lists', [
            'id' => $list->id,
            'slug' => 'list-arsip',
        ]);

        $this->assertDatabaseHas('task_lists', [
            'space_id' => $space->id,
            'slug' => 'list-arsip-2',
            'deleted_at' => null,
        ]);
    }

    public function test_folder_with_active_children_or_lists_cannot_be_deleted(): void
    {
        [$owner, $space, $workspace] = $this->createSpace();

        $folder = Folder::create([
            'uuid' => (string) Str::uuid(),
            'workspace_id' => $workspace->id,
            'space_id' => $space->id,
            'name' => 'Folder Induk',
            'slug' => 'folder-induk',
            'is_active' => true,
        ]);

        Folder::create([
            'uuid' => (string) Str::uuid(),
            'workspace_id' => $workspace->id,
            'space_id' => $space->id,
            'parent_id' => $folder->id,
            'name' => 'Subfolder Aktif',
            'slug' => 'subfolder-aktif',
            'is_active' => true,
        ]);

        $this->actingAs($owner)
            ->delete(route('folders.destroy', [$workspace, $space, $folder]))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('folders', [
            'id' => $folder->id,
            'deleted_at' => null,
        ]);

        $child = $folder->children()->firstOrFail();

        $child->delete();

        $list = TaskList::create([
            'uuid' => (string) Str::uuid(),
            'space_id' => $space->id,
            'folder_id' => $folder->id,
            'name' => 'List Aktif',
            'slug' => 'list-aktif',
            'is_active' => true,
        ]);

        $this->actingAs($owner)
            ->delete(route('folders.destroy', [$workspace, $space, $folder]))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('folders', [
            'id' => $folder->id,
            'deleted_at' => null,
        ]);

        $this->assertDatabaseHas('task_lists', [
            'id' => $list->id,
            'deleted_at' => null,
        ]);
    }

    public function test_folder_cannot_be_moved_under_its_own_descendant(): void
    {
        [$owner, $space, $workspace] = $this->createSpace();

        $parent = Folder::create([
            'uuid' => (string) Str::uuid(),
            'workspace_id' => $workspace->id,
            'space_id' => $space->id,
            'name' => 'Parent',
            'slug' => 'parent',
            'is_active' => true,
        ]);

        $child = Folder::create([
            'uuid' => (string) Str::uuid(),
            'workspace_id' => $workspace->id,
            'space_id' => $space->id,
            'parent_id' => $parent->id,
            'name' => 'Child',
            'slug' => 'child',
            'is_active' => true,
        ]);

        $this->actingAs($owner)
            ->put(route('folders.update', [$workspace, $space, $parent]), [
                'name' => 'Parent',
                'parent_id' => $child->id,
            ])
            ->assertUnprocessable();

        $this->assertDatabaseHas('folders', [
            'id' => $parent->id,
            'parent_id' => null,
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
