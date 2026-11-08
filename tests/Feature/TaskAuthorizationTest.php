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
                'return_url' => 'https://evil.example/phishing',
            ])
            ->assertRedirect(route('tasks.index', [
                'space_id' => $space->id,
                'list_id' => $list->id,
            ]));

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
            ->getJson(route('tasks.data', $task))
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
            ->patchJson(route('tasks.reorder'), [
                'space_id' => $space->id,
                'list_id' => $list->id,
                'columns' => [
                    [
                        'status' => 'done',
                        'tasks' => [$task->id],
                    ],
                ],
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
                'return_url' => 'https://evil.example/phishing',
            ])
            ->assertRedirect(route('tasks.index'));

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Diubah pembuat',
        ]);

        $this->actingAs($owner)
            ->delete(route('tasks.destroy', $task), [
                'return_url' => 'https://evil.example/phishing',
            ])
            ->assertRedirect(route('tasks.index'));

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

    public function test_invalid_return_url_type_is_rejected_before_task_mutation(): void
    {
        [$owner, $space, $list] = $this->createSpace();
        $creator = User::factory()->create();

        $space->members()->attach($creator->id, [
            'invited_by' => $owner->id,
        ]);

        $task = $this->createTask($space, $list, $creator);

        $this->actingAs($creator)
            ->put(route('tasks.update', $task), [
                'title' => 'Task Tidak Boleh Berubah',
                'description' => null,
                'status' => 'open',
                'priority' => 'normal',
                'return_url' => ['https://evil.example'],
            ])
            ->assertSessionHasErrors('return_url');

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Task test',
        ]);

        $this->actingAs($creator)
            ->delete(route('tasks.destroy', $task), [
                'return_url' => ['https://evil.example'],
            ])
            ->assertSessionHasErrors('return_url');

        $this->assertNotSoftDeleted('tasks', [
            'id' => $task->id,
        ]);
    }

    public function test_task_creator_can_delete_own_task(): void
    {
        [$owner, $space, $list] = $this->createSpace();
        $creator = User::factory()->create();

        $space->members()->attach($creator->id, [
            'invited_by' => $owner->id,
        ]);

        $task = $this->createTask($space, $list, $creator);

        $this->actingAs($creator)
            ->delete(route('tasks.destroy', $task))
            ->assertRedirect();

        $this->assertSoftDeleted('tasks', [
            'id' => $task->id,
        ]);
    }

    public function test_deleting_parent_task_soft_deletes_all_nested_subtasks(): void
    {
        [$owner, $space, $list] = $this->createSpace();
        $creator = User::factory()->create();

        $space->members()->attach($creator->id, [
            'invited_by' => $owner->id,
        ]);

        $parent = $this->createTask($space, $list, $creator);
        $child = $this->createTask($space, $list, $creator);
        $child->update(['parent_id' => $parent->id]);

        $grandchild = $this->createTask($space, $list, $creator);
        $grandchild->update(['parent_id' => $child->id]);

        $this->actingAs($creator)
            ->delete(route('tasks.destroy', $parent))
            ->assertRedirect();

        $this->assertSoftDeleted('tasks', ['id' => $parent->id]);
        $this->assertSoftDeleted('tasks', ['id' => $child->id]);
        $this->assertSoftDeleted('tasks', ['id' => $grandchild->id]);
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

    public function test_task_detail_page_preserves_safe_origin_url(): void
    {
        [$owner, $space, $list] = $this->createSpace();
        $task = $this->createTask($space, $list, $owner);
        $origin = route('tasks.board', [
            'space_id' => $space->id,
            'list_id' => $list->id,
        ]);

        $this->actingAs($owner)
            ->get(route('tasks.show', [
                'task' => $task->id,
                'return_url' => $origin,
            ]))
            ->assertOk()
            ->assertViewIs('tasks.show')
            ->assertSee('Task test')
            ->assertSee(e($origin), false);
    }

    public function test_task_update_redirects_to_validated_origin_url(): void
    {
        [$owner, $space, $list] = $this->createSpace();
        $task = $this->createTask($space, $list, $owner);
        $origin = route('tasks.calendar', [
            'month' => 4,
            'year' => 2027,
            'space_id' => $space->id,
            'list_id' => $list->id,
        ]);

        $this->actingAs($owner)
            ->put(route('tasks.update', $task), [
                'title' => 'Task diperbarui',
                'description' => 'Deskripsi baru',
                'status' => 'in_progress',
                'priority' => 'high',
                'return_url' => $origin,
            ])
            ->assertRedirect($origin);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Task diperbarui',
        ]);
    }

    public function test_task_detail_page_rejects_external_origin_and_uses_task_list_fallback(): void
    {
        [$owner, $space, $list] = $this->createSpace();
        $task = $this->createTask($space, $list, $owner);

        $this->actingAs($owner)
            ->get(route('tasks.show', [
                'task' => $task->id,
                'return_url' => 'https://evil.example/phishing',
            ]))
            ->assertOk()
            ->assertSee(route('tasks.index', [
                'space_id' => $space->id,
                'list_id' => $list->id,
            ]), false);
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
