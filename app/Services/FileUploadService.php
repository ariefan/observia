<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileUploadService
{
    /**
     * Upload an image file and return the public URL
     */
    public function uploadImage(UploadedFile $file, string $folder = 'uploads'): string
    {
        $path = $file->store($folder, 'public');
        return asset('storage/' . $path);
    }

    /**
     * Upload multiple images and return array of public URLs
     */
    public function uploadImages(array $files, string $folder = 'uploads'): array
    {
        $urls = [];
        
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $urls[] = $this->uploadImage($file, $folder);
            }
        }
        
        return $urls;
    }

    /**
     * Upload image and return the storage path (without asset URL)
     */
    public function uploadImagePath(UploadedFile $file, string $folder = 'uploads'): string
    {
        return $file->store($folder, 'public');
    }

    /**
     * Upload multiple images and return array of storage paths
     */
    public function uploadImagePaths(array $files, string $folder = 'uploads'): array
    {
        $paths = [];
        
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $paths[] = $this->uploadImagePath($file, $folder);
            }
        }
        
        return $paths;
    }

    /**
     * Delete a file from storage
     */
    public function deleteFile(string $path): bool
    {
        // Remove 'storage/' prefix if present for proper deletion
        $cleanPath = str_replace('storage/', '', $path);
        
        return Storage::disk('public')->delete($cleanPath);
    }

    /**
     * Process mixed array of existing paths and new files
     * Returns array of storage paths
     */
    public function processMixedFiles(array $files, string $folder = 'uploads'): array
    {
        $processedPaths = [];
        
        foreach ($files as $file) {
            if (is_string($file)) {
                // Existing file path
                $processedPaths[] = $file;
            } elseif ($file instanceof UploadedFile) {
                // New uploaded file
                $processedPaths[] = $this->uploadImagePath($file, $folder);
            }
        }
        
        return $processedPaths;
    }

    /**
     * Replace old file with new file (delete old, upload new)
     */
    public function replaceFile(UploadedFile $newFile, ?string $oldPath, string $folder = 'uploads'): string
    {
        // Upload new file
        $newPath = $this->uploadImagePath($newFile, $folder);
        
        // Delete old file if it exists
        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            $this->deleteFile($oldPath);
        }
        
        return $newPath;
    }
}