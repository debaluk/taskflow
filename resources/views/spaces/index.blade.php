@extends('layouts.task-manager')

@section('title', 'Master Space')
@section('page', 'Space')
@section('breadcrumb', 'Space')

@section('content')

{{-- =========================================================
DATATABLES CSS
========================================================= --}}

<link
    rel="stylesheet"
    href="https://cdn.datatables.net/3.0.3/css/dataTables.dataTables.min.css"
>

<div class="page-head">


<div>
    <h1>Space</h1>

    <p>
        Kelola Space dan struktur List di dalamnya.
    </p>
</div>

</div>

<div class="panel">


<div class="panel-head">

    <div>

        <strong>
            Daftar Space
        </strong>

        <div class="text-muted">
            Space aktif dapat digunakan untuk pengelolaan Task.
        </div>

    </div>

</div>


<div class="space-table-wrap">

    <table
        id="spacesTable"
        class="display"
        style="width:100%;"
    >

        <thead>

            <tr>

                <th>
                    Space
                </th>

                <th
                    style="text-align:center;"
                >
                    Folder
                </th>

                <th
                    style="text-align:center;"
                >
                    List
                </th>

                <th
                    style="text-align:center;"
                >
                    Status
                </th>

                <th
                    style="text-align:right;"
                >
                    Aksi
                </th>

            </tr>

        </thead>


        <tbody>

            @foreach($spaces as $space)

                <tr>

                    {{-- SPACE --}}
                    <td>

                        <div>

                            <div
                                style="
                                    font-weight:700;
                                    color:#222;
                                "
                            >
                                {{ $space->name }}
                            </div>


                            @if($space->description)

                                <div
                                    style="
                                        margin-top:3px;
                                        color:#8a9099;
                                        font-size:11px;
                                    "
                                >
                                    {{ $space->description }}
                                </div>

                            @endif

                        </div>

                    </td>


                    {{-- FOLDER --}}
                    <td
                        style="
                            text-align:center;
                        "
                    >
                        {{ $space->folders_count }}
                    </td>


                    {{-- LIST --}}
                    <td
                        style="
                            text-align:center;
                        "
                    >
                        {{ $space->task_lists_count }}
                    </td>


                    {{-- STATUS --}}
                    <td
                        style="
                            text-align:center;
                        "
                    >

                        <form
                            method="POST"
                            action="{{ route('spaces.toggle', $space) }}"
                            style="display:inline;"
                        >

                            @csrf
                            @method('PATCH')


                            <button
                                type="submit"
                                title="Klik untuk mengubah status"
                                style="
                                    border:0;
                                    cursor:pointer;
                                    padding:5px 11px;
                                    border-radius:999px;
                                    font-size:11px;
                                    font-weight:700;
                                    transition:.15s ease;
                                    {{ $space->is_active
                                        ? 'background:#dcfce7;color:#166534;'
                                        : 'background:#f1f5f9;color:#64748b;'
                                    }}
                                "
                            >

                                {{ $space->is_active
                                    ? 'Aktif'
                                    : 'Nonaktif'
                                }}

                            </button>

                        </form>

                    </td>


                    {{-- AKSI --}}
                    <td
                        style="
                            text-align:right;
                            white-space:nowrap;
                        "
                    >

                        <button
                            type="button"
                            class="btn ghost"
                            style="margin-right:5px;"
                            onclick='openEditSpaceModal(
								@json($space->slug),
								@json($space->name),
								@json($space->description)
							)'
                        >
                            Edit
                        </button>


                        <form
                            method="POST"
                            action="{{ route('spaces.destroy', $space) }}"
                            style="display:inline;"
                            id="deleteSpaceForm{{ $space->id }}"
                        >

                            @csrf
                            @method('DELETE')


                            <button
                                type="button"
                                class="btn"
                                style="
                                    background:#fee2e2;
                                    color:#b91c1c;
                                "
                                onclick='openDeleteSpaceModal(
                                    @json($space->name),
                                    "deleteSpaceForm{{ $space->id }}"
                                )'
                            >
                                Hapus
                            </button>

                        </form>

                    </td>

                </tr>

            @endforeach

        </tbody>

    </table>

</div>


</div>


</div>

{{-- =========================================================
MODAL EDIT SPACE
========================================================= --}}

<div
    id="editSpaceModal"
    class="modal-backdrop hidden"
