<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Task Management')</title>
    <link rel="stylesheet" href="{{ asset('css/taskflow.css') }}">
</head>

<body>
<div class="app-shell">

@php
    /*
    |--------------------------------------------------------------------------
    | SIDEBAR DATA
    |--------------------------------------------------------------------------
    | Space tetap dinamis dari database.
    | Hierarchy: Workspace → Space → List.
    | Task tidak pernah ditampilkan di sidebar.
    */

    $sidebarSpaces = \App\Models\Space::query()
        ->where('workspace_id', 1)
        ->where('is_active', true)
        ->with([
            'taskLists' => function ($query) {
                $query
                    ->where('is_active', true)
                    ->orderBy('position')
                    ->orderBy('name');
            }
        ])
        ->orderBy('id')
        ->get();

    $lists = $lists ?? collect();

    $listsBySpace = isset($listsBySpace)
        ? $listsBySpace
        : ($lists->count()
            ? $lists->groupBy('space_id')
            : $sidebarSpaces->mapWithKeys(
                fn ($space) => [$space->id => $space->taskLists]
            ));

    /*
    |--------------------------------------------------------------------------
    | CURRENT SPACE
    |--------------------------------------------------------------------------
    */

    $selectedSpaceId = request()->integer('space_id');

    $currentSpace = $selectedSpaceId
        ? $sidebarSpaces->firstWhere('id', $selectedSpaceId)
        : null;

    if (
        request()->routeIs('tasks.list-page') &&
        isset($space) &&
        $space
    ) {
        $currentSpace = $sidebarSpaces->firstWhere(
            'id',
            $space->id
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CURRENT LIST
    |--------------------------------------------------------------------------
    */

    $selectedListId = request()->integer('list_id');

    $allSidebarLists = $listsBySpace
        ->flatten()
        ->unique('id');

    $currentList = $selectedListId
        ? $allSidebarLists->firstWhere('id', $selectedListId)
        : null;

    if (
        request()->routeIs('tasks.list-page') &&
        isset($taskList) &&
        $taskList
    ) {
        $currentList = $allSidebarLists->firstWhere(
            'id',
            $taskList->id
        );
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDATE SPACE ↔ LIST
    |--------------------------------------------------------------------------
    */

    if (
        $currentList &&
        $currentSpace &&
        (int) $currentList->space_id !== (int) $currentSpace->id
    ) {
        $currentList = null;
    }

    /*
    |--------------------------------------------------------------------------
    | TASK VIEW PARAMS
    |--------------------------------------------------------------------------
    */

    $taskViewParams = [];

    if ($currentSpace) {
        $taskViewParams['space_id'] = $currentSpace->id;
    }

    if ($currentList) {
        $taskViewParams['list_id'] = $currentList->id;
    }

    /*
    |--------------------------------------------------------------------------
    | SYSTEM USERS
    |--------------------------------------------------------------------------
    */

    $systemUsers = \App\Models\User::query()
        ->orderBy('name')
        ->get();
@endphp


{{-- ================================================================
     SIDEBAR
     ================================================================ --}}
<aside class="sidebar" id="sidebar">

    <div class="brand">
        <span class="brand-mark">T</span>
        <span>TaskFlow</span>
    </div>

    <div class="sidebar-content">

        {{-- SEARCH --}}
        <div class="sidebar-search">
            <button
                type="button"
                class="sidebar-search-btn"
            >
                <span>⌕</span>
                <span>Search</span>
                <kbd>⌘ K</kbd>
            </button>
        </div>

        <nav class="nav">

            {{-- DASHBOARD --}}
            <a
                href="{{ route('dashboard') }}"
                class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}"
            >
                <span class="nav-icon">⌂</span>
                <span>Dashboard</span>
            </a>

            {{-- AGENDA --}}
            <a
                href="{{ route('agenda') }}"
                class="nav-item {{ request()->routeIs('agenda') ? 'active' : '' }}"
            >
                <span class="nav-icon">◷</span>
                <span>Agenda</span>
            </a>

            {{-- TASKS --}}
            <a
                href="{{ route('tasks.index') }}"
                class="nav-item {{
                    request()->routeIs(
                        'tasks.index',
                        'tasks.show',
                        'tasks.board',
                        'tasks.calendar',
                        'tasks.gantt'
                    )
                    && !$currentSpace
                    && !$currentList
                    ? 'active'
                    : ''
                }}"
            >
                <span class="nav-icon">☷</span>
                <span>Tasks</span>
            </a>

            {{-- INBOX --}}
            <a
                href="{{ route('inbox') }}"
                class="nav-item {{ request()->routeIs('inbox') ? 'active' : '' }}"
            >
                <span class="nav-icon">♧</span>
                <span>Inbox</span>

                <span
                    class="nav-badge"
                    id="inboxUnreadCount"
                >
                    0
                </span>
            </a>

            {{-- FAVORITES --}}
            <?php /*
            <a
                href="{{ route('favorites') }}"
                class="nav-item {{ request()->routeIs('favorites') ? 'active' : '' }}"
            >
                <span class="nav-icon">☆</span>
                <span>Favorites</span>
            </a>
            */ ?>

        </nav>


        {{-- ============================================================
             SPACE HEADER
             ============================================================ --}}
        <div class="sidebar-section-head">

            <a
                href="{{ route('spaces.index') }}"
                class="sidebar-section-title"
            >
                SPACE
            </a>

            <button
                type="button"
                id="globalCreateSpaceBtn"
                class="space-add-btn"
                aria-label="Tambah Space"
                title="Tambah Space"
            >+</button>

        </div>


        {{-- ============================================================
             SPACE TREE
             ============================================================ --}}
        <div class="workspace-tree">

            @forelse($sidebarSpaces as $space)

                @php
                    $spaceLists = $listsBySpace
                        ->get($space->id, collect())
                        ->where('is_active', true)
                        ->sortBy(function ($list) {
                            return [
                                $list->position ?? 0,
                                $list->name ?? ''
                            ];
                        });

                    $isCurrentSpace =
                        $currentSpace &&
                        (int) $currentSpace->id === (int) $space->id;
                @endphp


                <div class="tree-space">

                    {{-- SPACE ROW --}}
                    <div
                        class="tree-row tree-space-row {{ $isCurrentSpace ? 'active' : '' }}"
                    >

                        {{-- SPACE TOGGLE --}}
                        @if($spaceLists->count() > 0)

                            <button
                                type="button"
                                class="tree-toggle {{ $isCurrentSpace ? 'expanded' : '' }}"
                                onclick="
                                    event.stopPropagation();

                                    const parent = this.closest('.tree-space');
                                    const children = parent.querySelector('.tree-children');

                                    if (children) {
                                        children.classList.toggle('expanded');
                                    }

                                    this.classList.toggle('expanded');
                                "
                                aria-label="Toggle {{ $space->name }}"
                            >›</button>

                        @else

                            <span class="tree-toggle"></span>

                        @endif


                        {{-- SPACE LINK --}}
                        <a
                            href="{{ route('tasks.index', [
                                'space_id' => $space->id
                            ]) }}"
                            class="tree-link tree-space-link {{ $isCurrentSpace ? 'active' : '' }}"
                        >
                            <span class="tree-name">
                                {{ $space->name }}
                            </span>
                        </a>


                        {{-- ADD BUTTON --}}
                        <button
                            type="button"
                            class="space-tree-add"
                            onclick="openSpaceActionMenu(
                                event,
                                {{ $space->id }},
                                @js($space->name),
                                @js($space->slug)
                            )"
                            aria-label="Tambah pada {{ $space->name }}"
                            title="Tambah"
                        >+</button>

                    </div>


                    {{-- =================================================
                         LIST CHILDREN
                         ================================================= --}}
                    @if($spaceLists->count() > 0)

                        <div
                            class="tree-children {{ $isCurrentSpace ? 'expanded' : '' }}"
                        >

                            @foreach($spaceLists as $list)

                                @php
                                    $isCurrentList =
                                        $currentList &&
                                        (int) $currentList->id === (int) $list->id;
                                @endphp


                                {{-- LIST ROW --}}
                                <div
                                    class="tree-row tree-list-row {{ $isCurrentList ? 'active' : '' }}"
                                >

                                    <span class="tree-toggle"></span>


                                    {{-- LIST LINK --}}
                                    <a
                                        href="{{ route('tasks.list-page', [
                                            'space' => $space,
                                            'taskList' => $list->slug
                                        ]) }}"
                                        class="tree-link tree-list-link {{ $isCurrentList ? 'active' : '' }}"
                                    >
                                        <span class="tree-name">
                                            {{ $list->name }}
                                        </span>
                                    </a>


                                    {{-- LIST ACTION --}}
                                    <button
                                        type="button"
                                        class="space-tree-add list-tree-action"
                                        onclick="openListActionMenu(
                                            event,
                                            {{ $list->id }},
                                            @js($list->name),
                                            @js($list->slug),
                                            {{ $space->id }},
                                            @js($space->slug),
                                            @js($space->name),
                                            @js($list->description)
                                        )"
                                        aria-label="Aksi {{ $list->name }}"
                                        title="Aksi List"
                                    >⋮</button>

                                </div>

                            @endforeach

                        </div>

                    @endif

                </div>

            @empty

                <div class="tree-row">

                    <span class="tree-link">

                        <span class="tree-name">
                            Belum ada Space.
                        </span>

                    </span>

                </div>

            @endforelse

        </div>

    </div>


    {{-- USER --}}
    <div class="sidebar-bottom">

        {{-- LOGOUT --}}
        <form
            method="POST"
            action="{{ route('logout') }}"
        >
            @csrf

            <button type="submit">
                ⇥
                <span>Logout</span>
            </button>

        </form>

    </div>

