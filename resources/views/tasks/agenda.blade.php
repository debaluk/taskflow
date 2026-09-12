@extends('layouts.task-manager')

@section('title', 'Agenda')

@section('content')

<div class="page agenda-page">

    {{-- =========================================================
         HEADER
         ========================================================= --}}
    <div class="page-header">

        <div class="page-header-main">

            <div>

                <span class="eyebrow">
                    TASK MANAGEMENT
                </span>

                <h1>
                    Agenda
                </h1>

                <p class="page-subtitle">
                    Kelola dan pantau task berdasarkan jadwal.
                </p>

            </div>

        </div>


        {{-- DATE NAVIGATION --}}
        <div class="agenda-date-navigation">

            <button
                type="button"
                class="btn ghost"
                id="agendaPrevious"
                title="Hari sebelumnya"
            >
                ‹
            </button>

            <button
                type="button"
                class="btn ghost agenda-today-btn"
                id="agendaToday"
            >
                Today
            </button>

            <button
                type="button"
                class="btn ghost"
                id="agendaNext"
                title="Hari berikutnya"
            >
                ›
            </button>

        </div>

    </div>


    {{-- =========================================================
         SUMMARY
         ========================================================= --}}
    <div class="agenda-summary">

        {{-- TODAY --}}
        <div class="agenda-summary-card">

            <div class="agenda-summary-icon">
                ?
            </div>

            <div>

                <span class="agenda-summary-label">
                    Today
                </span>

                <strong>
                    {{ $todayCount ?? 0 }}
                </strong>

            </div>

        </div>


        {{-- UPCOMING --}}
        <div class="agenda-summary-card">

            <div class="agenda-summary-icon">
                ?
            </div>

            <div>

                <span class="agenda-summary-label">
                    Upcoming
                </span>

                <strong>
                    {{ $upcomingCount ?? 0 }}
                </strong>

            </div>

        </div>


        {{-- OVERDUE --}}
        <div class="agenda-summary-card">

            <div class="agenda-summary-icon">
                !
            </div>

            <div>

                <span class="agenda-summary-label">
                    Overdue
                </span>

                <strong>
                    {{ $overdueCount ?? 0 }}
                </strong>

            </div>

        </div>


        {{-- COMPLETED --}}
        <div class="agenda-summary-card">

            <div class="agenda-summary-icon">
                ?
            </div>

            <div>

                <span class="agenda-summary-label">
                    Completed
                </span>

                <strong>
                    {{ $completedCount ?? 0 }}
                </strong>

            </div>

        </div>

    </div>


    {{-- =========================================================
         AGENDA TOOLBAR
         ========================================================= --}}
    <div class="agenda-toolbar">

        <div class="agenda-toolbar-left">

            <div class="agenda-date-title">

                <strong id="agendaDateTitle">
                    {{ now()->translatedFormat('l, d F Y') }}
                </strong>

            </div>

        </div>


        <div class="agenda-toolbar-right">

            {{-- FILTER --}}
            <select
                id="agendaStatusFilter"
                class="agenda-filter"
            >
                <option value="">
                    Semua Status
                </option>

                <option value="open">
                    To Do
                </option>

                <option value="in_progress">
                    In Progress
                </option>

                <option value="review">
                    Review
                </option>

                <option value="done">
                    Done
                </option>
            </select>


            {{-- PRIORITY --}}
            <select
                id="agendaPriorityFilter"
                class="agenda-filter"
            >
                <option value="">
                    Semua Priority
                </option>

                <option value="urgent">
                    Urgent
                </option>

                <option value="high">
                    High
                </option>

                <option value="normal">
                    Normal
                </option>

                <option value="low">
                    Low
                </option>
            </select>

        </div>

    </div>


    {{-- =========================================================
         AGENDA CONTENT
         ========================================================= --}}
    <div class="agenda-container">


        {{-- =====================================================
             OVERDUE
             ===================================================== --}}
        @if(isset($overdueTasks) && $overdueTasks->count())

            <section class="agenda-section agenda-overdue">

                <div class="agenda-section-head">

                    <div class="agenda-section-title">

                        <span class="agenda-section-marker">
                            !
                        </span>

                        <div>

                            <strong>
                                Overdue
                            </strong>

                            <small>
                                Task yang melewati due date
                            </small>

                        </div>

                    </div>


                    <span class="agenda-section-count">
                        {{ $overdueTasks->count() }}
                    </span>

                </div>


                <div class="agenda-task-list">

                    @foreach($overdueTasks as $task)

                        @include(
                            'tasks.partials.agenda-task',
                            ['task' => $task]
                        )

                    @endforeach

                </div>

            </section>

        @endif



        {{-- =====================================================
             TODAY
             ===================================================== --}}
        <section class="agenda-section">

            <div class="agenda-section-head">

                <div class="agenda-section-title">

                    <span class="agenda-section-marker">
                        ?
                    </span>

                    <div>

                        <strong>
                            Today
                        </strong>

                        <small>
                            {{ now()->translatedFormat('d F Y') }}
                        </small>

                    </div>

                </div>


                <span class="agenda-section-count">
                    {{ isset($todayTasks) ? $todayTasks->count() : 0 }}
                </span>

            </div>


            @if(isset($todayTasks) && $todayTasks->count())

                <div class="agenda-task-list">

                    @foreach($todayTasks as $task)

                        @include(
                            'tasks.partials.agenda-task',
                            ['task' => $task]
                        )

                    @endforeach

                </div>

            @else

                <div class="agenda-empty">

                    <div class="agenda-empty-icon">
                        ?
                    </div>

                    <strong>
                        Tidak ada task untuk hari ini
                    </strong>

                    <span>
                        Agenda kamu kosong. Nikmati kemenangan kecil ini. ??
                    </span>

                </div>

            @endif

        </section>



        {{-- =====================================================
             UPCOMING
             ===================================================== --}}
        <section class="agenda-section">

            <div class="agenda-section-head">

                <div class="agenda-section-title">

                    <span class="agenda-section-marker">
                        ?
                    </span>

                    <div>

                        <strong>
                            Upcoming
                        </strong>

                        <small>
                            Task yang akan datang
                        </small>

                    </div>

                </div>


                <span class="agenda-section-count">
                    {{ isset($upcomingTasks) ? $upcomingTasks->count() : 0 }}
                </span>

            </div>


            @if(isset($upcomingTasks) && $upcomingTasks->count())

                <div class="agenda-task-list">

                    @foreach($upcomingTasks as $task)

                        @include(
                            'tasks.partials.agenda-task',
                            ['task' => $task]
                        )

                    @endforeach

                </div>

            @else

                <div class="agenda-empty">

                    <div class="agenda-empty-icon">
                        —
                    </div>

                    <strong>
                        Belum ada task mendatang
                    </strong>

                    <span>
                        Tidak ada task terjadwal setelah hari ini.
                    </span>

                </div>

            @endif

        </section>

    </div>

