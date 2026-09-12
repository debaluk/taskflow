@extends('layouts.task-manager')

@section('title', $workspace->name)

@section('content')

<div class="page-header">
    <div>
        <h1>{{ $workspace->name }}</h1>
        <p>Detail Workspace</p>
    </div>
</div>

<div class="workspace-nav">

    <a
        href="{{ route('workspaces.show', $workspace) }}"
        class="{{ request()->routeIs('workspaces.show') ? 'active' : '' }}"
    >
        Overview
    </a>

    <a
        href="{{ route('workspace.members', $workspace) }}"
        class="{{ request()->routeIs('workspace.members') ? 'active' : '' }}"
    >
        Members
    </a>

</div>

<div class="panel workspace-overview-panel">

    <div class="panel-head">
        <strong>Workspace Overview</strong>
        <span>{{ $workspace->is_active ? 'Aktif' : 'Nonaktif' }}</span>
    </div>

    <div class="workspace-overview">

        <div class="workspace-description">
            <span class="workspace-overview-label">Deskripsi</span>

            <div class="workspace-overview-description">
                {{ $workspace->description ?: 'Tidak ada deskripsi.' }}
            </div>
        </div>

        <div class="workspace-overview-stats">

            <div class="workspace-overview-stat">
                <span>Status</span>
                <strong>{{ $workspace->is_active ? 'Aktif' : 'Nonaktif' }}</strong>
            </div>

            <div class="workspace-overview-stat">
                <span>Owner</span>
                <strong>{{ $workspace->owner?->name ?? '-' }}</strong>
            </div>

            <div class="workspace-overview-stat">
                <span>Total Task</span>
                <strong>{{ $totalTasks }}</strong>
            </div>

            <div class="workspace-overview-stat">
                <span>In Progress</span>
                <strong>{{ $inProgress }}</strong>
            </div>

            <div class="workspace-overview-stat">
                <span>Done</span>
                <strong>{{ $completed }}</strong>
            </div>

            <div class="workspace-overview-stat">
                <span>Overdue</span>
                <strong>{{ $overdue }}</strong>
            </div>

            <div class="workspace-overview-stat">
                <span>Progress</span>
                <strong>{{ $progress }}%</strong>
            </div>

        </div>

    </div>

</div>

<div class="panel workspace-tasks-panel">

    <div class="panel-head">
        <strong>Task List</strong>
        <span>{{ $totalTasks }} tasks</span>
    </div>

    <div class="task-table">

        <div class="task-row header">
            <span>Task</span>
            <span>Status</span>
            <span>Priority</span>
            <span>Assignee</span>
            <span>Due</span>
        </div>

        @forelse($tasks as $task)

            <div
                class="task-row"
                onclick="openTaskModal(@js([
                    'id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'status' => $task->status,
                    'priority' => $task->priority,
                    'due_date' => $task->due_date?->format('Y-m-d'),
                ]))"
            >

                <span class="task-name">

                    <i class="check"></i>

                    <span>

                        <strong>
                            {{ $task->title }}
                        </strong>

                        <small class="task-progress">
                            Progress {{ $task->progress ?? 0 }}%
                        </small>

                        <span class="progress-bar">
                            <span style="width: {{ $task->progress ?? 0 }}%"></span>
                        </span>

                    </span>

                </span>

                <span>
                    <b class="status {{ str_replace('_', '-', $task->status) }}">
                        {{ match($task->status) {
                            'open' => 'To Do',
                            'in_progress' => 'In Progress',
                            'review' => 'Review',
                            'done' => 'Done',
                            default => $task->status,
                        } }}
                    </b>
                </span>

                <span>
                    <b class="priority {{ $task->priority }}">
                        {{ ucfirst($task->priority) }}
                    </b>
                </span>

                <span class="assignee">

                    <span class="avatar sm">
                        {{ strtoupper(substr($task->creator?->name ?? 'U', 0, 1)) }}
                    </span>

                    {{ $task->creator?->name ?? '-' }}

                </span>

                <span>
                    {{ $task->due_date?->format('d M') ?? '-' }}
                </span>

            </div>

        @empty

            <div class="task-row">
                <span>
                    Belum ada task dalam workspace ini.
                </span>
            </div>

        @endforelse

    </div>

</div>

@endsection