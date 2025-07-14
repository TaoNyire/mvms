<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Task;
use App\Models\Opportunity;
use App\Models\Assignment;

class TaskController extends Controller
{
    /**
     * Display tasks for an opportunity
     */
    public function index(Request $request, Opportunity $opportunity)
    {
        // Check if user owns this opportunity
        if ($opportunity->organization_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this opportunity.');
        }

        $tasks = Task::where('opportunity_id', $opportunity->id)
            ->with(['assignments.volunteer', 'creator'])
            ->orderBy('start_datetime', 'asc')
            ->paginate(10);

        return view('organization.tasks.index', compact('opportunity', 'tasks'));
    }

    /**
     * Show the form for creating a new task
     */
    public function create(Opportunity $opportunity)
    {
        // Check if user owns this opportunity
        if ($opportunity->organization_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this opportunity.');
        }

        return view('organization.tasks.create', compact('opportunity'));
    }

    /**
     * Store a newly created task
     */
    public function store(Request $request, Opportunity $opportunity)
    {
        // Check if user owns this opportunity
        if ($opportunity->organization_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this opportunity.');
        }

        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:20',
            'instructions' => 'nullable|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'start_datetime' => 'required|date|after:now',
            'end_datetime' => 'required|date|after:start_datetime',
            'location_type' => 'required|in:physical,remote,hybrid',
            'location_address' => 'required_if:location_type,physical,hybrid|string',
            'volunteers_needed' => 'required|integer|min:1|max:50',
            'required_skills' => 'nullable|array',
            'assignment_type' => 'required|in:manual,automatic,first_come',
            'assignment_deadline' => 'nullable|date|before:start_datetime',
            'special_requirements' => 'nullable|string',
            'estimated_hours' => 'nullable|integer|min:1',
            'equipment_needed' => 'nullable|array',
            'safety_requirements' => 'nullable|string',
        ];

        $validatedData = $request->validate($rules);

        // Set additional fields
        $validatedData['opportunity_id'] = $opportunity->id;
        $validatedData['created_by'] = Auth::id();
        $validatedData['allow_self_assignment'] = $request->has('allow_self_assignment');
        $validatedData['requires_check_in'] = $request->has('requires_check_in');
        $validatedData['requires_check_out'] = $request->has('requires_check_out');

        // Calculate duration in minutes
        $start = new \DateTime($validatedData['start_datetime']);
        $end = new \DateTime($validatedData['end_datetime']);
        $validatedData['duration_minutes'] = $start->diff($end)->h * 60 + $start->diff($end)->i;

        $task = Task::create($validatedData);

        if ($request->has('publish_now')) {
            $task->publish();
            $message = 'Task created and published successfully!';
        } else {
            $message = 'Task created as draft. You can publish it later.';
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'task' => $task
            ]);
        }

        return redirect()->route('tasks.show', [$opportunity, $task])
            ->with('success', $message);
    }

    /**
     * Display the specified task
     */
    public function show(Opportunity $opportunity, Task $task)
    {
        // Check if user owns this opportunity
        if ($opportunity->organization_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this opportunity.');
        }

        // Check if task belongs to opportunity
        if ($task->opportunity_id !== $opportunity->id) {
            abort(404, 'Task not found in this opportunity.');
        }

        $task->load([
            'assignments.volunteer.volunteerProfile',
            'creator',
            'opportunity'
        ]);

        return view('organization.tasks.show', compact('opportunity', 'task'));
    }

    /**
     * Show the form for editing the task
     */
    public function edit(Opportunity $opportunity, Task $task)
    {
        // Check if user owns this opportunity
        if ($opportunity->organization_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this opportunity.');
        }

        // Check if task belongs to opportunity
        if ($task->opportunity_id !== $opportunity->id) {
            abort(404, 'Task not found in this opportunity.');
        }

        return view('organization.tasks.edit', compact('opportunity', 'task'));
    }

    /**
     * Update the specified task
     */
    public function update(Request $request, Opportunity $opportunity, Task $task)
    {
        // Check if user owns this opportunity
        if ($opportunity->organization_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this opportunity.');
        }

        // Check if task belongs to opportunity
        if ($task->opportunity_id !== $opportunity->id) {
            abort(404, 'Task not found in this opportunity.');
        }

        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:20',
            'priority' => 'required|in:low,medium,high,urgent',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
            'volunteers_needed' => 'required|integer|min:1|max:50',
        ];

        $validatedData = $request->validate($rules);

        // Calculate duration in minutes
        $start = new \DateTime($validatedData['start_datetime']);
        $end = new \DateTime($validatedData['end_datetime']);
        $validatedData['duration_minutes'] = $start->diff($end)->h * 60 + $start->diff($end)->i;

        $task->update($validatedData);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Task updated successfully!',
                'task' => $task
            ]);
        }

        return redirect()->route('tasks.show', [$opportunity, $task])
            ->with('success', 'Task updated successfully!');
    }

    /**
     * Remove the specified task
     */
    public function destroy(Opportunity $opportunity, Task $task)
    {
        // Check if user owns this opportunity
        if ($opportunity->organization_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this opportunity.');
        }

        // Check if task belongs to opportunity
        if ($task->opportunity_id !== $opportunity->id) {
            abort(404, 'Task not found in this opportunity.');
        }

        // Check if task has accepted assignments
        if ($task->acceptedAssignments()->count() > 0) {
            return back()->with('error', 'Cannot delete task with accepted assignments.');
        }

        $task->delete();

        return redirect()->route('tasks.index', $opportunity)
            ->with('success', 'Task deleted successfully!');
    }

    /**
     * Publish a task
     */
    public function publish(Opportunity $opportunity, Task $task)
    {
        // Check if user owns this opportunity
        if ($opportunity->organization_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this opportunity.');
        }

        // Check if task belongs to opportunity
        if ($task->opportunity_id !== $opportunity->id) {
            abort(404, 'Task not found in this opportunity.');
        }

        $task->publish();

        return back()->with('success', 'Task published successfully!');
    }

    /**
     * Complete a task
     */
    public function complete(Request $request, Opportunity $opportunity, Task $task)
    {
        // Check if user owns this opportunity
        if ($opportunity->organization_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this opportunity.');
        }

        // Check if task belongs to opportunity
        if ($task->opportunity_id !== $opportunity->id) {
            abort(404, 'Task not found in this opportunity.');
        }

        $notes = $request->input('completion_notes');
        $task->complete($notes);

        return back()->with('success', 'Task marked as completed!');
    }

    /**
     * Cancel a task
     */
    public function cancel(Request $request, Opportunity $opportunity, Task $task)
    {
        // Check if user owns this opportunity
        if ($opportunity->organization_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this opportunity.');
        }

        // Check if task belongs to opportunity
        if ($task->opportunity_id !== $opportunity->id) {
            abort(404, 'Task not found in this opportunity.');
        }

        $task->update([
            'status' => 'cancelled',
            'completion_notes' => $request->input('cancellation_reason')
        ]);

        // Cancel all pending assignments
        $task->assignments()->where('status', 'pending')->update([
            'status' => 'cancelled'
        ]);

        return back()->with('success', 'Task cancelled successfully!');
    }
}
