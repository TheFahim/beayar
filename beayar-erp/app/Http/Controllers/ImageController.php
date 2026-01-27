<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Exception;
use Illuminate\Http\Request;
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

            // Get current company ID for folder isolation
            $companyId = auth()->user()->current_user_company_id;

            $fullDirectoryPath = public_path(self::UPLOAD_DIR . '/' . $companyId);
            if (!file_exists($fullDirectoryPath)) {
                mkdir($fullDirectoryPath, 0755, true);
            }

            $finalFileName = Str::slug($request->name).'-'.uniqid().'.'.$extension;
            $finalPath = $fullDirectoryPath.'/'.$finalFileName;

            // Simple move for now, assuming Intervention Image is not yet configured or handled in a separate service
            // In optimech there was compressAndSaveImage method, I'll simulate or copy it if I can access the Controller again.
            // For now, let's use move() to ensure basic functionality.
            $file->move($fullDirectoryPath, $finalFileName);
            $compressedSize = filesize($finalPath);

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
            ]);

        } catch (Exception $e) {
            Log::error('File Upload Failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'File upload failed: '.$e->getMessage(),
            ], 500);
        }
    }
}
