@extends('layouts.task-manager')
@section('title', 'Anggota')
@section('content')

<div class="page-header">

    <div class="page-header-main">

        <div>

            <span class="eyebrow">
                TASK MANAGEMENT
            </span>

            <h1>
                Anggota
            </h1>

            <p class="page-subtitle">
                Kelola anggota yang dapat ditugaskan ke tugas.
            </p>

        </div>

    </div>

    <button
        type="button"
        onclick="openAddMemberModal()"
        class="btn"
    >
        + Add Member
    </button>

</div>


    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-error">
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif


    {{-- Members Table --}}
    <div class="members-table">

    <table class="data-table">

        <thead>
            <tr>
                <th>Member</th>
                <th>Email</th>
                <th>Added By</th>
                <th>ClickUp ID</th>
                <th class="text-right">Action</th>
            </tr>
        </thead>

        <tbody>

            @forelse($members as $member)

                <tr>

                    <td>
                        <div class="member-name">
                            {{ $member->user->name ?? '-' }}
                        </div>
                    </td>

                    <td>
                        <div class="member-email">
                            {{ $member->user->email ?? '-' }}
                        </div>
                    </td>

                    <td>
                        <div class="member-added-by">
                            {{ $member->inviter->name ?? '-' }}
                        </div>
                    </td>

                    <td>
                        @if($member->clickup_user_id)
                            <span class="clickup-id">
                                {{ $member->clickup_user_id }}
                            </span>
                        @else
                            <span class="muted-value">-</span>
                        @endif
                    </td>

                    <td class="text-right">

                        <form
                            action="{{ route('members.destroy', $member) }}"
                            method="POST"
                            class="member-action-form"
                            onsubmit="return confirm('Remove this member?')"
                        >

                            @csrf
                            @method('DELETE')

                            <button
                                type="submit"
                                class="btn danger ghost"
                            >
                                Remove
                            </button>

                        </form>

                    </td>

                </tr>

            @empty

                <tr>

                    <td colspan="5">

                        <div class="empty-state">
                            <strong>No members yet.</strong>
                            <span>Add a member to make them available for task assignment.</span>
                        </div>

                    </td>

                </tr>

            @endforelse

        </tbody>

    </table>

</div>

</div>


    {{-- Pagination --}}
    @if($members->hasPages())
        <div class="mt-4">
            {{ $members->links() }}
        </div>
    @endif

</div>


{{-- =========================================================
     ADD MEMBER MODAL
========================================================= --}}

<div
    id="addMemberModal"
    class="modal-backdrop hidden"
    onclick="closeAddMemberModal(event)"
>

    <div
        class="modal"
        onclick="event.stopPropagation()"
    >

        <div class="modal-header">

            <div>
                <h2>Add Member</h2>

                <p>
                    Tambahkan user sebagai member yang dapat ditugaskan ke task.
                </p>
            </div>

            <button
                type="button"
                class="modal-close"
                onclick="closeAddMemberModal()"
            >
                ×
            </button>

        </div>


        <form
            action="{{ route('members.store') }}"
            method="POST"
            id="addMemberForm"
        >

            @csrf

            <div class="modal-body">

                <div class="form-group">

                    <label>
                        User
                    </label>

                    <div class="member-search">

                        <input
                            type="text"
                            id="memberSearchInput"
                            placeholder="Cari nama atau email..."
                            autocomplete="off"
                        >

                        <div
                            id="memberSearchResults"
                            class="member-search-results hidden"
                        ></div>

                    </div>

                    <input
                        type="hidden"
                        name="user_id"
                        id="selectedMemberId"
                        required
                    >

                    <div
                        id="selectedMember"
                        class="selected-member hidden"
                    ></div>

                </div>

            </div>


            <div class="modal-footer">

                <button
                    type="button"
                    class="btn ghost"
                    onclick="closeAddMemberModal()"
                >
                    Cancel
                </button>

                <button
                    type="submit"
                    class="btn"
                    id="addMemberSubmit"
                    disabled
                >
                    Add Member
                </button>

            </div>

        </form>

    </div>

</div>


<script>

let memberSearchTimer = null;

function openAddMemberModal()
{
    const modal = document.getElementById('addMemberModal');

    if (!modal) {
        console.error('addMemberModal tidak ditemukan.');
        return;
    }

    modal.classList.remove('hidden');

    const input = document.getElementById('memberSearchInput');

    if (input) {
        input.focus();
    }
}