>


<div
    class="task-modal"
    style="max-width:480px;"
>

    <div class="modal-head">

        <div>

            <strong>
                Edit Space
            </strong>

            <div class="text-muted">
                Perbarui informasi Space.
            </div>

        </div>


        <button
            type="button"
            class="modal-close"
            onclick="closeEditSpaceModal()"
        >
            ×
        </button>

    </div>


    <form
        id="editSpaceForm"
        method="POST"
    >

        @csrf
        @method('PUT')


        <div style="padding:20px;">

            <div style="margin-bottom:16px;">

                <label
                    for="edit_space_name"
                    style="
                        display:block;
                        margin-bottom:7px;
                        font-size:12px;
                        font-weight:700;
                        color:#444;
                    "
                >
                    Nama Space
                </label>


                <input
                    type="text"
                    id="edit_space_name"
                    name="name"
                    required
                    maxlength="255"
                    style="
                        width:100%;
                        border:1px solid #dfe2e7;
                        border-radius:8px;
                        padding:10px 12px;
                        font-size:13px;
                        outline:none;
                    "
                >

            </div>


            <div>

                <label
                    for="edit_space_description"
                    style="
                        display:block;
                        margin-bottom:7px;
                        font-size:12px;
                        font-weight:700;
                        color:#444;
                    "
                >
                    Deskripsi
                </label>


                <textarea
                    id="edit_space_description"
                    name="description"
                    rows="4"
                    style="
                        width:100%;
                        border:1px solid #dfe2e7;
                        border-radius:8px;
                        padding:10px 12px;
                        font-size:13px;
                        resize:vertical;
                        outline:none;
                    "
                ></textarea>

            </div>

        </div>


        <div
            class="modal-foot"
            style="
                display:flex;
                justify-content:flex-end;
                gap:8px;
                padding:14px 20px;
                border-top:1px solid #eee;
            "
        >

            <button
                type="button"
                class="btn ghost"
                onclick="closeEditSpaceModal()"
            >
                Batal
            </button>


            <button
                type="submit"
                class="btn primary"
            >
                Simpan Perubahan
            </button>

        </div>

    </form>

</div>


</div>

{{-- =========================================================
MODAL HAPUS SPACE
========================================================= --}}

<div
    id="deleteSpaceModal"
    class="modal-backdrop hidden"
>


<div
    class="task-modal"
    style="max-width:430px;"
>

    <div
        style="
            padding:25px;
            text-align:center;
        "
    >

        <div
            style="
                width:48px;
                height:48px;
                margin:0 auto 14px;
                border-radius:50%;
                background:#fee2e2;
                color:#b91c1c;
                display:flex;
                align-items:center;
                justify-content:center;
                font-size:22px;
                font-weight:800;
            "
        >
            !
        </div>


        <div
            style="
                font-size:10px;
                font-weight:800;
                letter-spacing:.08em;
                color:#b91c1c;
                margin-bottom:5px;
            "
        >
            PERINGATAN
        </div>


        <h3
            style="
                margin:0 0 12px;
                font-size:18px;
                color:#222;
            "
        >
            Hapus Space
        </h3>


        <div
            id="deleteSpaceMessage"
            style="
                color:#666;
                font-size:13px;
                line-height:1.6;
            "
        ></div>

    </div>


    <div
        style="
            display:flex;
            justify-content:flex-end;
            gap:8px;
            padding:14px 20px;
            border-top:1px solid #eee;
        "
    >

        <button
            type="button"
            class="btn ghost"
            onclick="closeDeleteSpaceModal()"
        >
            Batal
        </button>


        <button
            type="button"
            class="btn"
            style="
                background:#dc2626;
                color:#fff;
            "
            onclick="submitDeleteSpace()"
        >
            Hapus Space
        </button>

    </div>

</div>


</div>

{{-- =========================================================
DATATABLES JS
========================================================= --}}

<script
    src="https://cdn.datatables.net/3.0.3/js/dataTables.min.js"
></script>

