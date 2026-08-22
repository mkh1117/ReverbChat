<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class chat extends Model
{
    protected $table = 'chats';
    protected $fillable = ['room_id', 'sender_id', 'message','sequence_id', 'is_read','reply_to_id','sender_delete','receiver_delete'];
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

protected function getNextSequenceId(string $roomId)
{
    $cacheKey = "room:{$roomId}:seq";

    if (!Cache::has($cacheKey)) {
        $maxSeq = Chat::where('room_id', $roomId)->max('sequence_id') ?? 0;
        Cache::forever($cacheKey, $maxSeq);
    }

    return Cache::increment($cacheKey);
}

}