</aside>



{{-- ================================================================
     MAIN
     ================================================================ --}}
<main class="main">

    <header class="topbar">

        {{-- MOBILE SIDEBAR --}}
        <button
            type="button"
            class="icon-btn mobile-only"
            onclick="toggleSidebar()"
        >☰</button>


        {{-- BREADCRUMB --}}
        <div class="breadcrumb">

            @if(request()->routeIs('dashboard'))

                <strong>Dashboard</strong>

            @elseif(request()->routeIs('agenda'))

                <strong>Agenda</strong>

            @elseif(request()->routeIs('inbox'))

                <strong>Inbox</strong>

            @elseif(request()->routeIs('favorites'))

                <strong>Favorites</strong>

            @elseif(request()->routeIs('settings'))

                <strong>Settings</strong>

            @elseif(request()->routeIs('members.*'))

                <strong>Anggota</strong>

            @elseif($currentList)

                <a href="{{ route('tasks.index') }}">
                    Tasks
                </a>

                <span>›</span>

                <a
                    href="{{ route('tasks.index', [
                        'space_id' => $currentSpace->id
                    ]) }}"
                >
                    {{ $currentSpace->name }}
                </a>

                <span>›</span>

                <strong>
                    {{ $currentList->name }}
                </strong>

            @elseif($currentSpace)

                <a href="{{ route('tasks.index') }}">
                    Tasks
                </a>

                <span>›</span>

                <strong>
                    {{ $currentSpace->name }}
                </strong>

            @else

                <strong>Tasks</strong>

            @endif

        </div>


        {{-- TOP ACTIONS --}}
        <div class="top-actions">

            <button
                type="button"
                class="icon-btn"
            >⌕</button>


            {{-- ACTIVITY --}}
            <a
                href="{{ route('manage.activity') }}"
                class="icon-btn {{ request()->routeIs('manage.activity') ? 'active' : '' }}"
                title="Activity"
                aria-label="Activity"
            >◷</a>


            {{-- USER MENU --}}
            <div class="user-menu">

                <button
                    type="button"
                    class="avatar"
                    title="User menu"
                    aria-label="User menu"
                    onclick="toggleUserMenu(event)"
                >
                    {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 1)) }}
                </button>


                <div
                    id="userMenu"
                    class="user-menu-dropdown"
                    style="display:none;"
                    onclick="event.stopPropagation()"
                >

                    {{-- USER INFO --}}
                    <div class="user-menu-info">

                        <strong>
                            {{ auth()->user()?->name ?? 'User' }}
                        </strong>

                        <small>
                            {{ auth()->user()?->email ?? '' }}
                        </small>

                    </div>


                    <div class="user-menu-divider"></div>


                    {{-- PROFILE --}}
                    <a href="#">
                        Profile
                    </a>


                    {{-- MEMBERS --}}
                    <a href="{{ route('members.index') }}">
                        Anggota
                    </a>


                    {{-- SETTINGS --}}
                    <a href="{{ route('settings') }}">
                        Settings
                    </a>


                    <div class="user-menu-divider"></div>


                    {{-- LOGOUT --}}
                    <form
                        method="POST"
                        action="{{ route('logout') }}"
                    >
                        @csrf

                        <button type="submit">
                            ⇥
                            <span>Logout</span>
                        </button>

                    </form>

                </div>

            </div>

        </div>

    </header>


    {{-- CONTENT --}}
    <div class="content">
		 @if(session('error'))
        <div class="alert alert-error">
            {{ session('error') }}
        </div>
    @endif
        @yield('content')

    </div>

