<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Member extends Model
{
    protected $fillable = [
        'user_id',
        'invited_by',
        'clickup_user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
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