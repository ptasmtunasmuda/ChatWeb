<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatRoom;
use App\Models\User;
use App\Models\UserActivityLog;
use App\Events\UserJoinedChatRoom;
use App\Events\UserLeftChatRoom;
use App\Events\ChatRoomCreated;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ChatRoomController extends Controller
{
    /**
     * Display a listing of chat rooms for the authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = $user->chatRooms()
                     ->wherePivot('is_active', true)
                     ->where('chat_rooms.is_active', true)
                     ->with(['latestMessage.user', 'activeParticipants'])
                     ->withCount('activeParticipants');

        // Filter by type if specified
        if ($request->has('type')) {
            $query->where('type', $request->get('type'));
        }

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('name', 'like', "%{$search}%");
        }

        $chatRooms = $query->orderBy('updated_at', 'desc')
                          ->paginate($request->get('per_page', 20));

        return response()->json($chatRooms);
    }

    /**
     * Store a newly created chat room
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:private,group',
            'participants' => 'nullable|array',
            'participants.*' => 'exists:users,id',
            'settings' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $user = $request->user();

            // For private chat, check if chat already exists
            if ($request->type === 'private' && $request->has('participants')) {
                $participantId = $request->participants[0];

                // Check if private chat already exists between these users
                $existingChat = ChatRoom::where('type', 'private')
                    ->whereHas('participants', function($q) use ($user) {
                        $q->where('user_id', $user->id);
                    })
                    ->whereHas('participants', function($q) use ($participantId) {
                        $q->where('user_id', $participantId);
                    })
                    ->first();

                if ($existingChat) {
                    DB::commit();
                    $existingChat->load(['creator', 'activeParticipants']);

                    // Log existing chat found
                    \Log::info('Existing private chat found:', [
                        'chat_room_id' => $existingChat->id,
                        'participants' => $existingChat->activeParticipants->pluck('id', 'name')->toArray()
                    ]);

                    // Broadcast existing chat to ensure both users have it in their list
                    \Log::info('Broadcasting existing ChatRoomCreated event', ['chat_room_id' => $existingChat->id]);
                    broadcast(new ChatRoomCreated($existingChat));

                    return response()->json([
                        'message' => 'Private chat already exists',
                        'data' => $existingChat
                    ]);
                }
            }

            // Generate chat room name
            $chatRoomName = $request->name;
            if (!$chatRoomName && $request->type === 'private' && $request->has('participants')) {
                $participant = User::find($request->participants[0]);
                // Use just the participant's name for private chats
                $chatRoomName = $participant->name;
            } elseif (!$chatRoomName) {
                $chatRoomName = $request->type === 'private' ? 'Private Chat' : 'Group Chat';
            }

            // Create chat room
            $chatRoom = ChatRoom::create([
                'name' => $chatRoomName,
                'description' => $request->description,
                'type' => $request->type,
                'created_by' => $user->id,
                'settings' => $request->settings ?? [],
            ]);

            // Add creator as admin participant
            $chatRoom->addParticipant($user, 'admin');

            // Add other participants if specified
            if ($request->has('participants')) {
                foreach ($request->participants as $participantId) {
                    if ($participantId != $user->id) {
                        $participant = User::find($participantId);
                        if ($participant) {
                            $chatRoom->addParticipant($participant, 'member');
                        }
                    }
                }
            }

            // Log activity
            UserActivityLog::log($user, 'chat_room_created', "Created chat room: {$chatRoom->name}", [
                'chat_room_id' => $chatRoom->id,
                'chat_room_type' => $chatRoom->type,
            ]);

            DB::commit();

            // Load relationships for response
            $chatRoom->load(['creator', 'activeParticipants']);

            // Log participants for debugging
            \Log::info('ChatRoom created with participants:', [
                'chat_room_id' => $chatRoom->id,
                'participants' => $chatRoom->activeParticipants->pluck('id', 'name')->toArray()
            ]);

            // Broadcast chat room created event
            \Log::info('About to broadcast ChatRoomCreated event', ['chat_room_id' => $chatRoom->id]);
            $broadcastResult = broadcast(new ChatRoomCreated($chatRoom));
            \Log::info('ChatRoomCreated broadcast completed', ['result' => $broadcastResult]);

            return response()->json([
                'message' => 'Chat room created successfully',
                'data' => $chatRoom
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Failed to create chat room',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified chat room
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $user = $request->user();

        $chatRoom = ChatRoom::with(['creator', 'activeParticipants', 'messages' => function ($query) {
            $query->with('user')->latest()->limit(50);
        }])->findOrFail($id);

        // Check if user is participant
        if (!$chatRoom->isParticipant($user)) {
            return response()->json([
                'message' => 'Access denied. You are not a participant of this chat room.'
            ], 403);
        }

        // Log activity
        UserActivityLog::log($user, 'chat_room_viewed', "Viewed chat room: {$chatRoom->name}", [
            'chat_room_id' => $chatRoom->id,
        ]);

        return response()->json($chatRoom);
    }

    /**
     * Update the specified chat room
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $chatRoom = ChatRoom::findOrFail($id);

        // Check if user has permission to update (admin or creator)
        $userRole = $chatRoom->getParticipantRole($user);
        if (!in_array($userRole, ['admin']) && $chatRoom->created_by !== $user->id) {
            return response()->json([
                'message' => 'Access denied. You do not have permission to update this chat room.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string|max:1000',
            'settings' => 'sometimes|nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $updateData = $request->only(['name', 'description', 'settings']);
        $chatRoom->update($updateData);

        // Log activity
        UserActivityLog::log($user, 'chat_room_updated', "Updated chat room: {$chatRoom->name}", [
            'chat_room_id' => $chatRoom->id,
            'changes' => array_keys($updateData),
        ]);

        return response()->json([
            'message' => 'Chat room updated successfully',
            'chat_room' => $chatRoom->fresh(['creator', 'activeParticipants'])
        ]);
    }

    /**
     * Remove the specified chat room (soft delete)
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $chatRoom = ChatRoom::findOrFail($id);

        // Only creator can delete the chat room
        if ($chatRoom->created_by !== $user->id) {
            return response()->json([
                'message' => 'Access denied. Only the creator can delete this chat room.'
            ], 403);
        }

        $chatRoom->delete();

        // Log activity
        UserActivityLog::log($user, 'chat_room_deleted', "Deleted chat room: {$chatRoom->name}", [
            'chat_room_id' => $chatRoom->id,
        ]);

        return response()->json([
            'message' => 'Chat room deleted successfully'
        ]);
    }

    /**
     * Add participant to chat room
     */
    public function addParticipant(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $chatRoom = ChatRoom::findOrFail($id);

        // Check if user has permission (admin or creator)
        $userRole = $chatRoom->getParticipantRole($user);
        if (!in_array($userRole, ['admin']) && $chatRoom->created_by !== $user->id) {
            return response()->json([
                'message' => 'Access denied. You do not have permission to add participants.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'role' => 'sometimes|in:member,moderator,admin',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $participantUser = User::findOrFail($request->user_id);

        // Check if user is already a participant
        if ($chatRoom->isParticipant($participantUser)) {
            return response()->json([
                'message' => 'User is already a participant of this chat room.'
            ], 422);
        }

        $role = $request->get('role', 'member');
        $chatRoom->addParticipant($participantUser, $role);

        // Broadcast user joined event
        broadcast(new UserJoinedChatRoom($participantUser, $chatRoom));

        // Log activity
        UserActivityLog::log($user, 'participant_added', "Added {$participantUser->name} to chat room: {$chatRoom->name}", [
            'chat_room_id' => $chatRoom->id,
            'participant_id' => $participantUser->id,
            'role' => $role,
        ]);

        return response()->json([
            'message' => 'Participant added successfully',
            'participant' => $participantUser
        ]);
    }

    /**
     * Remove participant from chat room
     */
    public function removeParticipant(Request $request, string $id, string $userId): JsonResponse
    {
        $user = $request->user();
        $chatRoom = ChatRoom::findOrFail($id);
        $participantUser = User::findOrFail($userId);

        // Check permissions
        $userRole = $chatRoom->getParticipantRole($user);
        $isCreator = $chatRoom->created_by === $user->id;
        $isSelf = $user->id === $participantUser->id;

        if (!$isSelf && !in_array($userRole, ['admin']) && !$isCreator) {
            return response()->json([
                'message' => 'Access denied. You do not have permission to remove participants.'
            ], 403);
        }

        // Cannot remove creator
        if ($participantUser->id === $chatRoom->created_by) {
            return response()->json([
                'message' => 'Cannot remove the creator of the chat room.'
            ], 422);
        }

        $chatRoom->removeParticipant($participantUser);

        // Broadcast user left event
        broadcast(new UserLeftChatRoom($participantUser, $chatRoom));

        // Log activity
        $action = $isSelf ? 'left_chat_room' : 'participant_removed';
        $description = $isSelf
            ? "Left chat room: {$chatRoom->name}"
            : "Removed {$participantUser->name} from chat room: {$chatRoom->name}";

        UserActivityLog::log($user, $action, $description, [
            'chat_room_id' => $chatRoom->id,
            'participant_id' => $participantUser->id,
        ]);

        return response()->json([
            'message' => $isSelf ? 'Left chat room successfully' : 'Participant removed successfully'
        ]);
    }
}