</main>

</div>



{{-- ================================================================
     SPACE ACTION POPUP
     ================================================================ --}}
<div
    id="spaceActionPopup"
    class="space-action-popup"
    style="display:none;"
    onclick="event.stopPropagation()"
>

    <div class="space-action-popup-head">

        <div>

            <span class="eyebrow">
                SPACE
            </span>

            <strong id="spaceActionTitle">
                Space
            </strong>

        </div>


        <button
            type="button"
            class="space-action-close"
            onclick="closeSpaceActionMenu()"
            aria-label="Tutup"
        >×</button>

    </div>


    <div class="space-action-menu">

        {{-- LIST --}}
        <button
            type="button"
            class="space-action-item"
            onclick="handleSpaceAction('list')"
        >

            <span class="space-action-icon">

                <svg
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >
                    <rect
                        x="4"
                        y="4"
                        width="16"
                        height="16"
                        rx="2"
                    ></rect>

                    <line
                        x1="8"
                        y1="9"
                        x2="16"
                        y2="9"
                    ></line>

                    <line
                        x1="8"
                        y1="12"
                        x2="16"
                        y2="12"
                    ></line>

                    <line
                        x1="8"
                        y1="15"
                        x2="13"
                        y2="15"
                    ></line>
                </svg>

            </span>


            <span class="space-action-text">

                <strong>
                    List
                </strong>

                <small>
                    Buat List baru
                </small>

            </span>

        </button>


        {{-- FOLDER --}}
        <button
            type="button"
            class="space-action-item"
            onclick="handleSpaceAction('folder')"
        >

            <span class="space-action-icon">

                <svg
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >
                    <path
                        d="M3 7.5A2.5 2.5 0 0 1 5.5 5H10l2 2h6.5A2.5 2.5 0 0 1 21 9.5v7A2.5 2.5 0 0 1 18.5 19h-13A2.5 2.5 0 0 1 3 16.5z"
                    ></path>
                </svg>

            </span>


            <span class="space-action-text">

                <strong>
                    Folder
                </strong>

                <small>
                    Buat Folder baru
                </small>

            </span>

        </button>


        {{-- IMPORT --}}
        <button
            type="button"
            class="space-action-item"
            onclick="handleSpaceAction('import')"
        >

            <span class="space-action-icon">

                <svg
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >
                    <path d="M12 4v11"></path>
                    <path d="m8 11 4 4 4-4"></path>
                    <path d="M5 19h14"></path>
                </svg>

            </span>


            <span class="space-action-text">

                <strong>
                    Import
                </strong>

                <small>
                    Import data ke Space
                </small>

            </span>

        </button>


        {{-- EXPORT --}}
        <button
            type="button"
            class="space-action-item"
            onclick="handleSpaceAction('export')"
        >

            <span class="space-action-icon">

                <svg
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >
                    <path d="M12 15V4"></path>
                    <path d="m8 8 4-4 4 4"></path>
                    <path d="M5 20h14"></path>
                </svg>

            </span>


            <span class="space-action-text">

                <strong>
                    Export
                </strong>

                <small>
                    Export data dari Space
                </small>

            </span>

        </button>

    </div>

