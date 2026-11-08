@extends('layouts.task-manager')

@section('title', 'Detail Task')
@section('page', 'Detail Task')

@section('content')
<div class="page-head">
    <div>
        <span class="eyebrow">TASK DETAIL</span>
        <h1>{{ $task->title }}</h1>
        <p>
            {{ $task->taskList?->space?->name ?? 'Space' }}
            / {{ $task->taskList?->name ?? 'List' }}
        </p>
    </div>
    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
        @if($permissions['manage'])
            <button type="button" class="btn" onclick="openTaskModal({id: {{ $task->id }}})">Aksi cepat / Subtask</button>
        @endif
        <a class="btn ghost" href="{{ $returnUrl }}">← Kembali</a>
    </div>
</div>

<div class="panel" style="padding:24px;max-width:1000px;">
    <div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:20px;">
        <span class="badge">{{ ucfirst(str_replace('_', ' ', $task->status)) }}</span>
        <span class="badge">{{ ucfirst($task->priority) }}</span>
        <span class="text-muted">Dibuat oleh {{ $task->creator?->name ?? 'Pengguna' }}</span>
        @if($task->parent)
            <span class="text-muted">Subtask dari {{ $task->parent->title }}</span>
        @endif
    </div>

    @if($permissions['manage'])
        <form method="POST" action="{{ route('tasks.update', $task) }}" style="display:grid;gap:16px;">
            @csrf
            @method('PUT')
            <input type="hidden" name="return_url" value="{{ $returnUrl }}">
            <div>
                <label for="detailTitle">Judul Task</label>
                <input id="detailTitle" name="title" type="text" maxlength="255" required value="{{ old('title', $task->title) }}">
                @error('title') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
            <div>
                <label for="detailDescription">Deskripsi</label>
                <textarea id="detailDescription" name="description" rows="6">{{ old('description', $task->description) }}</textarea>
                @error('description') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;">
                <div>
                    <label for="detailStatus">Status</label>
                    <select id="detailStatus" name="status">
                        @foreach(['open' => 'To Do', 'in_progress' => 'In Progress', 'review' => 'Review', 'done' => 'Done'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $task->status) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="detailPriority">Prioritas</label>
                    <select id="detailPriority" name="priority">
                        @foreach(['low' => 'Low', 'normal' => 'Normal', 'high' => 'High', 'urgent' => 'Urgent'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('priority', $task->priority) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="detailStartDate">Tanggal mulai</label>
                    <input id="detailStartDate" type="date" name="start_date" value="{{ old('start_date', $task->start_date?->format('Y-m-d')) }}">
                </div>
                <div>
                    <label for="detailDueDate">Jatuh tempo</label>
                    <input id="detailDueDate" type="date" name="due_date" value="{{ old('due_date', $task->due_date?->format('Y-m-d')) }}">
                </div>
            </div>
            @error('due_date') <small class="text-danger">{{ $message }}</small> @enderror
            <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                <button
                    type="button"
                    class="btn danger"
                    onclick="if (confirm('Hapus Task ini beserta seluruh Subtask?')) document.getElementById('deleteTaskForm').submit()"
                >Hapus Task</button>
                <button type="submit" class="btn primary">Simpan perubahan</button>
            </div>
        </form>
        <form id="deleteTaskForm" method="POST" action="{{ route('tasks.destroy', $task) }}" style="display:none;">
            @csrf
            @method('DELETE')
            <input type="hidden" name="return_url" value="{{ $returnUrl }}">
        </form>
    @else
        <div style="display:grid;gap:16px;">
            <div>
                <strong>Deskripsi</strong>
                <div style="white-space:pre-wrap;margin-top:8px;">{{ $task->description ?: 'Belum ada deskripsi.' }}</div>
            </div>
            <div style="display:flex;gap:20px;flex-wrap:wrap;">
                <span>Status: {{ ucfirst(str_replace('_', ' ', $task->status)) }}</span>
                <span>Prioritas: {{ ucfirst($task->priority) }}</span>
                <span>Mulai: {{ $task->start_date?->format('d-m-Y') ?? '—' }}</span>
                <span>Jatuh tempo: {{ $task->due_date?->format('d-m-Y') ?? '—' }}</span>
            </div>
            @if($permissions['update_status'])
                <form id="taskStatusForm" style="display:flex;gap:10px;align-items:end;max-width:420px;">
                    <div style="flex:1;">
                        <label for="assigneeStatus">Perbarui status</label>
                        <select id="assigneeStatus" name="status">
                            @foreach(['open' => 'To Do', 'in_progress' => 'In Progress', 'review' => 'Review', 'done' => 'Done'] as $value => $label)
                                <option value="{{ $value }}" @selected($task->status === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button class="btn primary" type="submit">Simpan status</button>
                </form>
            @endif
        </div>
    @endif

    <div style="margin-top:28px;">
        <h3>Anggota yang ditugaskan</h3>
        @forelse($task->assignees as $assignee)
            <span class="badge">{{ $assignee->name }}</span>
        @empty
            <p class="text-muted">Belum ada assignee.</p>
        @endforelse
    </div>

    <div style="margin-top:28px;">
        <h3>Subtask ({{ $task->subtasks->count() }})</h3>
        @forelse($task->subtasks as $subtask)
            <div style="padding:10px 0;border-bottom:1px solid var(--border,#e5e7eb);display:flex;justify-content:space-between;gap:12px;">
                <span>{{ $subtask->title }}</span>
                <small>{{ ucfirst(str_replace('_', ' ', $subtask->status)) }}</small>
            </div>
        @empty
            <p class="text-muted">Belum ada subtask.</p>
        @endforelse

        @if($permissions['manage'])
            <form id="taskDetailSubtaskForm" style="display:grid;gap:10px;margin-top:16px;max-width:640px;">
                <label for="taskDetailSubtaskTitle">Tambah Subtask</label>
                <input id="taskDetailSubtaskTitle" name="title" type="text" maxlength="255" required placeholder="Judul Subtask">
                <textarea name="description" rows="2" placeholder="Deskripsi (opsional)"></textarea>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:10px;">
                    <select name="status" aria-label="Status Subtask">
                        <option value="open">To Do</option>
                        <option value="in_progress">In Progress</option>
                        <option value="review">Review</option>
                        <option value="done">Done</option>
                    </select>
                    <select name="priority" aria-label="Prioritas Subtask">
                        <option value="low">Low</option>
                        <option value="normal" selected>Normal</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                    <input name="due_date" type="date" aria-label="Jatuh tempo Subtask">
                </div>
                <div style="display:flex;justify-content:flex-end;">
                    <button class="btn primary" type="submit">Tambah Subtask</button>
                </div>
                <p id="taskDetailSubtaskError" class="text-danger" role="alert" hidden></p>
            </form>
        @endif
    </div>

    <section style="margin-top:28px;">
        <h3>Komentar Task</h3>
        <div id="taskDetailNotes" style="display:grid;gap:12px;margin:14px 0;">
            @forelse($notes as $note)
                <article style="padding:12px;background:var(--surface-muted,#f8fafc);border-radius:8px;">
                    <div style="display:flex;justify-content:space-between;gap:12px;">
                        <strong>{{ $note->user?->name ?? 'Pengguna' }}</strong>
                        <small>{{ $note->created_at?->format('d-m-Y H:i') }}</small>
                    </div>
                    <div style="white-space:pre-wrap;margin-top:6px;">{{ $note->description }}</div>
                </article>
            @empty
                <p class="text-muted" id="taskDetailNotesEmpty">Belum ada catatan.</p>
            @endforelse
        </div>
        @if($permissions['add_note'])
            <form id="taskDetailNoteForm" style="display:grid;gap:10px;">
                <textarea name="note" rows="3" maxlength="5000" required placeholder="Tulis komentar atau perkembangan pekerjaan..."></textarea>
                <div style="display:flex;justify-content:flex-end;">
                    <button class="btn primary" type="submit">Kirim komentar</button>
                </div>
            </form>
        @endif
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const statusForm = document.getElementById('taskStatusForm');
    if (statusForm) {
        statusForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            const response = await fetch(@json(route('tasks.status', $task)), {
                method: 'PATCH',
                headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf},
                body: JSON.stringify({status: document.getElementById('assigneeStatus').value})
            });
            const data = await response.json();
            if (!response.ok || !data.success) {
                alert(data.message || 'Gagal memperbarui status.');
                return;
            }
            window.location.reload();
        });
    }

    const subtaskForm = document.getElementById('taskDetailSubtaskForm');
    if (subtaskForm) {
        subtaskForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            const error = document.getElementById('taskDetailSubtaskError');
            const submit = subtaskForm.querySelector('button[type="submit"]');
            error.hidden = true;
            submit.disabled = true;

            const payload = Object.fromEntries(new FormData(subtaskForm).entries());
            try {
                const response = await fetch(@json(route('tasks.subtasks.store', $task)), {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf},
                    body: JSON.stringify(payload)
                });
                const data = await response.json();
                if (!response.ok || !data.success) {
                    const messages = data.errors ? Object.values(data.errors).flat().join(' ') : data.message;
                    throw new Error(messages || 'Gagal menambahkan Subtask.');
                }
                window.location.reload();
            } catch (exception) {
                error.textContent = exception.message || 'Gagal menambahkan Subtask.';
                error.hidden = false;
            } finally {
                submit.disabled = false;
            }
        });
    }

    const noteForm = document.getElementById('taskDetailNoteForm');
    if (noteForm) {
        noteForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            const input = noteForm.querySelector('[name="note"]');
            const response = await fetch(@json(route('tasks.notes.store', $task)), {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf},
                body: JSON.stringify({note: input.value})
            });
            const data = await response.json();
            if (!response.ok || !data.success) {
                alert(data.message || 'Gagal menambahkan catatan.');
                return;
            }
            window.location.reload();
        });
    }
});
</script>
@endsection