</div>


{{-- =============================================================
     AGENDA TASK PARTIAL FALLBACK
     ============================================================= --}}
<style>

.agenda-page {
    max-width: 1400px;
    margin: 0 auto;
}


/* HEADER */

.page-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 24px;
    margin-bottom: 24px;
}

.page-header h1 {
    margin: 4px 0 6px;
    font-size: 30px;
    line-height: 1.15;
}

.page-subtitle {
    margin: 0;
    color: var(--muted);
    font-size: 14px;
}


/* DATE NAVIGATION */

.agenda-date-navigation {
    display: flex;
    align-items: center;
    gap: 6px;
}

.agenda-today-btn {
    min-width: 72px;
}


/* SUMMARY */

.agenda-summary {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
    margin-bottom: 22px;
}

.agenda-summary-card {
    min-height: 82px;
    padding: 16px 18px;
    border: 1px solid var(--line);
    border-radius: 12px;
    background: var(--panel);

    display: flex;
    align-items: center;
    gap: 13px;
}

.agenda-summary-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;

    display: flex;
    align-items: center;
    justify-content: center;

    background: var(--primary-soft);
    color: var(--primary);
    font-weight: 700;
}

.agenda-summary-label {
    display: block;
    margin-bottom: 3px;
    color: var(--muted);
    font-size: 12px;
}

.agenda-summary-card strong {
    font-size: 21px;
}


/* TOOLBAR */

.agenda-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;

    padding: 14px 16px;
    margin-bottom: 16px;

    border: 1px solid var(--line);
    border-radius: 12px;
    background: var(--panel);
}

.agenda-date-title {
    font-size: 14px;
}

.agenda-toolbar-right {
    display: flex;
    align-items: center;
    gap: 8px;
}

