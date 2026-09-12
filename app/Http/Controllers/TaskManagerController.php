<?php

namespace App\Http\Controllers;

use App\Events\TaskCreated;
use App\Models\Space;
use App\Models\Task;
use App\Models\TaskDependency;
use App\Models\TaskList;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TaskManagerController extends Controller
{
    /**
	 * ID scope yang boleh diakses user login.
	 */
	private function accessibleWorkspaceIds()
	{
		return WorkspaceMember::query()
			->where('user_id', auth()->id())
			->where('is_active', true)
			->pluck('workspace_id');
	}

    /**
	 * Scope aktif utama.
	 */

	// =========================================================
	// DASHBOARD
	// =========================================================

	public function dashboard(Request $request)
	{
		$workspace = $this->currentWorkspace();

		$today = now()->startOfDay();

		/*
		|--------------------------------------------------------------------------
		| TASK DASAR
		|--------------------------------------------------------------------------
		*/

		$baseTasksQuery = Task::query()
			->where('workspace_id', $workspace->id)
			->whereNull('parent_id');

		/*
		|--------------------------------------------------------------------------
		| TASK HARI INI
		|--------------------------------------------------------------------------
		*/

		$todayTasks = (clone $baseTasksQuery)
			->whereDate('due_date', $today)
			->where('status', '!=', 'done')
			->with([
				'taskList',
				'creator',
				'assignees',
			])
			->orderBy('due_date')
			->orderBy('position')
			->get();

		/*
		|--------------------------------------------------------------------------
		| TASK SAYA
		|--------------------------------------------------------------------------
		*/

		$myTasks = (clone $baseTasksQuery)
			->whereHas('assignees', function ($query) {
				$query->where('users.id', auth()->id());
			})
			->where('status', '!=', 'done')
			->with([
				'taskList',
				'creator',
				'assignees',
			])
			->orderByRaw("
				CASE
					WHEN due_date IS NULL THEN 1
					ELSE 0
				END
			")
			->orderBy('due_date')
			->orderBy('position')
			->limit(10)
			->get();

		/*
		|--------------------------------------------------------------------------
		| TASK TERLAMBAT
		|--------------------------------------------------------------------------
		*/

		$overdueTasks = (clone $baseTasksQuery)
			->whereNotNull('due_date')
			->whereDate('due_date', '<', $today)
			->where('status', '!=', 'done')
			->with([
				'taskList',
				'creator',
				'assignees',
			])
			->orderBy('due_date')
			->orderBy('position')
			->get();

		/*
		|--------------------------------------------------------------------------
		| STATISTIK
		|--------------------------------------------------------------------------
		*/

		$dueTodayCount = (clone $baseTasksQuery)
			->whereDate('due_date', $today)
			->where('status', '!=', 'done')
			->count();

		$overdueCount = (clone $baseTasksQuery)
			->whereNotNull('due_date')
			->whereDate('due_date', '<', $today)
			->where('status', '!=', 'done')
			->count();

		$completedCount = (clone $baseTasksQuery)
			->where('status', 'done')
			->count();

		$inProgressCount = (clone $baseTasksQuery)
			->where('status', 'in_progress')
			->count();

		$todoCount = (clone $baseTasksQuery)
			->where('status', 'open')
			->count();

		/*
		|--------------------------------------------------------------------------
		| BELUM DITUGASKAN
		|--------------------------------------------------------------------------
		*/

		$unassignedCount = (clone $baseTasksQuery)
			->whereDoesntHave('assignees')
			->where('status', '!=', 'done')
			->count();

		/*
		|--------------------------------------------------------------------------
		| SPACE
		|--------------------------------------------------------------------------
		*/

		$spaces = Space::query()
			->where('workspace_id', $workspace->id)
			->where('is_active', true)
			->orderBy('id')
			->get();

		/*
		|--------------------------------------------------------------------------
		| DATA SPACE
		|--------------------------------------------------------------------------
		*/

		$spaces->each(function ($space) {

			$space->tasks_count = Task::query()
				->where('workspace_id', $space->workspace_id)
				->where('space_id', $space->id)
				->whereNull('parent_id')
				->count();

			$space->completed_tasks_count = Task::query()
				->where('workspace_id', $space->workspace_id)
				->where('space_id', $space->id)
				->whereNull('parent_id')
				->where('status', 'done')
				->count();
		});

		/*
		|--------------------------------------------------------------------------
		| AKTIVITAS TERBARU
		|--------------------------------------------------------------------------
		| Belum memakai tabel activity. Sementara kosong agar Dashboard
		| sudah siap dan nanti bisa disambungkan ke activity log.
		*/

		$recentActivities = collect();

		return view(
			'dashboard.dashboard',
			compact(
				'workspace',
				'todayTasks',
				'myTasks',
				'overdueTasks',
				'spaces',
				'recentActivities',
				'dueTodayCount',
				'overdueCount',
				'completedCount',
				'inProgressCount',
				'todoCount',
				'unassignedCount'
			)
		);
	}

	private function currentWorkspace(): Workspace
	{
		$workspace = Workspace::query()
			->where('id', 1)
			->where('is_active', true)
			->first();

		abort_unless(
			$workspace,
			403,
			'Scope utama belum tersedia.'
		);

		session([
			'workspace_id' => 1,
		]);

		return $workspace;
	}

    /**
     * Ambil Space berdasarkan context.
     */
    private function resolveSpace(
        Request $request,
        Workspace $workspace
    ): ?Space {
        $spaceId = $request->integer('space_id');

        if (!$spaceId) {
            return null;
        }

        $space = Space::query()
            ->where('id', $spaceId)
            ->where('workspace_id', $workspace->id)
            ->where('is_active', true)
            ->first();

        abort_unless(
            $space,
            404,
            'Space tidak ditemukan.'
        );

        return $space;
    }

    /**
     * Ambil List berdasarkan context.
     */
    private function resolveTaskList(
        Request $request,
        Workspace $workspace,
        ?Space $space = null
    ): ?TaskList {
        $listId = $request->integer('list_id');

        if (!$listId) {
            return null;
        }

        $query = TaskList::query()
            ->where('id', $listId)
            ->where('is_active', true)
            ->whereHas('space', function ($query) use ($workspace) {
                $query
                    ->where('workspace_id', $workspace->id)
                    ->where('is_active', true);
            });

        if ($space) {
            $query->where('space_id', $space->id);
        }

        $taskList = $query->first();

        abort_unless(
            $taskList,
            404,
            'List tidak ditemukan.'
        );

        return $taskList;
    }

    /**
     * Scope query Task.
     */
    private function scopedTasksQuery(
        Request $request,
        Workspace $workspace,
        ?Space $space = null,
        ?TaskList $taskList = null
    ) {
        return Task::query()
            ->where('workspace_id', $workspace->id)
            ->when($space, function ($query) use ($space) {
                $query->where('space_id', $space->id);
            })
            ->when($taskList, function ($query) use ($taskList) {
                $query->where('list_id', $taskList->id);
            })
            ->whereNull('parent_id');
    }

    /**
     * Pastikan task dapat diakses user.
     */
    private function authorizeTask(Task $task): void
	{
		abort_unless(
			(int) $task->workspace_id === 1,
			403,
			'Anda tidak memiliki akses ke task ini.'
		);

		if (session()->has('workspace_id')) {
			abort_unless(
				(int) session('workspace_id') === 1,
				403,
				'Task bukan milik workspace aktif.'
			);
		}
	}

    // =========================================================
    // LIST VIEW
    // =========================================================

    public function index(Request $request)
    {
        $workspace = $this->currentWorkspace();

        $space = $this->resolveSpace(
            $request,
            $workspace
        );

        $taskList = $this->resolveTaskList(
            $request,
            $workspace,
            $space
        );

        $tasks = $this->scopedTasksQuery(
            $request,
            $workspace,
            $space,
            $taskList
        )
            ->with([
                'taskList',
                'creator',
                'assignees',
            ])
            ->orderBy('position')
            ->orderBy('id')
            ->get();

        $listsQuery = TaskList::query()
            ->where('is_active', true)
            ->whereHas('space', function ($query) use ($workspace) {
                $query
                    ->where('workspace_id', $workspace->id)
                    ->where('is_active', true);
            })
            ->with([
                'space',
                'tasks' => function ($query) {
                    $query
                        ->whereNull('parent_id')
                        ->with([
                            'creator',
                            'assignees',
                        ])
                        ->orderBy('position')
                        ->orderBy('id');
                },
            ])
            ->orderBy('space_id')
            ->orderBy('position')
            ->orderBy('id');

        if ($space) {
            $listsQuery->where(
                'space_id',
                $space->id
            );
        }

        if ($taskList) {
            $listsQuery->where(
                'id',
                $taskList->id
            );
        }

        $lists = $listsQuery->get();

        $listsBySpace = $lists->groupBy('space_id');

        $totalTasks = $tasks->count();

        $inProgress = $tasks
            ->where('status', 'in_progress')
            ->count();

        $completed = $tasks
            ->where('status', 'done')
            ->count();

        $dueSoon = $tasks
            ->whereNotNull('due_date')
            ->where('due_date', '<=', now()->addDays(7))
            ->where('status', '!=', 'done')
            ->count();

        return view(
            'dashboard.tasks',
            compact(
                'tasks',
                'lists',
                'listsBySpace',
                'workspace',
                'space',
                'taskList',
                'totalTasks',
                'inProgress',
                'dueSoon',
                'completed'
            )
        );
    }

	public function agenda()
	{
		return view('tasks.agenda', [
			'todayTasks' => collect(),
			'upcomingTasks' => collect(),
			'overdueTasks' => collect(),

			'todayCount' => 0,
			'upcomingCount' => 0,
			'overdueCount' => 0,
			'completedCount' => 0,
		]);
	}

	public function inbox()
	{
		return view('tasks.inbox', [
			'inboxTasks' => collect(),
			'unreadCount' => 0,
		]);
	}

	public function favorites()
	{
		return view('tasks.favorites', [
			'favoriteTasks' => collect(),
			'favoriteCount' => 0,
		]);
	}

    // =========================================================
    // TASK LIST PAGE
    // =========================================================

    public function listPage(
    Space $space,
    string $taskList
	) {
		abort_unless(
			$space->is_active &&
			(int) $space->workspace_id === 1,
			404,
			'Space tidak ditemukan.'
		);

		$list = TaskList::query()
			->where('space_id', $space->id)
			->where('slug', $taskList)
			->where('is_active', true)
			->first();

		abort_unless(
			$list,
			404,
			'List tidak ditemukan.'
		);

		$workspace = $this->currentWorkspace();

		$tasks = Task::query()
			->where('workspace_id', $workspace->id)
			->where('space_id', $space->id)
			->where('list_id', $list->id)
			->whereNull('parent_id')
			->with([
				'taskList',
				'creator',
				'assignees',
			])
			->orderBy('position')
			->orderBy('id')
			->get();

		$lists = TaskList::query()
			->where('is_active', true)
			->where('space_id', $space->id)
			->with([
				'space',
				'tasks' => function ($query) {
					$query
						->whereNull('parent_id')
						->with([
							'creator',
							'assignees',
						])
						->orderBy('position')
						->orderBy('id');
				},
			])
			->orderBy('position')
			->orderBy('id')
			->get();

		$listsBySpace = $lists->groupBy('space_id');

		$totalTasks = $tasks->count();

		$inProgress = $tasks
			->where('status', 'in_progress')
			->count();

		$completed = $tasks
			->where('status', 'done')
			->count();

		$dueSoon = $tasks
			->whereNotNull('due_date')
			->where('due_date', '<=', now()->addDays(7))
			->where('status', '!=', 'done')
			->count();

		/*
		|--------------------------------------------------------------------------
		| CLEAN URL CONTEXT
		|--------------------------------------------------------------------------
		| Dipakai oleh layout sidebar, breadcrumb, dan task context.
		*/

		$currentSpace = $space;
		$currentList = $list;

		return view(
			'dashboard.tasks',
			[
				'tasks' => $tasks,
				'lists' => $lists,
				'listsBySpace' => $listsBySpace,

				'workspace' => $workspace,

				'space' => $space,
				'taskList' => $list,

				'currentSpace' => $currentSpace,
				'currentList' => $currentList,

				'totalTasks' => $totalTasks,
				'inProgress' => $inProgress,
				'dueSoon' => $dueSoon,
				'completed' => $completed,
			]
		);
	}

    // =========================================================
    // TASK DETAIL
    // =========================================================

    public function show(Task $task)
    {
        $this->authorizeTask($task);

        $task->load([
            'taskList',
            'creator',
            'parent',
            'subtasks',
            'assignees',
        ]);

        return response()->json([
            'success' => true,
            'task' => $task,
        ]);
    }

    // =========================================================
    // CREATE TASK
    // =========================================================

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'status' => [
                'required',
                'in:open,in_progress,review,done',
            ],

            'priority' => [
                'required',
                'in:low,normal,high,urgent',
            ],

            'start_date' => [
                'nullable',
                'date',
            ],

            'due_date' => [
                'nullable',
                'date',
            ],

            'list_id' => [
                'required',
                'integer',
                'exists:task_lists,id',
            ],

            'folder_id' => [
                'nullable',
                'integer',
                'exists:folders,id',
            ],
        ]);

        $workspace = $this->currentWorkspace();

        $taskList = TaskList::query()
            ->where('id', $data['list_id'])
            ->where('is_active', true)
            ->whereHas('space', function ($query) use ($workspace) {
                $query
                    ->where('workspace_id', $workspace->id)
                    ->where('is_active', true);
            })
            ->first();

        abort_unless(
            $taskList,
            422,
            'List tidak valid untuk workspace aktif.'
        );

        $space = $taskList->space;

        abort_unless(
            $space && $space->is_active,
            422,
            'Space tidak aktif.'
        );

        $folderId = null;

        if (!empty($data['folder_id'])) {
            $folder = $workspace->folders()
                ->where('id', $data['folder_id'])
                ->where('space_id', $space->id)
                ->where('is_active', true)
                ->first();

            abort_unless(
                $folder,
                422,
                'Folder tidak valid untuk Space ini.'
            );

            $folderId = $folder->id;
        }

        $position = (int) Task::query()
            ->where('workspace_id', $workspace->id)
            ->where('space_id', $space->id)
            ->where('list_id', $taskList->id)
            ->whereNull('parent_id')
            ->max('position') + 1;

        $task = Task::create([
            'uuid' => (string) Str::uuid(),

            'workspace_id' => $workspace->id,
            'space_id' => $space->id,
            'folder_id' => $folderId,
            'list_id' => $taskList->id,

            'parent_id' => null,

            'title' => $data['title'],
            'description' => $data['description'] ?? null,

            'task_type' => 'task',

            'status' => $data['status'],
            'priority' => $data['priority'],

            'start_date' => $data['start_date'] ?? null,
            'due_date' => $data['due_date'] ?? null,

            'progress' => $data['status'] === 'done'
                ? 100
                : 0,

            'position' => $position,

            'created_by' => auth()->id(),

            'version' => 1,
        ]);

        event(new TaskCreated($task));

        $returnUrl = $request->input('return_url');

        if (!$returnUrl) {
            $returnUrl = route(
                'tasks.index',
                [
                    'space_id' => $space->id,
                    'list_id' => $taskList->id,
                ]
            );
        }

        return redirect($returnUrl)
            ->with(
                'success',
                'Task berhasil dibuat.'
            );
    }

    // =========================================================
    // UPDATE TASK
    // =========================================================

    public function update(
        Request $request,
        Task $task
    ) {
        $this->authorizeTask($task);

        $data = $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'status' => [
                'required',
                'in:open,in_progress,review,done',
            ],

            'priority' => [
                'required',
                'in:low,normal,high,urgent',
            ],

            'start_date' => [
                'nullable',
                'date',
            ],

            'due_date' => [
                'nullable',
                'date',
            ],
        ]);

        $task->update([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,

            'status' => $data['status'],
            'priority' => $data['priority'],

            'start_date' => $data['start_date'] ?? null,
            'due_date' => $data['due_date'] ?? null,

            'progress' => $data['status'] === 'done'
                ? 100
                : $task->progress,

            'updated_by' => auth()->id(),
        ]);

        $returnUrl = $request->input('return_url');

        return redirect(
            $returnUrl ?: route('tasks.index')
        )->with(
            'success',
            'Task berhasil diperbarui.'
        );
    }

    // =========================================================
    // ASSIGNEE
    // =========================================================

    public function updateAssignee(
        Request $request,
        Task $task
    ) {
        $this->authorizeTask($task);

        $data = $request->validate([
            'user_id' => [
                'nullable',
                'integer',
                'exists:users,id',
            ],
        ]);

        $userId = $data['user_id'] ?? null;

        if ($userId !== null) {
            $isActiveMember = Workspace::query()
                ->where('id', $task->workspace_id)
                ->where('is_active', true)
                ->whereHas('members', function ($query) use ($userId) {
                    $query
                        ->where('user_id', $userId)
                        ->where('is_active', true);
                })
                ->exists();

            abort_unless(
                $isActiveMember,
                422,
                'User yang dipilih bukan anggota aktif.'
            );
        }

        $task->assignees()->sync(
            $userId !== null
                ? [$userId]
                : []
        );

        $task->load('assignees');

        return response()->json([
            'success' => true,

            'message' => $userId !== null
                ? 'PIC task berhasil diperbarui.'
                : 'PIC task berhasil dihapus.',

            'task' => [
                'id' => $task->id,

                'assignees' => $task->assignees
                    ->map(function ($user) {
                        return [
                            'id' => $user->id,
                            'name' => $user->name,
                        ];
                    })
                    ->values(),
            ],
        ]);
    }

    // =========================================================
    // STATUS
    // =========================================================

    public function updateStatus(
        Request $request,
        Task $task
    ) {
        $this->authorizeTask($task);

        $data = $request->validate([
            'status' => [
                'required',
                'in:open,in_progress,review,done',
            ],
        ]);

        $task->update([
            'status' => $data['status'],

            'progress' => $data['status'] === 'done'
                ? 100
                : $task->progress,

            'updated_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status task berhasil diperbarui.',
            'status' => $task->status,
        ]);
    }

    // =========================================================
    // PARENT PROGRESS
    // =========================================================

    private function updateParentProgress(Task $parent)
    {
        $total = $parent->subtasks()->count();

        if ($total === 0) {
            return;
        }

        $done = $parent->subtasks()
            ->where('status', 'done')
            ->count();

        $progress = (int) round(
            ($done / $total) * 100
        );

        $parent->update([
            'progress' => $progress,
        ]);
    }

    // =========================================================
    // DELETE TASK
    // =========================================================

    public function destroy(
        Request $request,
        Task $task
    ) {
        $this->authorizeTask($task);

        $task->delete();

        $returnUrl = $request->input('return_url');

        return redirect(
            $returnUrl ?: route('tasks.index')
        )->with(
            'success',
            'Task berhasil dihapus.'
        );
    }

    // =========================================================
    // SUBTASK
    // =========================================================

    public function storeSubtask(
        Request $request,
        Task $task
    ) {
        $this->authorizeTask($task);

        $data = $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'status' => [
                'required',
                'in:open,in_progress,review,done',
            ],

            'priority' => [
                'required',
                'in:low,normal,high,urgent',
            ],

            'due_date' => [
                'nullable',
                'date',
            ],
        ]);

        $position = (int) Task::query()
            ->where('parent_id', $task->id)
            ->max('position') + 1;

        Task::create([
            'uuid' => (string) Str::uuid(),

            'workspace_id' => $task->workspace_id,
            'space_id' => $task->space_id,
            'folder_id' => $task->folder_id,
            'list_id' => $task->list_id,

            'parent_id' => $task->id,

            'title' => $data['title'],
            'description' => $data['description'] ?? null,

            'task_type' => 'task',

            'status' => $data['status'],
            'priority' => $data['priority'],

            'due_date' => $data['due_date'] ?? null,

            'progress' => $data['status'] === 'done'
                ? 100
                : 0,

            'position' => $position,

            'created_by' => auth()->id(),

            'version' => 1,
        ]);

        $this->updateParentProgress($task);

        $returnUrl = $request->input('return_url');

        return redirect(
            $returnUrl ?: route('tasks.index')
        )->with(
            'success',
            'Subtask berhasil dibuat.'
        );
    }

    public function updateSubtask(
        Request $request,
        Task $task
    ) {
        $this->authorizeTask($task);

        $task->load('parent');

        $data = $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'status' => [
                'required',
                'in:open,in_progress,review,done',
            ],

            'priority' => [
                'required',
                'in:low,normal,high,urgent',
            ],

            'due_date' => [
                'nullable',
                'date',
            ],
        ]);

        $task->update([
            'title' => $data['title'],

            'status' => $data['status'],
            'priority' => $data['priority'],

            'due_date' => $data['due_date'] ?? null,

            'progress' => $data['status'] === 'done'
                ? 100
                : $task->progress,

            'updated_by' => auth()->id(),
        ]);

        if ($task->parent) {
            $this->updateParentProgress(
                $task->parent
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Subtask berhasil diperbarui.',
        ]);
    }

    public function destroySubtask(Task $task)
    {
        $this->authorizeTask($task);

        $task->load('parent');

        $parent = $task->parent;

        $task->delete();

        if ($parent) {
            $this->updateParentProgress($parent);
        }

        return response()->json([
            'success' => true,
            'message' => 'Subtask berhasil dihapus.',
        ]);
    }

    // =========================================================
    // BOARD
    // =========================================================

    public function board(Request $request)
    {
        $workspace = $this->currentWorkspace();

        $space = $this->resolveSpace(
            $request,
            $workspace
        );

        $taskList = $this->resolveTaskList(
            $request,
            $workspace,
            $space
        );

        $tasks = $this->scopedTasksQuery(
            $request,
            $workspace,
            $space,
            $taskList
        )
            ->with([
                'taskList',
                'creator',
                'assignees',
            ])
            ->orderBy('position')
            ->orderBy('id')
            ->get();

        $columns = [
            'open' => 'To Do',
            'in_progress' => 'In Progress',
            'review' => 'Review',
            'done' => 'Done',
        ];

        return view(
            'tasks.board',
            compact(
                'tasks',
                'columns',
                'workspace',
                'space',
                'taskList'
            )
        );
    }

    // =========================================================
    // REORDER BOARD
    // =========================================================

    public function reorder(Request $request)
    {
        $data = $request->validate([
            'columns' => [
                'required',
                'array',
            ],

            'columns.*.status' => [
                'required',
                'in:open,in_progress,review,done',
            ],

            'columns.*.tasks' => [
                'array',
            ],

            'columns.*.tasks.*' => [
                'required',
                'integer',
                'exists:tasks,id',
            ],
        ]);

        $taskIds = collect($data['columns'])
            ->pluck('tasks')
            ->flatten()
            ->values();

        $workspace = $this->currentWorkspace();

        $space = $this->resolveSpace(
            $request,
            $workspace
        );

        $taskList = $this->resolveTaskList(
            $request,
            $workspace,
            $space
        );

        $tasksQuery = Task::query()
            ->whereIn('id', $taskIds)
            ->where('workspace_id', $workspace->id);

        if ($space) {
            $tasksQuery->where(
                'space_id',
                $space->id
            );
        }

        if ($taskList) {
            $tasksQuery->where(
                'list_id',
                $taskList->id
            );
        }

        $tasks = $tasksQuery
            ->get()
            ->keyBy('id');

        abort_unless(
            $tasks->count() === $taskIds->unique()->count(),
            403,
            'Terdapat task yang bukan bagian dari context aktif.'
        );

        foreach ($data['columns'] as $column) {
            foreach ($column['tasks'] as $index => $taskId) {
                $task = $tasks->get($taskId);

                abort_unless(
                    $task,
                    403,
                    'Task tidak dapat diakses.'
                );

                $task->update([
                    'status' => $column['status'],

                    'position' => $index + 1,

                    'progress' => $column['status'] === 'done'
                        ? 100
                        : (
                            $column['status'] === 'open'
                                ? 0
                                : $task->progress
                        ),

                    'updated_by' => auth()->id(),
                ]);

                if ($task->parent_id) {
                    $this->updateParentProgress(
                        $task->parent
                    );
                }
            }
        }

        return response()->json([
            'success' => true,

            'message' => 'Urutan task berhasil disimpan.',

            'tasks' => $tasks
                ->values()
                ->map(function ($task) {
                    return [
                        'id' => $task->id,
                        'status' => $task->status,
                        'progress' => $task->progress,
                    ];
                }),
        ]);
    }

    // =========================================================
    // CALENDAR
    // =========================================================

    public function calendar(Request $request)
    {
        $month = $request->integer(
            'month',
            now()->month
        );

        $year = $request->integer(
            'year',
            now()->year
        );

        $date = \Carbon\Carbon::create(
            $year,
            $month,
            1
        );

        $workspace = $this->currentWorkspace();

        $space = $this->resolveSpace(
            $request,
            $workspace
        );

        $taskList = $this->resolveTaskList(
            $request,
            $workspace,
            $space
        );

        $tasks = $this->scopedTasksQuery(
            $request,
            $workspace,
            $space,
            $taskList
        )
            ->whereNotNull('due_date')
            ->with([
                'taskList',
                'creator',
                'assignees',
            ])
            ->orderBy('due_date')
            ->orderBy('position')
            ->get();

        return view(
            'tasks.calendar',
            compact(
                'tasks',
                'date',
                'month',
                'year',
                'workspace',
                'space',
                'taskList'
            )
        );
    }

    // =========================================================
    // GANTT
    // =========================================================

    public function gantt(Request $request)
    {
        $month = session(
            'gantt_month',
            now()->month
        );

        $year = session(
            'gantt_year',
            now()->year
        );

        $date = \Carbon\Carbon::create(
            $year,
            $month,
            1
        );

        $workspace = $this->currentWorkspace();

        $space = $this->resolveSpace(
            $request,
            $workspace
        );

        $taskList = $this->resolveTaskList(
            $request,
            $workspace,
            $space
        );

        $tasks = $this->scopedTasksQuery(
            $request,
            $workspace,
            $space,
            $taskList
        )
            ->with([
                'subtasks',
                'assignees',
                'dependencies',
                'dependedOnBy',
                'taskList',
            ])
            ->orderBy('position')
            ->orderBy('id')
            ->get();

        return view(
            'tasks.gantt',
            compact(
                'tasks',
                'date',
                'month',
                'year',
                'workspace',
                'space',
                'taskList'
            )
        );
    }

    // =========================================================
    // GANTT NAVIGATION
    // =========================================================

    public function ganttPrevious(Request $request)
    {
        $date = \Carbon\Carbon::create(
            session('gantt_year', now()->year),
            session('gantt_month', now()->month),
            1
        )->subMonth();

        session([
            'gantt_month' => $date->month,
            'gantt_year' => $date->year,
        ]);

        return redirect()->route(
            'tasks.gantt',
            $request->only([
                'space_id',
                'list_id',
            ])
        );
    }

    public function ganttNext(Request $request)
    {
        $date = \Carbon\Carbon::create(
            session('gantt_year', now()->year),
            session('gantt_month', now()->month),
            1
        )->addMonth();

        session([
            'gantt_month' => $date->month,
            'gantt_year' => $date->year,
        ]);

        return redirect()->route(
            'tasks.gantt',
            $request->only([
                'space_id',
                'list_id',
            ])
        );
    }

    // =========================================================
    // DEPENDENCY
    // =========================================================

    public function storeDependency(
        Request $request,
        Task $task
    ) {
        $this->authorizeTask($task);

        $data = $request->validate([
            'depends_on_task_id' => [
                'required',
                'integer',
                'exists:tasks,id',
            ],

            'dependency_type' => [
                'required',
                'string',
                'in:FS,SS,FF,SF',
            ],
        ]);

        $dependsOnTaskId = $data['depends_on_task_id'];
        $dependencyType = $data['dependency_type'];

        abort_if(
            $task->id == $dependsOnTaskId,
            422,
            'Task tidak boleh tergantung pada dirinya sendiri.'
        );

        $dependsOnTask = Task::findOrFail(
            $dependsOnTaskId
        );

        $this->authorizeTask($dependsOnTask);

        abort_unless(
            (int) $dependsOnTask->workspace_id ===
            (int) $task->workspace_id,
            422,
            'Dependency harus berada dalam workspace yang sama.'
        );

        if (
            $task->space_id !== null &&
            $dependsOnTask->space_id !== null
        ) {
            abort_unless(
                (int) $dependsOnTask->space_id ===
                (int) $task->space_id,
                422,
                'Dependency harus berada dalam Space yang sama.'
            );
        }

        $dependency = $task->dependencies()
            ->where(
                'depends_on_task_id',
                $dependsOnTaskId
            )
            ->first();

        if ($dependency) {
            $dependency->update([
                'dependency_type' => $dependencyType,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Dependency berhasil diperbarui.',
                'dependency' => [
                    'id' => $dependency->id,
                    'task_id' => $dependency->task_id,
                    'depends_on_task_id' =>
                        $dependency->depends_on_task_id,
                    'dependency_type' =>
                        $dependency->dependency_type,
                ],
            ]);
        }

        $dependency = $task->dependencies()->create([
            'depends_on_task_id' => $dependsOnTaskId,
            'dependency_type' => $dependencyType,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Dependency berhasil dibuat.',
            'dependency' => [
                'id' => $dependency->id,
                'task_id' => $task->id,
                'depends_on_task_id' =>
                    $dependency->depends_on_task_id,
                'dependency_type' =>
                    $dependency->dependency_type,
            ],
        ]);
    }

    // =========================================================
    // DELETE DEPENDENCY
    // =========================================================

    public function destroyDependency(
        Task $task,
        TaskDependency $dependency
    ) {
        $this->authorizeTask($task);

        abort_unless(
            $dependency->task_id == $task->id,
            404
        );

        $dependency->delete();

        return response()->json([
            'success' => true,
            'message' => 'Dependency berhasil dihapus.',
        ]);
    }


}
