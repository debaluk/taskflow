@extends('layouts.task-manager')

@section('title', 'Gantt')
@section('page', 'Gantt')

@section('content')

@php
    $daysInMonth = $date->daysInMonth;
    $previousMonth = $date->copy()->subMonth();
    $nextMonth = $date->copy()->addMonth();

    $ganttDependencies = $tasks
        ->mapWithKeys(function ($task) {
            return [
                $task->id => $task->dependencies
                    ->map(function ($dependency) {
                        return [
                            'id' =>
                                $dependency->id,

                            'task_id' =>
                                $dependency->task_id,

                            'depends_on_task_id' =>
                                $dependency->depends_on_task_id,

                            'dependency_type' =>
                                $dependency->dependency_type,
                        ];
                    })
                    ->values()
                    ->toArray(),
            ];
        })
        ->toArray();
@endphp


<div class="page-head">

    <div>
        <span class="eyebrow">VIEW</span>

        <h1>Gantt Chart</h1>

        <p>
            Timeline, hierarchy, progress, dan dependency.
        </p>
    </div>


    <div
        style="
            display:flex;
            align-items:center;
            gap:8px;
        "
    >

        <a
            href="{{ route('tasks.gantt.previous') }}"
            class="btn"
        >
            ‹
        </a>


        <div
            style="
                min-width:130px;
                text-align:center;
                font-size:12px;
                font-weight:700;
            "
        >
            {{ $date->translatedFormat('F Y') }}
        </div>


        <a
            href="{{ route('tasks.gantt.next') }}"
            class="btn"
        >
            ›
        </a>


        <button
            class="btn primary"
            onclick="@if(session()->has('workspace_id')) openTaskModal('New Task'); @else alert('Pilih workspace terlebih dahulu sebelum membuat task.'); @endif"
        >
            ＋ New Task
        </button>

    </div>

</div>



{{-- =========================================================
     GANTT
     ========================================================= --}}

<div
    class="gantt panel"
    style="--gantt-days:{{ $daysInMonth }};"
>


    {{-- =====================================================
         LEFT : TASK
         ===================================================== --}}

    <div class="gantt-left">

        <div class="gantt-title">
            TASK
        </div>


        @foreach($tasks as $task)

            <div
                class="gantt-task"
                style="
                    padding-left:{{ $task->parent_id ? 32 : 14 }}px;
                "
                data-task-id="{{ $task->id }}"
            >
                {{ $task->title }}
            </div>

        @endforeach

    </div>



    {{-- =====================================================
         RIGHT : TIMELINE
         ===================================================== --}}

    <div class="gantt-right">


        {{-- TIMELINE HEADER --}}

        <div
            class="timeline-head"
            style="
                grid-template-columns:
                repeat({{ $daysInMonth }}, 1fr);
            "
        >

            @for($day = 1; $day <= $daysInMonth; $day++)

                <span>
                    {{ $day }}
                </span>

            @endfor

        </div>



        {{-- GANTT BODY --}}

        <div class="gantt-body">


            {{-- DEPENDENCY SVG --}}

            <svg
                id="ganttDependencies"
                class="gantt-dependencies"
            ></svg>



            {{-- TASK ROWS --}}

            @foreach($tasks as $task)

                @php

                    $start = $task->start_date
                        ? \Carbon\Carbon::parse(
                            $task->start_date
                        )
                        : null;

                    $due = $task->due_date
                        ? \Carbon\Carbon::parse(
                            $task->due_date
                        )
                        : null;


                    $barLeft = 0;

                    $barWidth = 0;


                    if ($start && $due) {

                        $monthStart =
                            $date
                                ->copy()
                                ->startOfMonth();

                        $monthEnd =
                            $date
                                ->copy()
                                ->endOfMonth();


                        $visibleStart =
                            $start->lt($monthStart)
                                ? $monthStart
                                : $start;


                        $visibleEnd =
                            $due->gt($monthEnd)
                                ? $monthEnd
                                : $due;


                        if (
                            $visibleStart
                                ->lte($visibleEnd)
                        ) {

                            $barLeft =
                                (
                                    (
                                        $visibleStart->day - 1
                                    )
                                    /
                                    $daysInMonth
                                )
                                * 100;


                            $duration =
                                $visibleStart
                                    ->diffInDays(
                                        $visibleEnd
                                    )
                                + 1;


                            $barWidth =
                                (
                                    $duration
                                    /
                                    $daysInMonth
                                )
                                * 100;
                        }

                    }

                @endphp



                <div
                    class="gantt-line"
                    data-task-id="{{ $task->id }}"
                >


                    @if($barWidth > 0)

                        <div
                            id="gantt-task-{{ $task->id }}"
                            class="gantt-bar"
                            style="
                                left:{{ $barLeft }}%;
                                width:{{ $barWidth }}%;
                            "
                            data-task-id="{{ $task->id }}"
                        >


                            {{-- TARGET HANDLE --}}

                            <span
                                class="
                                    dependency-handle
                                    dependency-target
                                "
                                data-task-id="{{ $task->id }}"
                            ></span>



                            {{-- PROGRESS --}}

                            <span class="gantt-progress">
                                {{ $task->progress ?? 0 }}%
                            </span>



                            {{-- SOURCE HANDLE --}}

                            <span
                                class="
                                    dependency-handle
                                    dependency-source
                                "
                                data-task-id="{{ $task->id }}"
                                title="Tarik ke task lain"
                            ></span>


                        </div>

                    @endif


                </div>

            @endforeach


        </div>

    </div>

