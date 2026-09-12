@extends('layouts.task-manager')

@section('title', 'Favorites')

@section('content')

<div class="favorites-page">

    {{-- HEADER --}}
    <div class="favorites-header">
        <div>
            <div class="favorites-title-row">
                <span class="favorites-star">?</span>
                <h1>Favorites</h1>
            </div>

            <p class="favorites-subtitle">
                Akses cepat ke task yang kamu tandai sebagai favorit.
            </p>
        </div>

        <div class="favorites-header-actions">
            <button type="button" class="btn-secondary" onclick="refreshFavorites()">
                ? Refresh
            </button>
        </div>
    </div>


    {{-- SUMMARY --}}
    <div class="favorites-summary">

        <div class="favorite-summary-card">
            <div class="summary-icon">?</div>
            <div>
                <div class="summary-label">Total Favorites</div>
                <div class="summary-value">
                    {{ $favoriteCount ?? 0 }}
                </div>
            </div>
        </div>

        <div class="favorite-summary-card">
            <div class="summary-icon">?</div>
            <div>
                <div class="summary-label">Completed</div>
                <div class="summary-value">
                    {{ $favoriteTasks->where('status', 'completed')->count() }}
                </div>
            </div>
        </div>

        <div class="favorite-summary-card">
            <div class="summary-icon">!</div>
            <div>
                <div class="summary-label">Overdue</div>
                <div class="summary-value">
                    {{ $favoriteTasks->filter(function ($task) {
                        return isset($task->due_date)
                            && $task->due_date
                            && $task->due_date < now()->toDateString()
                            && ($task->status ?? null) !== 'completed';
                    })->count() }}
                </div>
            </div>
        </div>

    </div>


    {{-- TOOLBAR --}}
    <div class="favorites-toolbar">

        <div class="favorites-tabs">

            <button
                type="button"
                class="favorite-tab active"
                data-filter="all"
                onclick="filterFavorites('all', this)"
            >
                All
            </button>

            <button
                type="button"
                class="favorite-tab"
                data-filter="active"
                onclick="filterFavorites('active', this)"
            >
                Active
            </button>

            <button
                type="button"
                class="favorite-tab"
                data-filter="completed"
                onclick="filterFavorites('completed', this)"
            >
                Completed
            </button>

        </div>

        <div class="favorites-filter">

            <select id="favoritePriorityFilter" onchange="applyFavoriteFilters()">
                <option value="">All priorities</option>
                <option value="urgent">Urgent</option>
                <option value="high">High</option>
                <option value="normal">Normal</option>
                <option value="low">Low</option>
            </select>

        </div>

    </div>


    {{-- FAVORITES LIST --}}
    <div class="favorites-list" id="favoritesList">

        @forelse($favoriteTasks as $task)

            <div
                class="favorite-task-item"
                data-status="{{ $task->status ?? '' }}"
                data-priority="{{ $task->priority ?? '' }}"
            >

                {{-- STAR --}}
                <button
                    type="button"
                    class="favorite-star-btn active"
                    title="Remove from favorites"
                    onclick="toggleFavorite(this)"
                >
                    ?
                </button>


                {{-- STATUS --}}
                <button
                    type="button"
                    class="favorite-status"
                    title="Task status"
                >
                    @if(($task->status ?? '') === 'completed')
                        ?
                    @else
                        ?
                    @endif
                </button>


                {{-- MAIN --}}
                <div class="favorite-task-main">

                    <div class="favorite-task-title-row">

                        <a
                            href="{{ route('tasks.show', $task) }}"
                            class="favorite-task-title"
                        >
                            {{ $task->title }}
                        </a>

                        @if(($task->priority ?? '') === 'urgent')
                            <span class="priority-badge urgent">
                                Urgent
                            </span>
                        @elseif(($task->priority ?? '') === 'high')
                            <span class="priority-badge high">
                                High
                            </span>
                        @elseif(($task->priority ?? '') === 'low')
                            <span class="priority-badge low">
                                Low
                            </span>
                        @endif

                    </div>


                    {{-- CONTEXT --}}
                    <div class="favorite-task-context">

                        @if(isset($task->space))
                            <span>
                                {{ $task->space->name }}
                            </span>
                        @endif

                        @if(isset($task->taskList))
                            <span class="context-separator">›</span>
                            <span>
                                {{ $task->taskList->name }}
                            </span>
                        @endif

                    </div>


                    {{-- DESCRIPTION --}}
                    @if(!empty($task->description))
                        <div class="favorite-task-description">
                            {{ \Illuminate\Support\Str::limit($task->description, 140) }}
                        </div>
                    @endif


                    {{-- META --}}
                    <div class="favorite-task-meta">

                        @if(!empty($task->due_date))
                            <span class="task-meta-item">
                                ??
                                {{ \Carbon\Carbon::parse($task->due_date)->format('d M Y') }}
                            </span>
                        @endif

                        @if(!empty($task->assignee))
                            <span class="task-meta-item">
                                ??
                                {{ $task->assignee->name }}
                            </span>
                        @endif

                    </div>

                </div>


                {{-- ACTION --}}
                <div class="favorite-task-action">

                    <button
                        type="button"
                        class="task-action-btn"
                        onclick="openTaskFromFavorite({{ $task->id }})"
                        title="Open task"
                    >
                        ?
                    </button>

                </div>

            </div>

        @empty

            <div class="favorites-empty">

                <div class="favorites-empty-icon">
                    ?
                </div>

                <h3>Belum ada Favorite</h3>

                <p>
                    Task yang kamu tandai sebagai favorit akan muncul di sini.
                </p>

                <a
                    href="{{ route('tasks.index') }}"
                    class="btn-primary"
                >
                    Lihat Tasks
                </a>

            </div>

        @endforelse

    </div>

