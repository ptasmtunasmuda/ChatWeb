<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FileDownloadController extends Controller
{
    protected $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Download file attachment from message
     */
    public function download(Request $request, string $chatRoomId, string $messageId, string $fileId): BinaryFileResponse|JsonResponse
    {
        $user = $request->user();
        
        // Find message and verify access
        $message = Message::where('chat_room_id', $chatRoomId)
                         ->where('id', $messageId)
                         ->firstOrFail();

        // Check if user has access to this chat room
        if (!$message->chatRoom->isParticipant($user)) {
            return response()->json([
                'message' => 'Access denied. You are not a participant of this chat room.'
            ], 403);
        }

        // Get file info from attachment_info
        if (!$message->attachment_info || !isset($message->attachment_info['files'])) {
            return response()->json([
                'message' => 'No attachments found in this message.'
            ], 404);
        }

        // Find the specific file
        $fileIndex = (int) $fileId;
        $files = $message->attachment_info['files'];
        
        if (!isset($files[$fileIndex])) {
            return response()->json([
                'message' => 'File not found.'
            ], 404);
        }

        $fileInfo = $files[$fileIndex];
        
        try {
            // Verify file exists
            $this->fileUploadService->getFileInfo($fileInfo['path']);
            
            // Get full file path
            $fullPath = Storage::disk('public')->path($fileInfo['path']);
            
            // Return file download response
            return response()->download($fullPath, $fileInfo['original_name'], [
                'Content-Type' => $fileInfo['mime_type'],
                'Content-Length' => $fileInfo['size']
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'File not found or corrupted.',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Get file info without downloading
     */
    public function info(Request $request, string $chatRoomId, string $messageId, string $fileId): JsonResponse
    {
        $user = $request->user();
        
        // Find message and verify access
        $message = Message::where('chat_room_id', $chatRoomId)
                         ->where('id', $messageId)
                         ->firstOrFail();

        // Check if user has access to this chat room
        if (!$message->chatRoom->isParticipant($user)) {
            return response()->json([
                'message' => 'Access denied. You are not a participant of this chat room.'
            ], 403);
        }

        // Get file info from attachment_info
        if (!$message->attachment_info || !isset($message->attachment_info['files'])) {
            return response()->json([
                'message' => 'No attachments found in this message.'
            ], 404);
        }

        // Find the specific file
        $fileIndex = (int) $fileId;
        $files = $message->attachment_info['files'];
        
        if (!isset($files[$fileIndex])) {
            return response()->json([
                'message' => 'File not found.'
            ], 404);
        }

        $fileInfo = $files[$fileIndex];
        
        try {
            // Verify file exists and get updated info
            $storageInfo = $this->fileUploadService->getFileInfo($fileInfo['path']);
            
            return response()->json([
                'file_info' => array_merge($fileInfo, $storageInfo),
                'download_url' => route('api.files.download', [
                    'chatRoomId' => $chatRoomId,
                    'messageId' => $messageId,
                    'fileId' => $fileId
                ])
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'File not found or corrupted.',
                'error' => $e->getMessage()
            ], 404);
        }
    }
}