</div>



{{-- ================================================================
     LIST ACTION POPUP
     ================================================================ --}}
<div
    id="listActionPopup"
    class="space-action-popup"
    style="display:none;"
    onclick="event.stopPropagation()"
>

    <div class="space-action-popup-head">

        <div>

            <span class="eyebrow">
                LIST
            </span>

            <strong id="listActionTitle">
                List
            </strong>

        </div>


        <button
            type="button"
            class="space-action-close"
            onclick="closeListActionMenu()"
            aria-label="Tutup"
        >×</button>

    </div>


    <div class="space-action-menu">

        {{-- EDIT --}}
        <button
            type="button"
            class="space-action-item"
            onclick="handleListAction('edit')"
        >

            <span class="space-action-icon">

                <svg
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >
                    <path d="M4 20h4l10.5-10.5a2.12 2.12 0 0 0-3-3L5 17v3z"></path>
                    <path d="m14.5 7.5 2 2"></path>
                </svg>

            </span>


            <span class="space-action-text">

                <strong>
                    Edit List
                </strong>

                <small>
                    Ubah nama dan informasi List
                </small>

            </span>

        </button>


        {{-- DELETE --}}
        <button
            type="button"
            class="space-action-item"
            onclick="handleListAction('delete')"
        >

            <span class="space-action-icon">

                <svg
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >
                    <path d="M4 7h16"></path>
                    <path d="M10 11v6"></path>
                    <path d="M14 11v6"></path>
                    <path d="M6 7l1 13h10l1-13"></path>
                    <path d="M9 7V4h6v3"></path>
                </svg>

            </span>


            <span class="space-action-text">

                <strong>
                    Delete List
                </strong>

                <small>
                    Hapus List dari Space
                </small>

            </span>

        </button>

    </div>

</div>



{{-- ================================================================
     EDIT LIST MODAL
     ================================================================ --}}
<div
    id="editListModal"
    class="modal-backdrop hidden"
    onclick="closeEditListModal(event)"
>

    <div
        class="task-modal"
        onclick="event.stopPropagation()"
        style="max-width:520px;"
    >

        <div class="modal-head">

            <div>

                <span class="eyebrow">
                    LIST
                </span>

                <h2>
                    Edit List
                </h2>

                <div class="text-muted">
                    Ubah informasi List.
                </div>

            </div>


            <button
                type="button"
                class="icon-btn"
                onclick="closeEditListModal()"
                aria-label="Tutup"
            >×</button>

        </div>


        <form
            id="editListForm"
            method="POST"
        >

            @csrf
            @method('PUT')


            <div style="display:grid;gap:16px;">

                {{-- NAMA --}}
                <div>

                    <label for="editListName">
                        Nama List
                    </label>

                    <input
                        type="text"
                        id="editListName"
                        name="name"
                        required
                        maxlength="255"
                        placeholder="Nama List"
                        autocomplete="off"
                    >

                </div>


                {{-- DESKRIPSI --}}
                <div>

                    <label for="editListDescription">
                        Deskripsi
                    </label>

                    <textarea
                        id="editListDescription"
                        name="description"
                        rows="4"
                        placeholder="Deskripsi List (opsional)"
                    ></textarea>

                </div>


                {{-- SPACE --}}
                <div>

                    <label for="editListSpaceName">
                        Space
                    </label>

                    <input
                        type="text"
                        id="editListSpaceName"
                        value=""
                        readonly
                        tabindex="-1"
                    >

                </div>

            </div>


            <div class="modal-foot">

                <div
                    style="margin-left:auto;display:flex;gap:10px;"
                >

                    <button
                        type="button"
                        class="btn ghost"
                        onclick="closeEditListModal()"
                    >
                        Batal
                    </button>


                    <button
                        type="submit"
                        class="btn primary"
                    >
                        Simpan
                    </button>

                </div>

            </div>

        </form>

    </div>

