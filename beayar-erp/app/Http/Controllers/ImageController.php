<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ImageController extends Controller
{
    /**
     * Directory (relative to public path) where images are stored.
     */
    private const UPLOAD_DIR = 'uploads/images';

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Scoped by BelongsToCompany trait automatically
        $query = Image::query();

        if ($request->has('search') && $request->search !== '') {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $images = $query->latest()->paginate(12);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('tenant.images.partials.image-grid', compact('images'))->render(),
                'hasMore' => $images->hasMorePages(),
            ]);
        }

        return view('tenant.images.index', compact('images'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'required|image',
        ]);

        if (! $request->hasFile('image')) {
            return response()->json(['success' => false, 'message' => 'No image file found.'], 422);
        }

        try {
            $file = $request->file('image');

            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $mime = $file->getClientMimeType();
            $originalSize = $file->getSize();

            // Get current company ID for folder isolation
            $companyId = auth()->user()->current_user_company_id;

            $fullDirectoryPath = public_path(self::UPLOAD_DIR . '/' . $companyId);
            $this->ensureDirectoryExists($fullDirectoryPath);

            $finalFileName = Str::slug($request->name).'-'.uniqid().'.'.$extension;
            $finalPath = $fullDirectoryPath.'/'.$finalFileName;

            // Compress and save the image
            $compressedSize = $this->compressAndSaveImage($file, $finalPath, $extension);

            $relativePath = self::UPLOAD_DIR . '/' . $companyId . '/' . $finalFileName;

            $imageModel = Image::create([
                'user_company_id' => $companyId,
                'name' => $request->name,
                'original_name' => $originalName,
                'file_name' => $finalFileName,
                'path' => $relativePath,
                'mime_type' => $mime,
                'size' => $compressedSize,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Image uploaded successfully!',
                'image' => $imageModel,
                'compression_ratio' => $originalSize > 0 ? round((($originalSize - $compressedSize) / $originalSize) * 100, 2).'%' : '0%',
            ]);

        } catch (Exception $e) {
            Log::error('File Upload Failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'File upload failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Compress and save image using GD library
     */
    private function compressAndSaveImage($file, $destinationPath, $extension)
    {
        $tempPath = $file->getPathname();

        // Maximum dimensions
        $maxWidth = 800;
        $maxHeight = 800;
        $jpegQuality = 60; // Slightly higher quality than 30
        $pngCompression = 6;

        // Get original image info
        $imageInfo = getimagesize($tempPath);
        if (!$imageInfo) {
             // Fallback if not an image or unreadable
             move_uploaded_file($tempPath, $destinationPath);
             return filesize($destinationPath);
        }

        $originalWidth = $imageInfo[0];
        $originalHeight = $imageInfo[1];

        // Calculate new dimensions
        $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);

        // Only resize if image is larger than max dimensions
        if ($ratio < 1) {
            $newWidth = (int) ($originalWidth * $ratio);
            $newHeight = (int) ($originalHeight * $ratio);
        } else {
            $newWidth = $originalWidth;
            $newHeight = $originalHeight;
        }

        // Create image resource
        $sourceImage = null;
        switch (strtolower($extension)) {
            case 'jpg':
            case 'jpeg':
                $sourceImage = imagecreatefromjpeg($tempPath);
                break;
            case 'png':
                $sourceImage = imagecreatefrompng($tempPath);
                break;
            case 'gif':
                $sourceImage = imagecreatefromgif($tempPath);
                break;
            case 'webp':
                $sourceImage = imagecreatefromwebp($tempPath);
                break;
        }

        if (! $sourceImage) {
            move_uploaded_file($tempPath, $destinationPath);
            return filesize($destinationPath);
        }

        // Create new image
        $newImage = imagecreatetruecolor($newWidth, $newHeight);

        // Preserve transparency
        if (in_array(strtolower($extension), ['png', 'gif'])) {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
            imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
        }

        // Resize
        imagecopyresampled(
            $newImage,
            $sourceImage,
            0, 0, 0, 0,
            $newWidth, $newHeight,
            $originalWidth, $originalHeight
        );

        // Save
        switch (strtolower($extension)) {
            case 'jpg':
            case 'jpeg':
                imagejpeg($newImage, $destinationPath, $jpegQuality);
                break;
            case 'png':
                imagepng($newImage, $destinationPath, $pngCompression);
                break;
            case 'gif':
                imagegif($newImage, $destinationPath);
                break;
            case 'webp':
                imagewebp($newImage, $destinationPath, $jpegQuality);
                break;
            default:
                 imagejpeg($newImage, $destinationPath, $jpegQuality);
        }

        imagedestroy($sourceImage);
        imagedestroy($newImage);

        return filesize($destinationPath);
    }

    /**
     * Search images (API for modal)
     */
    public function search(Request $request)
    {
        $query = Image::query();

        if ($request->has('query') && $request->get('query') !== '') {
            $queryString = $request->get('query');
            $query->where('name', 'like', '%'.$queryString.'%')
                  ->orWhere('original_name', 'like', '%'.$queryString.'%');
        }

        $images = $query->latest()->paginate(12);

        return response()->json($images);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image',
        ]);

        $image = Image::findOrFail($id);
        
        // Ensure user belongs to the same company
        if ($image->user_company_id !== auth()->user()->current_user_company_id) {
            abort(403);
        }

        $data = [
            'name' => $request->name,
        ];

        if ($request->hasFile('image')) {
            try {
                // 1. Delete old file
                $oldFilePath = public_path($image->path);
                if (File::exists($oldFilePath)) {
                    File::delete($oldFilePath);
                }

                // 2. Process new file
                $file = $request->file('image');
                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $mime = $file->getClientMimeType();
                
                // Reuse company ID
                $companyId = $image->user_company_id;
                
                $fullDirectoryPath = public_path(self::UPLOAD_DIR . '/' . $companyId);
                $this->ensureDirectoryExists($fullDirectoryPath);
                
                $finalFileName = Str::slug($request->name).'-'.uniqid().'.'.$extension;
                $finalPath = $fullDirectoryPath.'/'.$finalFileName;
                
                // Compress and save
                $compressedSize = $this->compressAndSaveImage($file, $finalPath, $extension);
                
                $relativePath = self::UPLOAD_DIR . '/' . $companyId . '/' . $finalFileName;

                $data['original_name'] = $originalName;
                $data['file_name'] = $finalFileName;
                $data['path'] = $relativePath;
                $data['mime_type'] = $mime;
                $data['size'] = $compressedSize;
            } catch (Exception $e) {
                Log::error('Image Update Failed: '.$e->getMessage());
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to process image: ' . $e->getMessage(),
                    ], 500);
                }
                return redirect()->back()->with('error', 'Failed to update image.');
            }
        }

        $image->update($data);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Image updated successfully',
                'image' => $image,
            ]);
        }

        return redirect()->back()->with('success', 'Image updated successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    // public function update(Request $request, string $id)
    // {
    //     $request->validate([
    //         'name' => 'required|string|max:255',
    //     ]);

    //     $image = Image::findOrFail($id);
        
    //     // Ensure user belongs to the same company
    //     if ($image->user_company_id !== auth()->user()->current_user_company_id) {
    //         abort(403);
    //     }

    //     $image->update([
    //         'name' => $request->name,
    //     ]);

    //     if ($request->ajax()) {
    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Image updated successfully',
    //             'image' => $image,
    //         ]);
    //     }

    //     return redirect()->back()->with('success', 'Image updated successfully');
    // }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Find image scoped by tenant
        $image = Image::findOrFail($id);

        // Delete file
        $filePath = public_path($image->path);
        if (File::exists($filePath)) {
            File::delete($filePath);
        }

        // Delete from DB
        $image->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Image deleted successfully',
            ]);
        }

        return redirect()->route('tenant.images.index')
            ->with('success', 'Image deleted successfully');
    }

    /**
     * Ensure the upload directory exists and is writable.
     */
    private function ensureDirectoryExists(string $fullPath): void
    {
        if (! File::exists($fullPath)) {
            File::makeDirectory($fullPath, 0755, true);
        }
    }
}
