<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class chat extends Model
{
    protected $table = 'chats';
    protected $fillable = ['room_id', 'sender_id', 'message', 'is_read'];
}