</div>



{{-- ================================================================
     CREATE LIST MODAL
     ================================================================ --}}
<div
    id="createListModal"
    class="modal-backdrop hidden"
    onclick="closeCreateListModal(event)"
>

    <div
        class="task-modal"
        onclick="event.stopPropagation()"
        style="max-width:520px;"
    >

        <div class="modal-head">

            <div>

                <span class="eyebrow">
                    LIST
                </span>

                <h2>
                    Create List
                </h2>

            </div>


            <button
                type="button"
                class="icon-btn"
                onclick="closeCreateListModal()"
                aria-label="Tutup"
            >×</button>

        </div>


        <form
            id="createListForm"
            method="POST"
            data-store-template="{{ url('/spaces/__SPACE_SLUG__/lists') }}"
        >

            @csrf

            <input
                type="hidden"
                name="space_id"
                id="createListSpaceId"
            >


            <div style="display:grid;gap:16px;">

                {{-- NAMA LIST --}}
                <div>

                    <label for="createListName">
                        Nama List
                    </label>

                    <input
                        type="text"
                        name="name"
                        id="createListName"
                        placeholder="Masukkan nama List..."
                        maxlength="255"
                        required
                        autocomplete="off"
                    >

                </div>


                {{-- LOKASI SPACE --}}
                <div>

                    <label for="createListSpaceName">
                        Lokasi Space
                    </label>

                    <input
                        type="text"
                        id="createListSpaceName"
                        value=""
                        readonly
                        tabindex="-1"
                    >

                </div>

            </div>


            <div class="modal-foot">

                <div
                    style="margin-left:auto;display:flex;gap:10px;"
                >

                    <button
                        type="button"
                        class="btn ghost"
                        onclick="closeCreateListModal()"
                    >
                        Batal
                    </button>


                    <button
                        type="submit"
                        class="btn primary"
                    >
                        Simpan
                    </button>

                </div>

            </div>

        </form>

    </div>

</div>



{{-- ================================================================
     TASK MODAL
     ================================================================ --}}
<div
    id="taskModal"
    class="modal-backdrop hidden"
    onclick="closeTaskModal(event)"
