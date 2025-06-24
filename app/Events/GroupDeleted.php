<?php

namespace App\Events;

use App\Models\ChatRoom;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GroupDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $group;
    public $deletedBy;

    public function __construct(ChatRoom $group, User $deletedBy)
    {
        $this->group = $group;
        $this->deletedBy = $deletedBy;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('chat-room.' . $this->group->id);
    }

    public function broadcastWith()
    {
        return [
            'type' => 'group_deleted',
            'group' => [
                'id' => $this->group->id,
                'name' => $this->group->name,
                'type' => $this->group->type
            ],
            'deleted_by' => [
                'id' => $this->deletedBy->id,
                'name' => $this->deletedBy->name
            ],
            'message' => $this->deletedBy->name . ' deleted the group "' . $this->group->name . '"',
            'timestamp' => now()->toISOString()
        ];
    }

    public function broadcastAs()
    {
        return 'group.deleted';
    }
}
