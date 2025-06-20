<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Get user profile
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load(['chatRooms' => function ($query) {
            $query->active()->with('latestMessage');
        }]);

        return response()->json($user);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
            'avatar' => 'sometimes|nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user->update($request->only(['name', 'email', 'avatar']));

        // Log profile update activity
        UserActivityLog::log($user, 'profile_update', 'User updated profile');

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user
        ]);
    }

    /**
     * Change password
     */
    public function changePassword(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect'
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        // Log password change activity
        UserActivityLog::log($user, 'password_change', 'User changed password');

        return response()->json([
            'message' => 'Password changed successfully'
        ]);
    }

    /**
     * Get user activity logs
     */
    public function activityLogs(Request $request): JsonResponse
    {
        $user = $request->user();

        $logs = $user->activityLogs()
                    ->orderBy('created_at', 'desc')
                    ->paginate($request->get('per_page', 20));

        return response()->json($logs);
    }

    /**
     * Update last seen timestamp
     */
    public function updateLastSeen(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->updateLastSeen();

        return response()->json([
            'message' => 'Last seen updated'
        ]);
    }

    /**
     * Search users (for adding to chat rooms)
     */
    public function search(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:2',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $query = $request->get('query');
        $currentUser = $request->user();

        $users = User::where('is_active', true)
                    ->where('id', '!=', $currentUser->id)
                    ->where(function ($q) use ($query) {
                        $q->where('name', 'like', "%{$query}%")
                          ->orWhere('email', 'like', "%{$query}%");
                    })
                    ->select(['id', 'name', 'email', 'avatar', 'last_seen_at'])
                    ->limit(10)
                    ->get();

        return response()->json($users);
    }
}
