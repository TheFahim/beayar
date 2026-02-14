<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;

class ProductController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::with(['image', 'specifications'])->findOrFail($id);

        // Ensure tenant access
        if ($product->tenant_company_id !== auth()->user()->current_tenant_company_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $product,
        ]);
    }
}
