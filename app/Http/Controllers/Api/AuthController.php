<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Events\UserAvatarUpdated;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
            'is_active' => true,
        ]);

        // Log registration activity
        UserActivityLog::log($user, 'register', 'User registered successfully', [
            'registration_method' => 'web',
            'user_agent' => $request->userAgent(),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful',
            'user' => $user,
            'token' => $token
        ], 201);
    }

    /**
     * Login user
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        if (!$user->is_active) {
            return response()->json([
                'message' => 'Account is deactivated'
            ], 403);
        }

        // Check IP whitelist if configured
        if (!$user->isAllowedFromIp($request->ip())) {
            UserActivityLog::log($user, 'login_blocked', 'Login blocked due to IP restriction', [
                'ip' => $request->ip()
            ]);

            return response()->json([
                'message' => 'Access denied from this IP address'
            ], 403);
        }

        // Update last seen
        $user->updateLastSeen();

        // Log login activity
        UserActivityLog::log($user, 'login', 'User logged in successfully', [
            'login_method' => 'web',
            'user_agent' => $request->userAgent(),
            'ip_address' => $request->ip(),
            'session_id' => session()->getId(),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token
        ]);
    }

    /**
     * Logout user
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        // Log logout activity
        UserActivityLog::log($user, 'logout', 'User logged out successfully', [
            'session_duration' => $user->last_seen_at ? now()->diffInMinutes($user->last_seen_at) : null,
            'user_agent' => $request->userAgent(),
        ]);

        // Revoke current token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout successful'
        ]);
    }

    /**
     * Get authenticated user
     */
    public function user(Request $request): JsonResponse
    {
        return response()->json($request->user());
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

        $oldData = $user->only(['name', 'email', 'avatar']);
        $user->update($request->only(['name', 'email', 'avatar']));
        $newData = $user->fresh()->only(['name', 'email', 'avatar']);

        // Log profile update activity
        $changedFields = array_keys(array_diff_assoc($newData, $oldData));
        UserActivityLog::log($user, 'profile_updated', 'User updated profile information', [
            'changed_fields' => $changedFields,
            'old_data' => $oldData,
            'new_data' => $newData,
        ]);

        return response()->json($user);
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
        UserActivityLog::log($user, 'password_changed', 'User changed password successfully', [
            'changed_at' => now()->toISOString(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'message' => 'Password changed successfully'
        ]);
    }

    /**
     * Upload user avatar
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Delete old avatar if exists
            if ($user->avatar) {
                $oldAvatarPath = str_replace('/storage/', '', $user->avatar);
                if (Storage::disk('public')->exists($oldAvatarPath)) {
                    Storage::disk('public')->delete($oldAvatarPath);
                }
            }

            // Store new avatar
            $avatarFile = $request->file('avatar');
            $avatarName = Str::uuid() . '.' . $avatarFile->getClientOriginalExtension();
            $avatarPath = $avatarFile->storeAs('avatars', $avatarName, 'public');
            
            // Update user avatar URL
            $avatarUrl = '/storage/' . $avatarPath;
            $user->update(['avatar' => $avatarUrl]);

            // Log avatar upload activity
            UserActivityLog::log($user, 'avatar_upload', 'User uploaded new avatar');

            // Broadcast avatar update event
            broadcast(new UserAvatarUpdated($user))->toOthers();

            return response()->json([
                'message' => 'Avatar uploaded successfully',
                'user' => $user->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to upload avatar',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove user avatar
     */
    public function removeAvatar(Request $request): JsonResponse
    {
        $user = $request->user();

        try {
            // Delete avatar file if exists
            if ($user->avatar) {
                $avatarPath = str_replace('/storage/', '', $user->avatar);
                if (Storage::disk('public')->exists($avatarPath)) {
                    Storage::disk('public')->delete($avatarPath);
                }
            }

            // Remove avatar URL from user
            $user->update(['avatar' => null]);

            // Log avatar removal activity
            UserActivityLog::log($user, 'avatar_remove', 'User removed avatar');

            // Broadcast avatar update event
            broadcast(new UserAvatarUpdated($user))->toOthers();

            return response()->json([
                'message' => 'Avatar removed successfully',
                'user' => $user->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to remove avatar',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