>

    <div
        class="task-modal"
        onclick="event.stopPropagation()"
    >

        <div class="modal-head">

            <div>

                <span class="eyebrow">
                    TASK
                </span>

                <h2 id="modalTitle">
                    Task
                </h2>

            </div>


            <button
                type="button"
                class="icon-btn"
                onclick="closeTaskModal()"
            >×</button>

        </div>


        <div class="modal-grid">

            <section>

                <div>

                    <label for="modalTaskTitle">
                        Task Title
                    </label>

                    <input
                        type="text"
                        id="modalTaskTitle"
                        placeholder="Task title..."
                    >

                </div>


                <div style="margin-top:15px;">

                    <label for="modalDescription">
                        Description
                    </label>

                    <textarea
                        id="modalDescription"
                        placeholder="Describe this task..."
                    ></textarea>

                </div>

            </section>


            <aside class="task-meta">

                <div>

                    <label for="modalStatus">
                        Status
                    </label>

                    <select id="modalStatus">

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

                </div>


                <div>

                    <label for="modalPriority">
                        Priority
                    </label>

                    <select id="modalPriority">

                        <option value="low">
                            Low
                        </option>

                        <option
                            value="normal"
                            selected
                        >
                            Normal
                        </option>

                        <option value="high">
                            High
                        </option>

                        <option value="urgent">
                            Urgent
                        </option>

                    </select>

                </div>


                <div>

                    <label for="modalStartDate">
                        Start date
                    </label>

                    <input
                        type="date"
                        id="modalStartDate"
                    >

                </div>


                <div>

                    <label for="modalDueDate">
                        Due date
                    </label>

                    <input
                        type="date"
                        id="modalDueDate"
                    >

                </div>

            </aside>

        </div>


        {{-- SUBTASKS --}}
        <div class="task-subtasks">

            <div class="subtask-head">

                <div>

                    <span class="eyebrow">
                        SUBTASKS
                    </span>

                    <strong>

                        Subtasks

                        <span id="subtaskCount">
                            0
                        </span>

                    </strong>

                </div>


                <button
                    type="button"
                    class="btn ghost"
                    onclick="showAddSubtaskForm()"
                >
                    ＋ Add Subtask
                </button>

            </div>


            {{-- ADD SUBTASK FORM --}}
            <div
                id="addSubtaskForm"
                class="subtask-form hidden"
            >

                <div>

                    <label for="subtaskTitle">
                        Subtask Title
                    </label>

                    <input
                        type="text"
                        id="subtaskTitle"
                        placeholder="Subtask title..."
                    >

                </div>


                <div class="subtask-form-grid">

                    <div>

                        <label for="subtaskStatus">
                            Status
                        </label>

                        <select id="subtaskStatus">

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

                    </div>


                    <div>

                        <label for="subtaskPriority">
                            Priority
                        </label>

                        <select id="subtaskPriority">

                            <option value="low">
                                Low
                            </option>

                            <option
                                value="normal"
                                selected
                            >
                                Normal
                            </option>

                            <option value="high">
                                High
                            </option>

                            <option value="urgent">
                                Urgent
                            </option>

                        </select>

                    </div>


                    <div>

                        <label for="subtaskDueDate">
                            Due date
                        </label>

                        <input
                            type="date"
                            id="subtaskDueDate"
                        >

                    </div>

                </div>


                <div class="subtask-form-actions">

                    <button
                        type="button"
                        class="btn ghost"
                        onclick="hideAddSubtaskForm()"
                    >
                        Cancel
                    </button>


                    <button
                        type="button"
                        class="btn primary"
                        onclick="saveSubtask()"
                    >
                        Save Subtask
                    </button>

                </div>

            </div>


            {{-- SUBTASK LIST --}}
            <div
                id="subtaskList"
                class="subtask-list"
            >

                <div class="subtask-empty">
                    Tidak ada subtask.
                </div>

            </div>

        </div>


        {{-- TASK MODAL FOOTER --}}
        <div class="modal-foot">

            <button
                type="button"
                class="btn ghost"
                onclick="deleteTask()"
            >
                Delete
            </button>


            <div
                style="margin-left:auto;display:flex;gap:10px;"
            >

                <button
                    type="button"
                    class="btn ghost"
                    onclick="closeTaskModal()"
                >
                    Cancel
                </button>


                <button
                    type="button"
                    class="btn primary"
                    onclick="saveTask()"
                >
                    Save changes
                </button>

            </div>

        </div>

    </div>

</div>



{{-- ================================================================
     ASSIGNEE POPUP
     ================================================================ --}}
<div
    id="assigneePopup"
    class="assignee-popup"
    style="display:none;"
    onclick="event.stopPropagation()"
>

    <div class="assignee-popup-head">

        <strong>
            Assign PIC
        </strong>

        <button
            type="button"
            onclick="closeAssigneePopup()"
        >×</button>

    </div>


    <div class="assignee-popup-search">

        <input
            type="text"
            id="assigneeSearch"
            placeholder="Cari user..."
            autocomplete="off"
            oninput="filterAssignees()"
        >

    </div>


    <div
        id="assigneeList"
        class="assignee-list"
    ></div>


    <button
        type="button"
        class="assignee-unassign"
        onclick="assignTask(null)"
    >
        Unassign
    </button>

</div>



{{-- ================================================================
     CREATE SPACE MODAL - GLOBAL
     ================================================================ --}}
<div
    id="createSpaceModal"
    class="modal-backdrop hidden"
    onclick="closeCreateSpaceModal(event)"
>

    <div
        class="task-modal"
        onclick="event.stopPropagation()"
        style="max-width:480px;"
    >

        <div class="modal-head">

            <div>

                <span class="eyebrow">
                    SPACE
                </span>

                <h2>
                    Tambah Space
                </h2>

                <div class="text-muted">
                    Buat Space baru.
                </div>

            </div>


            <button
                type="button"
                class="icon-btn"
                onclick="closeCreateSpaceModal()"
                aria-label="Tutup"
            >×</button>

        </div>


        <form
            id="createSpaceForm"
            method="POST"
            action="{{ route('spaces.store') }}"
        >

            @csrf

            <div style="display:grid;gap:16px;">

                <div>

                    <label for="create_space_name">
                        Nama Space
                    </label>

                    <input
                        type="text"
                        id="create_space_name"
                        name="name"
                        required
                        maxlength="255"
                        placeholder="Masukkan nama Space"
                        autocomplete="off"
                    >

                </div>


                <div>

                    <label for="create_space_description">
                        Deskripsi
                    </label>

                    <textarea
                        id="create_space_description"
                        name="description"
                        rows="4"
                        placeholder="Deskripsi Space (opsional)"
                    ></textarea>

                </div>

            </div>


            <div class="modal-foot">

                <div
                    style="margin-left:auto;display:flex;gap:10px;"
                >

                    <button
                        type="button"
                        class="btn ghost"
                        onclick="closeCreateSpaceModal()"
                    >
                        Batal
                    </button>


                    <button
                        type="submit"
                        class="btn primary"
                    >
                        Simpan
                    </button>

                </div>

            </div>

        </form>

    </div>

