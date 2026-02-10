<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ModuleRequest;
use App\Models\Module;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ModuleController extends Controller
{
    public function index(): View
    {
        $modules = Module::orderBy('name')->get();

        return view('admin.modules.index', compact('modules'));
    }

    public function store(ModuleRequest $request): RedirectResponse
    {
        Module::create($request->validated());

        return back()->with('success', 'Module created successfully.');
    }

    public function update(ModuleRequest $request, Module $module): RedirectResponse
    {
        $module->update($request->validated());

        return back()->with('success', 'Module updated successfully.');
    }

    public function destroy(Module $module): RedirectResponse
    {
        $module->delete();

        return back()->with('success', 'Module deleted successfully.');
    }
}
