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

class UserRoleChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $group;
    public $member;
    public $oldRole;
    public $newRole;
    public $changedBy;

    public function __construct(ChatRoom $group, User $member, string $oldRole, string $newRole, User $changedBy)
    {
        $this->group = $group;
        $this->member = $member;
        $this->oldRole = $oldRole;
        $this->newRole = $newRole;
        $this->changedBy = $changedBy;
    }

    public function broadcastOn()
    {
        return [
            new PrivateChannel('chat-room.' . $this->group->id),
            new Channel('user-groups.' . $this->member->id) // Notify the member whose role changed
        ];
    }

    public function broadcastWith()
    {
        return [
            'type' => 'user_role_changed',
            'group' => [
                'id' => $this->group->id,
                'name' => $this->group->name,
                'type' => $this->group->type
            ],
            'member' => [
                'id' => $this->member->id,
                'name' => $this->member->name,
                'email' => $this->member->email,
                'avatar' => $this->member->avatar
            ],
            'old_role' => $this->oldRole,
            'new_role' => $this->newRole,
            'changed_by' => [
                'id' => $this->changedBy->id,
                'name' => $this->changedBy->name
            ],
            'message' => $this->changedBy->name . ' changed ' . $this->member->name . "'s role from " . 
                        ucfirst($this->oldRole) . ' to ' . ucfirst($this->newRole),
            'timestamp' => now()->toISOString()
        ];
    }

    public function broadcastAs()
    {
        return 'user.role.changed';
    }
}
