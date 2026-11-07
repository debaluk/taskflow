<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Member;
use App\Models\Space;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function ownedSpaces(): HasMany
    {
        return $this->hasMany(Space::class, 'owner_id');
    }

    public function spaceMemberships(): HasMany
    {
        return $this->hasMany(SpaceMember::class);
    }

    public function spaces(): BelongsToMany
    {
        return $this->belongsToMany(Space::class, 'space_members')
            ->withPivot('invited_by')
            ->withTimestamps();
    }

	public function workspaceMembers(): HasMany
	{
		return $this->hasMany(WorkspaceMember::class);
	}

	public function assignedTasks(): BelongsToMany
	{
		return $this->belongsToMany(Task::class, 'task_assignees');
	}
	public function member()
	{
		return $this->hasOne(Member::class);
	}

	public function invitedMembers()
	{
		return $this->hasMany(Member::class, 'invited_by');
	}
}
