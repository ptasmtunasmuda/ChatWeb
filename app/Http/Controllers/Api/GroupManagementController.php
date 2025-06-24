<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatRoom;
use App\Models\User;
use App\Events\UserJoinedGroup;
use App\Events\UserLeftGroup;
use App\Events\UserRoleChanged;
use App\Events\GroupInfoUpdated;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class GroupManagementController extends Controller
{
    /**
     * Get group information with members
     */
    public function getGroupInfo(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        
        $group = ChatRoom::where('id', $id)
                         ->where('type', 'group')
                         ->with([
                             'creator',
                             'activeParticipants' => function($query) {
                                 $query->orderBy('chat_room_participants.role', 'desc')
                                       ->orderBy('chat_room_participants.joined_at', 'asc');
                             }
                         ])
                         ->first();

        if (!$group) {
            return response()->json(['message' => 'Group not found'], 404);
        }

        // Check if user is participant
        if (!$group->isParticipant($user)) {
            return response()->json(['message' => 'You are not a member of this group'], 403);
        }

        // Get user's role in the group
        $userRole = $group->getParticipantRole($user);

        // Transform participants data
        $members = $group->activeParticipants->map(function ($member) {
            return [
                'id' => $member->id,
                'name' => $member->name,
                'email' => $member->email,
                'avatar' => $member->avatar,
                'role' => $member->pivot->role,
                'joined_at' => $member->pivot->joined_at,
                'is_online' => $member->is_online ?? false,
                'last_seen' => $member->last_seen,
            ];
        });

        $groupData = [
            'id' => $group->id,
            'name' => $group->name,
            'description' => $group->description,
            'avatar' => $group->avatar,
            'type' => $group->type,
            'created_by' => $group->created_by,
            'creator' => $group->creator,
            'created_at' => $group->created_at,
            'members_count' => $members->count(),
            'members' => $members,
            'user_role' => $userRole,
            'is_admin' => in_array($userRole, ['admin']),
            'can_manage' => in_array($userRole, ['admin']) || $group->created_by === $user->id,
        ];

        return response()->json([
            'success' => true,
            'group' => $groupData
        ]);
    }

    /**
     * Add member to group (Admin only)
     */
    public function addMember(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'role' => 'sometimes|in:member,admin,moderator'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $group = ChatRoom::where('id', $id)
                         ->where('type', 'group')
                         ->first();

        if (!$group) {
            return response()->json(['message' => 'Group not found'], 404);
        }

        // Check if user is admin or creator
        $userRole = $group->getParticipantRole($user);
        if (!in_array($userRole, ['admin']) && $group->created_by !== $user->id) {
            return response()->json(['message' => 'Only admins can add members'], 403);
        }

        $newMember = User::find($request->user_id);
        if (!$newMember) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Check if user is already a member
        if ($group->isParticipant($newMember)) {
            return response()->json(['message' => 'User is already a member of this group'], 400);
        }

        try {
            DB::beginTransaction();

            // Add participant to group
            $role = $request->get('role', 'member');
            $result = $group->addParticipant($newMember, $role);

            // Check if the user was actually added or was already a member
            if ($result === false) {
                DB::rollback();
                return response()->json([
                    'success' => false,
                    'message' => 'User is already a member of this group'
                ], 400);
            }

            // Determine message based on result
            $actionMessage = $result === 'reactivated' 
                ? $user->name . ' re-added ' . $newMember->name . ' to the group'
                : $user->name . ' added ' . $newMember->name . ' to the group';

            // Create system message
            $systemMessage = $group->messages()->create([
                'user_id' => null, // System message
                'content' => $actionMessage,
                'is_system' => true,
                'system_type' => 'user_added',
                'system_data' => [
                    'added_user_id' => $newMember->id,
                    'added_user_name' => $newMember->name,
                    'admin_user_id' => $user->id,
                    'admin_user_name' => $user->name,
                    'role' => $role,
                    'action' => $result === 'reactivated' ? 'reactivated' : 'added'
                ]
            ]);

            // Load fresh data for broadcast
            $group->load(['activeParticipants', 'creator']);
            
            $memberData = [
                'id' => $newMember->id,
                'name' => $newMember->name,
                'email' => $newMember->email,
                'avatar' => $newMember->avatar,
                'role' => $role,
                'joined_at' => now(),
                'is_online' => $newMember->is_online ?? false,
                'last_seen' => $newMember->last_seen,
            ];

            DB::commit();

            // Broadcast real-time event
            broadcast(new UserJoinedGroup($group, $newMember, $memberData, $user))->toOthers();

            return response()->json([
                'success' => true,
                'message' => 'Member added successfully',
                'member' => $memberData
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to add member: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove member from group (Admin only)
     */
    public function removeMember(Request $request, $id, $userId): JsonResponse
    {
        $user = $request->user();
        
        $group = ChatRoom::where('id', $id)
                         ->where('type', 'group')
                         ->first();

        if (!$group) {
            return response()->json(['message' => 'Group not found'], 404);
        }

        // Check if user is admin or creator
        $userRole = $group->getParticipantRole($user);
        if (!in_array($userRole, ['admin']) && $group->created_by !== $user->id) {
            return response()->json(['message' => 'Only admins can remove members'], 403);
        }

        $memberToRemove = User::find($userId);
        if (!$memberToRemove) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Check if user is a member
        if (!$group->isParticipant($memberToRemove)) {
            return response()->json(['message' => 'User is not a member of this group'], 400);
        }

        // Prevent removing the group creator
        if ($group->created_by === $memberToRemove->id) {
            return response()->json(['message' => 'Cannot remove the group creator'], 400);
        }

        // Prevent self-removal (use leave group instead)
        if ($memberToRemove->id === $user->id) {
            return response()->json(['message' => 'Use leave group to remove yourself'], 400);
        }

        try {
            DB::beginTransaction();

            // Remove participant from group
            $group->removeParticipant($memberToRemove);

            // Create system message
            $systemMessage = $group->messages()->create([
                'user_id' => null, // System message
                'content' => $user->name . ' removed ' . $memberToRemove->name . ' from the group',
                'is_system' => true,
                'system_type' => 'user_removed',
                'system_data' => [
                    'removed_user_id' => $memberToRemove->id,
                    'removed_user_name' => $memberToRemove->name,
                    'admin_user_id' => $user->id,
                    'admin_user_name' => $user->name,
                    'action' => 'removed'
                ]
            ]);

            DB::commit();

            // Broadcast real-time event
            broadcast(new UserLeftGroup($group, $memberToRemove, $user, 'removed'))->toOthers();

            return response()->json([
                'success' => true,
                'message' => 'Member removed successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove member: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Change member role (Admin only)
     */
    public function changeMemberRole(Request $request, $id, $userId): JsonResponse
    {
        $user = $request->user();
        
        $validator = Validator::make($request->all(), [
            'role' => 'required|in:member,admin,moderator'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $group = ChatRoom::where('id', $id)
                         ->where('type', 'group')
                         ->first();

        if (!$group) {
            return response()->json(['message' => 'Group not found'], 404);
        }

        // Check if user is admin or creator
        $userRole = $group->getParticipantRole($user);
        if (!in_array($userRole, ['admin']) && $group->created_by !== $user->id) {
            return response()->json(['message' => 'Only admins can change member roles'], 403);
        }

        $memberToChange = User::find($userId);
        if (!$memberToChange) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Check if user is a member
        if (!$group->isParticipant($memberToChange)) {
            return response()->json(['message' => 'User is not a member of this group'], 400);
        }

        // Prevent changing creator role
        if ($group->created_by === $memberToChange->id) {
            return response()->json(['message' => 'Cannot change the group creator role'], 400);
        }

        try {
            DB::beginTransaction();

            $newRole = $request->role;
            $oldRole = $group->getParticipantRole($memberToChange);

            // Update participant role
            $group->participants()->updateExistingPivot($memberToChange->id, [
                'role' => $newRole,
                'updated_at' => now()
            ]);

            DB::commit();

            // Broadcast real-time event
            broadcast(new UserRoleChanged($group, $memberToChange, $oldRole, $newRole, $user))->toOthers();

            return response()->json([
                'success' => true,
                'message' => 'Member role updated successfully',
                'user_id' => $memberToChange->id,
                'old_role' => $oldRole,
                'new_role' => $newRole
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update member role: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Leave group
     */
    public function leaveGroup(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        
        $group = ChatRoom::where('id', $id)
                         ->where('type', 'group')
                         ->first();

        if (!$group) {
            return response()->json(['message' => 'Group not found'], 404);
        }

        // Check if user is a member
        if (!$group->isParticipant($user)) {
            return response()->json(['message' => 'You are not a member of this group'], 400);
        }

        // Prevent creator from leaving (they need to transfer ownership or delete group)
        if ($group->created_by === $user->id) {
            return response()->json(['message' => 'Group creator cannot leave. Transfer ownership or delete the group.'], 400);
        }

        try {
            DB::beginTransaction();

            // Remove participant from group
            $group->removeParticipant($user);

            // Create system message
            $systemMessage = $group->messages()->create([
                'user_id' => null, // System message
                'content' => $user->name . ' left the group',
                'is_system' => true,
                'system_type' => 'user_left',
                'system_data' => [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'action' => 'left'
                ]
            ]);

            DB::commit();

            // Broadcast real-time event
            broadcast(new UserLeftGroup($group, $user, $user, 'left'))->toOthers();

            return response()->json([
                'success' => true,
                'message' => 'You have left the group successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to leave group: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update group info (Admin only)
     */
    public function updateGroupInfo(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $group = ChatRoom::where('id', $id)
                         ->where('type', 'group')
                         ->first();

        if (!$group) {
            return response()->json(['message' => 'Group not found'], 404);
        }

        // Check if user is admin or creator
        $userRole = $group->getParticipantRole($user);
        if (!in_array($userRole, ['admin']) && $group->created_by !== $user->id) {
            return response()->json(['message' => 'Only admins can update group info'], 403);
        }

        try {
            DB::beginTransaction();

            $oldData = [
                'name' => $group->name,
                'description' => $group->description
            ];

            // Update group information
            if ($request->has('name')) {
                $group->name = $request->name;
            }
            
            if ($request->has('description')) {
                $group->description = $request->description;
            }

            $group->save();

            $newData = [
                'name' => $group->name,
                'description' => $group->description
            ];

            DB::commit();

            // Broadcast real-time event
            broadcast(new GroupInfoUpdated($group, $oldData, $newData, $user))->toOthers();

            return response()->json([
                'success' => true,
                'message' => 'Group information updated successfully',
                'group' => [
                    'id' => $group->id,
                    'name' => $group->name,
                    'description' => $group->description
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update group info: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload group avatar (Admin only)
     */
    public function uploadAvatar(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        
        // Debug logging
        \Log::info('Upload avatar request received:', [
            'group_id' => $id,
            'user_id' => $user->id,
            'has_file' => $request->hasFile('avatar'),
            'all_files' => $request->allFiles(),
            'request_method' => $request->method(),
            'content_type' => $request->header('Content-Type'),
            'content_length' => $request->header('Content-Length'),
            'request_size' => strlen($request->getContent()),
            'php_input_size' => strlen(file_get_contents('php://input')),
            'post_max_size' => ini_get('post_max_size'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'max_file_uploads' => ini_get('max_file_uploads'),
        ]);
        
        $validator = Validator::make($request->all(), [
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120' // 5MB max
        ]);

        if ($validator->fails()) {
            \Log::error('Avatar upload validation failed:', $validator->errors()->toArray());
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $group = ChatRoom::where('id', $id)
                         ->where('type', 'group')
                         ->first();

        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Group not found'
            ], 404);
        }

        // Check if user is admin or creator
        $userRole = $group->getParticipantRole($user);
        if (!in_array($userRole, ['admin']) && $group->created_by !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Only admins can update group avatar'
            ], 403);
        }

        try {
            DB::beginTransaction();

            // Handle file upload with more robust error handling
            if (!$request->hasFile('avatar')) {
                throw new \Exception('No file uploaded');
            }

            $file = $request->file('avatar');
            
            if (!$file->isValid()) {
                throw new \Exception('Invalid file uploaded');
            }
            
            // Debug file info
            \Log::info('File info:', [
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'path' => $file->getPathname(),
                'is_valid' => $file->isValid()
            ]);
            
            // Get file extension safely using Laravel's built-in method
            $extension = $file->extension();
            
            if (empty($extension)) {
                // Fallback: get extension from original name
                $originalName = $file->getClientOriginalName();
                if ($originalName) {
                    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
                }
            }
            
            if (empty($extension)) {
                // Final fallback based on mime type
                $mimeType = $file->getMimeType();
                $extension = match($mimeType) {
                    'image/jpeg', 'image/jpg' => 'jpg',
                    'image/png' => 'png',
                    'image/gif' => 'gif',
                    'image/webp' => 'webp',
                    default => 'jpg'
                };
            }
            
            $fileName = 'group_' . $group->id . '_' . time() . '.' . strtolower($extension);
            
            // Store file using Laravel's storage system
            $storagePath = $file->storeAs('avatars/groups', $fileName, 'public');
            
            if (!$storagePath) {
                throw new \Exception('Failed to store file');
            }
            
            $avatarUrl = asset('storage/' . $storagePath);

            // Delete old avatar if exists
            if ($group->avatar) {
                $oldPath = str_replace(asset('storage/'), '', $group->avatar);
                $fullPath = storage_path('app/public/' . $oldPath);
                if (file_exists($fullPath)) {
                    @unlink($fullPath); // @ to suppress warnings if file doesn't exist
                }
            }

            // Update group avatar
            $group->avatar = $avatarUrl;
            $group->save();

            // Create system message
            $systemMessage = $group->messages()->create([
                'user_id' => null,
                'content' => $user->name . ' updated the group photo',
                'is_system' => true,
                'system_type' => 'group_avatar_updated',
                'system_data' => [
                    'admin_user_id' => $user->id,
                    'admin_user_name' => $user->name,
                    'action' => 'avatar_updated'
                ]
            ]);

            DB::commit();

            // Broadcast real-time event
            broadcast(new GroupInfoUpdated($group, ['avatar' => null], ['avatar' => $avatarUrl], $user))->toOthers();

            return response()->json([
                'success' => true,
                'message' => 'Group avatar updated successfully',
                'avatar_url' => $avatarUrl
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Avatar upload failed:', [
                'group_id' => $id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update group avatar: ' . $e->getMessage()
            ], 500);
        }
    }
    public function deleteGroup(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        
        $group = ChatRoom::where('id', $id)
                         ->where('type', 'group')
                         ->first();

        if (!$group) {
            return response()->json(['message' => 'Group not found'], 404);
        }

        // Only creator can delete group
        if ($group->created_by !== $user->id) {
            return response()->json(['message' => 'Only the group creator can delete this group'], 403);
        }

        try {
            DB::beginTransaction();

            // Soft delete the group
            $group->delete();

            DB::commit();

            // Broadcast real-time event to all members
            broadcast(new \App\Events\GroupDeleted($group, $user))->toOthers();

            return response()->json([
                'success' => true,
                'message' => 'Group deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete group: ' . $e->getMessage()
            ], 500);
        }
    }
}
