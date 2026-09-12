@extends('layouts.task-manager')

@section('title', 'Calendar')
@section('page', 'Calendar')

@section('content')

@php
    $startOfMonth = $date->copy()->startOfMonth();
    $daysInMonth = $date->daysInMonth;

    // Senin = 1 ... Minggu = 7
    $startDay = $startOfMonth->dayOfWeekIso;

    $previousMonth = $date->copy()->subMonth();
    $nextMonth = $date->copy()->addMonth();

    $today = now()->toDateString();
@endphp

<div class="page-head">
    <div>
        <span class="eyebrow">VIEW</span>

        <h1>{{ $date->translatedFormat('F Y') }}</h1>

        <p>Timeline task berdasarkan due date.</p>
    </div>

    <button
        class="btn primary"
        onclick="@if(session()->has('workspace_id')) openTaskModal('New Task'); @else alert('Pilih workspace terlebih dahulu sebelum membuat task.'); @endif"
    >
        ＋ New Task
    </button>
</div>

<div class="calendar panel">

    <div class="calendar-head">

        <a
            href="{{ route('tasks.calendar', [
                'month' => $previousMonth->month,
                'year' => $previousMonth->year
            ]) }}"
            class="icon-btn"
        >
            ‹
        </a>

        <strong>
            {{ $date->translatedFormat('F Y') }}
        </strong>

        <a
            href="{{ route('tasks.calendar', [
                'month' => $nextMonth->month,
                'year' => $nextMonth->year
            ]) }}"
            class="icon-btn"
        >
            ›
        </a>

    </div>

    <div class="weekdays">
        @foreach(['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $d)
            <span>{{ $d }}</span>
        @endforeach
    </div>

    <div class="days">

        {{-- Kotak kosong sebelum tanggal 1 --}}
        @for($i = 1; $i < $startDay; $i++)
            <div class="day empty"></div>
        @endfor

        {{-- Tanggal --}}
        @for($i = 1; $i <= $daysInMonth; $i++)

            @php
                $currentDate = $date->copy()->day($i);
                $dateString = $currentDate->toDateString();

                $dayTasks = $tasks->filter(function ($task) use ($currentDate) {

                    if (!$task->start_date && !$task->due_date) {
                        return false;
                    }

                    $start = $task->start_date
                        ? \Carbon\Carbon::parse($task->start_date)->startOfDay()
                        : \Carbon\Carbon::parse($task->due_date)->startOfDay();

                    $due = $task->due_date
                        ? \Carbon\Carbon::parse($task->due_date)->endOfDay()
                        : $start->copy()->endOfDay();

                    return $currentDate->betweenIncluded($start, $due);
                });
            @endphp

            <div class="day {{ $dateString === $today ? 'today' : '' }}">

                <span>{{ $i }}</span>

                @foreach($dayTasks as $task)

                    <div
                        class="calendar-task status-{{ str_replace('_', '-', $task->status) }}"
                        onclick='openTaskModal(@json($task))'
                    >
                        {{ $task->title }}
                    </div>

                @endforeach

            </div>

        @endfor

    </div>

</div>

<div class="calendar-legend">

    <span class="legend-item">
        <i class="legend-dot status-open"></i>
        To Do
    </span>

    <span class="legend-item">
        <i class="legend-dot status-in-progress"></i>
        In Progress
    </span>

    <span class="legend-item">
        <i class="legend-dot status-review"></i>
        Review
    </span>

    <span class="legend-item">
        <i class="legend-dot status-done"></i>
        Done
    </span>

</div>

@endsection