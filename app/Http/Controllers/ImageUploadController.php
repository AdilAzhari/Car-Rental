<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileImageUploadRequest;
use App\Http\Requests\VehicleImageUploadRequest;
use App\Services\ImageUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ImageUploadController extends Controller
{
    public function __construct(
        protected ImageUploadService $imageUploadService
    ) {}

    public function uploadProfileImage(ProfileImageUploadRequest $profileImageUploadRequest): JsonResponse
    {
        try {
            $imagePath = $this->imageUploadService->uploadProfileImage(
                $profileImageUploadRequest->file('image'),
                $profileImageUploadRequest->input('old_image_path')
            );

            return response()->json([
                'success' => true,
                'message' => 'Profile image uploaded successfully.',
                'data' => [
                    'image_path' => $imagePath,
                    'image_url' => $this->imageUploadService->getImageUrl($imagePath),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload profile image: '.$e->getMessage(),
            ], 422);
        }
    }

    public function uploadVehicleImage(VehicleImageUploadRequest $vehicleImageUploadRequest): JsonResponse
    {
        try {
            $imagePaths = $this->imageUploadService->uploadVehicleImage(
                $vehicleImageUploadRequest->file('image'),
                $vehicleImageUploadRequest->input('old_image_path')
            );

            return response()->json([
                'success' => true,
                'message' => 'Vehicle image uploaded successfully.',
                'data' => [
                    'main_image_path' => $imagePaths['main'],
                    'thumbnail_path' => $imagePaths['thumbnail'],
                    'main_image_url' => $this->imageUploadService->getImageUrl($imagePaths['main']),
                    'thumbnail_url' => $this->imageUploadService->getImageUrl($imagePaths['thumbnail']),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload vehicle image: '.$e->getMessage(),
            ], 422);
        }
    }

    public function deleteImage(Request $request): JsonResponse
    {
        $request->validate([
            'image_path' => 'required|string',
        ]);

        try {
            $this->imageUploadService->deleteImage($request->input('image_path'));

            return response()->json([
                'success' => true,
                'message' => 'Image deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete image: '.$e->getMessage(),
            ], 422);
        }
    }
}
