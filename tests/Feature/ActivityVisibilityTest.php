<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Space;
use App\Models\Task;
use App\Models\TaskList;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ActivityVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_activity_feed_only_shows_tasks_from_accessible_spaces(): void
    {
        $owner = User::factory()->create();
        $otherOwner = User::factory()->create();
        $member = User::factory()->create();

        $workspace = Workspace::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Workspace Activity',
            'slug' => 'workspace-activity',
            'owner_id' => $owner->id,
            'is_active' => true,
        ]);

        $spaceA = $this->createSpace($workspace, $owner, 'Space A');
        $spaceB = $this->createSpace($workspace, $otherOwner, 'Space B');

        $spaceA->members()->attach($member->id, [
            'invited_by' => $owner->id,
        ]);

        $taskA = $this->createTask($spaceA, $owner);
        $taskB = $this->createTask($spaceB, $otherOwner);

        Activity::create([
            'workspace_id' => $workspace->id,
            'user_id' => $owner->id,
            'action' => 'created',
            'subject_type' => Task::class,
            'subject_id' => $taskA->id,
            'description' => 'Aktivitas Space A yang dibagikan',
        ]);

        Activity::create([
            'workspace_id' => $workspace->id,
            'user_id' => $otherOwner->id,
            'action' => 'created',
            'subject_type' => Task::class,
            'subject_id' => $taskB->id,
            'description' => 'Aktivitas Space B rahasia',
        ]);

        $this->actingAs($member)
            ->get(route('manage.activity'))
            ->assertOk()
            ->assertSee('Aktivitas Space A yang dibagikan')
            ->assertDontSee('Aktivitas Space B rahasia');
    }

    private function createSpace(Workspace $workspace, User $owner, string $name): Space
    {
        $space = Space::create([
            'uuid' => (string) Str::uuid(),
            'workspace_id' => $workspace->id,
            'owner_id' => $owner->id,
            'name' => $name,
            'slug' => Str::slug($name),
            'is_active' => true,
        ]);

        $space->members()->attach($owner->id, [
            'invited_by' => $owner->id,
        ]);

        return $space;
    }

    private function createTask(Space $space, User $creator): Task
    {
        $list = TaskList::create([
            'uuid' => (string) Str::uuid(),
            'space_id' => $space->id,
            'name' => 'List ' . $space->name,
            'slug' => Str::slug('list-' . $space->name),
            'is_active' => true,
        ]);

        return Task::create([
            'uuid' => (string) Str::uuid(),
            'workspace_id' => $space->workspace_id,
            'space_id' => $space->id,
            'list_id' => $list->id,
            'title' => 'Task ' . $space->name,
            'task_type' => 'task',
            'status' => 'open',
            'priority' => 'normal',
            'position' => 1,
            'created_by' => $creator->id,
            'version' => 1,
        ]);
    }
}
