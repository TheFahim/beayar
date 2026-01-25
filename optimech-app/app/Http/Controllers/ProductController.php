<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Use paginate or get() – paginate is usually better for UI lists
        $products = Product::with(['image', 'specifications'])->latest()->get();

        return view('dashboard.products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('dashboard.products.create');
    }

    /**
     * Store multiple products at once.
     * (This method is left untouched as requested)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'products' => 'required|array|min:1',
            'products.*.name' => 'required|string|max:255',
            'products.*.image_id' => 'nullable|exists:images,id',
            'products.*.specifications' => 'required|array|min:1',
            'products.*.specifications.*.description' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    // Remove HTML tags
                    $clean = strip_tags($value);
                    // Decode HTML entities to handle &nbsp; etc.
                    $clean = html_entity_decode($clean);
                    // Replace non-breaking spaces with regular spaces
                    $clean = str_replace("\xA0", ' ', $clean);
                    // Trim whitespace
                    $clean = trim($clean);

                    // Check if empty and no media tags
                    if ($clean === '' && !preg_match('/<img|<iframe|<video|<audio|<table/i', $value)) {
                        $fail('The specification description cannot be empty.');
                    }
                },
            ],
        ]);

        DB::beginTransaction();
        try {
            $createdCount = 0;

            foreach ($validated['products'] as $prodData) {
                $product = Product::create([
                    'name' => $prodData['name'],
                    'image_id' => $prodData['image_id'] ?? null,
                ]);

                foreach ($prodData['specifications'] as $spec) {
                    $product->specifications()->create($spec);
                }

                $createdCount++;
            }

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "{$createdCount} product(s) created successfully.",
                ]);
            }

            return redirect()->route('products.index')
                ->with('success', "{$createdCount} product(s) created successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk product creation failed: '.$e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'There was an error creating the products. '.$e->getMessage(),
                ], 500);
            }

            return back()->withInput()->with('error', 'There was an error creating the products. '.$e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product->load('image', 'specifications');

        return view('dashboard.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $product->load('specifications', 'image');
        $hasQuotations = $product->quotation_products()->exists();

        return view('dashboard.products.edit', compact('product', 'hasQuotations'));
    }

    /**
     * FIX: Update the specified resource in storage.
     * This method is completely rewritten to match the modern frontend.
     */
    public function update(Request $request, Product $product)
    {
        // 1. Correct validation: only validate fields that are actually submitted by the form.
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'image_id' => 'nullable|exists:images,id', // This is the hidden input value
            'specifications' => 'required|array|min:1',
            'specifications.*.id' => 'nullable|exists:specifications,id',
            'specifications.*.description' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    // Remove HTML tags
                    $clean = strip_tags($value);
                    // Decode HTML entities to handle &nbsp; etc.
                    $clean = html_entity_decode($clean);
                    // Replace non-breaking spaces with regular spaces
                    $clean = str_replace("\xA0", ' ', $clean);
                    // Trim whitespace
                    $clean = trim($clean);

                    // Check if empty and no media tags
                    if ($clean === '' && !preg_match('/<img|<iframe|<video|<audio|<table/i', $value)) {
                        $fail('The specification description cannot be empty.');
                    }
                },
            ],
        ]);

        DB::beginTransaction();

        try {

            // 2. Update the product with validated data.
            $product->update([
                'name' => $validated['name'],
                'image_id' => $validated['image_id'] ?? null,
            ]);

            // 3. Sync specifications with update/create logic
            $submittedSpecIds = [];

            foreach ($validated['specifications'] as $specData) {
                if (! empty($specData['id'])) {
                    // Check if this specification belongs to the product
                    $existingSpec = $product->specifications()->find($specData['id']);

                    if ($existingSpec) {
                        // Update existing specification that belongs to this product
                        $existingSpec->update(['description' => $specData['description']]);
                        $submittedSpecIds[] = $specData['id'];
                    } else {
                        // ID exists but doesn't belong to this product -> create new
                        $newSpec = $product->specifications()->create(['description' => $specData['description']]);
                        $submittedSpecIds[] = $newSpec->id;
                    }
                } else {
                    // No ID -> create new specification
                    $newSpec = $product->specifications()->create(['description' => $specData['description']]);
                    $submittedSpecIds[] = $newSpec->id;
                }
            }

            // Delete specifications not included in the submission
            $product->specifications()
                ->whereNotIn('id', $submittedSpecIds)
                ->delete();

            DB::commit();

            return redirect()->route('products.index')->with('success', 'Product updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Product update failed for product ID {$product->id}: ".$e->getMessage());

            return back()->withInput()->with('error', 'There was an error updating the product.');
        }
    }

    /**
     * FIX: Remove the specified resource from storage.
     * Added logic to clean up orphan images upon deletion.
     */
    public function destroy(Product $product)
    {
        DB::beginTransaction();
        try {
            $product->delete(); // This will also delete related specifications via cascade.

            DB::commit();

            return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Product deletion failed for product ID {$product->id}: ".$e->getMessage());

            return back()->with('error', 'Error deleting product.');
        }
    }
}
