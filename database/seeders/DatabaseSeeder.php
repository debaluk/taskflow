<?php

namespace Database\Seeders;

use App\Models\Folder;
use App\Models\Space;
use App\Models\Task;
use App\Models\TaskList;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // User
        $user = User::factory()->create([
            'name' => 'Admin Task',
            'email' => 'admin@task.local',
        ]);

        // Workspace
        $workspace = Workspace::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'My Workspace',
            'slug' => 'my-workspace',
            'description' => 'Workspace utama Task Management',
            'owner_id' => $user->id,
            'is_active' => true,
        ]);

        // Workspace Member
        WorkspaceMember::create([
            'workspace_id' => $workspace->id,
            'user_id' => $user->id,
            'role' => 'owner',
            'is_active' => true,
        ]);

        // Space
        $space = Space::create([
            'uuid' => (string) Str::uuid(),
            'workspace_id' => $workspace->id,
            'name' => 'General',
            'slug' => 'general',
            'description' => 'Space utama',
            'is_active' => true,
        ]);

        // Folder
        $folder = Folder::create([
            'uuid' => (string) Str::uuid(),
            'space_id' => $space->id,
            'name' => 'Development',
            'slug' => 'development',
            'description' => 'Folder pengembangan aplikasi',
            'is_active' => true,
        ]);

        // Task List
        $taskList = TaskList::create([
            'uuid' => (string) Str::uuid(),
            'space_id' => $space->id,
            'folder_id' => $folder->id,
            'name' => 'Task List',
            'slug' => 'task-list',
            'description' => 'Daftar pekerjaan pengembangan',
            'position' => 0,
            'is_active' => true,
        ]);

        // Tasks
        $tasks = [
            [
                'title' => 'Setup Task Management',
                'description' => 'Menyiapkan fondasi aplikasi Task Management.',
                'status' => 'open',
                'priority' => 'high',
                'progress' => 100,
            ],
            [
                'title' => 'Buat Master User',
                'description' => 'Membuat manajemen user dan workspace member.',
                'status' => 'open',
                'priority' => 'high',
                'progress' => 25,
            ],
            [
                'title' => 'Buat Kanban Board',
                'description' => 'Menampilkan task dalam bentuk Kanban Board.',
                'status' => 'open',
                'priority' => 'normal',
                'progress' => 0,
            ],
            [
                'title' => 'Buat Gantt Chart',
                'description' => 'Menampilkan timeline dan progress task.',
                'status' => 'open',
                'priority' => 'normal',
                'progress' => 0,
            ],
            [
                'title' => 'Buat Calendar',
                'description' => 'Menampilkan task berdasarkan tanggal.',
                'status' => 'open',
                'priority' => 'low',
                'progress' => 0,
            ],
        ];

        foreach ($tasks as $position => $data) {
            Task::create([
                'uuid' => (string) Str::uuid(),
                'workspace_id' => $workspace->id,
                'space_id' => $space->id,
                'folder_id' => $folder->id,
                'list_id' => $taskList->id,
                'title' => $data['title'],
                'description' => $data['description'],
                'task_type' => 'task',
                'status' => $data['status'],
                'priority' => $data['priority'],
                'progress' => $data['progress'],
                'position' => $position,
                'created_by' => $user->id,
                'version' => 1,
            ]);
        }
    }
}