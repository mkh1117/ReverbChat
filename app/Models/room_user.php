<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class room_user extends Model
{

    protected $fillable = [
        'room_id',
        'user_id',
        'role',
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }
}
