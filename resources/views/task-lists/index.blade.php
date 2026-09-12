@extends('layouts.task-manager')

@section('title', 'Master List')
@section('page', 'List')

@section('content')

<div class="page-header">

    <div>
        <h1>Master List</h1>
        <p>
            Kelola List dalam Space {{ $space->name }}
        </p>
    </div>

</div>


<div class="panel">

    <div class="panel-head">

        <div>
            <h2>Daftar List</h2>
            <p>
                List dapat berada langsung di Space atau di dalam Folder.
            </p>
        </div>

        <button
            type="button"
            class="btn btn-primary"
            onclick="document.getElementById('listCreateForm').classList.toggle('hidden')"
        >
            + Tambah List
        </button>

    </div>


    {{-- FORM TAMBAH LIST --}}

    <div
        id="listCreateForm"
        class="hidden"
    >

        <form
            method="POST"
            action="{{ route('task-lists.store', $space) }}"
            class="form"
        >

            @csrf

            <div class="form-group">

                <label for="list_name">
                    Nama List
                </label>

                <input
                    type="text"
                    id="list_name"
                    name="name"
                    required
                >

            </div>


            <div class="form-group">

                <label for="list_description">
                    Deskripsi
                </label>

                <textarea
                    id="list_description"
                    name="description"
                    rows="3"
                ></textarea>

            </div>


            <div class="form-group">

                <label for="list_folder">
                    Folder
                </label>

                <select
                    id="list_folder"
                    name="folder_id"
                >

                    <option value="">
                        -- Root Space --
                    </option>

                    @foreach($folders as $folder)

                        <option value="{{ $folder->id }}">
                            📁 {{ $folder->name }}
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


    {{-- DAFTAR LIST --}}

    <div class="table-wrapper">

        <table class="data-table">

            <thead>

                <tr>
                    <th>List</th>
                    <th>Folder</th>
                    <th>Position</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>

            </thead>


            <tbody>

                @forelse($lists as $list)

                    <tr>

                        <td>

                            <strong>
                                📋 {{ $list->name }}
                            </strong>

                            @if($list->description)

                                <div class="text-muted">
                                    {{ $list->description }}
                                </div>

                            @endif

                        </td>


                        <td>

                            @if($list->folder)

                                📁 {{ $list->folder->name }}

                            @else

                                <span class="text-muted">
                                    Root Space
                                </span>

                            @endif

                        </td>


                        <td>
                            {{ $list->position }}
                        </td>


                        <td>

                            @if($list->is_active)

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
                                            action="{{ route('task-lists.update', [$space, $list]) }}"
                                            class="form"
                                        >

                                            @csrf
                                            @method('PUT')


                                            <div class="form-group">

                                                <label>
                                                    Nama List
                                                </label>

                                                <input
                                                    type="text"
                                                    name="name"
                                                    value="{{ $list->name }}"
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
                                                >{{ $list->description }}</textarea>

                                            </div>


                                            <div class="form-group">

                                                <label>
                                                    Folder
                                                </label>

                                                <select name="folder_id">

                                                    <option value="">
                                                        -- Root Space --
                                                    </option>

                                                    @foreach($folders as $folder)

                                                        <option
                                                            value="{{ $folder->id }}"
                                                            @selected($list->folder_id === $folder->id)
                                                        >
                                                            📁 {{ $folder->name }}
                                                        </option>

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
                                    action="{{ route('task-lists.toggle', [$space, $list]) }}"
                                >

                                    @csrf
                                    @method('PATCH')

                                    <button
                                        type="submit"
                                        class="btn btn-secondary"
                                    >
                                        {{ $list->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>

                                </form>


                                {{-- DELETE --}}

                                <form
                                    method="POST"
                                    action="{{ route('task-lists.destroy', [$space, $list]) }}"
                                    onsubmit="return confirm('Hapus List ini?')"
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
                            Belum ada List.
                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

@endsection
