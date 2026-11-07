<?php

namespace Tests\Feature;

use App\Models\Space;
use App\Models\Task;
use App\Models\TaskList;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TaskAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_space_member_can_create_task_in_shared_space(): void
    {
        [$owner, $space, $list] = $this->createSpace();
        $member = User::factory()->create();

        $space->members()->attach($member->id, [
            'invited_by' => $owner->id,
        ]);

        $this->actingAs($member)
            ->post(route('tasks.store'), [
                'title' => 'Task dari anggota',
                'description' => 'Dibuat oleh member Space',
                'status' => 'open',
                'priority' => 'normal',
                'list_id' => $list->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('tasks', [
            'space_id' => $space->id,
            'list_id' => $list->id,
            'title' => 'Task dari anggota',
            'created_by' => $member->id,
        ]);
    }

    public function test_assignee_can_update_status_and_add_note_but_not_manage_task(): void
    {
        [$owner, $space, $list] = $this->createSpace();
        $creator = User::factory()->create();
        $assignee = User::factory()->create();

        $space->members()->attach([
            $creator->id => ['invited_by' => $owner->id],
            $assignee->id => ['invited_by' => $owner->id],
        ]);

        $task = $this->createTask($space, $list, $creator);
        $task->assignees()->attach($assignee->id);

        $this->actingAs($assignee)
            ->getJson(route('tasks.show', $task))
            ->assertOk()
            ->assertJsonPath('permissions.manage', false)
            ->assertJsonPath('permissions.update_status', true)
            ->assertJsonPath('permissions.add_note', true);

        $this->actingAs($assignee)
            ->patchJson(route('tasks.status', $task), [
                'status' => 'in_progress',
            ])
            ->assertOk()
            ->assertJsonPath('status', 'in_progress');

        $this->actingAs($assignee)
            ->postJson(route('tasks.notes.store', $task), [
                'note' => 'Sedang saya kerjakan.',
            ])
            ->assertCreated()
            ->assertJsonPath('note.description', 'Sedang saya kerjakan.');

        $this->assertDatabaseHas('activities', [
            'workspace_id' => $space->workspace_id,
            'user_id' => $assignee->id,
            'action' => 'task_note',
            'subject_type' => Task::class,
            'subject_id' => $task->id,
            'description' => 'Sedang saya kerjakan.',
        ]);

        $this->actingAs($assignee)
            ->put(route('tasks.update', $task), [
                'title' => 'Diubah assignee',
                'description' => null,
                'status' => 'in_progress',
                'priority' => 'normal',
            ])
            ->assertForbidden();

        $this->actingAs($assignee)
            ->delete(route('tasks.destroy', $task))
            ->assertForbidden();

        $this->actingAs($assignee)
            ->patchJson(route('tasks.assignee.update', $task), [
                'user_id' => $creator->id,
            ])
            ->assertForbidden();

        $this->actingAs($assignee)
            ->post(route('tasks.subtasks.store', $task), [
                'title' => 'Subtask tidak boleh dibuat',
                'status' => 'open',
                'priority' => 'normal',
            ])
            ->assertForbidden();

        $this->actingAs($assignee)
            ->postJson(route('tasks.dependency.store', $task), [
                'depends_on_task_id' => $task->id + 1,
                'dependency_type' => 'FS',
            ])
            ->assertForbidden();
    }

    public function test_task_creator_can_manage_own_task_and_space_owner_can_manage_all_tasks(): void
    {
        [$owner, $space, $list] = $this->createSpace();
        $creator = User::factory()->create();

        $space->members()->attach($creator->id, [
            'invited_by' => $owner->id,
        ]);

        $task = $this->createTask($space, $list, $creator);

        $this->actingAs($creator)
            ->put(route('tasks.update', $task), [
                'title' => 'Diubah pembuat',
                'description' => 'Detail diperbarui',
                'status' => 'open',
                'priority' => 'high',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Diubah pembuat',
        ]);

        $this->actingAs($owner)
            ->delete(route('tasks.destroy', $task))
            ->assertRedirect();

        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }

    public function test_space_member_without_assignment_can_view_but_cannot_change_status_or_add_note(): void
    {
        [$owner, $space, $list] = $this->createSpace();
        $creator = User::factory()->create();
        $member = User::factory()->create();

        $space->members()->attach($creator->id, [
            'invited_by' => $owner->id,
        ]);
        $space->members()->attach($member->id, [
            'invited_by' => $owner->id,
        ]);

        $task = $this->createTask($space, $list, $creator);

        $this->actingAs($member)
            ->getJson(route('tasks.show', $task))
            ->assertOk()
            ->assertJsonPath('permissions.manage', false)
            ->assertJsonPath('permissions.update_status', false)
            ->assertJsonPath('permissions.add_note', false);

        $this->actingAs($member)
            ->patchJson(route('tasks.status', $task), [
                'status' => 'done',
            ])
            ->assertForbidden();

        $this->actingAs($member)
            ->postJson(route('tasks.notes.store', $task), [
                'note' => 'Tidak berwenang.',
            ])
            ->assertForbidden();
    }

    public function test_unrelated_user_cannot_view_or_change_task(): void
    {
        [$owner, $space, $list] = $this->createSpace();
        $creator = User::factory()->create();
        $outsider = User::factory()->create();

        $space->members()->attach($creator->id, [
            'invited_by' => $owner->id,
        ]);

        $task = $this->createTask($space, $list, $creator);

        $this->actingAs($outsider)
            ->getJson(route('tasks.show', $task))
            ->assertForbidden();

        $this->actingAs($outsider)
            ->patchJson(route('tasks.status', $task), [
                'status' => 'done',
            ])
            ->assertForbidden();

        $this->actingAs($outsider)
            ->postJson(route('tasks.notes.store', $task), [
                'note' => 'Tidak boleh.',
            ])
            ->assertForbidden();
    }

    public function test_task_can_only_be_assigned_to_a_member_of_its_space(): void
    {
        [$owner, $space, $list] = $this->createSpace();
        $creator = User::factory()->create();
        $member = User::factory()->create();
        $outsider = User::factory()->create();

        $space->members()->attach($creator->id, [
            'invited_by' => $owner->id,
        ]);
        $space->members()->attach($member->id, [
            'invited_by' => $owner->id,
        ]);

        $task = $this->createTask($space, $list, $creator);

        $this->actingAs($creator)
            ->patchJson(route('tasks.assignee.update', $task), [
                'user_id' => $outsider->id,
            ])
            ->assertUnprocessable();

        $this->actingAs($creator)
            ->patchJson(route('tasks.assignee.update', $task), [
                'user_id' => $member->id,
            ])
            ->assertOk();

        $this->assertDatabaseHas('task_assignees', [
            'task_id' => $task->id,
            'user_id' => $member->id,
        ]);
    }

    private function createSpace(): array
    {
        $owner = User::factory()->create();

        $workspace = Workspace::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Workspace Task Test',
            'slug' => 'workspace-task-' . Str::lower(Str::random(8)),
            'owner_id' => $owner->id,
            'is_active' => true,
        ]);

        $space = Space::create([
            'uuid' => (string) Str::uuid(),
            'workspace_id' => $workspace->id,
            'owner_id' => $owner->id,
            'name' => 'Space Task Test',
            'slug' => 'space-task-' . Str::lower(Str::random(8)),
            'is_active' => true,
        ]);

        $space->members()->attach($owner->id, [
            'invited_by' => $owner->id,
        ]);

        $list = TaskList::create([
            'uuid' => (string) Str::uuid(),
            'space_id' => $space->id,
            'name' => 'List Test',
            'slug' => 'list-test',
            'is_active' => true,
        ]);

        return [$owner, $space, $list];
    }

    private function createTask(Space $space, TaskList $list, User $creator): Task
    {
        return Task::create([
            'uuid' => (string) Str::uuid(),
            'workspace_id' => $space->workspace_id,
            'space_id' => $space->id,
            'list_id' => $list->id,
            'title' => 'Task test',
            'task_type' => 'task',
            'status' => 'open',
            'priority' => 'normal',
            'position' => 1,
            'created_by' => $creator->id,
            'version' => 1,
        ]);
    }
}
