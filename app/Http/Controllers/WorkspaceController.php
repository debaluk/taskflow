<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WorkspaceController extends Controller
{
    public function index()
	{
		$workspaces = Workspace::with('owner')
			->whereHas('members', function ($query) {
				$query->where('user_id', auth()->id())
					->where('is_active', true);
			})
			->orderBy('name')
			->get();

		return view('workspaces.index', compact('workspaces'));
	}

    public function store(Request $request)
	{
		$data = $request->validate([
			'name' => ['required', 'string', 'max:255'],
			'description' => ['nullable', 'string'],
		]);

		$workspace = Workspace::create([
			'uuid' => (string) Str::uuid(),
			'name' => $data['name'],
			'slug' => Str::slug($data['name']) . '-' . Str::lower(Str::random(8)),
			'description' => $data['description'] ?? null,
			'owner_id' => auth()->id(),
			'is_active' => true,
		]);

		$workspace->members()->create([
			'user_id' => auth()->id(),
			'role' => 'owner',
			'is_active' => true,
		]);

		return redirect()
			->route('workspaces.index')
			->with('success', 'Workspace berhasil dibuat.');
	}

    public function update(Request $request, Workspace $workspace)
	{
		$data = $request->validate([
			'name' => ['required', 'string', 'max:255'],
			'description' => ['nullable', 'string'],
		]);

		$workspace->update([
			'name' => $data['name'],
			'slug' => Str::slug($data['name']) . '-' . Str::lower(Str::random(8)),
			'description' => $data['description'] ?? null,
		]);

		return redirect()
			->route('workspaces.index')
			->with('success', 'Workspace berhasil diperbarui.');
	}

    public function toggle(Workspace $workspace)
    {
        $workspace->update([
            'is_active' => ! $workspace->is_active,
        ]);

        return redirect()
            ->route('workspaces.index')
            ->with('success', 'Status workspace berhasil diperbarui.');
    }

    public function destroy(Workspace $workspace)
    {
        $workspace->delete();

        return redirect()
            ->route('workspaces.index')
            ->with('success', 'Workspace berhasil dihapus.');
    }

	public function switch(Request $request, Workspace $workspace)
	{
		abort_unless($workspace->is_active, 404);

		$hasAccess = $workspace->members()
			->where('user_id', auth()->id())
			->where('is_active', true)
			->exists();

		abort_unless($hasAccess, 403);

		session(['workspace_id' => $workspace->id]);

		return back();
	}



	public function clear()
	{
		session()->forget('workspace_id');

		return back();
	}
	public function show(Workspace $workspace)
	{
		$hasAccess = $workspace->members()
			->where('user_id', auth()->id())
			->where('is_active', true)
			->exists();

		abort_unless($hasAccess, 403);

		$tasks = $workspace->tasks()
			->whereNull('parent_id')
			->orderBy('position')
			->orderBy('id')
			->get();

		$totalTasks = $tasks->count();

		$inProgress = $tasks
			->where('status', 'in_progress')
			->count();

		$completed = $tasks
			->where('status', 'done')
			->count();

		$overdue = $tasks
			->whereNotNull('due_date')
			->where('due_date', '<', now())
			->where('status', '!=', 'done')
			->count();

		$progress = $totalTasks > 0
			? round(($completed / $totalTasks) * 100)
			: 0;

		return view('workspaces.show', compact(
			'workspace',
			'tasks',
			'totalTasks',
			'inProgress',
			'completed',
			'overdue',
			'progress'
		));
	}
}
