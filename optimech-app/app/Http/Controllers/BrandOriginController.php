<?php

namespace App\Http\Controllers;

use App\Models\BrandOrigin;
use Illuminate\Http\Request;

class BrandOriginController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Create a new brand origin
        $brandOrigin = BrandOrigin::create($validatedData);

        // Return a JSON response
        return response()->json([
            'message' => 'Brand origin created successfully',
            'brandOrigin' => $brandOrigin,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(BrandOrigin $brandOrigin)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BrandOrigin $brandOrigin)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BrandOrigin $brandOrigin)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Update the brand origin
        $brandOrigin->update($validatedData);

        // Return a JSON response
        return response()->json([
            'message' => 'Brand origin updated successfully',
            'brandOrigin' => $brandOrigin,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BrandOrigin $brandOrigin)
    {
        // Delete the brand origin
        $brandOrigin->delete();

        // Return a JSON response
        return response()->json([
            'message' => 'Brand origin deleted successfully',
            'brandOrigin' => $brandOrigin,
        ], 200);
    }

    /**
     * Search for brand origins.
     */
    public function search(Request $request)
    {
        $query = $request->input('query');
        $brandOrigins = BrandOrigin::where('name', 'like', "%{$query}%")
            ->orderBy('name')
            ->get();

        return response()->json($brandOrigins);
    }
}
