<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    /**
     * Create a new event instance.
     */
    public function __construct(Message $message)
    {
        $this->message = $message->load(['user', 'replyToMessage.user']);

        \Log::info('MessageSent event created', [
            'message_id' => $this->message->id,
            'chat_room_id' => $this->message->chat_room_id,
            'user_id' => $this->message->user_id,
            'content' => substr($this->message->content, 0, 50) . '...'
        ]);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat-room.' . $this->message->chat_room_id),
            new Channel('admin-messages'), // For admin real-time updates
            new Channel('user-messages'), // For all users' chat list updates
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'MessageSent';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        $data = [
            'message' => $this->message,
            'chat_room_id' => $this->message->chat_room_id,
            'timestamp' => now()->toISOString(),
        ];

        \Log::info('MessageSent broadcasting data', [
            'message_id' => $this->message->id,
            'channels' => array_map(fn($channel) => $channel->name ?? get_class($channel), $this->broadcastOn()),
            'data_keys' => array_keys($data)
        ]);

        return $data;
    }
}
