<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Assignment;
use App\Models\Task;
use App\Models\User;

class AssignmentController extends Controller
{
    /**
     * Assign volunteer to task (Organization)
     */
    public function store(Request $request, Task $task)
    {
        $user = Auth::user();

        // Check if user owns the task's opportunity
        if ($task->opportunity->organization_id !== $user->id) {
            abort(403, 'Unauthorized access to this task.');
        }

        $rules = [
            'volunteer_id' => 'required|exists:users,id',
            'assignment_notes' => 'nullable|string|max:1000',
            'override_conflicts' => 'boolean',
        ];

        $validatedData = $request->validate($rules);

        $volunteer = User::findOrFail($validatedData['volunteer_id']);

        try {
            $assignment = $task->assignVolunteer($volunteer, $user, [
                'method' => 'manual',
                'notes' => $validatedData['assignment_notes'],
                'override_conflicts' => $validatedData['override_conflicts'] ?? false,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Volunteer assigned successfully!',
                    'assignment' => $assignment
                ]);
            }

            return back()->with('success', 'Volunteer assigned successfully!');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Show assignment details
     */
    public function show(Assignment $assignment)
    {
        $user = Auth::user();

        // Check if user can view this assignment
        if ($assignment->volunteer_id !== $user->id &&
            $assignment->task->opportunity->organization_id !== $user->id) {
            abort(403, 'Unauthorized access to this assignment.');
        }

        $assignment->load(['task.opportunity', 'volunteer.volunteerProfile', 'assigner']);

        return view('assignments.show', compact('assignment'));
    }

    /**
     * Accept assignment (Volunteer)
     */
    public function accept(Request $request, Assignment $assignment)
    {
        $user = Auth::user();

        // Check if user owns this assignment
        if ($assignment->volunteer_id !== $user->id) {
            abort(403, 'Unauthorized access to this assignment.');
        }

        if ($assignment->status !== 'pending') {
            return back()->with('error', 'Assignment cannot be accepted in its current state.');
        }

        $notes = $request->input('notes');
        $assignment->accept($notes);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Assignment accepted successfully!',
                'assignment' => $assignment
            ]);
        }

        return back()->with('success', 'Assignment accepted! You will receive further details soon.');
    }

    /**
     * Decline assignment (Volunteer)
     */
    public function decline(Request $request, Assignment $assignment)
    {
        $user = Auth::user();

        // Check if user owns this assignment
        if ($assignment->volunteer_id !== $user->id) {
            abort(403, 'Unauthorized access to this assignment.');
        }

        if ($assignment->status !== 'pending') {
            return back()->with('error', 'Assignment cannot be declined in its current state.');
        }

        $reason = $request->input('reason');
        $assignment->decline($reason);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Assignment declined.',
                'assignment' => $assignment
            ]);
        }

        return back()->with('success', 'Assignment declined. Thank you for letting us know.');
    }

    /**
     * Check in to assignment (Volunteer)
     */
    public function checkIn(Request $request, Assignment $assignment)
    {
        $user = Auth::user();

        // Check if user owns this assignment
        if ($assignment->volunteer_id !== $user->id) {
            abort(403, 'Unauthorized access to this assignment.');
        }

        try {
            $location = $request->input('location');
            $notes = $request->input('notes');

            $assignment->checkIn($location, $notes);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Checked in successfully!',
                    'assignment' => $assignment
                ]);
            }

            return back()->with('success', 'Checked in successfully! Have a great volunteering session.');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Check out from assignment (Volunteer)
     */
    public function checkOut(Request $request, Assignment $assignment)
    {
        $user = Auth::user();

        // Check if user owns this assignment
        if ($assignment->volunteer_id !== $user->id) {
            abort(403, 'Unauthorized access to this assignment.');
        }

        try {
            $location = $request->input('location');
            $notes = $request->input('notes');
            $taskCompleted = $request->boolean('task_completed');

            $assignment->checkOut($location, $notes, $taskCompleted);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Checked out successfully!',
                    'assignment' => $assignment
                ]);
            }

            return back()->with('success', 'Checked out successfully! Thank you for your service.');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Cancel assignment (Organization)
     */
    public function cancel(Request $request, Assignment $assignment)
    {
        $user = Auth::user();

        // Check if user owns the task's opportunity
        if ($assignment->task->opportunity->organization_id !== $user->id) {
            abort(403, 'Unauthorized access to this assignment.');
        }

        $assignment->update([
            'status' => 'cancelled',
            'assignment_notes' => $request->input('reason')
        ]);

        // Update task volunteers count
        $assignment->task->decrement('volunteers_assigned');

        return back()->with('success', 'Assignment cancelled successfully.');
    }

    /**
     * Mark assignment as no show (Organization)
     */
    public function markNoShow(Request $request, Assignment $assignment)
    {
        $user = Auth::user();

        // Check if user owns the task's opportunity
        if ($assignment->task->opportunity->organization_id !== $user->id) {
            abort(403, 'Unauthorized access to this assignment.');
        }

        $notes = $request->input('notes');
        $assignment->markAsNoShow($notes);

        return back()->with('success', 'Assignment marked as no show.');
    }

    /**
     * Resolve conflict (Organization)
     */
    public function resolveConflict(Request $request, Assignment $assignment)
    {
        $user = Auth::user();

        // Check if user owns the task's opportunity
        if ($assignment->task->opportunity->organization_id !== $user->id) {
            abort(403, 'Unauthorized access to this assignment.');
        }

        $resolution = $request->input('resolution', 'resolved');
        $assignment->resolveConflict($resolution);

        return back()->with('success', 'Conflict resolved successfully.');
    }

    /**
     * Bulk assign volunteers (Organization)
     */
    public function bulkAssign(Request $request, Task $task)
    {
        $user = Auth::user();

        // Check if user owns the task's opportunity
        if ($task->opportunity->organization_id !== $user->id) {
            abort(403, 'Unauthorized access to this task.');
        }

        $rules = [
            'volunteer_ids' => 'required|array',
            'volunteer_ids.*' => 'exists:users,id',
            'assignment_notes' => 'nullable|string',
            'override_conflicts' => 'boolean',
        ];

        $validatedData = $request->validate($rules);

        $successCount = 0;
        $errors = [];

        foreach ($validatedData['volunteer_ids'] as $volunteerId) {
            try {
                $volunteer = User::findOrFail($volunteerId);
                $task->assignVolunteer($volunteer, $user, [
                    'method' => 'manual',
                    'notes' => $validatedData['assignment_notes'],
                    'override_conflicts' => $validatedData['override_conflicts'] ?? false,
                ]);
                $successCount++;
            } catch (\Exception $e) {
                $errors[] = "Failed to assign volunteer {$volunteerId}: " . $e->getMessage();
            }
        }

        $message = "{$successCount} volunteers assigned successfully.";
        if (!empty($errors)) {
            $message .= " Errors: " . implode(', ', $errors);
        }

        return back()->with('success', $message);
    }

    /**
     * Get volunteer assignments for calendar
     */
    public function calendar(Request $request)
    {
        $user = Auth::user();

        $assignments = Assignment::where('volunteer_id', $user->id)
            ->whereIn('status', ['pending', 'accepted'])
            ->with(['task.opportunity'])
            ->get();

        $events = $assignments->map(function ($assignment) {
            return [
                'id' => $assignment->id,
                'title' => $assignment->task->title,
                'start' => $assignment->scheduled_start->toISOString(),
                'end' => $assignment->scheduled_end->toISOString(),
                'color' => $assignment->status === 'accepted' ? '#28a745' : '#ffc107',
                'url' => route('assignments.show', $assignment),
                'extendedProps' => [
                    'status' => $assignment->status,
                    'opportunity' => $assignment->task->opportunity->title,
                    'location' => $assignment->task->location_address,
                ]
            ];
        });

        if ($request->expectsJson()) {
            return response()->json($events);
        }

        return view('volunteer.calendar', compact('events'));
    }
}
