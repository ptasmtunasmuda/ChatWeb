<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\ChatRoom;
use App\Models\UserActivityLog;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    /**
     * Display messages for a specific chat room
     */
    public function index(Request $request, string $chatRoomId): JsonResponse
    {
        $user = $request->user();
        $chatRoom = ChatRoom::findOrFail($chatRoomId);

        // Check if user is participant
        if (!$chatRoom->isParticipant($user)) {
            return response()->json([
                'message' => 'Access denied. You are not a participant of this chat room.'
            ], 403);
        }

        $query = $chatRoom->messages()
                         ->with(['user', 'replyToMessage.user'])
                         ->withCount('readByUsers');

        // Pagination
        $perPage = $request->get('per_page', 50);
        $messages = $query->orderBy('created_at', 'desc')
                         ->paginate($perPage);

        // Mark messages as read for current user
        $unreadMessages = $chatRoom->messages()
                                  ->whereDoesntHave('readByUsers', function ($q) use ($user) {
                                      $q->where('user_id', $user->id);
                                  })
                                  ->get();

        foreach ($unreadMessages as $message) {
            $message->markAsRead($user);
        }

        return response()->json($messages);
    }

    /**
     * Store a newly created message
     */
    public function store(Request $request, string $chatRoomId): JsonResponse
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
            'content' => 'required_without:attachments|string|max:5000',
            'type' => 'sometimes|in:text,image,audio,file',
            'reply_to_message_id' => 'sometimes|nullable|exists:messages,id',
            'attachments' => 'sometimes|array|max:5',
            'attachments.*' => 'file|max:10240', // 10MB max per file
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Create message
            $message = Message::create([
                'chat_room_id' => $chatRoom->id,
                'user_id' => $user->id,
                'content' => $request->content,
                'type' => $request->get('type', 'text'),
                'reply_to_message_id' => $request->reply_to_message_id,
            ]);

            // Handle file attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $message->addMediaFromRequest('attachments')
                           ->each(function ($fileAdder) {
                               $fileAdder->toMediaCollection('attachments');
                           });
                }
            }

            // Mark as read by sender
            $message->markAsRead($user);

            // Update chat room timestamp
            $chatRoom->touch();

            // Log activity
            UserActivityLog::log($user, 'message_sent', "Sent message in chat room: {$chatRoom->name}", [
                'chat_room_id' => $chatRoom->id,
                'message_id' => $message->id,
                'message_type' => $message->type,
            ]);

            DB::commit();

            // Load relationships for response
            $message->load(['user', 'replyToMessage.user']);

            // Broadcast the message to all participants (including sender)
            \Log::info('Broadcasting MessageSent event', [
                'message_id' => $message->id,
                'chat_room_id' => $message->chat_room_id,
                'user_id' => $message->user_id,
                'content' => $message->content
            ]);
            broadcast(new MessageSent($message));
            \Log::info('MessageSent event broadcasted successfully');

            return response()->json([
                'message' => 'Message sent successfully',
                'data' => $message
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Failed to send message',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified message
     */
    public function show(Request $request, string $chatRoomId, string $id): JsonResponse
    {
        $user = $request->user();
        $chatRoom = ChatRoom::findOrFail($chatRoomId);

        // Check if user is participant
        if (!$chatRoom->isParticipant($user)) {
            return response()->json([
                'message' => 'Access denied. You are not a participant of this chat room.'
            ], 403);
        }

        $message = Message::with(['user', 'replyToMessage.user', 'readByUsers'])
                         ->where('chat_room_id', $chatRoom->id)
                         ->findOrFail($id);

        // Mark as read
        $message->markAsRead($user);

        return response()->json($message);
    }

    /**
     * Update the specified message
     */
    public function update(Request $request, string $chatRoomId, string $id): JsonResponse
    {
        $user = $request->user();
        $chatRoom = ChatRoom::findOrFail($chatRoomId);
        $message = Message::where('chat_room_id', $chatRoom->id)->findOrFail($id);

        // Check if user is the message sender
        if ($message->user_id !== $user->id) {
            return response()->json([
                'message' => 'Access denied. You can only edit your own messages.'
            ], 403);
        }

        // Check if message is not too old (e.g., 15 minutes)
        if ($message->created_at->diffInMinutes(now()) > 15) {
            return response()->json([
                'message' => 'Message is too old to edit.'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'content' => 'required|string|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $message->update([
            'content' => $request->content,
        ]);

        $message->markAsEdited();

        // Log activity
        UserActivityLog::log($user, 'message_edited', "Edited message in chat room: {$chatRoom->name}", [
            'chat_room_id' => $chatRoom->id,
            'message_id' => $message->id,
        ]);

        return response()->json([
            'message' => 'Message updated successfully',
            'data' => $message->fresh(['user', 'replyToMessage.user'])
        ]);
    }

    /**
     * Remove the specified message (soft delete)
     */
    public function destroy(Request $request, string $chatRoomId, string $id): JsonResponse
    {
        $user = $request->user();
        $chatRoom = ChatRoom::findOrFail($chatRoomId);
        $message = Message::where('chat_room_id', $chatRoom->id)->findOrFail($id);

        // Check permissions (message sender or chat room admin/creator)
        $userRole = $chatRoom->getParticipantRole($user);
        $isMessageSender = $message->user_id === $user->id;
        $isRoomAdmin = in_array($userRole, ['admin']) || $chatRoom->created_by === $user->id;

        if (!$isMessageSender && !$isRoomAdmin) {
            return response()->json([
                'message' => 'Access denied. You do not have permission to delete this message.'
            ], 403);
        }

        $message->delete();

        // Log activity
        $action = $isMessageSender ? 'message_deleted_self' : 'message_deleted_admin';
        $description = $isMessageSender
            ? "Deleted own message in chat room: {$chatRoom->name}"
            : "Deleted message by {$message->user->name} in chat room: {$chatRoom->name}";

        UserActivityLog::log($user, $action, $description, [
            'chat_room_id' => $chatRoom->id,
            'message_id' => $message->id,
            'original_sender_id' => $message->user_id,
        ]);

        return response()->json([
            'message' => 'Message deleted successfully'
        ]);
    }

    /**
     * Mark message as read
     */
    public function markAsRead(Request $request, string $chatRoomId, string $id): JsonResponse
    {
        $user = $request->user();
        $chatRoom = ChatRoom::findOrFail($chatRoomId);

        // Check if user is participant
        if (!$chatRoom->isParticipant($user)) {
            return response()->json([
                'message' => 'Access denied. You are not a participant of this chat room.'
            ], 403);
        }

        $message = Message::where('chat_room_id', $chatRoom->id)->findOrFail($id);
        $message->markAsRead($user);

        return response()->json([
            'message' => 'Message marked as read'
        ]);
    }

    /**
     * Get message read status
     */
    public function readStatus(Request $request, string $chatRoomId, string $id): JsonResponse
    {
        $user = $request->user();
        $chatRoom = ChatRoom::findOrFail($chatRoomId);

        // Check if user is participant
        if (!$chatRoom->isParticipant($user)) {
            return response()->json([
                'message' => 'Access denied. You are not a participant of this chat room.'
            ], 403);
        }

        $message = Message::with('readByUsers')->where('chat_room_id', $chatRoom->id)->findOrFail($id);

        return response()->json([
            'message_id' => $message->id,
            'read_by' => $message->readByUsers,
            'read_count' => $message->readByUsers->count(),
            'total_participants' => $chatRoom->activeParticipants->count(),
        ]);
    }

    /**
     * Search messages in chat room
     */
    public function search(Request $request, string $chatRoomId): JsonResponse
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
            'query' => 'required|string|min:2',
            'type' => 'sometimes|in:text,image,audio,file',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $query = $chatRoom->messages()->with(['user']);

        // Search in content
        $searchTerm = $request->get('query');
        $query->where('content', 'like', "%{$searchTerm}%");

        // Filter by type if specified
        if ($request->has('type')) {
            $query->where('type', $request->get('type'));
        }

        $messages = $query->orderBy('created_at', 'desc')
                         ->paginate($request->get('per_page', 20));

        return response()->json($messages);
    }
}