function closeAddMemberModal(event)
{
    if (
        event &&
        event.target &&
        event.target !== event.currentTarget
    ) {
        return;
    }

    const modal = document.getElementById('addMemberModal');

    if (modal) {
        modal.classList.add('hidden');
    }

    resetMemberSearch();
}

function resetMemberSearch()
{
    const input = document.getElementById('memberSearchInput');
    const selectedId = document.getElementById('selectedMemberId');
    const results = document.getElementById('memberSearchResults');
    const selected = document.getElementById('selectedMember');
    const submit = document.getElementById('addMemberSubmit');

    if (input) {
        input.value = '';
    }

    if (selectedId) {
        selectedId.value = '';
    }

    if (results) {
        results.innerHTML = '';
        results.classList.add('hidden');
    }

    if (selected) {
        selected.innerHTML = '';
        selected.classList.add('hidden');
    }

    if (submit) {
        submit.disabled = true;
    }
}

document.addEventListener('DOMContentLoaded', function ()
{
    const input = document.getElementById('memberSearchInput');

    if (!input) {
        return;
    }

    input.addEventListener('input', function ()
    {
        const search = this.value.trim();

        clearTimeout(memberSearchTimer);

        const results = document.getElementById('memberSearchResults');

        if (search.length < 2) {

            if (results) {
                results.innerHTML = '';
                results.classList.add('hidden');
            }

            return;
        }

        memberSearchTimer = setTimeout(function ()
        {
            searchMembers(search);
        }, 300);
    });
});

function searchMembers(search)
{
    const results = document.getElementById('memberSearchResults');

    if (!results) {
        return;
    }

    results.classList.remove('hidden');

    results.innerHTML =
        '<div class="member-search-empty">' +
            'Searching...' +
        '</div>';

    fetch(
        '{{ route('members.search-users') }}?q=' +
        encodeURIComponent(search),
        {
            headers: {
                'Accept': 'application/json'
            }
        }
    )
    .then(function (response)
    {
        if (!response.ok) {
            throw new Error('Search request failed');
        }

        return response.json();
    })
    .then(function (users)
    {
        if (!users.length) {

            results.innerHTML =
                '<div class="member-search-empty">' +
                    'User tidak ditemukan.' +
                '</div>';

            return;
        }

        results.innerHTML = '';

        users.forEach(function (user)
        {
            const item = document.createElement('div');

            item.className = 'member-search-result';

            const name = document.createElement('strong');

            name.textContent = user.name;

            const email = document.createElement('span');

            email.textContent = user.email;

            item.appendChild(name);
            item.appendChild(email);

            item.addEventListener('click', function ()
            {
                selectMember(
                    user.id,
                    user.name,
                    user.email
                );
            });

            results.appendChild(item);
        });
    })
    .catch(function (error)
    {
        console.error(error);

        results.innerHTML =
            '<div class="member-search-empty">' +
                'Gagal mencari user.' +
            '</div>';
    });
}

function selectMember(id, name, email)
{
    const selectedId = document.getElementById('selectedMemberId');
    const input = document.getElementById('memberSearchInput');
    const results = document.getElementById('memberSearchResults');
    const selected = document.getElementById('selectedMember');
    const submit = document.getElementById('addMemberSubmit');

    if (
        !selectedId ||
        !input ||
        !results ||
        !selected ||
        !submit
    ) {
        return;
    }

    selectedId.value = id;

    input.value = '';

    results.innerHTML = '';

    results.classList.add('hidden');

    selected.innerHTML = '';

    const info = document.createElement('div');

    const strong = document.createElement('strong');

    strong.textContent = name;

    const span = document.createElement('span');

    span.textContent = email;

    info.appendChild(strong);
    info.appendChild(span);

    const removeButton = document.createElement('button');

    removeButton.type = 'button';

    removeButton.className = 'btn ghost';

    removeButton.textContent = 'X';

    removeButton.addEventListener('click', function ()
    {
        resetMemberSearch();
    });

    selected.appendChild(info);

    selected.appendChild(removeButton);

    selected.classList.remove('hidden');

    submit.disabled = false;
}

</script>

@endsection