</div>


<style>

    .favorites-page {
        padding: 24px 28px 40px;
        max-width: 1400px;
        margin: 0 auto;
    }

    /* HEADER */

    .favorites-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 24px;
    }

    .favorites-title-row {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .favorites-title-row h1 {
        margin: 0;
        font-size: 26px;
        font-weight: 700;
        color: #20242c;
    }

    .favorites-star {
        color: #f59e0b;
        font-size: 25px;
    }

    .favorites-subtitle {
        margin: 6px 0 0 35px;
        color: #747b87;
        font-size: 14px;
    }

    .favorites-header-actions {
        display: flex;
        gap: 8px;
    }


    /* BUTTONS */

    .btn-secondary,
    .btn-primary {
        border: 0;
        border-radius: 8px;
        padding: 9px 14px;
        font-size: 13px;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .btn-secondary {
        background: #fff;
        border: 1px solid #e2e5ea;
        color: #4b5563;
    }

    .btn-secondary:hover {
        background: #f8f9fb;
    }

    .btn-primary {
        background: #4f46e5;
        color: #fff;
    }

    .btn-primary:hover {
        background: #4338ca;
    }


    /* SUMMARY */

    .favorites-summary {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 14px;
        margin-bottom: 24px;
    }

    .favorite-summary-card {
        background: #fff;
        border: 1px solid #e6e8ed;
        border-radius: 12px;
        padding: 16px 18px;
        display: flex;
        align-items: center;
        gap: 13px;
    }

    .summary-icon {
        width: 38px;
        height: 38px;
        border-radius: 9px;
        background: #fff7df;
        color: #f59e0b;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 17px;
        font-weight: 700;
    }

    .summary-label {
        color: #747b87;
        font-size: 12px;
        margin-bottom: 3px;
    }

    .summary-value {
        color: #20242c;
        font-size: 20px;
        font-weight: 700;
    }


    /* TOOLBAR */

    .favorites-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        background: #fff;
        border: 1px solid #e6e8ed;
        border-radius: 12px 12px 0 0;
        padding: 12px 15px;
    }

    .favorites-tabs {
        display: flex;
        gap: 4px;
    }

    .favorite-tab {
        border: 0;
        background: transparent;
        color: #747b87;
        padding: 8px 13px;
        border-radius: 7px;
        font-size: 13px;
        cursor: pointer;
    }

    .favorite-tab:hover {
        background: #f5f6f8;
        color: #333;
    }

    .favorite-tab.active {
        background: #eef0ff;
        color: #4f46e5;
        font-weight: 600;
    }

    .favorites-filter select {
        border: 1px solid #e0e3e8;
        border-radius: 7px;
        padding: 8px 10px;
        background: #fff;
        color: #555;
        font-size: 13px;
        outline: none;
    }


    /* LIST */

    .favorites-list {
        background: #fff;
        border: 1px solid #e6e8ed;
        border-top: 0;
        border-radius: 0 0 12px 12px;
        overflow: hidden;
    }

    .favorite-task-item {
        display: flex;
        align-items: center;
        gap: 13px;
        padding: 14px 16px;
        border-top: 1px solid #eef0f3;
        transition: background .15s ease;
    }

    .favorite-task-item:first-child {
        border-top: 0;
    }

    .favorite-task-item:hover {
        background: #fafbfc;
    }

    .favorite-star-btn,
    .favorite-status,
    .task-action-btn {
        flex: 0 0 auto;
        border: 0;
        background: transparent;
        cursor: pointer;
    }

    .favorite-star-btn {
        color: #f59e0b;
        font-size: 18px;
        width: 24px;
    }

    .favorite-star-btn:hover {
        color: #d97706;
    }

    .favorite-status {
        width: 24px;
        height: 24px;
        border: 1px solid #d7dbe2;
        border-radius: 50%;
        color: #6b7280;
        font-size: 12px;
    }

    .favorite-task-main {
        flex: 1;
        min-width: 0;
    }

    .favorite-task-title-row {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
    }

    .favorite-task-title {
        color: #20242c;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
    }

    .favorite-task-title:hover {
        color: #4f46e5;
    }

    .favorite-task-context {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-top: 4px;
        font-size: 12px;
        color: #8a909b;
    }

    .context-separator {
        color: #b5bac3;
    }

    .favorite-task-description {
        margin-top: 5px;
        color: #737985;
        font-size: 12px;
    }

    .favorite-task-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 14px;
        margin-top: 7px;
    }

    .task-meta-item {
        color: #8a909b;
        font-size: 11px;
    }

    .favorite-task-action {
        flex: 0 0 auto;
    }

    .task-action-btn {
        width: 30px;
        height: 30px;
        border-radius: 7px;
        color: #8a909b;
        font-size: 17px;
    }

    .task-action-btn:hover {
        background: #eef0ff;
        color: #4f46e5;
    }


    /* PRIORITY */

    .priority-badge {
        display: inline-flex;
        align-items: center;
        padding: 3px 7px;
        border-radius: 5px;
        font-size: 10px;
        font-weight: 600;
    }

    .priority-badge.urgent {
        background: #fff0f0;
        color: #dc2626;
    }

    .priority-badge.high {
        background: #fff7ed;
        color: #ea580c;
    }

    .priority-badge.low {
        background: #f3f4f6;
        color: #6b7280;
    }


    /* EMPTY */

    .favorites-empty {
        text-align: center;
        padding: 70px 20px;
    }

    .favorites-empty-icon {
        width: 58px;
        height: 58px;
        margin: 0 auto 15px;
        border-radius: 14px;
        background: #fff7df;
        color: #f59e0b;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 27px;
    }

    .favorites-empty h3 {
        margin: 0 0 7px;
        font-size: 16px;
        color: #30343b;
    }

    .favorites-empty p {
        margin: 0 auto 18px;
        color: #8a909b;
        font-size: 13px;
        max-width: 420px;
    }


    /* RESPONSIVE */

    @media (max-width: 900px) {

        .favorites-summary {
            grid-template-columns: 1fr;
        }

    }

    @media (max-width: 700px) {

        .favorites-page {
            padding: 18px 15px 30px;
        }

        .favorites-header {
            align-items: flex-start;
        }

        .favorites-header-actions {
            display: none;
        }

        .favorites-toolbar {
            flex-direction: column;
            align-items: stretch;
        }

        .favorites-filter select {
            width: 100%;
        }

        .favorite-task-item {
            padding: 13px 11px;
        }

        .favorite-task-description {
            display: none;
        }

    }

