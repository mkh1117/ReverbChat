<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    /**
     * پیام‌های مربوط به این روم
     */
    public function messages(): HasMany
    {
        return $this->hasMany(chat::class, 'room_id');
    }
}
