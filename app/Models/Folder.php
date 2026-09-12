<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Folder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'workspace_id',
        'space_id',
        'parent_id',
        'name',
        'slug',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Workspace parent utama.
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    /**
     * Space tempat Folder berada.
     */
    public function space(): BelongsTo
    {
        return $this->belongsTo(Space::class);
    }

    /**
     * Parent Folder.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }

    /**
     * Child Folder / Subfolder.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Folder::class, 'parent_id');
    }

    /**
     * List yang berada langsung di Folder ini.
     */
    public function taskLists(): HasMany
    {
        return $this->hasMany(TaskList::class);
    }

    /**
     * Legacy - sementara dipertahankan.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}