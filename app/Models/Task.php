<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Task extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'workspace_id',
        'space_id',
        'folder_id',
        'list_id',
        'parent_id',
        'title',
        'description',
        'task_type',
        'status',
        'priority',
        'start_date',
        'due_date',
        'progress',
        'estimated_minutes',
        'actual_minutes',
        'position',
        'created_by',
        'updated_by',
        'completed_at',
        'version',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'due_date' => 'datetime',
        'completed_at' => 'datetime',
        'progress' => 'integer',
        'estimated_minutes' => 'integer',
        'actual_minutes' => 'integer',
        'position' => 'integer',
        'version' => 'integer',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function space(): BelongsTo
    {
        return $this->belongsTo(Space::class);
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    public function taskList(): BelongsTo
    {
        return $this->belongsTo(TaskList::class, 'list_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_id');
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(Task::class, 'parent_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

	public function assignees(): BelongsToMany
	{
		return $this->belongsToMany(User::class, 'task_assignees');
	}

	public function dependencies()
	{
		return $this->hasMany(
			TaskDependency::class,
			'task_id'
		);
	}

	public function dependedOnBy()
	{
		return $this->hasMany(
			TaskDependency::class,
			'depends_on_task_id'
		);
	}
}
