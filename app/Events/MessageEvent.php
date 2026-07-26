<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MessageEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public $id;
    public $message;
    public $room_id;
    public $sender_id;
    public $is_read;
    public $sender_name;
    public $created_at;

    public function __construct($chat)
    {
        $this->id        = $chat->id;
        $this->message   = $chat->message;
        $this->room_id   = $chat->room_id;
        $this->sender_id = $chat->sender_id;
        $this->is_read   = $chat->is_read ?? 0;
        $this->sender_name = $chat->sender->name;
        $this->created_at = $chat->created_at->toIso8601String();
        log::error($this->room_id);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        return new privateChannel('message.'.$this->room_id);
    }
}
