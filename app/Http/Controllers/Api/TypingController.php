<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatRoom;
use App\Events\UserTyping;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class TypingController extends Controller
{
    /**
     * Broadcast typing status
     */
    public function typing(Request $request, string $chatRoomId): JsonResponse
    {
        $user = $request->user();
        $chatRoom = ChatRoom::findOrFail($chatRoomId);

        // Check if user is participant
        if (!$chatRoom->isParticipant($user)) {
            return response()->json([
                'message' => 'Access denied. You are not a participant of this chat room.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'is_typing' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $isTyping = $request->get('is_typing');

        // Broadcast typing event to other participants
        broadcast(new UserTyping($user, $chatRoom->id, $isTyping))->toOthers();

        return response()->json([
            'message' => 'Typing status broadcasted',
            'is_typing' => $isTyping
        ]);
    }
}
