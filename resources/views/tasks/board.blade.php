@extends('layouts.task-manager')

@section('title', 'Board')
@section('page', 'Board')

@section('content')

<div class="page-head">
    <div>
        <span class="eyebrow">VIEW</span>
        <h1>Kanban Board</h1>
        <p>Kelola task berdasarkan status pekerjaan.</p>
    </div>

    <button
    class="btn primary"
    onclick="@if(session()->has('workspace_id')) openTaskModal('New Task'); @else alert('Pilih workspace terlebih dahulu sebelum membuat task.'); @endif"
>
    ＋ New Task
</button>
</div>

<div class="board">

    @foreach($columns as $status => $label)

        @php
            $columnTasks = $tasks->where('status', $status);
        @endphp

        <div
            class="kanban-col"
            data-status="{{ $status }}"
            ondragover="allowDrop(event)"
            ondrop="dropTask(event)"
        >

            <div class="kanban-head">
                <strong>{{ $label }}</strong>
                <span>{{ $columnTasks->count() }}</span>
            </div>

            @forelse($columnTasks as $task)

                <article
                    class="kanban-card"
                    draggable="true"
                    data-task-id="{{ $task->id }}"
                    ondragstart="dragTask(event)"
                    onclick="if (!window.isDraggingTask) openTaskModal(@js([
                        'id' => $task->id,
                        'title' => $task->title,
                        'description' => $task->description,
                        'status' => $task->status,
                        'priority' => $task->priority,
                        'due_date' => $task->due_date?->format('Y-m-d'),
                    ]))"
                >

                    <h3>
                        {{ $task->title }}
                    </h3>

                    @if($task->description)
                        <p style="margin-bottom:15px;">
                            {{ \Illuminate\Support\Str::limit($task->description, 80) }}
                        </p>
                    @endif
					<div class="task-card-progress">
    <div class="progress-info">
        <span>Progress</span>
        <strong>{{ $task->progress ?? 0 }}%</strong>
    </div>

    <div class="progress-bar">
        <span style="width: {{ $task->progress ?? 0 }}%"></span>
    </div>
</div>

                    <div class="card-foot">

                        <b class="priority {{ strtolower($task->priority) }}">
                            {{ ucfirst($task->priority) }}
                        </b>

                        <span class="avatar sm">
                            {{ strtoupper(substr($task->creator?->name ?? 'U', 0, 1)) }}
                        </span>

                    </div>

                </article>

            @empty

                <div
    class="kanban-empty"
    style="
        padding:20px 10px;
        text-align:center;
        color:var(--muted);
        font-size:10px;
    "
>
    Tidak ada task
</div>

            @endforelse

            <button
                class="add-card"
                onclick="openTaskModal('New Task')"
            >
                ＋ Add task
            </button>

        </div>

    @endforeach

</div>

@endsection