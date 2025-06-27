<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ChatRoom;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AdminUserController extends Controller
{
    // Middleware akan ditangani di routes, tidak perlu di constructor

    public function index(Request $request)
    {
        $query = User::query();

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->has('role') && $request->role) {
            $query->where('role', $request->role);
        }

        // Filter by status
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->where('last_seen_at', '>=', Carbon::now()->subDays(7));
            } elseif ($request->status === 'inactive') {
                $query->where('last_seen_at', '<', Carbon::now()->subDays(7))
                      ->orWhereNull('last_seen_at');
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
        $users = $query->paginate($perPage);

        // Add additional data for each user
        $users->getCollection()->transform(function ($user) {
            $user->chat_rooms_count = ChatRoom::whereHas('participants', function($q) use ($user) {
                $q->where('chat_room_participants.user_id', $user->id)
                  ->where('chat_room_participants.is_active', true);
            })->count();

            $user->messages_count = Message::where('user_id', $user->id)->count();
            $user->last_message_at = Message::where('user_id', $user->id)->latest()->value('created_at');
            $user->is_online = $user->last_seen_at && $user->last_seen_at >= Carbon::now()->subMinutes(2);

            return $user;
        });

        return response()->json($users);
    }

    public function show($id)
    {
        $user = User::findOrFail($id);

        // Get user statistics
        $stats = [
            'total_messages' => Message::where('user_id', $id)->count(),
            'total_chat_rooms' => ChatRoom::whereHas('participants', function($q) use ($id) {
                $q->where('user_id', $id);
            })->count(),
            'messages_today' => Message::where('user_id', $id)->whereDate('created_at', Carbon::today())->count(),
            'messages_this_week' => Message::where('user_id', $id)->whereBetween('created_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ])->count(),
            'messages_this_month' => Message::where('user_id', $id)->whereMonth('created_at', Carbon::now()->month)->count(),
        ];

        // Get recent activity
        $recentMessages = Message::with(['chatRoom:id,name'])
            ->where('user_id', $id)
            ->latest()
            ->take(10)
            ->get();

        $recentChatRooms = ChatRoom::whereHas('participants', function($q) use ($id) {
                $q->where('chat_room_participants.user_id', $id)
                  ->where('chat_room_participants.is_active', true);
            })
            ->with(['participants' => function($q) {
                $q->take(3);
            }])
            ->latest()
            ->take(5)
            ->get();

        // Get message activity over time (last 30 days)
        $messageActivity = collect();
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $count = Message::where('user_id', $id)->whereDate('created_at', $date)->count();
            $messageActivity->push([
                'date' => $date->format('Y-m-d'),
                'count' => $count,
                'label' => $date->format('M j')
            ]);
        }

        return response()->json([
            'user' => $user,
            'stats' => $stats,
            'recent_messages' => $recentMessages,
            'recent_chat_rooms' => $recentChatRooms,
            'message_activity' => $messageActivity
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:user,admin',
            'is_active' => 'sometimes|boolean',
            'allowed_ips' => 'sometimes|nullable|array',
            'allowed_ips.*' => 'ip',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'email_verified_at' => now(),
            'is_active' => $request->get('is_active', true),
        ];

        // Handle IP whitelist
        if ($request->has('allowed_ips')) {
            $allowedIps = $request->allowed_ips;
            
            // Remove empty values and duplicates
            if (is_array($allowedIps)) {
                $allowedIps = array_values(array_unique(array_filter($allowedIps, function($ip) {
                    return !empty(trim($ip));
                })));
            }
            
            $userData['allowed_ips'] = empty($allowedIps) ? null : $allowedIps;
        }

        $user = User::create($userData);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'sometimes|nullable|string|min:8|confirmed',
            'role' => 'sometimes|required|in:user,admin',
            'is_active' => 'sometimes|boolean',
            'allowed_ips' => 'sometimes|nullable|array',
            'allowed_ips.*' => 'ip',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $updateData = $request->only(['name', 'email', 'role', 'is_active']);

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        // Handle IP whitelist
        if ($request->has('allowed_ips')) {
            $allowedIps = $request->allowed_ips;
            
            // Remove empty values and duplicates
            if (is_array($allowedIps)) {
                $allowedIps = array_values(array_unique(array_filter($allowedIps, function($ip) {
                    return !empty(trim($ip));
                })));
            }
            
            $updateData['allowed_ips'] = empty($allowedIps) ? null : $allowedIps;
        }

        $user->update($updateData);

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user->fresh()
        ]);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Prevent admin from deleting themselves
        if ($user->id === auth()->id()) {
            return response()->json([
                'message' => 'You cannot delete your own account'
            ], 403);
        }

        // Soft delete the user
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }

    public function restore($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();

        return response()->json([
            'message' => 'User restored successfully',
            'user' => $user
        ]);
    }

    public function forceDelete($id)
    {
        $user = User::withTrashed()->findOrFail($id);

        // Prevent admin from force deleting themselves
        if ($user->id === auth()->id()) {
            return response()->json([
                'message' => 'You cannot permanently delete your own account'
            ], 403);
        }

        $user->forceDelete();

        return response()->json([
            'message' => 'User permanently deleted'
        ]);
    }

    public function bulkAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:delete,restore,force_delete,update_role',
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'integer|exists:users,id',
            'role' => 'required_if:action,update_role|in:user,admin'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $userIds = $request->user_ids;
        $action = $request->action;

        // Prevent admin from performing bulk actions on themselves
        if (in_array(auth()->id(), $userIds)) {
            return response()->json([
                'message' => 'You cannot perform bulk actions on your own account'
            ], 403);
        }

        $affectedCount = 0;

        switch ($action) {
            case 'delete':
                $affectedCount = User::whereIn('id', $userIds)->delete();
                break;
            case 'restore':
                $affectedCount = User::withTrashed()->whereIn('id', $userIds)->restore();
                break;
            case 'force_delete':
                $affectedCount = User::withTrashed()->whereIn('id', $userIds)->forceDelete();
                break;
            case 'update_role':
                $affectedCount = User::whereIn('id', $userIds)->update(['role' => $request->role]);
                break;
        }

        return response()->json([
            'message' => "Bulk action '{$action}' completed successfully",
            'affected_count' => $affectedCount
        ]);
    }

    public function getDeletedUsers(Request $request)
    {
        $query = User::onlyTrashed();

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $perPage = $request->get('per_page', 15);
        $deletedUsers = $query->orderBy('deleted_at', 'desc')->paginate($perPage);

        return response()->json($deletedUsers);
    }

    /**
     * Get user's activity logs
     */
    public function getActivityLogs($id, Request $request)
    {
        $user = User::findOrFail($id);

        $query = \App\Models\UserActivityLog::where('user_id', $id)
            ->orderBy('created_at', 'desc');

        // Filter by action type
        if ($request->has('action') && $request->action) {
            $query->where('action', $request->action);
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search in description
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%");
            });
        }

        $perPage = $request->get('per_page', 20);
        $logs = $query->paginate($perPage);

        // Add formatted data for each log
        $logs->getCollection()->transform(function ($log) {
            $log->formatted_action = $this->formatAction($log->action);
            $log->formatted_date = $log->created_at->format('M j, Y g:i A');
            $log->time_ago = $log->created_at->diffForHumans();
            $log->metadata_formatted = $this->formatMetadata($log->metadata);
            return $log;
        });

        return response()->json([
            'logs' => $logs,
            'user' => $user,
            'available_actions' => $this->getAvailableActions()
        ]);
    }

    /**
     * Get available action types for filtering
     */
    public function getAvailableActions()
    {
        return [
            'login' => 'Login',
            'logout' => 'Logout',
            'profile_updated' => 'Profile Updated',
            'password_changed' => 'Password Changed',
            'access_blocked' => 'Access Blocked',
            'ip_whitelist_updated' => 'IP Whitelist Updated',
            'message_sent' => 'Message Sent',
            'file_uploaded' => 'File Uploaded',
            'file_downloaded' => 'File Downloaded',
            'chat_joined' => 'Chat Joined',
            'chat_left' => 'Chat Left',
            'user_created' => 'User Created',
            'user_updated' => 'User Updated',
            'user_deleted' => 'User Deleted',
        ];
    }

    /**
     * Format action for display
     */
    private function formatAction($action)
    {
        $actions = $this->getAvailableActions();
        return $actions[$action] ?? ucwords(str_replace('_', ' ', $action));
    }

    /**
     * Format metadata for display
     */
    private function formatMetadata($metadata)
    {
        if (!$metadata || !is_array($metadata)) {
            return null;
        }

        $formatted = [];
        foreach ($metadata as $key => $value) {
            $formattedKey = ucwords(str_replace('_', ' ', $key));
            
            if (is_array($value)) {
                $formatted[$formattedKey] = implode(', ', $value);
            } elseif (is_string($value) && filter_var($value, FILTER_VALIDATE_IP)) {
                $formatted[$formattedKey] = $value;
            } elseif (is_string($value) && strlen($value) > 50) {
                $formatted[$formattedKey] = substr($value, 0, 50) . '...';
            } else {
                $formatted[$formattedKey] = $value;
            }
        }

        return $formatted;
    }

    /**
     * Update IP whitelist for a user
     */
    public function updateIpWhitelist(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'allowed_ips' => 'nullable|array',
            'allowed_ips.*' => 'ip',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $allowedIps = $request->allowed_ips;

        // Remove empty values and duplicates
        if (is_array($allowedIps)) {
            $allowedIps = array_values(array_unique(array_filter($allowedIps, function($ip) {
                return !empty(trim($ip));
            })));
        }

        $user->update([
            'allowed_ips' => empty($allowedIps) ? null : $allowedIps
        ]);

        // Log activity
        \App\Models\UserActivityLog::log($user, 'ip_whitelist_updated', 'IP whitelist updated by admin', [
            'admin_id' => auth()->id(),
            'admin_email' => auth()->user()->email,
            'old_ips' => $user->getOriginal('allowed_ips'),
            'new_ips' => $allowedIps,
            'changed_by' => 'admin_panel',
        ]);

        return response()->json([
            'message' => 'IP whitelist updated successfully',
            'user' => $user->fresh(),
            'allowed_ips' => $user->allowed_ips
        ]);
    }

    /**
     * Add IP to user's whitelist
     */
    public function addIpToWhitelist(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'ip' => 'required|ip',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $currentIps = $user->allowed_ips ?? [];
        $newIp = $request->ip;

        if (!in_array($newIp, $currentIps)) {
            $currentIps[] = $newIp;
            $user->update(['allowed_ips' => $currentIps]);
            
            // Log activity
            \App\Models\UserActivityLog::log($user, 'ip_whitelist_updated', "IP address {$newIp} added to whitelist by admin", [
                'admin_id' => auth()->id(),
                'admin_email' => auth()->user()->email,
                'action_type' => 'add',
                'ip_added' => $newIp,
                'total_ips' => count($currentIps),
            ]);
        }

        return response()->json([
            'message' => 'IP added to whitelist successfully',
            'user' => $user->fresh(),
            'allowed_ips' => $user->allowed_ips
        ]);
    }

    /**
     * Remove IP from user's whitelist
     */
    public function removeIpFromWhitelist(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'ip' => 'required|ip',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $currentIps = $user->allowed_ips ?? [];
        $ipToRemove = $request->ip;

        $currentIps = array_values(array_filter($currentIps, function($ip) use ($ipToRemove) {
            return $ip !== $ipToRemove;
        }));

        $user->update(['allowed_ips' => empty($currentIps) ? null : $currentIps]);

        // Log activity
        \App\Models\UserActivityLog::log($user, 'ip_whitelist_updated', "IP address {$ipToRemove} removed from whitelist by admin", [
            'admin_id' => auth()->id(),
            'admin_email' => auth()->user()->email,
            'action_type' => 'remove',
            'ip_removed' => $ipToRemove,
            'total_ips' => count($currentIps),
        ]);

        return response()->json([
            'message' => 'IP removed from whitelist successfully',
            'user' => $user->fresh(),
            'allowed_ips' => $user->allowed_ips
        ]);
    }

    /**
     * Get user's current IP (for easy addition to whitelist)
     */
    public function getCurrentUserIp(Request $request)
    {
        return response()->json([
            'current_ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toISOString()
        ]);
    }
}
