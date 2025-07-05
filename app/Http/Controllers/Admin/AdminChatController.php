<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatRoom;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Events\ChatRoomCreated;
use App\Events\ChatRoomUpdated;
use App\Events\ChatRoomDeleted;

class AdminChatController extends Controller
{
    // Middleware akan ditangani di routes, tidak perlu di constructor

    public function index(Request $request)
    {
        $query = ChatRoom::with(['creator:id,name'])
            ->withCount(['messages', 'participants']);

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by type
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $perPage = $request->get('per_page', 15);
        $chatRooms = $query->paginate($perPage);

        // Add additional data
        $chatRooms->getCollection()->transform(function ($chatRoom) {
            $chatRoom->latest_message = Message::where('chat_room_id', $chatRoom->id)
                ->with('user:id,name')
                ->latest()
                ->first();

            $chatRoom->messages_today = Message::where('chat_room_id', $chatRoom->id)
                ->whereDate('created_at', Carbon::today())
                ->count();

            return $chatRoom;
        });

        // Log for debugging
        \Log::info('Admin Chat Rooms API Response:', [
            'total' => $chatRooms->total(),
            'count' => $chatRooms->count(),
            'current_page' => $chatRooms->currentPage(),
            'per_page' => $chatRooms->perPage(),
        ]);

        return response()->json($chatRooms);
    }

    public function show($id)
    {
        $chatRoom = ChatRoom::with(['creator:id,name', 'participants'])
            ->withCount(['messages'])
            ->findOrFail($id);

        // Get chat room statistics
        $stats = [
            'total_messages' => Message::where('chat_room_id', $id)->count(),
            'deleted_messages' => Message::onlyTrashed()->where('chat_room_id', $id)->count(),
            'messages_today' => Message::where('chat_room_id', $id)->whereDate('created_at', Carbon::today())->count(),
            'messages_this_week' => Message::where('chat_room_id', $id)->whereBetween('created_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ])->count(),
            'messages_this_month' => Message::where('chat_room_id', $id)->whereMonth('created_at', Carbon::now()->month)->count(),
            'active_participants' => $chatRoom->participants()->where('is_active', true)->count(),
        ];

        // Get recent messages
        $recentMessages = Message::with(['user:id,name'])
            ->where('chat_room_id', $id)
            ->latest()
            ->take(20)
            ->get();

        // Get deleted messages
        $deletedMessages = Message::onlyTrashed()
            ->with(['user:id,name'])
            ->where('chat_room_id', $id)
            ->latest('deleted_at')
            ->take(10)
            ->get();

        // Get message activity over time (last 30 days)
        $messageActivity = collect();
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $count = Message::where('chat_room_id', $id)->whereDate('created_at', $date)->count();
            $messageActivity->push([
                'date' => $date->format('Y-m-d'),
                'count' => $count,
                'label' => $date->format('M j')
            ]);
        }

        return response()->json([
            'chat_room' => $chatRoom,
            'stats' => $stats,
            'recent_messages' => $recentMessages,
            'deleted_messages' => $deletedMessages,
            'message_activity' => $messageActivity
        ]);
    }

    public function getMessages(Request $request, $id)
    {
        $chatRoom = ChatRoom::findOrFail($id);

        $query = Message::with(['user:id,name'])
            ->where('chat_room_id', $id);

        // Include deleted messages if requested
        if ($request->boolean('include_deleted')) {
            $query->withTrashed();
        }

        // Search in message content
        if ($request->has('search') && $request->search) {
            $query->where('content', 'like', "%{$request->search}%");
        }

        // Filter by message type
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        // Filter by user
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $perPage = $request->get('per_page', 50);
        $messages = $query->latest()->paginate($perPage);

        return response()->json($messages);
    }

    public function deleteMessage($chatRoomId, $messageId)
    {
        $message = Message::where('chat_room_id', $chatRoomId)
            ->findOrFail($messageId);

        $message->delete();

        return response()->json([
            'message' => 'Message deleted successfully'
        ]);
    }

    public function restoreMessage($chatRoomId, $messageId)
    {
        $message = Message::onlyTrashed()
            ->where('chat_room_id', $chatRoomId)
            ->findOrFail($messageId);

        $message->restore();

        return response()->json([
            'message' => 'Message restored successfully'
        ]);
    }

