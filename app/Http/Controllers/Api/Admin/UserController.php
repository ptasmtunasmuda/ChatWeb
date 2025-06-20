<?php

namespace App\Http\Controllers\Api\Admin;

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
     * Display a listing of users
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::withTrashed();

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->has('role')) {
            $query->where('role', $request->get('role'));
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

        $users = $query->orderBy('created_at', 'desc')
                      ->paginate($request->get('per_page', 15));

        return response()->json($users);
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:user,admin',
            'is_active' => 'boolean',
            'allowed_ips' => 'nullable|array',
            'allowed_ips.*' => 'ip',
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
            'role' => $request->role,
            'is_active' => $request->get('is_active', true),
            'allowed_ips' => $request->allowed_ips,
        ]);

        // Log admin activity
        UserActivityLog::log($request->user(), 'user_created', "Created user: {$user->email}", [
            'created_user_id' => $user->id,
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user
        ], 201);
    }

    /**
     * Display the specified user
     */
    public function show(string $id): JsonResponse
    {
        $user = User::withTrashed()->findOrFail($id);

        // Load relationships
        $user->load(['activityLogs' => function ($query) {
            $query->latest()->limit(10);
        }]);

        return response()->json($user);
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $user = User::withTrashed()->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|nullable|string|min:8',
            'role' => 'sometimes|required|in:user,admin',
            'is_active' => 'sometimes|boolean',
            'allowed_ips' => 'nullable|array',
            'allowed_ips.*' => 'ip',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $updateData = $request->only(['name', 'email', 'role', 'is_active', 'allowed_ips']);

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        // Log admin activity
        UserActivityLog::log($request->user(), 'user_updated', "Updated user: {$user->email}", [
            'updated_user_id' => $user->id,
            'changes' => array_keys($updateData),
        ]);

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user->fresh()
        ]);
    }

    /**
     * Remove the specified user (soft delete)
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $user = User::findOrFail($id);

        // Prevent admin from deleting themselves
        if ($user->id === $request->user()->id) {
            return response()->json([
                'message' => 'You cannot delete your own account'
            ], 422);
        }

        $user->delete();

        // Log admin activity
        UserActivityLog::log($request->user(), 'user_deleted', "Deleted user: {$user->email}", [
            'deleted_user_id' => $user->id,
        ]);

        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }

    /**
     * Restore a soft deleted user
     */
    public function restore(Request $request, string $id): JsonResponse
    {
        $user = User::withTrashed()->findOrFail($id);

        if (!$user->trashed()) {
            return response()->json([
                'message' => 'User is not deleted'
            ], 422);
        }

        $user->restore();

        // Log admin activity
        UserActivityLog::log($request->user(), 'user_restored', "Restored user: {$user->email}", [
            'restored_user_id' => $user->id,
        ]);

        return response()->json([
            'message' => 'User restored successfully',
            'user' => $user
        ]);
    }

    /**
     * Reset user password
     */
    public function resetPassword(Request $request, string $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        // Log admin activity
        UserActivityLog::log($request->user(), 'password_reset', "Reset password for user: {$user->email}", [
            'target_user_id' => $user->id,
        ]);

        return response()->json([
            'message' => 'Password reset successfully'
        ]);
    }

    /**
     * Get user activity logs
     */
    public function activityLogs(Request $request, string $id): JsonResponse
    {
        $user = User::withTrashed()->findOrFail($id);

        $logs = $user->activityLogs()
                    ->orderBy('created_at', 'desc')
                    ->paginate($request->get('per_page', 20));

        return response()->json($logs);
    }
}
