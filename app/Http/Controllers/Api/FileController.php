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
    public function download(Request $request, string $chatRoomId, string $messageId, string $fileIndex)
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
        
        // Get file info from attachment_info
        $attachmentInfo = $message->attachment_info;
        if (!$attachmentInfo || !isset($attachmentInfo['files'])) {
            return response()->json([
                'message' => 'No attachments found'
            ], 404);
        }

        $files = $attachmentInfo['files'];
        $index = (int) $fileIndex;
        
        if (!isset($files[$index])) {
            return response()->json([
                'message' => 'File not found'
            ], 404);
        }

        $file = $files[$index];
        
        // Construct file path (assuming files are stored in public storage)
        $filePath = str_replace('/storage/', '', $file['url']);
        
        if (!Storage::disk('public')->exists($filePath)) {
            return response()->json([
                'message' => 'File not found on disk'
            ], 404);
        }

        // Log activity
        UserActivityLog::log($user, 'file_downloaded', "Downloaded file from chat room: {$chatRoom->name}", [
            'chat_room_id' => $chatRoom->id,
            'message_id' => $message->id,
            'file_index' => $index,
            'file_name' => $file['original_name'],
        ]);

        $fullPath = Storage::disk('public')->path($filePath);
        $fileName = $file['original_name'] ?? 'download';

        return response()->download($fullPath, $fileName);
    }

    /**
     * Get file info
     */
    public function info(Request $request, string $chatRoomId, string $messageId, string $fileIndex): JsonResponse
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
        
        // Get file info from attachment_info
        $attachmentInfo = $message->attachment_info;
        if (!$attachmentInfo || !isset($attachmentInfo['files'])) {
            return response()->json([
                'message' => 'No attachments found'
            ], 404);
        }

        $files = $attachmentInfo['files'];
        $index = (int) $fileIndex;
        
        if (!isset($files[$index])) {
            return response()->json([
                'message' => 'File not found'
            ], 404);
        }

        $file = $files[$index];

        return response()->json([
            'index' => $index,
            'original_name' => $file['original_name'],
            'size' => $file['size'],
            'mime_type' => $file['mime_type'],
            'category' => $file['category'],
            'was_converted' => $file['was_converted'] ?? false,
            'url' => $file['url'],
            'created_at' => $message->created_at,
        ]);
    }

    /**
     * Delete file
     */
    public function delete(Request $request, string $chatRoomId, string $messageId, string $fileIndex): JsonResponse
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

        // Get file info from attachment_info
        $attachmentInfo = $message->attachment_info;
        if (!$attachmentInfo || !isset($attachmentInfo['files'])) {
            return response()->json([
                'message' => 'No attachments found'
            ], 404);
        }

        $files = $attachmentInfo['files'];
        $index = (int) $fileIndex;
        
        if (!isset($files[$index])) {
            return response()->json([
                'message' => 'File not found'
            ], 404);
        }

        $file = $files[$index];

        // Log activity before deletion
        UserActivityLog::log($user, 'file_deleted', "Deleted file from chat room: {$chatRoom->name}", [
            'chat_room_id' => $chatRoom->id,
            'message_id' => $message->id,
            'file_index' => $index,
            'file_name' => $file['original_name'],
        ]);

        // Remove file from attachment_info array
        unset($files[$index]);
        
        // Reindex array to maintain proper indices
        $files = array_values($files);
        
        // Update message attachment_info
        $attachmentInfo['files'] = $files;
        $attachmentInfo['total_files'] = count($files);
        
        $message->update(['attachment_info' => $attachmentInfo]);

        // If no files left, optionally update message content
        if (empty($files)) {
            $message->update(['content' => '[File deleted]']);
        }

        return response()->json([
            'message' => 'File deleted successfully'
        ]);
    }
}
