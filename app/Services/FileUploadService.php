<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class FileUploadService
{
    /**
     * Allowed file extensions for different categories
     */
    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp'];
    private const DOCUMENT_EXTENSIONS = [
        'pdf', 'doc', 'docx', 'txt', 'csv', 'xlsx', 'xls', 
        'ppt', 'pptx', 'rtf', 'odt', 'ods', 'odp'
    ];

    /**
     * Generate date-based path for file storage
     */
    private function getDateBasedPath($category = 'attachments'): string
    {
        $now = now();
        return sprintf(
            '%s/%s/%s/%s',
            $category,
            $now->year,         // 2025
            $now->format('m'),  // 06
            $now->format('d')   // 24
        );
    }

    /**
     * Check if file is an image
     */
    public function isImage(UploadedFile $file): bool
    {
        $extension = strtolower($file->getClientOriginalExtension());
        return in_array($extension, self::IMAGE_EXTENSIONS);
    }

    /**
     * Check if file is a document
     */
    public function isDocument(UploadedFile $file): bool
    {
        $extension = strtolower($file->getClientOriginalExtension());
        return in_array($extension, self::DOCUMENT_EXTENSIONS);
    }

    /**
     * Check if file needs conversion to ZIP
     */
    public function needsConversion(UploadedFile $file): bool
    {
        return !$this->isImage($file) && !$this->isDocument($file);
    }

    /**
     * Store file in appropriate category folder
     */
    public function storeFile(UploadedFile $file, string $type): array
    {
        $datePath = $this->getDateBasedPath();
        $fullPath = $datePath . '/' . $type;
        
        // Generate unique filename
        $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $filePath = $fullPath . '/' . $fileName;
        
        // Store file
        $storedPath = Storage::disk('public')->putFileAs($fullPath, $file, $fileName);
        
        return [
            'path' => $storedPath,
            'url' => '/storage/' . $storedPath,
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'type' => $type
        ];
    }

    /**
     * Convert file to ZIP and store
     */
    public function convertToZip(UploadedFile $file): array
    {
        $tempPath = $file->store('temp');
        $tempFullPath = Storage::path($tempPath);
        
        // Create ZIP file
        $zip = new ZipArchive();
        $zipFileName = Str::uuid() . '.zip';
        $zipTempPath = storage_path('app/temp/' . $zipFileName);
        
        if ($zip->open($zipTempPath, ZipArchive::CREATE) === TRUE) {
            $zip->addFile($tempFullPath, $file->getClientOriginalName());
            $zip->close();
            
            // Create UploadedFile from ZIP
            $zipFile = new UploadedFile(
                $zipTempPath,
                $file->getClientOriginalName() . '.zip',
                'application/zip',
                null,
                true
            );
            
            // Store ZIP file
            $result = $this->storeFile($zipFile, 'converted');
            $result['was_converted'] = true;
            $result['original_name'] = $file->getClientOriginalName();
            
            // Cleanup temp files
            Storage::delete($tempPath);
            unlink($zipTempPath);
            
            return $result;
        }
        
        throw new \Exception('Failed to create ZIP file');
    }

    /**
     * Process uploaded file based on type
     */
    public function processUpload(UploadedFile $file): array
    {
        if ($this->isImage($file)) {
            $result = $this->storeFile($file, 'images');
            $result['category'] = 'image';
            $result['was_converted'] = false;
        } elseif ($this->isDocument($file)) {
            $result = $this->storeFile($file, 'documents');
            $result['category'] = 'document';
            $result['was_converted'] = false;
        } else {
            $result = $this->convertToZip($file);
            $result['category'] = 'other';
        }
        
        return $result;
    }

    /**
     * Get file info for download
     */
    public function getFileInfo(string $path): array
    {
        if (!Storage::disk('public')->exists($path)) {
            throw new \Exception('File not found');
        }
        
        $fullPath = Storage::disk('public')->path($path);
        
        return [
            'path' => $path,
            'url' => '/storage/' . $path,
            'size' => Storage::disk('public')->size($path),
            'mime_type' => mime_content_type($fullPath),
            'exists' => true
        ];
    }
}