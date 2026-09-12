@extends('layouts.task-manager')

@section(
    'title',
    $taskList?->name
        ?? $space?->name
        ?? $workspace?->name
        ?? 'Task Management'
)

@section(
    'page',
    $taskList?->name
        ?? $space?->name
        ?? $workspace?->name
        ?? 'Task Management'
)

@section('content')

@php

/*
|--------------------------------------------------------------------------
| CONTEXT
|--------------------------------------------------------------------------
|
| Workspace
|     ↓
| Space
|     ↓
| List
|     ↓
| Task
|
| Behavior:
|
| 1. Workspace root
|    Menampilkan seluruh task berdasarkan Space → List.
|
| 2. Space dipilih
|    Menampilkan seluruh task dalam Space,
|    grouped berdasarkan List.
|
| 3. List dipilih
|    Menampilkan hanya task dalam List tersebut.
|
| Task TIDAK ditampilkan sebagai tree sidebar.
|
*/


$currentWorkspace = $workspace ?? null;
$currentSpace = $space ?? null;
$currentList = $taskList ?? null;


/*
|--------------------------------------------------------------------------
| VIEW PARAMETERS
|--------------------------------------------------------------------------
*/

$viewParams = [];

if ($currentSpace) {

    $viewParams['space_id'] = $currentSpace->id;

}

if ($currentList) {

    $viewParams['list_id'] = $currentList->id;

}


/*
|--------------------------------------------------------------------------
| STATUS LABEL
|--------------------------------------------------------------------------
*/

$statusLabels = [

    'open' => 'To Do',

    'in_progress' => 'In Progress',

    'review' => 'Review',

    'done' => 'Done',

];


/*
|--------------------------------------------------------------------------
| GROUP TASKS BY LIST
|--------------------------------------------------------------------------
*/

$groupedTasks = collect($tasks ?? [])
    ->groupBy(function ($task) {

        return $task->list_id ?? 0;

    });


/*
|--------------------------------------------------------------------------
| LISTS BY SPACE
|--------------------------------------------------------------------------
|
| Data ini sudah disiapkan oleh TaskManagerController.
|
*/

$listsBySpace = $listsBySpace ?? collect();


/*
|--------------------------------------------------------------------------
| TASK MODAL DATA
|--------------------------------------------------------------------------
*/

$taskModalData = function ($task) {

    return [

        'id' => $task->id,

        'title' => $task->title,

        'description' => $task->description,

        'status' => $task->status,

        'priority' => $task->priority,

        'start_date' => $task->start_date?->format('Y-m-d'),

        'due_date' => $task->due_date?->format('Y-m-d'),

        'assignee_ids' => $task->assignees
            ->pluck('id')
            ->values()
            ->toArray(),

        'space_id' => $task->space_id,

        'list_id' => $task->list_id,

    ];

};

@endphp


{{-- ========================================================= --}}
{{-- PAGE HEADER --}}
{{-- ========================================================= --}}

<div class="page-head">

    <div>

        <span class="eyebrow">

            @if($currentList)

                LIST

            @elseif($currentSpace)

                SPACE

            @else

                WORKSPACE

            @endif

        </span>


        <h1>

            @if($currentList)

                {{ $currentList->name }}

            @elseif($currentSpace)

                {{ $currentSpace->name }}

            @else

                {{ $currentWorkspace?->name ?? 'Workspace' }}

            @endif

        </h1>


        <p>

            @if($currentList)

                Kelola task dalam list ini.

            @elseif($currentSpace)

                Kelola semua task dalam space ini, dikelompokkan berdasarkan list.

            @else

                Kelola seluruh task dalam workspace ini.

            @endif

        </p>

    </div>


    {{-- ===================================================== --}}
    {{-- CREATE TODO --}}
    {{-- ===================================================== --}}
    {{--
        Todo hanya boleh dibuat dari context List.
    --}}

    @if($currentList)

        <button
            type="button"
            class="btn primary"
            onclick="openTaskModal('New Task')"
        >
            ＋ New Task
        </button>

    @else

        <button
            type="button"
            class="btn primary"
            disabled
            title="Pilih List terlebih dahulu untuk membuat Todo."
            style="opacity:.55;cursor:not-allowed;"
        >
            ＋ New Task
        </button>

    @endif

</div>


{{-- ========================================================= --}}
{{-- STATS --}}
{{-- ========================================================= --}}

