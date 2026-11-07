@extends('layouts.task-manager')

@section('title', 'Anggota Space')
@section('page', 'Space')
@section('breadcrumb', 'Anggota Space')

@section('content')
<div class="page-head">
    <div>
        <h1>Anggota Space</h1>
        <p>Kelola pengguna yang dapat mengakses Space <strong>{{ $space->name }}</strong>.</p>
    </div>
    <a href="{{ route('spaces.index') }}" class="btn ghost">Kembali ke Space</a>
</div>

<div class="panel">
    <div class="panel-head">
        <div>
            <strong>Owner</strong>
            <div class="text-muted">Owner memiliki kendali penuh atas Space.</div>
        </div>
    </div>
    <div style="padding:16px 20px;border-bottom:1px solid #eee;">
        <strong>{{ $space->owner->name }}</strong>
        <div class="text-muted">{{ $space->owner->email }}</div>
    </div>

    <div class="panel-head">
        <div>
            <strong>Tambah Anggota</strong>
            <div class="text-muted">Pilih pengguna yang akan menjadi anggota Space.</div>
        </div>
    </div>
    <form method="POST" action="{{ route('spaces.members.store', $space) }}" style="padding:16px 20px;">
        @csrf
        <div style="display:flex;gap:10px;align-items:end;flex-wrap:wrap;">
            <div style="flex:1;min-width:240px;">
                <label for="user_id" style="display:block;margin-bottom:7px;font-weight:700;">Pengguna</label>
                <select id="user_id" name="user_id" required style="width:100%;padding:10px;border:1px solid #dfe2e7;border-radius:8px;">
                    <option value="">Pilih pengguna</option>
                    @foreach($availableUsers as $user)
                        <option value="{{ $user->id }}" @selected(old('user_id') == $user->id)>
                            {{ $user->name }} — {{ $user->email }}
                        </option>
                    @endforeach
                </select>
                @error('user_id')
                    <div style="color:#b91c1c;margin-top:6px;font-size:12px;">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn primary">Tambah Anggota</button>
        </div>
    </form>
</div>

<div class="panel" style="margin-top:18px;">
    <div class="panel-head">
        <div>
            <strong>Daftar Anggota</strong>
            <div class="text-muted">{{ $members->count() }} anggota terdaftar.</div>
        </div>
    </div>
    <div class="space-table-wrap">
        <table style="width:100%;">
            <thead>
                <tr>
                    <th style="text-align:left;padding:12px 16px;">Nama</th>
                    <th style="text-align:left;padding:12px 16px;">Email</th>
                    <th style="text-align:left;padding:12px 16px;">Ditambahkan oleh</th>
                    <th style="text-align:right;padding:12px 16px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($members as $membership)
                    <tr>
                        <td style="padding:12px 16px;">{{ $membership->user->name }}</td>
                        <td style="padding:12px 16px;">{{ $membership->user->email }}</td>
                        <td style="padding:12px 16px;">{{ $membership->inviter?->name ?? '—' }}</td>
                        <td style="padding:12px 16px;text-align:right;">
                            <form method="POST" action="{{ route('spaces.members.destroy', [$space, $membership->user]) }}" onsubmit="return confirm('Keluarkan {{ addslashes($membership->user->name) }} dari Space ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn" style="background:#fee2e2;color:#b91c1c;">Keluarkan</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="padding:24px;text-align:center;color:#8a9099;">Belum ada anggota Space.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
