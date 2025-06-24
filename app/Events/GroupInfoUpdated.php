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

class GroupInfoUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $group;
    public $oldData;
    public $newData;
    public $updatedBy;

    public function __construct(ChatRoom $group, array $oldData, array $newData, User $updatedBy)
    {
        $this->group = $group;
        $this->oldData = $oldData;
        $this->newData = $newData;
        $this->updatedBy = $updatedBy;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('chat-room.' . $this->group->id);
    }

    public function broadcastWith()
    {
        $changes = [];
        
        if ($this->oldData['name'] !== $this->newData['name']) {
            $changes[] = 'name changed from "' . $this->oldData['name'] . '" to "' . $this->newData['name'] . '"';
        }
        
        if ($this->oldData['description'] !== $this->newData['description']) {
            $changes[] = 'description updated';
        }

        return [
            'type' => 'group_info_updated',
            'group' => [
                'id' => $this->group->id,
                'name' => $this->newData['name'],
                'description' => $this->newData['description'],
                'type' => $this->group->type
            ],
            'old_data' => $this->oldData,
            'new_data' => $this->newData,
            'updated_by' => [
                'id' => $this->updatedBy->id,
                'name' => $this->updatedBy->name
            ],
            'changes' => $changes,
            'message' => $this->updatedBy->name . ' updated the group information',
            'timestamp' => now()->toISOString()
        ];
    }

    public function broadcastAs()
    {
        return 'group.info.updated';
    }
}