<div class="stats">

    <div class="stat">

        <span>
            Total Tasks
        </span>

        <strong>
            {{ $totalTasks }}
        </strong>

        <small>
            Task dalam scope ini
        </small>

    </div>


    <div class="stat">

        <span>
            In Progress
        </span>

        <strong>
            {{ $inProgress }}
        </strong>

        <small>
            Sedang dikerjakan
        </small>

    </div>


    <div class="stat">

        <span>
            Due Soon
        </span>

        <strong>
            {{ $dueSoon }}
        </strong>

        <small>
            7 hari ke depan
        </small>

    </div>


    <div class="stat">

        <span>
            Completed
        </span>

        <strong>
            {{ $completed }}
        </strong>

        <small>
            Selesai
        </small>

    </div>

</div>


{{-- ========================================================= --}}
{{-- TOOLBAR --}}
{{-- ========================================================= --}}

<div class="toolbar">

    <div class="segmented">

        <a
            href="{{ route('tasks.index', $viewParams) }}"
            class="{{ request()->routeIs('tasks.index') ? 'selected' : '' }}"
        >
            List
        </a>


        <a
            href="{{ route('tasks.board', $viewParams) }}"
            class="{{ request()->routeIs('tasks.board') ? 'selected' : '' }}"
        >
            Board
        </a>


        <a
            href="{{ route('tasks.calendar', $viewParams) }}"
            class="{{ request()->routeIs('tasks.calendar') ? 'selected' : '' }}"
        >
            Calendar
        </a>


        <a
            href="{{ route('tasks.gantt', $viewParams) }}"
            class="{{ request()->routeIs('tasks.gantt') ? 'selected' : '' }}"
        >
            Gantt
        </a>

    </div>


    <div class="filters">

        <button
            type="button"
            class="filter"
        >
            ☷ Filter
        </button>


        <button
            type="button"
            class="filter"
        >
            ↕ Sort
        </button>

    </div>

</div>


{{-- ========================================================= --}}
{{-- MAIN TASK PANEL --}}
{{-- ========================================================= --}}