</div>



{{-- ================================================================
     LIST / SPACE JAVASCRIPT
     ================================================================ --}}
<script>

let currentListActionId = null;
let currentListActionSlug = null;
let currentListActionSpaceId = null;
let currentListActionSpaceSlug = null;
let currentListActionSpaceName = null;
let currentListActionName = null;
let currentListActionDescription = null;


/*
|--------------------------------------------------------------------------
| OPEN LIST ACTION
|--------------------------------------------------------------------------
*/

function openListActionMenu(
    event,
    listId,
    listName = null,
    listSlug = null,
    spaceId = null,
    spaceSlug = null,
    spaceName = null,
    listDescription = null
) {

    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }


    const popup =
        document.getElementById('listActionPopup');


    if (!popup) {

        console.error(
            'listActionPopup tidak ditemukan.'
        );

        return;
    }


    currentListActionId = listId;
    currentListActionSlug = listSlug;
    currentListActionSpaceId = spaceId;
    currentListActionSpaceSlug = spaceSlug;
    currentListActionSpaceName = spaceName;
    currentListActionName = listName;
    currentListActionDescription = listDescription;


    const title =
        document.getElementById('listActionTitle');


    if (title) {

        title.textContent =
            listName || 'List';

    }


    popup.style.display = 'block';


    /*
    |--------------------------------------------------------------------------
    | POSISI POPUP
    |--------------------------------------------------------------------------
    */

    const button =
        event?.currentTarget;


    if (button) {

        const rect =
            button.getBoundingClientRect();


        popup.style.position = 'fixed';

        popup.style.top =
            `${rect.bottom + 6}px`;

        popup.style.left =
            `${Math.max(10, rect.right - 240)}px`;

    }

}


/*
|--------------------------------------------------------------------------
| CLOSE LIST ACTION
|--------------------------------------------------------------------------
*/

function closeListActionMenu() {

    const popup =
        document.getElementById('listActionPopup');


    if (popup) {

        popup.style.display = 'none';

    }


    currentListActionId = null;
    currentListActionSlug = null;
    currentListActionSpaceId = null;
    currentListActionSpaceSlug = null;
    currentListActionSpaceName = null;
    currentListActionName = null;
    currentListActionDescription = null;

}


/*
|--------------------------------------------------------------------------
| HANDLE LIST ACTION
|--------------------------------------------------------------------------
*/

function handleListAction(action) {

    if (!currentListActionId) {

        console.error(
            'List belum dipilih.'
        );

        return;
    }


    if (action === 'edit') {

        openEditListModal();

        return;
    }


    if (action === 'delete') {

        deleteCurrentList();

        return;
    }

}


/*
|--------------------------------------------------------------------------
| OPEN EDIT LIST MODAL
|--------------------------------------------------------------------------
*/

