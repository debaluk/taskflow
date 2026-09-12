@extends('layouts.task-manager')

@section('title', 'Master Folder')
@section('page', 'Folder')

@section('content')

<div class="page-header">

    <div>
        <h1>Master Folder</h1>
        <p>
            Kelola Folder dalam Space {{ $space->name }}
        </p>
    </div>

</div>


<div class="panel">

    <div class="panel-head">

        <div>
            <h2>Daftar Folder</h2>
            <p>
                Folder bersifat opsional dan dapat memiliki Subfolder.
            </p>
        </div>

        <button
            type="button"
            class="btn btn-primary"
            onclick="document.getElementById('folderCreateForm').classList.toggle('hidden')"
        >
            + Tambah Folder
        </button>

    </div>


    {{-- =====================================================
         FORM TAMBAH FOLDER
    ====================================================== --}}

    <div
        id="folderCreateForm"
        class="hidden"
    >

        <form
            method="POST"
            action="{{ route('folders.store', [$workspace, $space]) }}"
            class="form"
        >

            @csrf

            <div class="form-group">

                <label for="folder_name">
                    Nama Folder
                </label>

                <input
                    type="text"
                    id="folder_name"
                    name="name"
                    required
                >

            </div>


            <div class="form-group">

                <label for="folder_description">
                    Deskripsi
                </label>

                <textarea
                    id="folder_description"
                    name="description"
                    rows="3"
                ></textarea>

            </div>


            <div class="form-group">

                <label for="folder_parent">
                    Parent Folder
                </label>

                <select
                    id="folder_parent"
                    name="parent_id"
                >

                    <option value="">
                        -- Root Folder --
                    </option>

                    @foreach($folders as $folder)

                        <option value="{{ $folder->id }}">
                            {{ $folder->name }}
                        </option>

                    @endforeach

                </select>

            </div>


            <div class="form-actions">

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Simpan
                </button>

            </div>

        </form>

    </div>


    {{-- =====================================================
         DAFTAR FOLDER
    ====================================================== --}}

    <div class="table-wrapper">

        <table class="data-table">

            <thead>

                <tr>
                    <th>Folder</th>
                    <th>Subfolder</th>
                    <th>List</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>

            </thead>

            <tbody>

                @forelse($folders as $folder)

                    <tr>

                        <td>

                            <strong>
                                📁 {{ $folder->name }}
                            </strong>

                            @if($folder->description)

                                <div class="text-muted">
                                    {{ $folder->description }}
                                </div>

                            @endif

                        </td>


                        <td>
                            {{ $folder->children_count }}
                        </td>


                        <td>
                            {{ $folder->task_lists_count }}
                        </td>


                        <td>

                            @if($folder->is_active)

                                <span class="badge badge-success">
                                    Aktif
                                </span>

                            @else

                                <span class="badge">
                                    Nonaktif
                                </span>

                            @endif

                        </td>


                        <td>

                            <div class="table-actions">

                                {{-- EDIT --}}

                                <details>

                                    <summary class="btn btn-secondary">
                                        Edit
                                    </summary>

                                    <div class="panel">

                                        <form
                                            method="POST"
                                            action="{{ route('folders.update', [$workspace, $space, $folder]) }}"
                                            class="form"
                                        >

                                            @csrf
                                            @method('PUT')


                                            <div class="form-group">

                                                <label>
                                                    Nama Folder
                                                </label>

                                                <input
                                                    type="text"
                                                    name="name"
                                                    value="{{ $folder->name }}"
                                                    required
                                                >

                                            </div>


                                            <div class="form-group">

                                                <label>
                                                    Deskripsi
                                                </label>

                                                <textarea
                                                    name="description"
                                                    rows="3"
                                                >{{ $folder->description }}</textarea>

                                            </div>


                                            <div class="form-group">

                                                <label>
                                                    Parent Folder
                                                </label>

                                                <select name="parent_id">

                                                    <option value="">
                                                        -- Root Folder --
                                                    </option>

                                                    @foreach($folders as $parentFolder)

                                                        @if($parentFolder->id !== $folder->id)

                                                            <option
                                                                value="{{ $parentFolder->id }}"
                                                                @selected($folder->parent_id === $parentFolder->id)
                                                            >
                                                                {{ $parentFolder->name }}
                                                            </option>

                                                        @endif

                                                    @endforeach

                                                </select>

                                            </div>


                                            <div class="form-actions">

                                                <button
                                                    type="submit"
                                                    class="btn btn-primary"
                                                >
                                                    Simpan Perubahan
                                                </button>

                                            </div>

                                        </form>

                                    </div>

                                </details>


                                {{-- TOGGLE STATUS --}}

                                <form
                                    method="POST"
                                    action="{{ route('folders.toggle', [$workspace, $space, $folder]) }}"
                                >

                                    @csrf
                                    @method('PATCH')

                                    <button
                                        type="submit"
                                        class="btn btn-secondary"
                                    >
                                        {{ $folder->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>

                                </form>


                                {{-- DELETE --}}

                                <form
                                    method="POST"
                                    action="{{ route('folders.destroy', [$workspace, $space, $folder]) }}"
                                    onsubmit="return confirm('Hapus Folder ini?')"
                                >

                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="btn btn-danger"
                                    >
                                        Hapus
                                    </button>

                                </form>

                            </div>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td
                            colspan="5"
                            class="empty-state"
                        >
                            Belum ada Folder.
                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

@endsection
