<?php

namespace App\Models;

use App\Services\ChatEncryptionService;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class chat extends Model
{
    protected $table = 'chats';
    protected $fillable = ['room_id', 'sender_id', 'message','sequence_id', 'is_read','reply_to_id','forwarded_from_id','sender_delete','receiver_delete'];
    protected $casts = [
        'is_read' => 'boolean',
    ];
protected function message(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => ChatEncryptionService::decrypt($value,(int) $this->room_id),
            set: fn (string $value) => ChatEncryptionService::encrypt($value,(int) $this->attributes['room_id'] ?? request('room_id'))
        );
    }
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

public function forwardedFrom()
{
    return $this->belongsTo(chat::class, 'forwarded_from_id')->with('sender');
}

public function attachments()
{
    return $this->hasMany(ChatAttachment::class);
}

}
