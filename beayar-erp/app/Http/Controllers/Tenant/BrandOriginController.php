<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\BrandOrigin;
use Illuminate\Http\Request;

class BrandOriginController extends Controller
{
    public function search(Request $request)
    {
        $query = BrandOrigin::query();

        if ($request->has('query') && $request->get('query')) {
            $search = $request->get('query');
            $query->where('name', 'like', "%{$search}%");
        }

        return response()->json(
            $query->orderBy('name')->get(['id', 'name', 'country'])
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'country' => 'nullable|string|max:255',
        ]);

        $brandOrigin = BrandOrigin::create($validated);

        return response()->json([
            'brandOrigin' => $brandOrigin->only(['id', 'name', 'country']),
        ]);
    }

    public function update(Request $request, BrandOrigin $brandOrigin)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'country' => 'nullable|string|max:255',
        ]);

        $brandOrigin->update($validated);

        return response()->json([
            'brandOrigin' => $brandOrigin->only(['id', 'name', 'country']),
        ]);
    }

    public function destroy(BrandOrigin $brandOrigin)
    {
        $brandOrigin->delete();

        return response()->json([
            'success' => true,
        ]);
    }
}
