<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TenantCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class TaskCalendarController extends Controller
{
    /**
     * Display the task calendar.
     */
    public function index()
    {
        $tenantId = Auth::user()->getCurrentCompanyId();
        $company = TenantCompany::findOrFail($tenantId);
        
        // Fetch employees/members for the assign dropdown
        $employees = $company->members()->get();

        return view('tenant.tasks.calendar', compact('employees'));
    }

    /**
     * Get tasks for FullCalendar.
     */
    public function getTasks(Request $request)
    {
        // Global scope in BelongsToCompany handles the tenant_company_id filtering
        $tasks = Task::all();

        $formattedTasks = $tasks->map(function ($task) {
            return [
                'id' => $task->id,
                'title' => $task->title,
                'start' => $task->start_date->toIso8601String(),
                'end' => $task->end_date->toIso8601String(),
                'description' => $task->description,
                'assigned_to' => $task->assigned_to,
                'status' => $task->status,
                'color' => $this->getStatusColor($task->status),
            ];
        });

        return response()->json($formattedTasks);
    }

    /**
     * Store a newly created task.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'assigned_to' => 'required|exists:users,id',
            'status' => 'required|in:pending,in_progress,completed',
        ]);

        $validated['created_by'] = Auth::id();
        
        // tenant_company_id is auto-assigned via BelongsToCompany trait

        $task = Task::create($validated);

        return response()->json([
            'success' => true,
            'task' => $task,
            'message' => 'Task created successfully.'
        ]);
    }

    /**
     * Update the specified task.
     */
    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after_or_equal:start_date',
            'assigned_to' => 'sometimes|required|exists:users,id',
            'status' => 'sometimes|required|in:pending,in_progress,completed',
        ]);

        $task->update($validated);

        return response()->json([
            'success' => true,
            'task' => $task,
            'message' => 'Task updated successfully.'
        ]);
    }

    /**
     * Remove the specified task.
     */
    public function destroy(Task $task)
    {
        $task->delete();

        return response()->json([
            'success' => true,
            'message' => 'Task deleted successfully.'
        ]);
    }

    /**
     * Get color based on task status.
     */
    private function getStatusColor($status)
    {
        return match ($status) {
            'pending' => '#f59e0b', // Amber
            'in_progress' => '#3b82f6', // Blue
            'completed' => '#10b981', // Emerald
            default => '#6b7280', // Gray
        };
    }
}
