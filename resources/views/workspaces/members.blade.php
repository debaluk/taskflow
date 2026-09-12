@extends('layouts.task-manager')

@section('title', $workspace->name . ' - Members')

@section('content')

<div class="page-header">
    <div>
        <h1>{{ $workspace->name }}</h1>
        <p>Workspace Members</p>
    </div>
</div>

<div class="workspace-nav">

    <a href="{{ route('workspaces.show', $workspace) }}">
        Overview
    </a>

    <a
    href="{{ route('workspace.members', $workspace) }}"
    class="{{ request()->routeIs('workspace.members') && request()->route('workspace')->id === $workspace->id ? 'active' : '' }}"
>
    Members
</a>

</div>

@if (session('error'))
    <div class="alert alert-error">
        {{ session('error') }}
    </div>
@endif

@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<div class="panel workspace-members-panel">

    <div class="panel-head">

        <strong>Members</strong>

        <button
            type="button"
            class="btn btn-primary"
            onclick="openWorkspaceMemberModal()"
        >
            ＋ Tambah Member
        </button>

    </div>

    <div class="workspace-member-table">

        <div class="workspace-member-row header">
            <span>User</span>
            <span>Email</span>
            <span>Role</span>
            <span>Status</span>
        </div>

        @forelse($members as $member)

            <div class="workspace-member-row">

                <span class="workspace-member-user">

                    <span class="avatar sm">
                        {{ strtoupper(substr($member->user?->name ?? 'U', 0, 1)) }}
                    </span>

                    <strong>
                        {{ $member->user?->name ?? '-' }}
                    </strong>

                </span>

                <span>
                    {{ $member->user?->email ?? '-' }}
                </span>

                <span>
                    <b class="member-role">
                        {{ ucfirst($member->role) }}
                    </b>
                </span>

                <span>

                    <form
                        method="POST"
                        action="{{ route('workspace.members.toggle', [$workspace, $member]) }}"
                        style="display:inline;"
                    >

                        @csrf
                        @method('PATCH')

                        <button
                            type="submit"
                            class="member-status {{ $member->is_active ? 'active' : 'inactive' }}"
                        >
                            {{ $member->is_active ? 'Aktif' : 'Nonaktif' }}
                        </button>

                    </form>

                </span>

            </div>

        @empty

            <div class="workspace-member-empty">
                Belum ada member dalam workspace ini.
            </div>

        @endforelse

    </div>

</div>


<!-- Modal Tambah Member -->

<div id="workspaceMemberModal" class="modal-overlay">

    <form
        method="POST"
        action="{{ route('workspace.members.store', $workspace) }}"
        class="modal"
    >

        @csrf

        <div class="modal-header">

            <div>

                <h3>Tambah Member</h3>

                <p>
                    Tambahkan user ke workspace ini
                </p>

            </div>

            <button
                type="button"
                class="modal-close"
                onclick="closeWorkspaceMemberModal()"
            >
                ×
            </button>

        </div>


        <div class="modal-body">

            <div class="form-group">

                <label>User</label>

                <select
                    id="workspaceMemberUser"
                    name="user_id"
                    class="form-control"
                    required
                >

                    <option value="">
                        Pilih User
                    </option>

                    @foreach($users as $user)

                        <option
                            value="{{ $user->id }}"
                            {{ old('user_id') == $user->id ? 'selected' : '' }}
                        >
                            {{ $user->name }} — {{ $user->email }}
                        </option>

                    @endforeach

                </select>

            </div>


            <div class="form-group">

                <label>Role</label>

                <select
                    id="workspaceMemberRole"
                    name="role"
                    class="form-control"
                    required
                >

                    <option
                        value="member"
                        {{ old('role', 'member') === 'member' ? 'selected' : '' }}
                    >
                        Member
                    </option>

                    <option
                        value="admin"
                        {{ old('role') === 'admin' ? 'selected' : '' }}
                    >
                        Admin
                    </option>

                </select>

            </div>

        </div>


        <div class="modal-footer">

            <button
                type="button"
                class="btn"
                onclick="closeWorkspaceMemberModal()"
            >
                Batal
            </button>

            <button
                type="submit"
                class="btn btn-primary"
            >
                Simpan
            </button>

        </div>

    </form>

</div>

@endsection
