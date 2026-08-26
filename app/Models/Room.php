<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Room extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'type',
        'owner_id'
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'room_users', 'room_id', 'user_id')
                    ->withTimestamps();
    }


    public function messages(): HasMany
    {
        return $this->hasMany(chat::class, 'room_id');
    }

    public function avatars()
    {
        return $this->hasMany(RoomAvatar::class)->latest();
    }


    public function currentAvatar()
    {
        return $this->hasOne(RoomAvatar::class)->latestOfMany();
    }

    public function scopeWithUnreadCount(Builder $query, int $userId): Builder
{
    return $query->withCount(['messages as unread_count' => function ($q) use ($userId) {
        $q->whereColumn('chats.sequence_id', '>', DB::raw("(
            SELECT COALESCE(last_read_sequence_id, 0)
            FROM room_users
            WHERE room_users.room_id = chats.room_id
              AND room_users.user_id = {$userId}
            LIMIT 1
        )"))
        ->where(function ($sub) use ($userId) {
            $sub->where('chats.sender_id', '!=', $userId) 
                ->orWhereNull('chats.sender_id');
        });
    }]);
}
}

