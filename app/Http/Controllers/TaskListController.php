<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Models\Space;
use App\Models\TaskList;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TaskListController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INTERNAL WORKSPACE
    |--------------------------------------------------------------------------
    |
    | workspace_id = 1 hanya digunakan sebagai aturan internal backend.
    | Workspace tidak ditampilkan di URL dan tidak digunakan sebagai
    | authorization user.
    |
    */

    private const WORKSPACE_ID = 1;


    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index(
        Space $space
    ) {
        $this->authorizeSpace($space);

        $lists = $space->taskLists()
            ->with('folder')
            ->orderBy('position')
            ->orderBy('name')
            ->get();

        $folders = $space->folders()
            ->orderBy('name')
            ->get();

        return view('task-lists.index', compact(
            'space',
            'lists',
            'folders'
        ));
    }


    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request,
        Space $space
    ) {
        $this->authorizeSpace($space);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'folder_id' => ['nullable', 'integer'],
        ]);

        $folder = null;

        if (! empty($data['folder_id'])) {
            $folder = Folder::query()
                ->where('id', $data['folder_id'])
                ->where('workspace_id', self::WORKSPACE_ID)
                ->where('space_id', $space->id)
                ->firstOrFail();
        }

        $position = (int) $space->taskLists()
            ->where('folder_id', $folder?->id)
            ->max('position') + 1;

        /*
         * Generate unique slug di dalam Space.
         */
        $baseSlug = Str::slug($data['name']);

        if ($baseSlug === '') {
            $baseSlug = 'list';
        }

        $slug = $baseSlug;
        $counter = 2;

        while (
            TaskList::query()
                ->where('space_id', $space->id)
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        $taskList = TaskList::create([
            'uuid' => (string) Str::uuid(),
            'space_id' => $space->id,
            'folder_id' => $folder?->id,
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'position' => $position,
            'is_active' => true,
        ]);

        return redirect()
            ->route('tasks.list-page', [
                'space' => $space->slug,
                'taskList' => $taskList->slug,
            ])
            ->with(
                'success',
                'List berhasil ditambahkan.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(
    Request $request,
    Space $space,
    TaskList $taskList
	) {
		$this->authorizeTaskList(
			$space,
			$taskList
		);

		$data = $request->validate([
			'name' => ['required', 'string', 'max:255'],
			'description' => ['nullable', 'string'],
			'folder_id' => ['nullable', 'integer'],
		]);

		$folder = null;

		if (! empty($data['folder_id'])) {
			$folder = Folder::query()
				->where('id', $data['folder_id'])
				->where('workspace_id', self::WORKSPACE_ID)
				->where('space_id', $space->id)
				->firstOrFail();
		}

		$baseSlug = Str::slug($data['name']);

		if ($baseSlug === '') {
			$baseSlug = 'list';
		}

		$slug = $baseSlug;
		$counter = 2;

		while (
			TaskList::query()
				->where('space_id', $space->id)
				->where('slug', $slug)
				->where('id', '!=', $taskList->id)
				->exists()
		) {
			$slug = $baseSlug . '-' . $counter;
			$counter++;
		}

		$taskList->update([
			'folder_id' => $folder?->id,
			'name' => $data['name'],
			'slug' => $slug,
			'description' => $data['description'] ?? null,
		]);

		return redirect()
			->route('tasks.list-page', [
				'space' => $space->slug,
				'taskList' => $taskList->slug,
			])
			->with(
				'success',
				"List \"{$taskList->name}\" berhasil diperbarui."
			);
	}


    /*
    |--------------------------------------------------------------------------
    | TOGGLE
    |--------------------------------------------------------------------------
    */

    public function toggle(
        Space $space,
        TaskList $taskList
    ) {
        $this->authorizeTaskList(
            $space,
            $taskList
        );

        $taskList->update([
            'is_active' => ! $taskList->is_active,
        ]);

        return back()->with(
            'success',
            'Status List berhasil diubah.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    */

    public function destroy(
    Space $space,
    TaskList $taskList
	) {
		$this->authorizeTaskList(
			$space,
			$taskList
		);

		$taskCount = \App\Models\Task::query()
			->where('list_id', $taskList->id)
			->count();

		if ($taskCount > 0) {
			return redirect()
				->route('tasks.index', [
					'space_id' => $space->id,
				])
				->with(
					'error',
					"List \"{$taskList->name}\" tidak dapat dihapus karena masih memiliki {$taskCount} Task. Hapus atau pindahkan Task terlebih dahulu."
				);
		}

		$listName = $taskList->name;
		$spaceId = $space->id;

		$taskList->delete();

		return redirect()
			->route('tasks.index', [
				'space_id' => $spaceId,
			])
			->with(
				'success',
				"List \"{$listName}\" berhasil dihapus."
			);
	}


    /*
    |--------------------------------------------------------------------------
    | AUTHORIZE SPACE
    |--------------------------------------------------------------------------
    |
    | User tidak diverifikasi melalui WorkspaceMember.
    | Space hanya boleh berasal dari workspace internal TaskFlow.
    |
    */

    private function authorizeSpace(
        Space $space
    ): void {
        abort_unless(
            (int) $space->workspace_id === self::WORKSPACE_ID,
            404
        );
    }


    /*
    |--------------------------------------------------------------------------
    | AUTHORIZE TASK LIST
    |--------------------------------------------------------------------------
    */

    private function authorizeTaskList(
        Space $space,
        TaskList $taskList
    ): void {
        $this->authorizeSpace($space);

        /*
         * Pastikan List memang milik Space yang sedang diakses.
         */
        abort_unless(
            (int) $taskList->space_id === (int) $space->id,
            404
        );

        /*
         * Jika List menggunakan Folder, pastikan Folder
         * juga milik Workspace dan Space yang sama.
         */
        if ($taskList->folder_id !== null) {
            abort_unless(
                Folder::query()
                    ->where('id', $taskList->folder_id)
                    ->where(
                        'workspace_id',
                        self::WORKSPACE_ID
                    )
                    ->where(
                        'space_id',
                        $space->id
                    )
                    ->exists(),
                404
            );
        }
    }
}