function openEditListModal() {

    const modal =
        document.getElementById('editListModal');

    const form =
        document.getElementById('editListForm');

    const nameInput =
        document.getElementById('editListName');

    const descriptionInput =
        document.getElementById('editListDescription');

    const spaceInput =
        document.getElementById('editListSpaceName');


    if (!modal || !form) {

        console.error(
            'Edit List modal tidak ditemukan.'
        );

        return;
    }


    if (!currentListActionSpaceSlug ||
        !currentListActionSlug) {

        console.error(
            'Space/List slug tidak tersedia.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | FORM ACTION
    |--------------------------------------------------------------------------
    */

    form.action =
        `/spaces/${encodeURIComponent(
            currentListActionSpaceSlug
        )}/lists/${encodeURIComponent(
            currentListActionSlug
        )}`;


    /*
    |--------------------------------------------------------------------------
    | ISI FORM
    |--------------------------------------------------------------------------
    */

    if (nameInput) {

        nameInput.value =
            currentListActionName || '';

    }


    if (descriptionInput) {

        descriptionInput.value =
            currentListActionDescription || '';

    }


    if (spaceInput) {

        spaceInput.value =
            currentListActionSpaceName || '';

    }


    closeListActionMenu();


    modal.classList.remove('hidden');


    if (nameInput) {

        setTimeout(function () {

            nameInput.focus();

            nameInput.select();

        }, 100);

    }

}


/*
|--------------------------------------------------------------------------
| CLOSE EDIT LIST MODAL
|--------------------------------------------------------------------------
*/

function closeEditListModal(event) {

    const modal =
        document.getElementById('editListModal');


    if (!modal) {

        return;

    }


    if (
        event &&
        event.target !== event.currentTarget
    ) {

        return;

    }


    modal.classList.add('hidden');

}


/*
|--------------------------------------------------------------------------
| DELETE LIST
|--------------------------------------------------------------------------
*/

function deleteCurrentList() {

    if (
        !currentListActionId ||
        !currentListActionSpaceSlug ||
        !currentListActionSlug
    ) {

        console.error(
            'Data List tidak lengkap.'
        );

        return;
    }


    const listName =
        currentListActionName || 'List';


    const confirmed =
        confirm(
            `Hapus List "${listName}"?\n\n` +
            `List yang masih memiliki Task tidak dapat dihapus.`
        );


    if (!confirmed) {

        return;

    }


    const form =
        document.createElement('form');


    form.method = 'POST';


    form.action =
        `/spaces/${encodeURIComponent(
            currentListActionSpaceSlug
        )}/lists/${encodeURIComponent(
            currentListActionSlug
        )}`;


    const csrf =
        document.querySelector(
            'meta[name="csrf-token"]'
        );


    if (!csrf) {

        alert(
            'CSRF token tidak ditemukan.'
        );

        return;
    }


    const csrfInput =
        document.createElement('input');


    csrfInput.type = 'hidden';

    csrfInput.name = '_token';

    csrfInput.value =
        csrf.getAttribute('content');


    form.appendChild(csrfInput);


    const methodInput =
        document.createElement('input');


    methodInput.type = 'hidden';

    methodInput.name = '_method';

    methodInput.value = 'DELETE';


    form.appendChild(methodInput);


    document.body.appendChild(form);


    form.submit();

}


/*
|--------------------------------------------------------------------------
| CLICK OUTSIDE LIST POPUP
|--------------------------------------------------------------------------
*/

document.addEventListener('click', function (event) {

    const popup =
        document.getElementById('listActionPopup');


    if (!popup) {

        return;

    }


    if (
        popup.style.display === 'block' &&
        !popup.contains(event.target) &&
        !event.target.closest('.list-tree-action')
    ) {

        closeListActionMenu();

    }

});


/*
|--------------------------------------------------------------------------
| ESCAPE
|--------------------------------------------------------------------------
*/

document.addEventListener('keydown', function (event) {

    if (event.key !== 'Escape') {

        return;

    }


    const editModal =
        document.getElementById('editListModal');


    if (
        editModal &&
        !editModal.classList.contains('hidden')
    ) {

        closeEditListModal();

        return;

    }


    closeListActionMenu();

});

</script>



{{-- ================================================================
     CREATE SPACE JAVASCRIPT
     ================================================================ --}}
<script>

document.addEventListener('DOMContentLoaded', function () {

    const createSpaceBtn =
        document.getElementById('globalCreateSpaceBtn');

    const createSpaceModal =
        document.getElementById('createSpaceModal');

    const createSpaceForm =
        document.getElementById('createSpaceForm');

    const createSpaceName =
        document.getElementById('create_space_name');


    if (createSpaceBtn && createSpaceModal) {

        createSpaceBtn.addEventListener(
            'click',
            function (event) {

                event.preventDefault();
                event.stopPropagation();

                createSpaceModal.classList.remove(
                    'hidden'
                );


                if (createSpaceName) {

                    createSpaceName.value = '';


                    setTimeout(function () {

                        createSpaceName.focus();

                    }, 100);

                }

            }
        );

    }


    window.closeCreateSpaceModal =
        function (event) {

            if (!createSpaceModal) {

                return;

            }


            if (
                event &&
                event.target !== event.currentTarget
            ) {

                return;

            }


            createSpaceModal.classList.add(
                'hidden'
            );


            if (createSpaceForm) {

                createSpaceForm.reset();

            }

        };

});

</script>



{{-- ================================================================
     JAVASCRIPT CONTEXT
     ================================================================ --}}
<script>

window.taskContext = {

    workspace_id: 1,

    space_id:
        @json($currentSpace?->id),

    list_id:
        @json($currentList?->id)

};


window.systemUsers =
    @json($systemUsers);

</script>


<script>

window.taskContext = {

    workspace_id: 1,

    space_id:
        @json($currentSpace?->id),

    list_id:
        @json($currentList?->id)

};


window.systemUsers =
    @json($systemUsers);


/*
|--------------------------------------------------------------------------
| USER MENU
|--------------------------------------------------------------------------
*/

function toggleUserMenu(event) {

    event.stopPropagation();


    const menu =
        document.getElementById('userMenu');


    if (!menu) {

        return;

    }


    const isVisible =
        menu.style.display === 'block';


    menu.style.display =
        isVisible
            ? 'none'
            : 'block';

}


/*
|--------------------------------------------------------------------------
| CLOSE USER MENU
|--------------------------------------------------------------------------
*/

document.addEventListener(
    'click',
    function (event) {

        const userMenu =
            document.querySelector('.user-menu');

        const menu =
            document.getElementById('userMenu');


        if (!userMenu || !menu) {

            return;

        }


        if (!userMenu.contains(event.target)) {

            menu.style.display = 'none';

        }

    }
);

</script>


<script src="{{ asset('js/task-manager.js') }}"></script>

</body>
</html>