<div class="panel">

    <div class="panel-head">

        <strong>

            @if($currentList)

                Tasks

            @elseif($currentSpace)

                Lists & Tasks

            @else

                Spaces, Lists & Tasks

            @endif

        </strong>


        <span>

            {{ $totalTasks }}

            {{ $totalTasks == 1 ? 'task' : 'tasks' }}

        </span>

    </div>


    <div class="task-table">


        {{-- ================================================= --}}
        {{-- TABLE HEADER --}}
        {{-- ================================================= --}}

        <div class="task-row header">

            <span>
                Task
            </span>

            <span>
                Status
            </span>

            <span>
                Priority
            </span>

            <span>
                Assignee
            </span>

            <span>
                Due
            </span>

        </div>


        {{-- ================================================= --}}
        {{-- LIST CONTEXT --}}
        {{-- ================================================= --}}
        {{--
            Hanya task dari List yang dipilih.
        --}}

        @if($currentList)

            @forelse($tasks as $task)

                @php

                    $statusLabel =
                        $statusLabels[$task->status]
                        ?? ucfirst(
                            str_replace(
                                '_',
                                ' ',
                                $task->status
                            )
                        );


                    $priorityLabel =
                        ucfirst(
                            $task->priority ?? 'Normal'
                        );


                    $progress = max(
                        0,
                        min(
                            100,
                            (int) ($task->progress ?? 0)
                        )
                    );

                @endphp


                <div
                    class="task-row"
                    onclick="openTaskModal(@js($taskModalData($task)))"
                >

                    <span class="task-name">

                        <i class="check"></i>

                        <span>

                            <strong>
                                {{ $task->title }}
                            </strong>


                            <small class="task-progress">
                                Progress {{ $progress }}%
                            </small>


                            <span class="progress-bar">

                                <span
                                    style="width:{{ $progress }}%"
                                ></span>

                            </span>

                        </span>

                    </span>


                    <span>

                        <b
                            class="status {{ str_replace('_', '-', $task->status) }}"
                        >
                            {{ $statusLabel }}
                        </b>

                    </span>


                    <span>

                        <b
                            class="priority {{ strtolower($task->priority ?? 'normal') }}"
                        >
                            {{ $priorityLabel }}
                        </b>

                    </span>


                    <span
                        class="assignee"
                        onclick="event.stopPropagation(); openAssigneePopup(event, {{ $task->id }})"
                    >

                        @if($task->assignees->count())

                            @php
                                $assignee = $task->assignees->first();
                            @endphp

                            <span class="avatar sm">

                                {{ strtoupper(
                                    substr(
                                        $assignee->name,
                                        0,
                                        1
                                    )
                                ) }}

                            </span>


                            <span>
                                {{ $assignee->name }}
                            </span>

                        @else

                            <span class="avatar sm">
                                U
                            </span>


                            <span>
                                Unassigned
                            </span>

                        @endif

                    </span>


                    <span>

                        {{ $task->due_date
                            ? $task->due_date->format('d M')
                            : '—'
                        }}

                    </span>

                </div>


            @empty

                <div class="task-row">

                    <span style="grid-column:1 / -1;">

                        Belum ada task dalam list ini.

                    </span>

                </div>

            @endforelse


        {{-- ================================================= --}}
        {{-- SPACE CONTEXT --}}
        {{-- ================================================= --}}
        {{--
            Semua List dalam Space.
            Task dikelompokkan berdasarkan List.
        --}}

        @elseif($currentSpace)

            @forelse($lists as $list)

                @php

                    $listTasks = $groupedTasks->get(
                        $list->id,
                        collect()
                    );

                @endphp


                {{-- ========================================= --}}
                {{-- LIST HEADER --}}
                {{-- ========================================= --}}

                <div
                    class="task-row"
                    style="
                        background:var(--bg);
                        font-weight:600;
                        cursor:pointer;
                    "
                    onclick="window.location='{{ route('tasks.index', [
                        'space_id' => $currentSpace->id,
                        'list_id' => $list->id
                    ]) }}'"
                >

                    <span
                        style="
                            grid-column:1 / -1;
                            display:flex;
                            align-items:center;
                            gap:8px;
                        "
                    >

                        <span>
                            ▾
                        </span>


                        <span>
                            {{ $list->name }}
                        </span>


                        <small
                            style="
                                color:var(--muted);
                                font-weight:400;
                            "
                        >

                            {{ $listTasks->count() }}

                            {{ $listTasks->count() == 1
                                ? 'task'
                                : 'tasks'
                            }}

                        </small>

                    </span>

                </div>


                {{-- ========================================= --}}
                {{-- TASKS --}}
                {{-- ========================================= --}}

                @forelse($listTasks as $task)

                    @php

                        $statusLabel =
                            $statusLabels[$task->status]
                            ?? ucfirst(
                                str_replace(
                                    '_',
                                    ' ',
                                    $task->status
                                )
                            );


                        $priorityLabel =
                            ucfirst(
                                $task->priority ?? 'Normal'
                            );


                        $progress = max(
                            0,
                            min(
                                100,
                                (int) ($task->progress ?? 0)
                            )
                        );

                    @endphp


                    <div
                        class="task-row"
                        onclick="openTaskModal(@js($taskModalData($task)))"
                    >

                        <span class="task-name">

                            <i class="check"></i>

                            <span>

                                <strong>
                                    {{ $task->title }}
                                </strong>


                                <small class="task-progress">
                                    Progress {{ $progress }}%
                                </small>


                                <span class="progress-bar">

                                    <span
                                        style="width:{{ $progress }}%"
                                    ></span>

                                </span>

                            </span>

                        </span>


                        <span>

                            <b
                                class="status {{ str_replace('_', '-', $task->status) }}"
                            >
                                {{ $statusLabel }}
                            </b>

                        </span>


                        <span>

                            <b
                                class="priority {{ strtolower($task->priority ?? 'normal') }}"
                            >
                                {{ $priorityLabel }}
                            </b>

                        </span>


                        <span
                            class="assignee"
                            onclick="event.stopPropagation(); openAssigneePopup(event, {{ $task->id }})"
                        >

                            @if($task->assignees->count())

                                @php
                                    $assignee = $task->assignees->first();
                                @endphp


                                <span class="avatar sm">

                                    {{ strtoupper(
                                        substr(
                                            $assignee->name,
                                            0,
                                            1
                                        )
                                    ) }}

                                </span>


                                <span>
                                    {{ $assignee->name }}
                                </span>

                            @else

                                <span class="avatar sm">
                                    U
                                </span>


                                <span>
                                    Unassigned
                                </span>

                            @endif

                        </span>


                        <span>

                            {{ $task->due_date
                                ? $task->due_date->format('d M')
                                : '—'
                            }}

                        </span>

                    </div>


                @empty

                    <div
                        class="task-row"
                        style="cursor:default;"
                    >

                        <span
                            style="
                                grid-column:1 / -1;
                                color:var(--muted);
                            "
                        >

                            Belum ada task dalam list ini.

                        </span>

                    </div>

                @endforelse


            @empty

                <div class="task-row">

                    <span style="grid-column:1 / -1;">

                        Tidak ada List dalam Space ini.

                    </span>

                </div>

            @endforelse


        {{-- ================================================= --}}
        {{-- WORKSPACE CONTEXT --}}
        {{-- ================================================= --}}
        {{--
            Root workspace:
            Space → List → Task

            Tidak ada hardcode nama Space.
            Semua berasal dari $listsBySpace.
        --}}

        @else

            @forelse($listsBySpace as $spaceLists)

                @php

                    $rootSpace =
                        $spaceLists->first()?->space;

                @endphp


                @if($rootSpace)

                    {{-- ===================================== --}}
                    {{-- SPACE HEADER --}}
                    {{-- ===================================== --}}

                    <div
                        class="task-row"
                        style="
                            background:var(--bg);
                            font-weight:700;
                            cursor:pointer;
                        "
                        onclick="window.location='{{ route('tasks.index', [
                            'space_id' => $rootSpace->id
                        ]) }}'"
                    >

                        <span
                            style="
                                grid-column:1 / -1;
                                display:flex;
                                align-items:center;
                                gap:8px;
                            "
                        >

                            <span>
                                ▾
                            </span>


                            <span>
                                {{ $rootSpace->name }}
                            </span>

                        </span>

                    </div>

                @endif


                {{-- ===================================== --}}
                {{-- LISTS --}}
                {{-- ===================================== --}}

                @foreach($spaceLists as $list)

                    @php

                        $listTasks = $groupedTasks->get(
                            $list->id,
                            collect()
                        );

                    @endphp


                    <div
                        class="task-row"
                        style="
                            background:var(--panel);
                            font-weight:600;
                            cursor:pointer;
                        "
                        onclick="window.location='{{ route('tasks.index', [
                            'space_id' => $rootSpace?->id,
                            'list_id' => $list->id
                        ]) }}'"
                    >

                        <span
                            style="
                                grid-column:1 / -1;
                                display:flex;
                                align-items:center;
                                gap:8px;
                                padding-left:20px;
                            "
                        >

                            <span>
                                ↳
                            </span>


                            <span>
                                {{ $list->name }}
                            </span>


                            <small
                                style="
                                    color:var(--muted);
                                    font-weight:400;
                                "
                            >

                                {{ $listTasks->count() }}

                                {{ $listTasks->count() == 1
                                    ? 'task'
                                    : 'tasks'
                                }}

                            </small>

                        </span>

                    </div>


                    {{-- ================================= --}}
                    {{-- TASKS --}}
                    {{-- ================================= --}}

                    @forelse($listTasks as $task)

                        @php

                            $statusLabel =
                                $statusLabels[$task->status]
                                ?? ucfirst(
                                    str_replace(
                                        '_',
                                        ' ',
                                        $task->status
                                    )
                                );


                            $priorityLabel =
                                ucfirst(
                                    $task->priority ?? 'Normal'
                                );


                            $progress = max(
                                0,
                                min(
                                    100,
                                    (int) ($task->progress ?? 0)
                                )
                            );

                        @endphp


                        <div
                            class="task-row"
                            onclick="openTaskModal(@js($taskModalData($task)))"
                        >

                            <span
                                class="task-name"
                                style="padding-left:40px;"
                            >

                                <i class="check"></i>

                                <span>

                                    <strong>
                                        {{ $task->title }}
                                    </strong>


                                    <small class="task-progress">
                                        Progress {{ $progress }}%
                                    </small>


                                    <span class="progress-bar">

                                        <span
                                            style="width:{{ $progress }}%"
                                        ></span>

                                    </span>

                                </span>

                            </span>


                            <span>

                                <b
                                    class="status {{ str_replace('_', '-', $task->status) }}"
                                >
                                    {{ $statusLabel }}
                                </b>

                            </span>


                            <span>

                                <b
                                    class="priority {{ strtolower($task->priority ?? 'normal') }}"
                                >
                                    {{ $priorityLabel }}
                                </b>

                            </span>


                            <span
                                class="assignee"
                                onclick="event.stopPropagation(); openAssigneePopup(event, {{ $task->id }})"
                            >

                                @if($task->assignees->count())

                                    @php
                                        $assignee = $task->assignees->first();
                                    @endphp


                                    <span class="avatar sm">

                                        {{ strtoupper(
                                            substr(
                                                $assignee->name,
                                                0,
                                                1
                                            )
                                        ) }}

                                    </span>


                                    <span>
                                        {{ $assignee->name }}
                                    </span>

                                @else

                                    <span class="avatar sm">
                                        U
                                    </span>


                                    <span>
                                        Unassigned
                                    </span>

                                @endif

                            </span>


                            <span>

                                {{ $task->due_date
                                    ? $task->due_date->format('d M')
                                    : '—'
                                }}

                            </span>

                        </div>


                    @empty

                        <div
                            class="task-row"
                            style="cursor:default;"
                        >

                            <span
                                style="
                                    grid-column:1 / -1;
                                    padding-left:40px;
                                    color:var(--muted);
                                "
                            >

                                Belum ada task dalam list ini.

                            </span>

                        </div>

                    @endforelse

                @endforeach


            @empty

                <div class="task-row">

                    <span style="grid-column:1 / -1;">

                        Tidak ada Space atau List dalam workspace ini.

                    </span>

                </div>

            @endforelse

        @endif

    </div>

</div>

@endsection
