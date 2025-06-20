<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\ChatRoom;
use App\Models\UserActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class FileController extends Controller
{
    /**
     * Upload file for message
     */
    public function upload(Request $request, string $chatRoomId): JsonResponse
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
            'file' => 'required|file|max:10240', // 10MB max
            'type' => 'sometimes|in:image,audio,file',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $file = $request->file('file');
            $fileType = $request->get('type', 'file');

            // Determine file type based on mime type if not specified
            if (!$request->has('type')) {
                $mimeType = $file->getMimeType();
                if (str_starts_with($mimeType, 'image/')) {
                    $fileType = 'image';
                } elseif (str_starts_with($mimeType, 'audio/')) {
                    $fileType = 'audio';
                } else {
                    $fileType = 'file';
                }
            }

            // Create a temporary message for file attachment
            $message = Message::create([
                'chat_room_id' => $chatRoom->id,
                'user_id' => $user->id,
                'content' => $file->getClientOriginalName(),
                'type' => $fileType,
            ]);

            // Add file to message
            $media = $message->addMediaFromRequest('file')
                           ->usingName($file->getClientOriginalName())
                           ->toMediaCollection('attachments');

            // Log activity
            UserActivityLog::log($user, 'file_uploaded', "Uploaded file in chat room: {$chatRoom->name}", [
                'chat_room_id' => $chatRoom->id,
                'message_id' => $message->id,
                'file_name' => $file->getClientOriginalName(),
                'file_type' => $fileType,
                'file_size' => $file->getSize(),
            ]);

            return response()->json([
                'message' => 'File uploaded successfully',
                'data' => [
                    'message' => $message->load('user'),
                    'media' => $media,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to upload file',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download file
     */
    public function download(Request $request, string $chatRoomId, string $messageId, string $mediaId): JsonResponse
    {
        $user = $request->user();
        $chatRoom = ChatRoom::findOrFail($chatRoomId);

        // Check if user is participant
        if (!$chatRoom->isParticipant($user)) {
            return response()->json([
                'message' => 'Access denied. You are not a participant of this chat room.'
            ], 403);
        }

        $message = Message::where('chat_room_id', $chatRoom->id)->findOrFail($messageId);
        $media = $message->getMedia('attachments')->where('id', $mediaId)->first();

        if (!$media) {
            return response()->json([
                'message' => 'File not found'
            ], 404);
        }

        // Log activity
        UserActivityLog::log($user, 'file_downloaded', "Downloaded file from chat room: {$chatRoom->name}", [
            'chat_room_id' => $chatRoom->id,
            'message_id' => $message->id,
            'media_id' => $media->id,
            'file_name' => $media->name,
        ]);

        return response()->download($media->getPath(), $media->name);
    }

    /**
     * Get file info
     */
    public function info(Request $request, string $chatRoomId, string $messageId, string $mediaId): JsonResponse
    {
        $user = $request->user();
        $chatRoom = ChatRoom::findOrFail($chatRoomId);

        // Check if user is participant
        if (!$chatRoom->isParticipant($user)) {
            return response()->json([
                'message' => 'Access denied. You are not a participant of this chat room.'
            ], 403);
        }

        $message = Message::where('chat_room_id', $chatRoom->id)->findOrFail($messageId);
        $media = $message->getMedia('attachments')->where('id', $mediaId)->first();

        if (!$media) {
            return response()->json([
                'message' => 'File not found'
            ], 404);
        }

        return response()->json([
            'id' => $media->id,
            'name' => $media->name,
            'file_name' => $media->file_name,
            'mime_type' => $media->mime_type,
            'size' => $media->size,
            'human_readable_size' => $media->human_readable_size,
            'url' => $media->getUrl(),
            'created_at' => $media->created_at,
        ]);
    }

    /**
     * Delete file
     */
    public function delete(Request $request, string $chatRoomId, string $messageId, string $mediaId): JsonResponse
    {
        $user = $request->user();
        $chatRoom = ChatRoom::findOrFail($chatRoomId);
        $message = Message::where('chat_room_id', $chatRoom->id)->findOrFail($messageId);

        // Check permissions (message sender or chat room admin/creator)
        $userRole = $chatRoom->getParticipantRole($user);
        $isMessageSender = $message->user_id === $user->id;
        $isRoomAdmin = in_array($userRole, ['admin']) || $chatRoom->created_by === $user->id;

        if (!$isMessageSender && !$isRoomAdmin) {
            return response()->json([
                'message' => 'Access denied. You do not have permission to delete this file.'
            ], 403);
        }

        $media = $message->getMedia('attachments')->where('id', $mediaId)->first();

        if (!$media) {
            return response()->json([
                'message' => 'File not found'
            ], 404);
        }

        // Log activity before deletion
        UserActivityLog::log($user, 'file_deleted', "Deleted file from chat room: {$chatRoom->name}", [
            'chat_room_id' => $chatRoom->id,
            'message_id' => $message->id,
            'media_id' => $media->id,
            'file_name' => $media->name,
        ]);

        $media->delete();

        return response()->json([
            'message' => 'File deleted successfully'
        ]);
    }
}
