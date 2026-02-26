<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\FeedbackRequest;
use App\Models\Feedback;
use App\Models\FeedbackImage;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FeedbackController extends Controller
{
    /**
     * Directory (relative to public path) where feedback screenshots are stored.
     */
    private const UPLOAD_DIR = 'uploads/feedback';

    public function __construct()
    {
        $this->authorizeResource(Feedback::class, 'feedback');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Feedback::query()->withCount('images');

        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%");
            });
        }

        $feedback = $query->latest()->paginate(10);

        return view('tenant.feedback.index', compact('feedback'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('tenant.feedback.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FeedbackRequest $request)
    {
        $data = $request->validated();

        $feedback = Feedback::create([
            'tenant_company_id' => auth()->user()->current_tenant_company_id,
            'created_by' => auth()->id(),
            'subject' => $data['subject'] ?? null,
            'message' => $data['message'],
            'status' => 'open',
        ]);

        if ($request->hasFile('screenshots')) {
            foreach ($request->file('screenshots') as $file) {
                if (! $file) {
                    continue;
                }

                try {
                    $originalName = $file->getClientOriginalName();
                    $extension = $file->getClientOriginalExtension();
                    $mime = $file->getClientMimeType();
                    $originalSize = $file->getSize();

                    $companyId = auth()->user()->current_tenant_company_id;

                    $fullDirectoryPath = public_path(self::UPLOAD_DIR.'/'.$companyId);
                    $this->ensureDirectoryExists($fullDirectoryPath);

                    $fileBaseName = $feedback->subject ?: ('feedback-'.$feedback->id);
                    $finalFileName = Str::slug($fileBaseName).'-'.uniqid().'.'.$extension;
                    $finalPath = $fullDirectoryPath.'/'.$finalFileName;

                    $compressedSize = $this->compressAndSaveImage($file, $finalPath, $extension);

                    $relativePath = self::UPLOAD_DIR.'/'.$companyId.'/'.$finalFileName;

                    FeedbackImage::create([
                        'tenant_company_id' => $companyId,
                        'feedback_id' => $feedback->id,
                        'name' => $fileBaseName,
                        'original_name' => $originalName,
                        'file_name' => $finalFileName,
                        'path' => $relativePath,
                        'mime_type' => $mime,
                        'size' => $compressedSize,
                    ]);
                } catch (Exception $e) {
                    Log::error('Feedback screenshot upload failed: '.$e->getMessage());
                }
            }
        }

        return redirect()->route('tenant.feedback.index')
            ->with('success', 'Feedback submitted successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Feedback $feedback)
    {
        $feedback->load(['images']);

        return view('tenant.feedback.show', compact('feedback'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Feedback $feedback)
    {
        foreach ($feedback->images as $img) {
            $filePath = public_path($img->path);
            if (File::exists($filePath)) {
                File::delete($filePath);
            }
        }

        $feedback->delete();

        return redirect()->route('tenant.feedback.index')
            ->with('success', 'Feedback deleted successfully.');
    }

    /**
     * Compress and save image using GD library
     */
    private function compressAndSaveImage($file, $destinationPath, $extension)
    {
        $tempPath = $file->getPathname();

        $maxWidth = 1200;
        $maxHeight = 1200;
        $jpegQuality = 70;
        $pngCompression = 6;

        $imageInfo = getimagesize($tempPath);
        if (! $imageInfo) {
            move_uploaded_file($tempPath, $destinationPath);

            return filesize($destinationPath);
        }

        $originalWidth = $imageInfo[0];
        $originalHeight = $imageInfo[1];

        $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
        if ($ratio < 1) {
            $newWidth = (int) ($originalWidth * $ratio);
            $newHeight = (int) ($originalHeight * $ratio);
        } else {
            $newWidth = $originalWidth;
            $newHeight = $originalHeight;
        }

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

        $newImage = imagecreatetruecolor($newWidth, $newHeight);

        if (in_array(strtolower($extension), ['png', 'gif'])) {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
            imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
        }

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
     * Ensure the upload directory exists and is writable.
     */
    private function ensureDirectoryExists(string $fullPath): void
    {
        if (! File::exists($fullPath)) {
            File::makeDirectory($fullPath, 0755, true);
        }
    }
}
