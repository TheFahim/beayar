<?php

namespace App\Http\Controllers;

use App\Models\Image as ImageModel;
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
        $query = ImageModel::query();

        // Search functionality (preserve original behavior)
        if ($request->has('search') && $request->search !== '') {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        // Paginate for infinite scroll
        $images = $query->latest()->paginate(12);

        if ($request->ajax()) {
            return response()->json([
                'html' => $this->renderImageGrid($images),
                'hasMore' => $images->hasMorePages(),
            ]);
        }

        return view('dashboard.images.index', compact('images'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validation (same rules as original)
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'required|image',
        ]);

        if (! $request->hasFile('image')) {
            return response()->json(['success' => false, 'message' => 'No image file found.'], 422);
        }

        try {
            $file = $request->file('image');

            // Capture metadata before moving the file
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $mime = $file->getClientMimeType();
            $originalSize = $file->getSize();

            $fullDirectoryPath = public_path(self::UPLOAD_DIR);
            $this->ensureDirectoryExists($fullDirectoryPath);

            $finalFileName = Str::slug($request->name).'-'.uniqid().'.'.$extension;
            $finalPath = $fullDirectoryPath.'/'.$finalFileName;

            // Compress and save the image
            $compressedSize = $this->compressAndSaveImage($file, $finalPath, $extension);

            $relativePath = self::UPLOAD_DIR.'/'.$finalFileName;

            $imageModel = ImageModel::create([
                'name' => $request->name,
                'original_name' => $originalName,
                'file_name' => $finalFileName,
                'path' => $relativePath,
                'mime_type' => $mime,
                'size' => $compressedSize, // Store compressed size
                // 'original_size' => $originalSize,   // Store original size for reference
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Image uploaded and compressed successfully!',
                'image' => $imageModel,
                'compression_ratio' => round((($originalSize - $compressedSize) / $originalSize) * 100, 2).'%',
            ]);

        } catch (Exception $e) {
            Log::error('File Upload Failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Server error: Could not upload the image. Please check logs.',
            ], 500);
        }
    }

    /**
     * Compress and save image using GD library (available on most shared hosting)
     */
    private function compressAndSaveImage($file, $destinationPath, $extension)
    {
        $tempPath = $file->getPathname();

        // Maximum dimensions (adjust based on your needs)

        $maxWidth = 800;
        $maxHeight = 800;
        $jpegQuality = 30;

        $pngCompression = 6; // 0-9, higher = smaller file

        // Get original image info
        $imageInfo = getimagesize($tempPath);
        $originalWidth = $imageInfo[0];
        $originalHeight = $imageInfo[1];

        // Calculate new dimensions while maintaining aspect ratio
        $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);

        // Only resize if image is larger than max dimensions
        if ($ratio < 1) {
            $newWidth = (int) ($originalWidth * $ratio);
            $newHeight = (int) ($originalHeight * $ratio);
        } else {
            $newWidth = $originalWidth;
            $newHeight = $originalHeight;
        }

        // Create image resource from uploaded file
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
            default:
                // Fallback: just move the file without compression
                move_uploaded_file($tempPath, $destinationPath);

                return filesize($destinationPath);
        }

        if (! $sourceImage) {
            // Fallback: just move the file without compression
            move_uploaded_file($tempPath, $destinationPath);

            return filesize($destinationPath);
        }

        // Create new image with calculated dimensions
        $newImage = imagecreatetruecolor($newWidth, $newHeight);

        // Preserve transparency for PNG and GIF
        if (in_array(strtolower($extension), ['png', 'gif'])) {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
            imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
        }

        // Resize the image
        imagecopyresampled(
            $newImage,
            $sourceImage,
            0,
            0,
            0,
            0,
            $newWidth,
            $newHeight,
            $originalWidth,
            $originalHeight
        );

        // Save compressed image
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
        }

        // Clean up memory
        imagedestroy($sourceImage);
        imagedestroy($newImage);

        return filesize($destinationPath);
    }

    /**
     * Search images
     */
    public function search(Request $request)
    {
        $queryString = $request->get('query', '');

        $images = ImageModel::where('name', 'like', '%'.$queryString.'%')
            ->orWhere('original_name', 'like', '%'.$queryString.'%')
            ->latest()
            ->paginate(12);

        if ($request->ajax()) {
            return response()->json([
                'html' => $this->renderImageGrid($images),
                'hasMore' => $images->hasMorePages(),
            ]);
        }

        // Preserve original behavior: return paginator JSON when not AJAX
        return response()->json($images);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $image = ImageModel::findOrFail($id);

        // Delete file from public path (preserve behavior)
        $filePath = public_path($image->path);
        if (File::exists($filePath)) {
            File::delete($filePath);
        }

        // Delete from database
        $image->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Image deleted successfully',
            ]);
        }

        return redirect()->route('images.index')
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

    /**
     * Render the images partial used for AJAX responses.
     *
     * @param  \Illuminate\Contracts\Pagination\LengthAwarePaginator  $images
     */
    private function renderImageGrid($images): string
    {
        return view('dashboard.images.partials.image-grid', compact('images'))->render();
    }
}
