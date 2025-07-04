<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ChatRoomController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\FileController;
use App\Http\Controllers\Api\FileDownloadController;
use App\Http\Controllers\Api\TypingController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\Admin\ChatController as AdminChatController;
use App\Http\Controllers\Api\GroupManagementController;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public authentication routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Broadcasting routes for WebSocket authentication
Broadcast::routes(['middleware' => ['auth:sanctum']]);

// Protected routes
Route::middleware(['auth:sanctum', 'ip.whitelist'])->group(function () {
    // Authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::put('/password', [AuthController::class, 'changePassword']);
    });

    // Profile avatar routes
    Route::prefix('profile')->group(function () {
        Route::post('/avatar', [AuthController::class, 'uploadAvatar']);
        Route::delete('/avatar', [AuthController::class, 'removeAvatar']);
    });

    // User routes
    Route::prefix('user')->group(function () {
        Route::get('/profile', [UserController::class, 'profile']);
        Route::put('/profile', [UserController::class, 'updateProfile']);
        Route::put('/password', [UserController::class, 'changePassword']);
        Route::get('/activity-logs', [UserController::class, 'activityLogs']);
        Route::post('/update-last-seen', [UserController::class, 'updateLastSeen']);
        Route::post('/heartbeat', [UserController::class, 'heartbeat']);
        Route::get('/search', [UserController::class, 'search']);
    });

    // Users list for contacts
    Route::get('/users', [UserController::class, 'index']);

    // Chat Room routes
    Route::apiResource('chat-rooms', ChatRoomController::class);
    Route::post('chat-rooms/{id}/participants', [ChatRoomController::class, 'addParticipant']);
    Route::delete('chat-rooms/{id}/participants/{userId}', [ChatRoomController::class, 'removeParticipant']);

    // Message routes
    Route::get('chat-rooms/{chatRoomId}/messages', [MessageController::class, 'index']);
    Route::post('chat-rooms/{chatRoomId}/messages', [MessageController::class, 'store']);
    Route::get('chat-rooms/{chatRoomId}/messages/{id}', [MessageController::class, 'show']);
    Route::put('chat-rooms/{chatRoomId}/messages/{id}', [MessageController::class, 'update']);
    Route::delete('chat-rooms/{chatRoomId}/messages/{id}', [MessageController::class, 'destroy']);
    Route::post('chat-rooms/{chatRoomId}/messages/{id}/read', [MessageController::class, 'markAsRead']);
    Route::get('chat-rooms/{chatRoomId}/messages/{id}/read-status', [MessageController::class, 'readStatus']);
    Route::get('chat-rooms/{chatRoomId}/messages/search', [MessageController::class, 'search']);

    // File routes
    Route::post('chat-rooms/{chatRoomId}/files', [FileController::class, 'upload']);
    Route::get('chat-rooms/{chatRoomId}/messages/{messageId}/files/{fileIndex}', [FileController::class, 'download'])->name('file.download');
    Route::get('chat-rooms/{chatRoomId}/messages/{messageId}/files/{fileIndex}/info', [FileController::class, 'info']);
    Route::delete('chat-rooms/{chatRoomId}/messages/{messageId}/files/{fileIndex}', [FileController::class, 'delete']);

    // Typing indicator
    Route::post('chat-rooms/{chatRoomId}/typing', [TypingController::class, 'typing']);

    // Group Management routes
    Route::prefix('groups')->group(function () {
        Route::get('{id}/info', [GroupManagementController::class, 'getGroupInfo']);
        Route::post('{id}/members', [GroupManagementController::class, 'addMember']);
        Route::delete('{id}/members/{userId}', [GroupManagementController::class, 'removeMember']);
        Route::put('{id}/members/{userId}/role', [GroupManagementController::class, 'changeMemberRole']);
        Route::post('{id}/leave', [GroupManagementController::class, 'leaveGroup']);
        Route::put('{id}/info', [GroupManagementController::class, 'updateGroupInfo']);
        Route::post('{id}/avatar', [GroupManagementController::class, 'uploadAvatar']);
        Route::delete('{id}', [GroupManagementController::class, 'deleteGroup']);
    });

    // Admin routes
    Route::middleware('admin')->prefix('admin')->group(function () {
        // Dashboard
        Route::get('dashboard', [App\Http\Controllers\Admin\AdminDashboardController::class, 'index']);
        Route::get('system-health', [App\Http\Controllers\Admin\AdminDashboardController::class, 'systemHealth']);
        Route::post('export-data', [App\Http\Controllers\Admin\AdminDashboardController::class, 'exportData']);

        // User management
        Route::get('users', [App\Http\Controllers\Admin\AdminUserController::class, 'index']);
        Route::post('users', [App\Http\Controllers\Admin\AdminUserController::class, 'store']);
        Route::get('users/{id}', [App\Http\Controllers\Admin\AdminUserController::class, 'show']);
        Route::put('users/{id}', [App\Http\Controllers\Admin\AdminUserController::class, 'update']);
        Route::delete('users/{id}', [App\Http\Controllers\Admin\AdminUserController::class, 'destroy']);
        Route::post('users/{id}/restore', [App\Http\Controllers\Admin\AdminUserController::class, 'restore']);
        Route::delete('users/{id}/force', [App\Http\Controllers\Admin\AdminUserController::class, 'forceDelete']);
        Route::post('users/bulk-action', [App\Http\Controllers\Admin\AdminUserController::class, 'bulkAction']);
        Route::get('users-deleted', [App\Http\Controllers\Admin\AdminUserController::class, 'getDeletedUsers']);

        // IP Whitelist management
        Route::put('users/{id}/ip-whitelist', [App\Http\Controllers\Admin\AdminUserController::class, 'updateIpWhitelist']);
        Route::post('users/{id}/ip-whitelist/add', [App\Http\Controllers\Admin\AdminUserController::class, 'addIpToWhitelist']);
        Route::delete('users/{id}/ip-whitelist/remove', [App\Http\Controllers\Admin\AdminUserController::class, 'removeIpFromWhitelist']);
        Route::get('current-ip', [App\Http\Controllers\Admin\AdminUserController::class, 'getCurrentUserIp']);

        // Activity Logs
        Route::get('users/{id}/activity-logs', [App\Http\Controllers\Admin\AdminUserController::class, 'getActivityLogs']);

        // Chat management
        Route::get('chat-rooms', [App\Http\Controllers\Admin\AdminChatController::class, 'index']);
        Route::get('chat-rooms/{id}', [App\Http\Controllers\Admin\AdminChatController::class, 'show']);
        Route::put('chat-rooms/{id}', [App\Http\Controllers\Admin\AdminChatController::class, 'updateChatRoom']);
        Route::delete('chat-rooms/{id}', [App\Http\Controllers\Admin\AdminChatController::class, 'deleteChatRoom']);
        Route::post('chat-rooms/{id}/restore', [App\Http\Controllers\Admin\AdminChatController::class, 'restoreChatRoom']);
        Route::post('chat-rooms/bulk-action', [App\Http\Controllers\Admin\AdminChatController::class, 'bulkAction']);
        Route::get('chat-rooms-deleted', [App\Http\Controllers\Admin\AdminChatController::class, 'getDeletedChatRooms']);
        Route::get('chat-rooms/{id}/analytics', [App\Http\Controllers\Admin\AdminChatController::class, 'getChatRoomAnalytics']);
        Route::get('chat-activity-stats', [App\Http\Controllers\Admin\AdminChatController::class, 'getChatActivityStats']);

        // Message management
        Route::get('chat-rooms/{id}/messages', [App\Http\Controllers\Admin\AdminChatController::class, 'getMessages']);
        Route::delete('chat-rooms/{chatRoomId}/messages/{messageId}', [App\Http\Controllers\Admin\AdminChatController::class, 'deleteMessage']);
        Route::post('chat-rooms/{chatRoomId}/messages/{messageId}/restore', [App\Http\Controllers\Admin\AdminChatController::class, 'restoreMessage']);
        Route::delete('chat-rooms/{chatRoomId}/messages/{messageId}/force', [App\Http\Controllers\Admin\AdminChatController::class, 'forceDeleteMessage']);
        Route::post('chat-rooms/{chatRoomId}/messages/bulk-delete', [App\Http\Controllers\Admin\AdminChatController::class, 'bulkDeleteMessages']);

        // Additional admin routes
        Route::get('messages', [App\Http\Controllers\Admin\AdminChatController::class, 'getAllMessages']);
        Route::get('messages-deleted', [App\Http\Controllers\Admin\AdminChatController::class, 'getDeletedMessages']);
        Route::delete('chat-rooms/{id}/force', [App\Http\Controllers\Admin\AdminChatController::class, 'forceDeleteChatRoom']);
        Route::get('chat-rooms/{id}/analytics', [App\Http\Controllers\Admin\AdminChatController::class, 'getChatRoomAnalytics']);
    });
});