    public function forceDeleteMessage($chatRoomId, $messageId)
    {
        $message = Message::withTrashed()
            ->where('chat_room_id', $chatRoomId)
            ->findOrFail($messageId);

        $message->forceDelete();

        return response()->json([
            'message' => 'Message permanently deleted'
        ]);
    }

    public function updateChatRoom(Request $request, $id)
    {
        $chatRoom = ChatRoom::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string|max:1000',
            'type' => 'sometimes|required|in:private,group',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $chatRoom->update($request->only(['name', 'description', 'type', 'is_active']));

        // Broadcast the update
        broadcast(new ChatRoomUpdated($chatRoom))->toOthers();

        return response()->json([
            'message' => 'Chat room updated successfully',
            'chat_room' => $chatRoom->fresh()
        ]);
    }

    public function deleteChatRoom($id)
    {
        $chatRoom = ChatRoom::findOrFail($id);
        $chatRoomName = $chatRoom->name;

        $chatRoom->delete();

        // Broadcast the deletion
        broadcast(new ChatRoomDeleted($id, $chatRoomName))->toOthers();

        return response()->json([
            'message' => 'Chat room deleted successfully'
        ]);
    }

    public function restoreChatRoom($id)
    {
        $chatRoom = ChatRoom::onlyTrashed()->findOrFail($id);
        $chatRoom->restore();

        return response()->json([
            'message' => 'Chat room restored successfully',
            'chat_room' => $chatRoom
        ]);
    }

    public function getDeletedChatRooms(Request $request)
    {
        $query = ChatRoom::onlyTrashed()
            ->with(['creator:id,name'])
            ->withCount(['messages']);

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $perPage = $request->get('per_page', 15);
        $deletedChatRooms = $query->orderBy('deleted_at', 'desc')->paginate($perPage);

        return response()->json($deletedChatRooms);
    }

