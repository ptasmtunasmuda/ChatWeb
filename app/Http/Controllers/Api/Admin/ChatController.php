<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatRoom;
use App\Models\Message;
use App\Models\User;
use App\Models\UserActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    /**
     * Get all chat rooms with statistics
     */
    public function chatRooms(Request $request): JsonResponse
    {
        $query = ChatRoom::withTrashed()
                        ->with(['creator', 'activeParticipants'])
                        ->withCount(['messages', 'activeParticipants']);

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('name', 'like', "%{$search}%");
        }

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->get('type'));
        }

        // Filter by status
        if ($request->has('status')) {
            if ($request->get('status') === 'active') {
                $query->where('is_active', true)->whereNull('deleted_at');
            } elseif ($request->get('status') === 'inactive') {
                $query->where('is_active', false)->whereNull('deleted_at');
            } elseif ($request->get('status') === 'deleted') {
                $query->whereNotNull('deleted_at');
            }
        }

        $chatRooms = $query->orderBy('created_at', 'desc')
                          ->paginate($request->get('per_page', 15));

        return response()->json($chatRooms);
    }

    /**
     * Get detailed chat room information
     */
    public function chatRoomDetails(Request $request, string $id): JsonResponse
    {
        $chatRoom = ChatRoom::withTrashed()
                          ->with(['creator', 'participants', 'messages.user'])
                          ->withCount(['messages', 'participants'])
                          ->findOrFail($id);

        return response()->json($chatRoom);
    }

    /**
     * Get all messages with admin view (including soft deleted)
     */
    public function messages(Request $request): JsonResponse
    {
        $query = Message::withTrashed()
                       ->with(['user', 'chatRoom'])
                       ->withCount('readByUsers');

        // Filter by chat room
        if ($request->has('chat_room_id')) {
            $query->where('chat_room_id', $request->get('chat_room_id'));
        }

        // Filter by user
        if ($request->has('user_id')) {
            $query->where('user_id', $request->get('user_id'));
        }

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->get('type'));
        }

        // Filter by status
        if ($request->has('status')) {
            if ($request->get('status') === 'active') {
                $query->whereNull('deleted_at');
            } elseif ($request->get('status') === 'deleted') {
                $query->whereNotNull('deleted_at');
            }
        }

        // Search in content
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('content', 'like', "%{$search}%");
        }

        $messages = $query->orderBy('created_at', 'desc')
                         ->paginate($request->get('per_page', 20));

        return response()->json($messages);
    }

    /**
     * Get message details with read status
     */
    public function messageDetails(Request $request, string $id): JsonResponse
    {
        $message = Message::withTrashed()
                         ->with(['user', 'chatRoom', 'readByUsers', 'replyToMessage.user'])
                         ->findOrFail($id);

        return response()->json($message);
    }

    /**
     * Force delete message (permanent deletion)
     */
    public function forceDeleteMessage(Request $request, string $id): JsonResponse
    {
        $message = Message::withTrashed()->findOrFail($id);

        // Log admin activity
        UserActivityLog::log($request->user(), 'admin_message_force_deleted',
            "Force deleted message by {$message->user->name} in chat room: {$message->chatRoom->name}", [
            'message_id' => $message->id,
            'original_sender_id' => $message->user_id,
            'chat_room_id' => $message->chat_room_id,
        ]);

        $message->forceDelete();

        return response()->json([
            'message' => 'Message permanently deleted'
        ]);
    }

    /**
     * Restore soft deleted message
     */
    public function restoreMessage(Request $request, string $id): JsonResponse
    {
        $message = Message::withTrashed()->findOrFail($id);

        if (!$message->trashed()) {
            return response()->json([
                'message' => 'Message is not deleted'
            ], 422);
        }

        $message->restore();

        // Log admin activity
        UserActivityLog::log($request->user(), 'admin_message_restored',
            "Restored message by {$message->user->name} in chat room: {$message->chatRoom->name}", [
            'message_id' => $message->id,
            'original_sender_id' => $message->user_id,
            'chat_room_id' => $message->chat_room_id,
        ]);

        return response()->json([
            'message' => 'Message restored successfully',
            'data' => $message
        ]);
    }

    /**
     * Get chat statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $stats = [
            'total_chat_rooms' => ChatRoom::count(),
            'active_chat_rooms' => ChatRoom::where('is_active', true)->count(),
            'total_messages' => Message::count(),
            'messages_today' => Message::whereDate('created_at', today())->count(),
            'total_participants' => DB::table('chat_room_participants')
                                     ->where('is_active', true)
                                     ->distinct('user_id')
                                     ->count(),
            'deleted_messages' => Message::onlyTrashed()->count(),
        ];

        // Messages by type
        $messagesByType = Message::select('type', DB::raw('count(*) as count'))
                                ->groupBy('type')
                                ->pluck('count', 'type');

        // Chat rooms by type
        $chatRoomsByType = ChatRoom::select('type', DB::raw('count(*) as count'))
                                  ->groupBy('type')
                                  ->pluck('count', 'type');

        // Recent activity
        $recentActivity = UserActivityLog::with('user')
                                        ->whereIn('action', [
                                            'chat_room_created', 'message_sent',
                                            'participant_added', 'participant_removed'
                                        ])
                                        ->orderBy('created_at', 'desc')
                                        ->limit(10)
                                        ->get();

        return response()->json([
            'statistics' => $stats,
            'messages_by_type' => $messagesByType,
            'chat_rooms_by_type' => $chatRoomsByType,
            'recent_activity' => $recentActivity,
        ]);
    }

    /**
     * Force delete chat room (permanent deletion)
     */
    public function forceDeleteChatRoom(Request $request, string $id): JsonResponse
    {
        $chatRoom = ChatRoom::withTrashed()->findOrFail($id);

        // Log admin activity
        UserActivityLog::log($request->user(), 'admin_chat_room_force_deleted',
            "Force deleted chat room: {$chatRoom->name}", [
            'chat_room_id' => $chatRoom->id,
            'creator_id' => $chatRoom->created_by,
        ]);

        $chatRoom->forceDelete();

        return response()->json([
            'message' => 'Chat room permanently deleted'
        ]);
    }

    /**
     * Restore soft deleted chat room
     */
    public function restoreChatRoom(Request $request, string $id): JsonResponse
    {
        $chatRoom = ChatRoom::withTrashed()->findOrFail($id);

        if (!$chatRoom->trashed()) {
            return response()->json([
                'message' => 'Chat room is not deleted'
            ], 422);
        }

        $chatRoom->restore();

        // Log admin activity
        UserActivityLog::log($request->user(), 'admin_chat_room_restored',
            "Restored chat room: {$chatRoom->name}", [
            'chat_room_id' => $chatRoom->id,
            'creator_id' => $chatRoom->created_by,
        ]);

        return response()->json([
            'message' => 'Chat room restored successfully',
            'data' => $chatRoom
        ]);
    }
}
