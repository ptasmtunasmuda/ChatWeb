<?php

namespace App\Events;

use App\Models\ChatRoom;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatRoomCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $chatRoom;

    /**
     * Create a new event instance.
     */
    public function __construct(ChatRoom $chatRoom)
    {
        $this->chatRoom = $chatRoom->load(['creator', 'activeParticipants']);
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
        return 'ChatRoomCreated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        $participants = $this->chatRoom->activeParticipants->map(function ($participant) {
            return [
                'id' => $participant->id,
                'name' => $participant->name,
                'email' => $participant->email,
                'avatar' => $participant->avatar,
            ];
        });

        // Log for debugging
        \Log::info('Broadcasting ChatRoomCreated event:', [
            'chat_room_id' => $this->chatRoom->id,
            'participants_count' => $participants->count(),
            'participant_ids' => $participants->pluck('id')->toArray()
        ]);

        return [
            'chatRoom' => [
                'id' => $this->chatRoom->id,
                'name' => $this->chatRoom->name,
                'description' => $this->chatRoom->description,
                'type' => $this->chatRoom->type,
                'is_active' => $this->chatRoom->is_active,
                'created_at' => $this->chatRoom->created_at,
                'updated_at' => $this->chatRoom->updated_at,
                'creator' => $this->chatRoom->creator,
                'participants' => $participants,
                'participants_count' => $participants->count(),
                'messages_count' => $this->chatRoom->messages()->count(),
            ]
        ];
    }
}
