<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\ChatRoom;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Chat room private channel authorization
Broadcast::channel('chat-room.{chatRoomId}', function ($user, $chatRoomId) {
    $chatRoom = ChatRoom::find($chatRoomId);

    if (!$chatRoom) {
        return false;
    }

    // Check if user is a participant of the chat room
    return $chatRoom->isParticipant($user);
});
