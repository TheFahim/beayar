<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FeatureRequest;
use App\Models\Feature;
use App\Models\Module;

class FeatureController extends Controller
{
    public function index()
    {
        $features = Feature::with('module')->orderBy('sort_order')->orderBy('name')->get();
        $modules = Module::orderBy('name')->get();

        return view('admin.features.index', compact('features', 'modules'));
    }

    public function store(FeatureRequest $request)
    {
        Feature::create($request->validated());

        return redirect()->route('admin.features.index')->with('success', 'Feature created successfully.');
    }

    public function update(FeatureRequest $request, Feature $feature)
    {
        $feature->update($request->validated());

        return redirect()->route('admin.features.index')->with('success', 'Feature updated successfully.');
    }

    public function destroy(Feature $feature)
    {
        $feature->delete();

        return redirect()->route('admin.features.index')->with('success', 'Feature deleted successfully.');
    }
}