</style>


<script>

    let currentFavoriteFilter = 'all';

    function filterFavorites(filter, button) {

        currentFavoriteFilter = filter;

        document.querySelectorAll('.favorite-tab').forEach(tab => {
            tab.classList.remove('active');
        });

        if (button) {
            button.classList.add('active');
        }

        applyFavoriteFilters();
    }


    function applyFavoriteFilters() {

        const priority =
            document.getElementById('favoritePriorityFilter')?.value || '';

        document.querySelectorAll('.favorite-task-item').forEach(item => {

            const status = item.dataset.status || '';
            const itemPriority = item.dataset.priority || '';

            let visible = true;

            if (currentFavoriteFilter === 'active') {
                visible = status !== 'completed';
            }

            if (currentFavoriteFilter === 'completed') {
                visible = status === 'completed';
            }

            if (priority && itemPriority !== priority) {
                visible = false;
            }

            item.style.display = visible ? '' : 'none';
        });
    }


    function toggleFavorite(button) {

        const item = button.closest('.favorite-task-item');

        if (!item) {
            return;
        }

        /*
         * Layout-only sementara.
         *
         * Nanti fungsi ini akan diarahkan ke endpoint
         * favorite/unfavorite task.
         */

        item.remove();

        updateFavoriteEmptyState();
    }


    function updateFavoriteEmptyState() {

        const visibleItems =
            [...document.querySelectorAll('.favorite-task-item')]
            .filter(item => item.style.display !== 'none');

        if (visibleItems.length === 0) {

            const list = document.getElementById('favoritesList');

            if (!list) {
                return;
            }

            list.innerHTML = `
                <div class="favorites-empty">
                    <div class="favorites-empty-icon">?</div>
                    <h3>Tidak ada Favorite</h3>
                    <p>
                        Belum ada task yang sesuai dengan filter ini.
                    </p>
                    <a href="{{ route('tasks.index') }}" class="btn-primary">
                        Lihat Tasks
                    </a>
                </div>
            `;
        }
    }


    function openTaskFromFavorite(taskId) {

        if (!taskId) {
            return;
        }

        window.location.href = `/tasks/${taskId}`;
    }


    function refreshFavorites() {

        window.location.reload();

    }

</script>

@endsection