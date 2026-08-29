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
    public $reply_to;
    public $sequence_id;
    public $views_count;
    public $sender_name;
    public $created_at;
    public $forwarded_from;

    public function __construct($chat)
    {
        $this->id          = $chat->id;
        $this->message     = $chat->message;
        $this->room_id     = $chat->room_id;
        $this->sender_id   = $chat->sender_id;
        $this->reply_to    = $chat->parent;
        $this->sequence_id = $chat->sequence_id;
        $this->views_count = $chat->views_count ?? 0;
        $this->sender_name = $chat->sender->name;
        $this->forwarded_from = $chat->forwarded_from_id;
        $this->created_at  = $chat->created_at->toIso8601String();

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
