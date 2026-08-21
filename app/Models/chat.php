<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class chat extends Model
{
    protected $table = 'chats';
    protected $fillable = ['room_id', 'sender_id', 'message', 'is_read','reply_to_id','sender_delete','receiver_delete'];
    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function sender()
{
    return $this->belongsTo(User::class, 'sender_id');
}
public function parent()
{
    return $this->belongsTo(Chat::class, 'reply_to_id')
                ->select(['id', 'sender_id', 'message'])
                ->with('sender:id,name');
}
public function replies()
{
    return $this->hasMany(Chat::class, 'reply_to_id');
}

}
