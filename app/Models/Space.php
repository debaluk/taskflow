<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;

class Space extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'workspace_id',
        'owner_id',
        'name',
        'slug',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeAccessibleTo(Builder $query, int $userId): Builder
    {
        return $query->where(function (Builder $query) use ($userId) {
            $query->where('owner_id', $userId)
                ->orWhereHas('members', function (Builder $members) use ($userId) {
                    $members->where('users.id', $userId);
                });
        });
    }

    public function isAccessibleBy(int $userId): bool
    {
        return static::query()
            ->whereKey($this->getKey())
            ->accessibleTo($userId)
            ->exists();
    }

    public function isOwnedBy(int $userId): bool
    {
        return (int) $this->owner_id === $userId;
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(SpaceMember::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'space_members')
            ->withPivot('invited_by')
            ->withTimestamps();
    }

    public function folders(): HasMany
    {
        return $this->hasMany(Folder::class);
    }

    public function taskLists(): HasMany
	{
		return $this->hasMany(TaskList::class);
	}

	public function getRouteKeyName(): string
	{
		return 'slug';
	}
}