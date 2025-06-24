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

class UserJoinedGroup implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $group;
    public $newMember;
    public $memberData;
    public $addedBy;

    public function __construct(ChatRoom $group, User $newMember, array $memberData, User $addedBy)
    {
        $this->group = $group;
        $this->newMember = $newMember;
        $this->memberData = $memberData;
        $this->addedBy = $addedBy;
    }

    public function broadcastOn()
    {
        return [
            new PrivateChannel('chat-room.' . $this->group->id),
            new Channel('user-groups.' . $this->newMember->id) // Notify the new member
        ];
    }

    public function broadcastWith()
    {
        return [
            'type' => 'user_joined_group',
            'group' => [
                'id' => $this->group->id,
                'name' => $this->group->name,
                'type' => $this->group->type
            ],
            'member' => $this->memberData,
            'added_by' => [
                'id' => $this->addedBy->id,
                'name' => $this->addedBy->name
            ],
            'message' => $this->addedBy->name . ' added ' . $this->newMember->name . ' to the group',
            'timestamp' => now()->toISOString()
        ];
    }

    public function broadcastAs()
    {
        return 'user.joined.group';
    }
}