<script>

    /* ========================================================
       DATATABLES
    ======================================================== */

    document.addEventListener('DOMContentLoaded', function () {

        const table = document.getElementById('spacesTable');

        if (!table) {
            return;
        }


        new DataTable('#spacesTable', {

            pageLength: 10,

            lengthMenu: [
                [10, 25, 50, 100],
                [10, 25, 50, 100]
            ],

            order: [
                [0, 'asc']
            ],

            columnDefs: [

                {
                    targets: [1, 2, 3, 4],
                    orderable: true
                },

                {
                    targets: 4,
                    searchable: false
                }

            ],

            language: {

                search: 'Cari:',

                lengthMenu: 'Tampilkan _MENU_ data',

                info: 'Menampilkan _START_–_END_ dari _TOTAL_ Space',

                infoEmpty: 'Tidak ada Space',

                infoFiltered: '(difilter dari _MAX_ Space)',

                zeroRecords: 'Space tidak ditemukan',

                emptyTable: 'Belum ada Space',

                paginate: {

                    first: '«',

                    last: '»',

                    next: '›',

                    previous: '‹'

                }

            }

        });

    });


    /* ========================================================
       CREATE SPACE
    ======================================================== */

    function openCreateSpaceModal()
    {
        const modal = document.getElementById('createSpaceModal');

        if (!modal) return;

        modal.classList.remove('hidden');

        const input = document.getElementById('create_space_name');

        if (input) {

            setTimeout(() => {
                input.focus();
            }, 100);

        }
    }


    function closeCreateSpaceModal()
    {
        const modal = document.getElementById('createSpaceModal');

        if (!modal) return;

        modal.classList.add('hidden');
    }


    /* ========================================================
       EDIT SPACE
    ======================================================== */

    function openEditSpaceModal(slug, name, description)
	{
		const modal = document.getElementById('editSpaceModal');
		const form = document.getElementById('editSpaceForm');
		const nameInput =
			document.getElementById('edit_space_name');
		const descriptionInput =
			document.getElementById('edit_space_description');
		if (!modal || !form) return;
		form.action = `/spaces/${encodeURIComponent(slug)}`;
		nameInput.value = name || '';
		descriptionInput.value =description || '';
		modal.classList.remove('hidden');
		setTimeout(() => {
			nameInput.focus();
		}, 100);
	}


    function closeEditSpaceModal()
    {
        const modal =
            document.getElementById('editSpaceModal');

        if (!modal) return;

        modal.classList.add('hidden');
    }


    /* ========================================================
       DELETE SPACE
    ======================================================== */

    let deleteSpaceForm = null;


    function escapeHtml(value)
    {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }


    function openDeleteSpaceModal(spaceName, formId)
    {
        const modal =
            document.getElementById('deleteSpaceModal');

        const message =
            document.getElementById('deleteSpaceMessage');

        const form =
            document.getElementById(formId);


        if (!modal || !message || !form) {
            return;
        }


        deleteSpaceForm = form;


        message.innerHTML = `

            <strong>
                "${escapeHtml(spaceName)}"
            </strong>?


            <br><br>


            <div
                style="
                    padding:12px;
                    border-radius:8px;
                    background:#fff1f2;
                    color:#9f1239;
                    text-align:left;
                    border:1px solid #fecdd3;
                "
            >

                <strong>
                    PERINGATAN
                </strong>


                <br><br>


                Jika Space ini masih memiliki Task,
                penghapusan akan <strong>gagal</strong>.


                <br><br>


                Task yang ada di dalam Space
                <strong>
                    tidak akan ikut terhapus secara otomatis
                </strong>.

            </div>

        `;


        modal.classList.remove('hidden');
    }


    function closeDeleteSpaceModal()
    {
        const modal =
            document.getElementById('deleteSpaceModal');


        if (!modal) {
            return;
        }


        modal.classList.add('hidden');

        deleteSpaceForm = null;
    }


    function submitDeleteSpace()
    {
        if (!deleteSpaceForm) {
            return;
        }

        deleteSpaceForm.submit();
    }


    /* ========================================================
       CLOSE MODAL
    ======================================================== */

    document.addEventListener('keydown', function(event)
    {
        if (event.key !== 'Escape') {
            return;
        }


        closeCreateSpaceModal();

        closeEditSpaceModal();

        closeDeleteSpaceModal();
    });


    /* ========================================================
       CLICK BACKDROP
    ======================================================== */

    document.addEventListener('click', function(event)
    {
        if (
            event.target.classList.contains('modal-backdrop')
        ) {

            event.target.classList.add('hidden');


            if (
                event.target.id === 'deleteSpaceModal'
            ) {

                deleteSpaceForm = null;

            }

        }
    });

</script>

@endsection
