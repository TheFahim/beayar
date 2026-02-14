<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Product::with(['image', 'specifications']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        $products = $query->latest()->paginate(12);

        return view('tenant.products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('tenant.products.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'products' => 'required|array|min:1',
            'products.*.name' => 'required|string|max:255',
            'products.*.image_id' => 'nullable|exists:images,id',
            'products.*.specifications' => 'nullable|array',
            'products.*.specifications.*.description' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $createdCount = 0;
            $tenantCompanyId = auth()->user()->current_tenant_company_id;

            foreach ($validated['products'] as $prodData) {
                $product = Product::create([
                    'tenant_company_id' => $tenantCompanyId,
                    'name' => $prodData['name'],
                    'image_id' => $prodData['image_id'] ?? null,
                ]);

                if (isset($prodData['specifications'])) {
                    foreach ($prodData['specifications'] as $spec) {
                        $product->specifications()->create([
                            'description' => $spec['description'],
                        ]);
                    }
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

            return redirect()->route('tenant.products.index')
                ->with('success', "{$createdCount} product(s) created successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product creation failed: '.$e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create products. '.$e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Failed to create products.')->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $product = Product::with(['image', 'specifications'])->findOrFail($id);

        // Ensure tenant access
        if ($product->tenant_company_id !== auth()->user()->current_tenant_company_id) {
            abort(403);
        }

        return view('tenant.products.edit', compact('product'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $product = Product::findOrFail($id);

        // Ensure tenant access
        if ($product->tenant_company_id !== auth()->user()->current_tenant_company_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'image_id' => 'nullable|exists:images,id',
            'specifications' => 'nullable|array',
            'specifications.*.description' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $product->update([
                'name' => $validated['name'],
                'image_id' => $validated['image_id'] ?? null,
            ]);

            // Sync specifications: Delete all and recreate
            $product->specifications()->delete();

            if (isset($validated['specifications'])) {
                foreach ($validated['specifications'] as $spec) {
                    $product->specifications()->create([
                        'description' => $spec['description'],
                    ]);
                }
            }

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Product updated successfully.',
                ]);
            }

            return redirect()->route('tenant.products.index')
                ->with('success', 'Product updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product update failed: '.$e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update product. '.$e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Failed to update product.')->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);

        // Ensure tenant access
        if ($product->tenant_company_id !== auth()->user()->current_tenant_company_id) {
            abort(403);
        }

        if (! $product->is_deletable) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete product because it is used in quotations or challans.',
                ], 422);
            }

            return back()->with('error', 'Cannot delete product because it is used in quotations or challans.');
        }

        $product->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully',
            ]);
        }

        return redirect()->route('tenant.products.index')
            ->with('success', 'Product deleted successfully');
    }
}
