<?php

namespace Tests\Feature;

use App\Models\Space;
use App\Models\Task;
use App\Models\TaskDependency;
use App\Models\TaskList;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TaskWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_creator_can_manage_subtask_lifecycle(): void
    {
        [$owner, $space, $list, $creator] = $this->createSpaceWithMember();
        $parent = $this->createTask($space, $list, $creator);

        $this->actingAs($creator)
            ->post(route('tasks.subtasks.store', $parent), [
                'title' => 'Subtask baru',
                'description' => 'Rincian pekerjaan',
                'status' => 'open',
                'priority' => 'normal',
            ])
            ->assertRedirect();

        $subtask = Task::query()
            ->where('parent_id', $parent->id)
            ->firstOrFail();

        $this->assertSame($creator->id, $subtask->created_by);

        $this->actingAs($creator)
            ->putJson(route('tasks.subtasks.update', $subtask), [
                'title' => 'Subtask diperbarui',
                'status' => 'in_progress',
                'priority' => 'high',
            ])
            ->assertOk();

        $this->assertDatabaseHas('tasks', [
            'id' => $subtask->id,
            'title' => 'Subtask diperbarui',
            'status' => 'in_progress',
        ]);

        $this->actingAs($creator)
            ->deleteJson(route('tasks.subtasks.destroy', $subtask))
            ->assertOk();

        $this->assertSoftDeleted('tasks', ['id' => $subtask->id]);
    }

    public function test_task_creator_can_create_update_and_delete_dependency(): void
    {
        [$owner, $space, $list, $creator] = $this->createSpaceWithMember();
        $task = $this->createTask($space, $list, $creator, 'Task utama');
        $dependsOn = $this->createTask($space, $list, $creator, 'Task prasyarat');

        $response = $this->actingAs($creator)
            ->postJson(route('tasks.dependency.store', $task), [
                'depends_on_task_id' => $dependsOn->id,
                'dependency_type' => 'FS',
            ])
            ->assertOk()
            ->assertJsonPath('dependency.dependency_type', 'FS');

        $dependencyId = $response->json('dependency.id');

        $this->actingAs($creator)
            ->postJson(route('tasks.dependency.store', $task), [
                'depends_on_task_id' => $dependsOn->id,
                'dependency_type' => 'SS',
            ])
            ->assertOk()
            ->assertJsonPath('dependency.dependency_type', 'SS');

        $this->assertDatabaseHas('task_dependencies', [
            'id' => $dependencyId,
            'task_id' => $task->id,
            'depends_on_task_id' => $dependsOn->id,
            'dependency_type' => 'SS',
        ]);

        $dependency = TaskDependency::findOrFail($dependencyId);

        $this->actingAs($creator)
            ->deleteJson(route('tasks.dependency.destroy', [$task, $dependency]))
            ->assertOk();

        $this->assertDatabaseMissing('task_dependencies', [
            'id' => $dependencyId,
        ]);
    }

    public function test_dependency_must_stay_within_the_same_space(): void
    {
        [$owner, $space, $list, $creator] = $this->createSpaceWithMember();
        $task = $this->createTask($space, $list, $creator, 'Task Space A');

        $otherSpace = Space::create([
            'uuid' => (string) Str::uuid(),
            'workspace_id' => $space->workspace_id,
            'owner_id' => $owner->id,
            'name' => 'Space Lain',
            'slug' => 'space-lain-' . Str::lower(Str::random(6)),
            'is_active' => true,
        ]);
        $otherSpace->members()->attach([
            $owner->id => ['invited_by' => $owner->id],
            $creator->id => ['invited_by' => $owner->id],
        ]);

        $otherList = TaskList::create([
            'uuid' => (string) Str::uuid(),
            'space_id' => $otherSpace->id,
            'name' => 'List Space Lain',
            'slug' => 'list-space-lain',
            'is_active' => true,
        ]);
        $otherTask = $this->createTask(
            $otherSpace,
            $otherList,
            $creator,
            'Task Space B'
        );

        $this->actingAs($creator)
            ->postJson(route('tasks.dependency.store', $task), [
                'depends_on_task_id' => $otherTask->id,
                'dependency_type' => 'FS',
            ])
            ->assertUnprocessable();

        $this->assertDatabaseMissing('task_dependencies', [
            'task_id' => $task->id,
            'depends_on_task_id' => $otherTask->id,
        ]);
    }

    public function test_task_creator_can_reorder_tasks_in_accessible_list(): void
    {
        [$owner, $space, $list, $creator] = $this->createSpaceWithMember();
        $first = $this->createTask($space, $list, $creator, 'Task pertama');
        $second = $this->createTask($space, $list, $creator, 'Task kedua');

        $this->actingAs($creator)
            ->patchJson(route('tasks.reorder'), [
                'space_id' => $space->id,
                'list_id' => $list->id,
                'columns' => [
                    [
                        'status' => 'in_progress',
                        'tasks' => [$second->id, $first->id],
                    ],
                ],
            ])
            ->assertOk();

        $this->assertDatabaseHas('tasks', [
            'id' => $second->id,
            'position' => 1,
            'status' => 'in_progress',
        ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $first->id,
            'position' => 2,
            'status' => 'in_progress',
        ]);
    }

    public function test_subtask_creation_rejects_external_return_url(): void
    {
        [$owner, $space, $list, $creator] = $this->createSpaceWithMember();
        $parent = $this->createTask($space, $list, $creator);

        $this->actingAs($creator)
            ->post(route('tasks.subtasks.store', $parent), [
                'title' => 'Subtask redirect test',
                'status' => 'open',
                'priority' => 'normal',
                'return_url' => 'https://evil.example/phishing',
            ])
            ->assertRedirect(route('tasks.index'));

        $this->assertDatabaseHas('tasks', [
            'parent_id' => $parent->id,
            'title' => 'Subtask redirect test',
        ]);
    }

    public function test_invalid_subtask_return_url_is_rejected_without_creating_subtask(): void
    {
        [$owner, $space, $list, $creator] = $this->createSpaceWithMember();
        $parent = $this->createTask($space, $list, $creator);

        $this->actingAs($creator)
            ->post(route('tasks.subtasks.store', $parent), [
                'title' => 'Subtask invalid redirect',
                'status' => 'open',
                'priority' => 'normal',
                'return_url' => ['https://evil.example'],
            ])
            ->assertSessionHasErrors('return_url');

        $this->assertDatabaseMissing('tasks', [
            'parent_id' => $parent->id,
            'title' => 'Subtask invalid redirect',
        ]);
    }

    public function test_reorder_rolls_back_all_changes_when_one_task_cannot_be_managed(): void
    {
        [$owner, $space, $list, $creator] = $this->createSpaceWithMember();
        $otherCreator = User::factory()->create();

        $space->members()->attach($otherCreator->id, [
            'invited_by' => $owner->id,
        ]);

        $ownTask = $this->createTask($space, $list, $creator, 'Task milik creator');
        $otherTask = $this->createTask($space, $list, $otherCreator, 'Task milik member lain');

        $this->actingAs($creator)
            ->patchJson(route('tasks.reorder'), [
                'space_id' => $space->id,
                'list_id' => $list->id,
                'columns' => [
                    [
                        'status' => 'done',
                        'tasks' => [$ownTask->id, $otherTask->id],
                    ],
                ],
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('tasks', [
            'id' => $ownTask->id,
            'status' => 'open',
            'position' => 1,
        ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $otherTask->id,
            'status' => 'open',
            'position' => 1,
        ]);
    }

    public function test_task_dates_cannot_have_due_date_before_start_date(): void
    {
        [$owner, $space, $list, $creator] = $this->createSpaceWithMember();

        $this->actingAs($creator)
            ->post(route('tasks.store'), [
                'title' => 'Tanggal tidak valid',
                'status' => 'open',
                'priority' => 'normal',
                'list_id' => $list->id,
                'start_date' => '2026-12-10',
                'due_date' => '2026-12-09',
            ])
            ->assertSessionHasErrors('due_date');

        $this->assertDatabaseMissing('tasks', [
            'title' => 'Tanggal tidak valid',
        ]);
    }

    public function test_reorder_rejects_duplicate_task_ids(): void
    {
        [$owner, $space, $list, $creator] = $this->createSpaceWithMember();
        $task = $this->createTask($space, $list, $creator);

        $this->actingAs($creator)
            ->patchJson(route('tasks.reorder'), [
                'space_id' => $space->id,
                'list_id' => $list->id,
                'columns' => [
                    [
                        'status' => 'done',
                        'tasks' => [$task->id, $task->id],
                    ],
                ],
            ])
            ->assertUnprocessable();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'open',
        ]);
    }

    public function test_member_without_space_access_cannot_create_task_in_that_space(): void
    {
        [$owner, $space, $list] = $this->createSpaceWithMember();
        $outsider = User::factory()->create();

        $this->actingAs($outsider)
            ->post(route('tasks.store'), [
                'title' => 'Task tanpa akses',
                'status' => 'open',
                'priority' => 'normal',
                'list_id' => $list->id,
            ])
            ->assertUnprocessable();

        $this->assertDatabaseMissing('tasks', [
            'title' => 'Task tanpa akses',
        ]);
    }

    private function createSpaceWithMember(): array
    {
        $owner = User::factory()->create();
        $creator = User::factory()->create();

        $workspace = Workspace::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Workspace Workflow',
            'slug' => 'workspace-workflow-' . Str::lower(Str::random(6)),
            'owner_id' => $owner->id,
            'is_active' => true,
        ]);

        $space = Space::create([
            'uuid' => (string) Str::uuid(),
            'workspace_id' => $workspace->id,
            'owner_id' => $owner->id,
            'name' => 'Space Workflow',
            'slug' => 'space-workflow-' . Str::lower(Str::random(6)),
            'is_active' => true,
        ]);

        $space->members()->attach([
            $owner->id => ['invited_by' => $owner->id],
            $creator->id => ['invited_by' => $owner->id],
        ]);

        $list = TaskList::create([
            'uuid' => (string) Str::uuid(),
            'space_id' => $space->id,
            'name' => 'List Workflow',
            'slug' => 'list-workflow-' . Str::lower(Str::random(6)),
            'is_active' => true,
        ]);

        return [$owner, $space, $list, $creator];
    }

    private function createTask(
        Space $space,
        TaskList $list,
        User $creator,
        string $title = 'Task workflow'
    ): Task {
        return Task::create([
            'uuid' => (string) Str::uuid(),
            'workspace_id' => $space->workspace_id,
            'space_id' => $space->id,
            'list_id' => $list->id,
            'title' => $title,
            'task_type' => 'task',
            'status' => 'open',
            'priority' => 'normal',
            'position' => 1,
            'created_by' => $creator->id,
            'version' => 1,
        ]);
    }
}
