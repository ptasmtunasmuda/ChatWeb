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

class UserLeftGroup implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $group;
    public $member;
    public $actionBy;
    public $action; // 'left' or 'removed'

    public function __construct(ChatRoom $group, User $member, User $actionBy, string $action)
    {
        $this->group = $group;
        $this->member = $member;
        $this->actionBy = $actionBy;
        $this->action = $action;
    }

    public function broadcastOn()
    {
        return [
            new PrivateChannel('chat-room.' . $this->group->id),
            new Channel('user-groups.' . $this->member->id) // Notify the member who left/was removed
        ];
    }

    public function broadcastWith()
    {
        $message = $this->action === 'left' 
            ? $this->member->name . ' left the group'
            : $this->actionBy->name . ' removed ' . $this->member->name . ' from the group';

        return [
            'type' => 'user_left_group',
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
            'action_by' => [
                'id' => $this->actionBy->id,
                'name' => $this->actionBy->name
            ],
            'action' => $this->action,
            'message' => $message,
            'timestamp' => now()->toISOString()
        ];
    }

    public function broadcastAs()
    {
        return 'user.left.group';
    }
}