</div>



{{-- =========================================================
     DEPENDENCY LEGEND
     ========================================================= --}}

<div class="gantt-legend">


    <div class="gantt-legend-item">

        <span
            class="
                gantt-legend-line
                dependency-fs
            "
        ></span>

        <span>
            <strong>FS</strong>
            Finish → Start
        </span>

    </div>



    <div class="gantt-legend-item">

        <span
            class="
                gantt-legend-line
                dependency-ss
            "
        ></span>

        <span>
            <strong>SS</strong>
            Start → Start
        </span>

    </div>



    <div class="gantt-legend-item">

        <span
            class="
                gantt-legend-line
                dependency-ff
            "
        ></span>

        <span>
            <strong>FF</strong>
            Finish → Finish
        </span>

    </div>



    <div class="gantt-legend-item">

        <span
            class="
                gantt-legend-line
                dependency-sf
            "
        ></span>

        <span>
            <strong>SF</strong>
            Start → Finish
        </span>

    </div>


</div>



{{-- =========================================================
     DEPENDENCY TYPE POPUP
     ========================================================= --}}

<div
    id="dependencyTypePopup"
    class="dependency-type-popup"
    style="display:none;"
    onclick="event.stopPropagation()"
>


    <div class="dependency-type-popup-head">

        <strong>
            Dependency Type
        </strong>


        <button
            type="button"
            onclick="closeDependencyTypePopup()"
        >
            ×
        </button>

    </div>



    <div class="dependency-type-list">


        {{-- FS --}}

        <label class="dependency-type-option">

            <input
                type="radio"
                name="dependency_type"
                value="FS"
                checked
            >

            <span>

                <strong>FS</strong>

                Finish → Start

            </span>

        </label>



        {{-- SS --}}

        <label class="dependency-type-option">

            <input
                type="radio"
                name="dependency_type"
                value="SS"
            >

            <span>

                <strong>SS</strong>

                Start → Start

            </span>

        </label>



        {{-- FF --}}

        <label class="dependency-type-option">

            <input
                type="radio"
                name="dependency_type"
                value="FF"
            >

            <span>

                <strong>FF</strong>

                Finish → Finish

            </span>

        </label>



        {{-- SF --}}

        <label class="dependency-type-option">

            <input
                type="radio"
                name="dependency_type"
                value="SF"
            >

            <span>

                <strong>SF</strong>

                Start → Finish

            </span>

        </label>


    </div>



    <div class="dependency-type-popup-actions">


        <button
            type="button"
            class="btn"
            onclick="closeDependencyTypePopup()"
        >
            Cancel
        </button>


        <button
            type="button"
            class="btn primary"
            onclick="saveDependencyWithType()"
        >
            Save
        </button>


    </div>

</div>



<script>

    window.ganttDependencies =
        @json($ganttDependencies);

</script>


@endsection