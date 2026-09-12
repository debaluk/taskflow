@extends('layouts.task-manager')

@section('title', 'Master Workspace')
@section('page', 'Workspace')

@section('content')

<div>

    {{-- HEADER --}}
<div style="
    display:flex;
    align-items:center;
    justify-content:space-between;
    margin-bottom:24px;
">

    <div>
        <h1 style="margin:0 0 5px; font-size:27px;">
            Master Workspace
        </h1>

        <p>
            Kelola workspace yang digunakan dalam Task Management.
        </p>
    </div>


    <div style="
        display:flex;
        align-items:center;
        gap:10px;
    ">

        {{-- FILTER --}}
        <label
            for="workspaceFilter"
            style="
                margin:0;
                font-size:11px;
                color:var(--muted);
            ">
            Filter:
        </label>

        <select
            id="workspaceFilter"
            onchange="filterWorkspaces()"
            style="
                width:auto;
                min-width:130px;
            ">

            <option value="all">Semua</option>
            <option value="active">Aktif</option>
            <option value="inactive">Nonaktif</option>

        </select>


        {{-- ADD WORKSPACE --}}
        <button
            type="button"
            class="btn primary"
            onclick="openWorkspaceModal()">
            + Workspace
        </button>

    </div>

</div>
    @if(session('success'))

        <div style="
            margin-bottom:20px;
            padding:12px 16px;
            border:1px solid #bbf7d0;
            border-radius:8px;
            background:#f0fdf4;
            color:#15803d;
            font-size:12px;
        ">
            {{ session('success') }}
        </div>

    @endif


    {{-- WORKSPACE TABLE --}}
    <div class="panel" style="overflow:hidden;">

        <table style="width:100%; border-collapse:collapse; font-size:12px;">

            <thead style="background:#f7f8fa; border-bottom:1px solid var(--line);">

                <tr>

                    <th style="padding:13px 17px; text-align:left;">
                        Workspace
                    </th>

                    <th style="padding:13px 17px; text-align:left;">
                        Slug
                    </th>

                    <th style="padding:13px 17px; text-align:left;">
                        Owner
                    </th>

                    <th style="padding:13px 17px; text-align:center;">
                        Status
                    </th>

                    <th style="padding:13px 17px; text-align:right;">
                        Aksi
                    </th>

                </tr>

            </thead>


            <tbody>

                @forelse($workspaces as $workspace)

                    <tr
    class="workspace-row"
    data-status="{{ $workspace->is_active ? 'active' : 'inactive' }}"
    style="border-bottom:1px solid #f0f1f3;">

                        <td style="padding:15px 17px;">

                            <div style="font-weight:700;">
                                {{ $workspace->name }}
                            </div>

                            @if($workspace->description)

                                <div style="
                                    margin-top:4px;
                                    color:var(--muted);
                                    font-size:10px;
                                ">
                                    {{ $workspace->description }}
                                </div>

                            @endif

                        </td>


                        <td style="
                            padding:15px 17px;
                            color:var(--muted);
                        ">
                            {{ $workspace->slug }}
                        </td>


                        <td style="padding:15px 17px;">
                            {{ $workspace->owner?->name ?? '-' }}
                        </td>


                        <td style="
                            padding:15px 17px;
                            text-align:center;
                        ">

                            @if($workspace->is_active)

                                <span style="
                                    display:inline-block;
                                    padding:4px 9px;
                                    border-radius:999px;
                                    background:#e8f7ef;
                                    color:#28754a;
                                    font-size:10px;
                                    font-weight:700;
                                ">
                                    Aktif
                                </span>

                            @else

                                <span style="
                                    display:inline-block;
                                    padding:4px 9px;
                                    border-radius:999px;
                                    background:#edf0f4;
                                    color:#65707b;
                                    font-size:10px;
                                    font-weight:700;
                                ">
                                    Nonaktif
                                </span>

                            @endif

                        </td>


                        <td style="
                            padding:15px 17px;
                            text-align:right;
                        ">

                            <div style="
                                display:flex;
                                justify-content:flex-end;
                                gap:6px;
                            ">

                                {{-- EDIT --}}
                                <button
                                    type="button"
                                    class="btn ghost"
                                    onclick="openEditWorkspaceModal(
                                        {{ $workspace->id }},
                                        @js($workspace->name),
                                        @js($workspace->slug),
                                        @js($workspace->description)
                                    )">
                                    Edit
                                </button>


                                {{-- TOGGLE --}}
                                <form
                                    method="POST"
                                    action="{{ route('workspaces.toggle', $workspace) }}">

                                    @csrf
                                    @method('PATCH')

                                    <button
                                        type="submit"
                                        class="btn ghost">
                                        {{ $workspace->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>

                                </form>


                                {{-- DELETE --}}
                                <button
    type="button"
    class="btn ghost"
    style="color:#b91c1c;"
    onclick="openDeleteWorkspaceModal(
        {{ $workspace->id }},
        @js($workspace->name)
    )">
    Hapus
</button>

                            </div>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td
                            colspan="5"
                            style="
                                padding:40px;
                                text-align:center;
                                color:var(--muted);
                            ">
                            Belum ada workspace.
                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>



{{-- ========================================================= --}}
{{-- TAMBAH WORKSPACE MODAL --}}
{{-- ========================================================= --}}

<div
    id="workspaceModal"
    style="
        display:none;
        position:fixed;
        inset:0;
        z-index:9999;
        background:rgba(12,15,22,.45);
    "
    onclick="closeWorkspaceModal(event)">

    <div style="
        min-height:100%;
        display:flex;
        align-items:center;
        justify-content:center;
        padding:20px;
    ">

        <div
            style="
                width:100%;
                max-width:500px;
                background:#fff;
                border-radius:14px;
                box-shadow:0 25px 70px rgba(0,0,0,.25);
            "
            onclick="event.stopPropagation()">


            {{-- MODAL HEADER --}}
            <div style="
                padding:20px;
                border-bottom:1px solid var(--line);
                display:flex;
                align-items:center;
                justify-content:space-between;
            ">

                <div>

                    <div style="
                        font-size:18px;
                        font-weight:700;
                    ">
                        Tambah Workspace
                    </div>

                    <p style="margin-top:5px;">
                        Buat workspace baru.
                    </p>

                </div>


                <button
                    type="button"
                    class="icon-btn"
                    onclick="closeWorkspaceModal()">
                    ×
                </button>

            </div>


            {{-- MODAL BODY --}}
            <form
                method="POST"
                action="{{ route('workspaces.store') }}">

                @csrf

                <div style="
                    padding:20px;
                    display:grid;
                    gap:15px;
                ">


                    <div>

                        <label for="workspaceName">
                            Nama Workspace
                        </label>

                        <input
                            id="workspaceName"
                            type="text"
                            name="name"
                            required
                            placeholder="Contoh: Manajemen">

                    </div>





                    <div>

                        <label for="workspaceDescription">
                            Deskripsi
                        </label>

                        <textarea
                            id="workspaceDescription"
                            name="description"
                            rows="3"
                            placeholder="Deskripsi workspace"
                            style="height:90px;"></textarea>

                    </div>

                </div>


                {{-- MODAL FOOTER --}}
                <div style="
                    padding:15px 20px;
                    border-top:1px solid var(--line);
                    display:flex;
                    justify-content:flex-end;
                    gap:8px;
                ">

                    <button
                        type="button"
                        class="btn ghost"
                        onclick="closeWorkspaceModal()">
                        Batal
                    </button>

                    <button
                        type="submit"
                        class="btn primary">
                        Simpan
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>



{{-- ========================================================= --}}
{{-- EDIT WORKSPACE MODAL --}}
{{-- ========================================================= --}}

<div
    id="editWorkspaceModal"
    style="
        display:none;
        position:fixed;
        inset:0;
        z-index:9999;
        background:rgba(12,15,22,.45);
    "
    onclick="closeEditWorkspaceModal(event)">

    <div style="
        min-height:100%;
        display:flex;
        align-items:center;
        justify-content:center;
        padding:20px;
    ">

        <div
            style="
                width:100%;
                max-width:500px;
                background:#fff;
                border-radius:14px;
                box-shadow:0 25px 70px rgba(0,0,0,.25);
            "
            onclick="event.stopPropagation()">


            {{-- MODAL HEADER --}}
            <div style="
                padding:20px;
                border-bottom:1px solid var(--line);
                display:flex;
                align-items:center;
                justify-content:space-between;
            ">

                <div>

                    <div style="
                        font-size:18px;
                        font-weight:700;
                    ">
                        Edit Workspace
                    </div>

                    <p style="margin-top:5px;">
                        Perbarui informasi workspace.
                    </p>

                </div>


                <button
                    type="button"
                    class="icon-btn"
                    onclick="closeEditWorkspaceModal()">
                    ×
                </button>

            </div>


            {{-- EDIT FORM --}}
            <form
                id="editWorkspaceForm"
                method="POST">

                @csrf
                @method('PUT')

                <div style="
                    padding:20px;
                    display:grid;
                    gap:15px;
                ">


                    <div>

                        <label for="editWorkspaceName">
                            Nama Workspace
                        </label>

                        <input
                            id="editWorkspaceName"
                            type="text"
                            name="name"
                            required>

                    </div>




                    <div>

                        <label for="editWorkspaceDescription">
                            Deskripsi
                        </label>

                        <textarea
                            id="editWorkspaceDescription"
                            name="description"
                            rows="3"
                            style="height:90px;"></textarea>

                    </div>

                </div>


                {{-- MODAL FOOTER --}}
                <div style="
                    padding:15px 20px;
                    border-top:1px solid var(--line);
                    display:flex;
                    justify-content:flex-end;
                    gap:8px;
                ">

                    <button
                        type="button"
                        class="btn ghost"
                        onclick="closeEditWorkspaceModal()">
                        Batal
                    </button>

                    <button
                        type="submit"
                        class="btn primary">
                        Simpan Perubahan
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

{{-- ========================================================= --}}
{{-- DELETE WORKSPACE MODAL --}}
{{-- ========================================================= --}}

<div
    id="deleteWorkspaceModal"
    style="
        display:none;
        position:fixed;
        inset:0;
        z-index:10000;
        background:rgba(12,15,22,.45);
    "
    onclick="closeDeleteWorkspaceModal(event)">

    <div style="
        min-height:100%;
        display:flex;
        align-items:center;
        justify-content:center;
        padding:20px;
    ">

        <div
            style="
                width:100%;
                max-width:430px;
                background:#fff;
                border-radius:14px;
                box-shadow:0 25px 70px rgba(0,0,0,.25);
            "
            onclick="event.stopPropagation()">

            {{-- HEADER --}}
            <div style="
                padding:20px;
                border-bottom:1px solid var(--line);
                display:flex;
                align-items:center;
                justify-content:space-between;
            ">

                <div>

                    <div style="
                        font-size:18px;
                        font-weight:700;
                    ">
                        Hapus Workspace
                    </div>

                    <p style="margin-top:5px;">
                        Konfirmasi penghapusan workspace.
                    </p>

                </div>

                <button
                    type="button"
                    class="icon-btn"
                    onclick="closeDeleteWorkspaceModal()">
                    ×
                </button>

            </div>


            {{-- BODY --}}
            <div style="padding:20px;">

                <div style="
                    padding:14px;
                    border:1px solid #fecaca;
                    border-radius:10px;
                    background:#fef2f2;
                    color:#991b1b;
                    font-size:12px;
                    line-height:1.6;
                ">

                    <strong id="deleteWorkspaceName"></strong>

                    akan dihapus.

                    <br>

                    <span>
                        Pastikan Anda benar-benar ingin melanjutkan
                        tindakan ini.
                    </span>

                </div>

            </div>


            {{-- FOOTER --}}
            <div style="
                padding:15px 20px;
                border-top:1px solid var(--line);
                display:flex;
                justify-content:flex-end;
                gap:8px;
            ">

                <button
                    type="button"
                    class="btn ghost"
                    onclick="closeDeleteWorkspaceModal()">
                    Batal
                </button>

                <form
                    id="deleteWorkspaceForm"
                    method="POST">

                    @csrf
                    @method('DELETE')

                    <button
                        type="submit"
                        class="btn"
                        style="
                            background:#b91c1c;
                            color:#fff;
                            border-color:#b91c1c;
                        ">
                        Hapus Workspace
                    </button>

                </form>

            </div>

        </div>

    </div>

</div>


<script>

function openEditWorkspaceModal(id, name, slug, description) {

    document.getElementById('editWorkspaceName').value = name ?? '';



    document.getElementById('editWorkspaceDescription').value =
        description ?? '';

    document.getElementById('editWorkspaceForm').action =
        '/workspaces/' + id;

    document.getElementById('editWorkspaceModal').style.display = 'block';

}


function closeEditWorkspaceModal(event) {

    if (event && event.target !== event.currentTarget) {
        return;
    }

    document.getElementById('editWorkspaceModal').style.display = 'none';

}


function filterWorkspaces() {

    const filter = document.getElementById('workspaceFilter').value;

    document.querySelectorAll('.workspace-row').forEach(function(row) {

        const status = row.dataset.status;

        row.style.display =
            filter === 'all' || filter === status
                ? ''
                : 'none';

    });

}
function openDeleteWorkspaceModal(id, name) {

    document.getElementById('deleteWorkspaceName').textContent =
        name ?? '';

    document.getElementById('deleteWorkspaceForm').action =
        '/workspaces/' + id;

    document.getElementById('deleteWorkspaceModal').style.display =
        'block';
}


function closeDeleteWorkspaceModal(event) {

    if (event && event.target !== event.currentTarget) {
        return;
    }

    document.getElementById('deleteWorkspaceModal').style.display =
        'none';
}

</script>

@endsection