    public function bulkDeleteMessages(Request $request, $chatRoomId)
    {
        $validator = Validator::make($request->all(), [
            'message_ids' => 'required|array|min:1',
            'message_ids.*' => 'integer|exists:messages,id',
            'permanent' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $messageIds = $request->message_ids;
        $permanent = $request->boolean('permanent');

        if ($permanent) {
            $affectedCount = Message::withTrashed()
                ->where('chat_room_id', $chatRoomId)
                ->whereIn('id', $messageIds)
                ->forceDelete();
        } else {
            $affectedCount = Message::where('chat_room_id', $chatRoomId)
                ->whereIn('id', $messageIds)
                ->delete();
        }

        return response()->json([
            'message' => 'Bulk delete completed successfully',
            'affected_count' => $affectedCount
        ]);
    }

    public function getChatRoomAnalytics($id)
    {
        $chatRoom = ChatRoom::findOrFail($id);

        // Message frequency by hour
        $hourlyActivity = collect();
        for ($hour = 0; $hour < 24; $hour++) {
            $count = Message::where('chat_room_id', $id)
                ->whereRaw('HOUR(created_at) = ?', [$hour])
                ->count();
            $hourlyActivity->push([
                'hour' => $hour,
                'count' => $count,
                'label' => sprintf('%02d:00', $hour)
            ]);
        }

        // Top participants by message count
        $topParticipants = Message::where('chat_room_id', $id)
            ->select('user_id')
            ->selectRaw('COUNT(*) as message_count')
            ->with('user:id,name')
            ->groupBy('user_id')
            ->orderByDesc('message_count')
            ->take(10)
            ->get();

        // Message types distribution
        $messageTypes = Message::where('chat_room_id', $id)
            ->select('type')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('type')
            ->get();

        return response()->json([
            'hourly_activity' => $hourlyActivity,
            'top_participants' => $topParticipants,
            'message_types' => $messageTypes
        ]);
    }

    public function getAllMessages(Request $request)
    {
        \Log::info('getAllMessages called with params:', $request->all());

        $query = Message::with(['user:id,name', 'chatRoom:id,name']);

        // Show only deleted messages if requested
        if ($request->boolean('show_deleted_only')) {
            \Log::info('Filtering for deleted messages only');
            $query->onlyTrashed();
        } else {
            \Log::info('Showing active messages');
            // Include deleted messages if requested (for backward compatibility)
            if ($request->boolean('include_deleted')) {
                $query->withTrashed();
            }
        }

        // Search in message content
        if ($request->has('search') && $request->search) {
            $query->where('content', 'like', "%{$request->search}%");
        }

        // Filter by message type
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        // Filter by user
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by chat room
        if ($request->has('chat_room_id') && $request->chat_room_id) {
            $query->where('chat_room_id', $request->chat_room_id);
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $perPage = $request->get('per_page', 50);
        $messages = $query->latest()->paginate($perPage);

        return response()->json($messages);
    }

    public function getDeletedMessages(Request $request)
    {
        $query = Message::onlyTrashed()->with(['user:id,name', 'chatRoom:id,name']);

        // Search in message content
        if ($request->has('search') && $request->search) {
            $query->where('content', 'like', "%{$request->search}%");
        }

        // Filter by message type
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        // Filter by user
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by chat room
        if ($request->has('chat_room_id') && $request->chat_room_id) {
            $query->where('chat_room_id', $request->chat_room_id);
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('deleted_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('deleted_at', '<=', $request->date_to);
        }

        $perPage = $request->get('per_page', 50);
        $messages = $query->latest('deleted_at')->paginate($perPage);

        return response()->json($messages);
    }

    public function forceDeleteChatRoom($id)
    {
        $chatRoom = ChatRoom::withTrashed()->findOrFail($id);
        $chatRoom->forceDelete();

        return response()->json([
            'message' => 'Chat room permanently deleted'
        ]);
    }

    public function bulkAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:activate,deactivate,delete,restore',
            'room_ids' => 'required|array|min:1',
            'room_ids.*' => 'integer|exists:chat_rooms,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $action = $request->action;
        $roomIds = $request->room_ids;
        $affectedCount = 0;

        switch ($action) {
            case 'activate':
                $affectedCount = ChatRoom::whereIn('id', $roomIds)->update(['is_active' => true]);
                break;

            case 'deactivate':
                $affectedCount = ChatRoom::whereIn('id', $roomIds)->update(['is_active' => false]);
                break;

            case 'delete':
                $rooms = ChatRoom::whereIn('id', $roomIds)->get();
                foreach ($rooms as $room) {
                    $room->delete();
                    // Broadcast deletion
                    broadcast(new ChatRoomDeleted($room->id, $room->name))->toOthers();
                }
                $affectedCount = $rooms->count();
                break;

            case 'restore':
                $affectedCount = ChatRoom::onlyTrashed()->whereIn('id', $roomIds)->restore();
                break;
        }

        return response()->json([
            'message' => "Bulk {$action} completed successfully",
            'affected_count' => $affectedCount
        ]);
    }

    public function getChatActivityStats(Request $request)
    {
        try {
            // Get last 30 days of message activity
            $endDate = Carbon::now();
            $startDate = Carbon::now()->subDays(29);

            $dailyActivity = [];

            // Generate data for each day in the last 30 days
            for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
                $messageCount = Message::whereDate('created_at', $date->format('Y-m-d'))
                    ->whereNull('deleted_at')
                    ->count();

                $dailyActivity[] = [
                    'date' => $date->format('Y-m-d'),
                    'message_count' => $messageCount,
                    'formatted_date' => $date->format('M j')
                ];
            }

            // Additional stats
            $totalMessages = Message::whereNull('deleted_at')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();

            $totalRooms = ChatRoom::whereNull('deleted_at')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();

            $activeUsers = User::whereHas('messages', function($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            })->count();

            return response()->json([
                'daily_activity' => $dailyActivity,
                'summary' => [
                    'total_messages' => $totalMessages,
                    'total_rooms' => $totalRooms,
                    'active_users' => $activeUsers,
                    'period' => [
                        'start_date' => $startDate->format('Y-m-d'),
                        'end_date' => $endDate->format('Y-m-d'),
                        'days' => 30
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching chat activity stats: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to fetch chat activity stats',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
