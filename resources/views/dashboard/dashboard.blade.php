@extends('layouts.task-manager')

@section('title', 'Dasbor')
@section('page', 'Dasbor')

@section('content')

<style>
    /* =====================================================
       DASHBOARD ONLY
       Semua CSS di sini khusus halaman Dashboard
       Tidak mengubah CSS Task
       ===================================================== */

    .dashboard {
        width: 100%;
    }

    .dashboard-grid {
        display: grid;
        grid-template-columns: minmax(0, 2fr) minmax(280px, 1fr);
        gap: 18px;
        margin-top: 18px;
    }

    .dashboard-panel {
        background: var(--panel);
        border: 1px solid var(--border);
        border-radius: 10px;
        overflow: hidden;
    }

    .dashboard-panel-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 18px;
        border-bottom: 1px solid var(--border);
    }

    .dashboard-panel-head strong {
        font-size: 13px;
    }

    .dashboard-link {
        color: var(--muted);
        text-decoration: none;
        font-size: 11px;
    }

    .dashboard-link:hover {
        color: inherit;
    }

    /* TODAY */

    .dashboard-today {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        margin-top: 18px;
        background: var(--panel);
        border: 1px solid var(--border);
        border-radius: 10px;
        overflow: hidden;
    }

    .dashboard-today-item {
        padding: 18px 20px;
        border-right: 1px solid var(--border);
    }

    .dashboard-today-item:last-child {
        border-right: 0;
    }

    .dashboard-today-item span {
        display: block;
        color: var(--muted);
        font-size: 11px;
        margin-bottom: 7px;
    }

    .dashboard-today-item strong {
        display: block;
        font-size: 25px;
        line-height: 1;
    }

    .dashboard-today-item small {
        display: block;
        margin-top: 6px;
        color: var(--muted);
        font-size: 10px;
    }

    /* MY TASK */

    .dashboard-task {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto auto;
        align-items: center;
        gap: 16px;
        padding: 13px 18px;
        border-bottom: 1px solid var(--border);
    }

    .dashboard-task:last-child {
        border-bottom: 0;
    }

    .dashboard-task-main {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        min-width: 0;
    }

    .dashboard-task-check {
        width: 15px;
        height: 15px;
        min-width: 15px;
        margin-top: 2px;
        border: 1px solid var(--border);
        border-radius: 50%;
    }

    .dashboard-task-info {
        min-width: 0;
    }

    .dashboard-task-title {
        display: block;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .dashboard-task-meta {
        display: block;
        margin-top: 4px;
        color: var(--muted);
        font-size: 10px;
    }

    /* PROGRESS */

    .dashboard-progress {
        padding: 24px 20px;
        text-align: center;
    }

    .dashboard-progress-number {
        font-size: 42px;
        font-weight: 700;
        line-height: 1;
    }

    .dashboard-progress-label {
        display: block;
        margin-top: 7px;
        color: var(--muted);
        font-size: 11px;
    }

    .dashboard-progress-bar {
        height: 7px;
        margin-top: 22px;
        background: var(--bg);
        border-radius: 10px;
        overflow: hidden;
    }

    .dashboard-progress-fill {
        height: 100%;
        background: currentColor;
        border-radius: inherit;
    }

    .dashboard-progress-detail {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
        margin-top: 20px;
    }

    .dashboard-progress-detail span {
        display: block;
        color: var(--muted);
        font-size: 10px;
    }

    .dashboard-progress-detail strong {
        display: block;
        margin-top: 4px;
        font-size: 12px;
    }

    /* ATTENTION */

    .dashboard-attention-item {
        padding: 15px 18px;
        border-bottom: 1px solid var(--border);
    }

    .dashboard-attention-item:last-child {
        border-bottom: 0;
    }

    .dashboard-attention-title {
        font-size: 12px;
        font-weight: 600;
    }

    .dashboard-attention-desc {
        display: block;
        margin-top: 4px;
        color: var(--muted);
        font-size: 10px;
    }

    /* ACTIVITY */

    .dashboard-activity-item {
        padding: 13px 18px;
        border-bottom: 1px solid var(--border);
    }

    .dashboard-activity-item:last-child {
        border-bottom: 0;
    }

    .dashboard-activity-title {
        display: block;
        font-size: 11px;
        font-weight: 600;
    }

    .dashboard-activity-time {
        display: block;
        margin-top: 4px;
        color: var(--muted);
        font-size: 10px;
    }

    /* SPACES */

    .dashboard-spaces {
        display: grid;
        grid-template-columns: repeat(
            auto-fit,
            minmax(190px, 1fr)
        );
        gap: 12px;
        padding: 16px;
    }

    .dashboard-space {
        display: block;
        padding: 16px;
        color: inherit;
        text-decoration: none;
        border: 1px solid var(--border);
        border-radius: 8px;
        transition: transform .15s ease, border-color .15s ease;
    }

    .dashboard-space:hover {
        transform: translateY(-1px);
        border-color: var(--muted);
    }

    .dashboard-space-name {
        display: block;
        font-size: 13px;
        font-weight: 600;
    }

    .dashboard-space-count {
        display: block;
        margin-top: 12px;
        color: var(--muted);
        font-size: 10px;
    }

    .dashboard-space-progress {
        height: 5px;
        margin-top: 10px;
        background: var(--bg);
        border-radius: 10px;
        overflow: hidden;
    }

    .dashboard-space-progress span {
        display: block;
        height: 100%;
        background: currentColor;
    }

    .dashboard-space-percent {
        display: block;
        margin-top: 6px;
        color: var(--muted);
        font-size: 10px;
    }

    /* EMPTY */

    .dashboard-empty {
        padding: 24px 18px;
        color: var(--muted);
        font-size: 11px;
    }

    /* RESPONSIVE */

    @media (max-width: 900px) {

        .dashboard-grid {
            grid-template-columns: 1fr;
        }

        .dashboard-today {
            grid-template-columns: repeat(2, 1fr);
        }

        .dashboard-today-item:nth-child(2) {
            border-right: 0;
        }

        .dashboard-today-item:nth-child(-n+2) {
            border-bottom: 1px solid var(--border);
        }

    }

    @media (max-width: 600px) {

        .dashboard-today {
            grid-template-columns: 1fr;
        }

        .dashboard-today-item {
            border-right: 0;
            border-bottom: 1px solid var(--border);
        }

        .dashboard-today-item:last-child {
            border-bottom: 0;
        }

        .dashboard-task {
            grid-template-columns: 1fr;
            gap: 7px;
        }

    }
</style>


<div class="dashboard">


    {{-- ================================================= --}}
    {{-- HEADER --}}
    {{-- ================================================= --}}

    <div class="page-head">

        <div>

            <span class="eyebrow">
                DASBOR
            </span>

            <h1>
                Selamat sore, {{ auth()->user()->name ?? 'Pengguna' }}
            </h1>

            <p>
                Berikut pekerjaan yang perlu Anda perhatikan hari ini.
            </p>

        </div>

    </div>


    {{-- ================================================= --}}
    {{-- RINGKASAN HARI INI --}}
    {{-- ================================================= --}}

    <div class="dashboard-today">

        <div class="dashboard-today-item">

            <span>
                Jatuh Tempo
            </span>

            <strong>
                {{ $dueTodayCount ?? 0 }}
            </strong>

            <small>
                Hari ini
            </small>

        </div>


        <div class="dashboard-today-item">

            <span>
                Terlambat
            </span>

            <strong>
                {{ $overdueCount ?? 0 }}
            </strong>

            <small>
                Perlu ditindaklanjuti
            </small>

        </div>


        <div class="dashboard-today-item">

            <span>
                Selesai
            </span>

            <strong>
                {{ $completedCount ?? 0 }}
            </strong>

            <small>
                Task selesai
            </small>

        </div>


        <div class="dashboard-today-item">

            <span>
                Dikerjakan
            </span>

            <strong>
                {{ $inProgressCount ?? 0 }}
            </strong>

            <small>
                Sedang dikerjakan
            </small>

        </div>

    </div>


    {{-- ================================================= --}}
    {{-- PEKERJAAN + PROGRES --}}
    {{-- ================================================= --}}

    <div class="dashboard-grid">


        {{-- ============================================= --}}
        {{-- PEKERJAAN SAYA --}}
        {{-- ============================================= --}}

        <div class="dashboard-panel">

            <div class="dashboard-panel-head">

                <strong>
                    Pekerjaan Saya
                </strong>

                <a
                    href="{{ route('tasks.index') }}"
                    class="dashboard-link"
                >
                    Lihat semua ?
                </a>

            </div>


            @forelse(
                collect($myTasks ?? [])->take(6)
                as $task
            )

                <div class="dashboard-task">

                    <div class="dashboard-task-main">

                        <i class="dashboard-task-check"></i>

                        <div class="dashboard-task-info">

                            <strong class="dashboard-task-title">
                                {{ $task->title }}
                            </strong>

                            <small class="dashboard-task-meta">

                                @if($task->space)
                                    {{ $task->space->name }}
                                @endif

                                @if($task->taskList)
                                    / {{ $task->taskList->name }}
                                @endif

                                @if($task->due_date)
                                    · {{ $task->due_date->format('d M') }}
                                @endif

                            </small>

                        </div>

                    </div>


                    <span>

                        <b
                            class="status {{ str_replace('_', '-', $task->status) }}"
                        >
                            {{
                                [
                                    'open' => 'To Do',
                                    'in_progress' => 'In Progress',
                                    'review' => 'Review',
                                    'done' => 'Selesai'
                                ][$task->status]
                                ?? ucfirst($task->status)
                            }}
                        </b>

                    </span>


                    <b
                        class="priority {{ strtolower($task->priority ?? 'normal') }}"
                    >
                        {{ ucfirst($task->priority ?? 'Normal') }}
                    </b>

                </div>

            @empty

                <div class="dashboard-empty">
                    Tidak ada pekerjaan yang perlu dikerjakan.
                </div>

            @endforelse

        </div>


        {{-- ============================================= --}}
        {{-- PROGRES --}}
        {{-- ============================================= --}}

        <div class="dashboard-panel">

            <div class="dashboard-panel-head">

                <strong>
                    Progres
                </strong>

            </div>


            @php

                $completed =
                    $completedCount ?? 0;

                $inProgress =
                    $inProgressCount ?? 0;

                $todo =
                    $todoCount ?? 0;

                $progressTotal =
                    $completed +
                    $inProgress +
                    $todo;

                $completionPercent =
                    $progressTotal > 0
                        ? round(
                            ($completed / $progressTotal) * 100
                        )
                        : 0;

            @endphp


            <div class="dashboard-progress">

                <div class="dashboard-progress-number">
                    {{ $completionPercent }}%
                </div>

                <span class="dashboard-progress-label">
                    Pekerjaan selesai
                </span>


                <div class="dashboard-progress-bar">

                    <div
                        class="dashboard-progress-fill"
                        style="
                            width:{{ $completionPercent }}%;
                        "
                    ></div>

                </div>


                <div class="dashboard-progress-detail">

                    <div>

                        <span>
                            Selesai
                        </span>

                        <strong>
                            {{ $completed }}
                        </strong>

                    </div>


                    <div>

                        <span>
                            Dikerjakan
                        </span>

                        <strong>
                            {{ $inProgress }}
                        </strong>

                    </div>


                    <div>

                        <span>
                            To Do
                        </span>

                        <strong>
                            {{ $todo }}
                        </strong>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- ================================================= --}}
    {{-- PERHATIAN + AKTIVITAS --}}
    {{-- ================================================= --}}

    <div class="dashboard-grid">


        {{-- ============================================= --}}
        {{-- PERLU PERHATIAN --}}
        {{-- ============================================= --}}

        <div class="dashboard-panel">

            <div class="dashboard-panel-head">

                <strong>
                    Perlu Perhatian
                </strong>

            </div>


            <div class="dashboard-attention-item">

                <strong class="dashboard-attention-title">
                    ?? {{ $overdueCount ?? 0 }} tugas terlambat
                </strong>

                <small class="dashboard-attention-desc">
                    Segera ditindaklanjuti
                </small>

            </div>


            <div class="dashboard-attention-item">

                <strong class="dashboard-attention-title">
                    ?? {{ $dueTodayCount ?? 0 }}
                    tugas jatuh tempo hari ini
                </strong>

                <small class="dashboard-attention-desc">
                    Perlu diselesaikan hari ini
                </small>

            </div>


            <div class="dashboard-attention-item">

                <strong class="dashboard-attention-title">
                    ?? {{ $unassignedCount ?? 0 }}
                    tugas belum ditugaskan
                </strong>

                <small class="dashboard-attention-desc">
                    Perlu menentukan assignee
                </small>

            </div>

        </div>


        {{-- ============================================= --}}
        {{-- AKTIVITAS TERBARU --}}
        {{-- ============================================= --}}

        <div class="dashboard-panel">

            <div class="dashboard-panel-head">

                <strong>
                    Aktivitas Terbaru
                </strong>

            </div>


            @forelse(
                collect($recentActivities ?? [])->take(5)
                as $activity
            )

                <div class="dashboard-activity-item">

                    <strong class="dashboard-activity-title">

                        {{
                            $activity->description
                            ?? $activity->message
                            ?? 'Aktivitas diperbarui'
                        }}

                    </strong>

                    <small class="dashboard-activity-time">

                        {{ $activity->created_at?->diffForHumans() }}

                    </small>

                </div>

            @empty

                <div class="dashboard-empty">
                    Belum ada aktivitas terbaru.
                </div>

            @endforelse

        </div>

    </div>


    {{-- ================================================= --}}
    {{-- SPACE ANDA --}}
    {{-- ================================================= --}}

    <div
        class="dashboard-panel"
        style="margin-top:18px;"
    >

        <div class="dashboard-panel-head">

            <strong>
                Space Anda
            </strong>

            <span
                style="
                    color:var(--muted);
                    font-size:11px;
                "
            >
                {{ collect($spaces ?? [])->count() }} Space
            </span>

        </div>


        <div class="dashboard-spaces">

            @forelse($spaces ?? [] as $space)

                @php

                    $total =
                        $space->tasks_count ?? 0;

                    $completed =
                        $space->completed_tasks_count ?? 0;

                    $percent =
                        $total > 0
                            ? round(
                                ($completed / $total) * 100
                            )
                            : 0;

                @endphp


                <a
                    href="{{ route('tasks.index', [
                        'space_id' => $space->id
                    ]) }}"
                    class="dashboard-space"
                >

                    <strong class="dashboard-space-name">
                        ? {{ $space->name }}
                    </strong>


                    <small class="dashboard-space-count">
                        {{ $total }} task
                    </small>


                    <div class="dashboard-space-progress">

                        <span
                            style="width:{{ $percent }}%;"
                        ></span>

                    </div>


                    <small class="dashboard-space-percent">
                        {{ $percent }}% selesai
                    </small>

                </a>

            @empty

                <div class="dashboard-empty">
                    Belum ada Space yang dapat diakses.
                </div>

            @endforelse

        </div>

    </div>

</div>

@endsection