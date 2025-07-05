<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatRoomDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $chatRoomId;
    public $chatRoomName;

    /**
     * Create a new event instance.
     */
    public function __construct($chatRoomId, $chatRoomName)
    {
        $this->chatRoomId = $chatRoomId;
        $this->chatRoomName = $chatRoomName;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('admin-chat-rooms'), // For admin updates
            new Channel('chat-rooms'), // For all users updates
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'ChatRoomDeleted';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'chatRoomId' => $this->chatRoomId,
            'chatRoomName' => $this->chatRoomName,
        ];
    }
}
