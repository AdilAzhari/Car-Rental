<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ImageUploadService
{
    protected ImageManager $imageManager;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver);
    }

    public function uploadProfileImage(UploadedFile $uploadedFile, ?string $oldImagePath = null): string
    {
        $this->validateImage($uploadedFile, 'profile');

        // Delete old image if exists
        if ($oldImagePath) {
            $this->deleteImage($oldImagePath);
        }

        $config = config('image.profile_images');
        $filename = $this->generateFilename($uploadedFile);
        $path = "profiles/{$filename}";

        $image = $this->imageManager->read($uploadedFile->getRealPath());

        // Resize while maintaining aspect ratio
        $image->scaleDown(
            width: $config['max_width'],
            height: $config['max_height']
        );

        // Save to storage
        $fullPath = storage_path("app/public/images/{$path}");
        $this->ensureDirectoryExists(dirname($fullPath));

        $image->save($fullPath, quality: $config['quality']);

        return $path;
    }

    public function uploadVehicleImage(UploadedFile $uploadedFile, ?string $oldImagePath = null): array
    {
        $this->validateImage($uploadedFile, 'vehicle');

        // Delete old image if exists
        if ($oldImagePath) {
            $this->deleteImage($oldImagePath);
        }

        $config = config('image.vehicle_images');
        $filename = $this->generateFilename($uploadedFile);
        $mainPath = "vehicles/{$filename}";
        $thumbnailPath = "vehicles/thumbnails/thumb_{$filename}";

        $image = $this->imageManager->read($uploadedFile->getRealPath());

        // Create main image
        $mainImage = clone $image;
        $mainImage->scaleDown(
            width: $config['max_width'],
            height: $config['max_height']
        );

        $mainFullPath = storage_path("app/public/images/{$mainPath}");
        $this->ensureDirectoryExists(dirname($mainFullPath));
        $mainImage->save($mainFullPath, quality: $config['quality']);

        // Create thumbnail
        $thumbnailImage = clone $image;
        $thumbnailImage->cover(
            width: $config['thumbnail']['width'],
            height: $config['thumbnail']['height']
        );

        $thumbnailFullPath = storage_path("app/public/images/{$thumbnailPath}");
        $this->ensureDirectoryExists(dirname($thumbnailFullPath));
        $thumbnailImage->save($thumbnailFullPath, quality: $config['quality']);

        return [
            'main' => $mainPath,
            'thumbnail' => $thumbnailPath,
        ];
    }

    protected function validateImage(UploadedFile $uploadedFile, string $type): void
    {
        $config = config("image.{$type}_images");
        $maxSize = $config['max_file_size'] * 1024; // Convert KB to bytes

        if ($uploadedFile->getSize() > $maxSize) {
            throw new \InvalidArgumentException("File size exceeds maximum allowed size of {$config['max_file_size']}KB");
        }

        $allowedTypes = config('image.allowed_types');
        $extension = strtolower($uploadedFile->getClientOriginalExtension());

        if (! in_array($extension, $allowedTypes)) {
            throw new \InvalidArgumentException('File type not allowed. Allowed types: '.implode(', ', $allowedTypes));
        }
    }

    protected function generateFilename(UploadedFile $uploadedFile): string
    {
        $extension = strtolower($uploadedFile->getClientOriginalExtension());
        $timestamp = now()->format('Y-m-d_H-i-s');
        $random = Str::random(8);

        return "{$timestamp}_{$random}.{$extension}";
    }

    protected function ensureDirectoryExists(string $directory): void
    {
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
    }

    public function deleteImage(?string $imagePath): void
    {
        if ($imagePath && Storage::disk('public')->exists("images/{$imagePath}")) {
            Storage::disk('public')->delete("images/{$imagePath}");

            // Also delete thumbnail if it's a vehicle image
            if (str_contains($imagePath, 'vehicles/') && ! str_contains($imagePath, 'thumbnails/')) {
                $thumbnailPath = str_replace('vehicles/', 'vehicles/thumbnails/thumb_', $imagePath);
                if (Storage::disk('public')->exists("images/{$thumbnailPath}")) {
                    Storage::disk('public')->delete("images/{$thumbnailPath}");
                }
            }
        }
    }

    public function getImageUrl(string $imagePath): string
    {
        return asset("storage/images/{$imagePath}");
    }
}