.agenda-filter {
    height: 34px;
    padding: 0 10px;

    border: 1px solid var(--line);
    border-radius: 8px;

    background: var(--panel);
    color: var(--text);

    font-size: 13px;
}


/* SECTION */

.agenda-container {
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.agenda-section {
    overflow: hidden;

    border: 1px solid var(--line);
    border-radius: 12px;
    background: var(--panel);
}

.agenda-section-head {
    min-height: 64px;
    padding: 12px 18px;

    display: flex;
    align-items: center;
    justify-content: space-between;

    border-bottom: 1px solid var(--line);
}

.agenda-section-title {
    display: flex;
    align-items: center;
    gap: 12px;
}

.agenda-section-title > div {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.agenda-section-title strong {
    font-size: 14px;
}

.agenda-section-title small {
    color: var(--muted);
    font-size: 12px;
}

.agenda-section-marker {
    width: 30px;
    height: 30px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 8px;

    background: var(--primary-soft);
    color: var(--primary);

    font-weight: 700;
}

.agenda-section-count {
    min-width: 28px;
    height: 24px;
    padding: 0 8px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    border-radius: 12px;

    background: var(--primary-soft);
    color: var(--primary);

    font-size: 12px;
    font-weight: 700;
}


/* TASK LIST */

.agenda-task-list {
    display: flex;
    flex-direction: column;
}


/* TASK ROW */

.agenda-task-row {
    min-height: 68px;
    padding: 12px 18px;

    display: grid;
    grid-template-columns: 28px minmax(0, 1fr) auto;
    align-items: center;
    gap: 12px;

    border-bottom: 1px solid var(--line);

    cursor: pointer;
    transition: background .15s ease;
}

.agenda-task-row:last-child {
    border-bottom: 0;
}

.agenda-task-row:hover {
    background: #fafbfc;
}

.agenda-task-check {
    width: 20px;
    height: 20px;

    border: 1px solid var(--line);
    border-radius: 50%;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 11px;
}

.agenda-task-main {
    min-width: 0;
}

.agenda-task-title {
    display: block;

    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;

    font-size: 14px;
    font-weight: 600;
}

.agenda-task-context {
    margin-top: 5px;

    display: flex;
    align-items: center;
    gap: 6px;

    color: var(--muted);
    font-size: 11px;
}

.agenda-task-context .separator {
    opacity: .5;
}

.agenda-task-meta {
    display: flex;
    align-items: center;
    gap: 8px;
}

.agenda-task-priority {
    padding: 4px 8px;

    border-radius: 6px;

    background: var(--primary-soft);
    color: var(--primary);

    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
}

.agenda-task-assignee {
    width: 28px;
    height: 28px;

    border-radius: 50%;

    display: flex;
    align-items: center;
    justify-content: center;

    background: var(--primary-soft);
    color: var(--primary);

    font-size: 11px;
    font-weight: 700;
}


/* EMPTY */

.agenda-empty {
    min-height: 170px;
    padding: 30px 20px;

    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;

    text-align: center;
}

.agenda-empty-icon {
    width: 42px;
    height: 42px;

    margin-bottom: 10px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 50%;

    background: var(--primary-soft);
    color: var(--primary);

    font-weight: 700;
}

.agenda-empty strong {
    font-size: 14px;
}

.agenda-empty span {
    margin-top: 5px;

    color: var(--muted);
    font-size: 12px;
}


/* OVERDUE */

.agenda-overdue .agenda-section-marker {
    background: #fff1f1;
    color: #dc2626;
}

.agenda-overdue .agenda-section-count {
    background: #fff1f1;
    color: #dc2626;
}


/* RESPONSIVE */

@media (max-width: 900px) {

    .agenda-summary {
        grid-template-columns: repeat(2, 1fr);
    }

    .page-header {
        align-items: flex-start;
        flex-direction: column;
    }

    .agenda-toolbar {
        align-items: flex-start;
        flex-direction: column;
    }

    .agenda-toolbar-right {
        width: 100%;
    }

    .agenda-filter {
        flex: 1;
    }

}


@media (max-width: 600px) {

    .agenda-summary {
        grid-template-columns: 1fr;
    }

    .agenda-task-row {
        grid-template-columns: 24px minmax(0, 1fr);
    }

    .agenda-task-meta {
        grid-column: 2;
    }

    .agenda-task-context {
        flex-wrap: wrap;
    }

}

</style>

@endsection