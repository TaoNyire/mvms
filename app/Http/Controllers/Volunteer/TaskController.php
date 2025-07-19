<?php

namespace App\Http\Controllers\Volunteer;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    /**
     * Display volunteer's assigned tasks
     */
    public function index(Request $request)
    {
        $status = $request->get('status', 'all');
        $priority = $request->get('priority', 'all');

        $query = Assignment::where('volunteer_id', Auth::id())
            ->with(['task.opportunity.organization', 'assignedBy']);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($priority !== 'all') {
            $query->whereHas('task', function($q) use ($priority) {
                $q->where('priority', $priority);
            });
        }

        $assignments = $query->orderBy('scheduled_start')->paginate(15);

        $stats = [
            'total' => Assignment::where('volunteer_id', Auth::id())->count(),
            'pending' => Assignment::where('volunteer_id', Auth::id())->where('status', 'pending')->count(),
            'accepted' => Assignment::where('volunteer_id', Auth::id())->where('status', 'accepted')->count(),
            'completed' => Assignment::where('volunteer_id', Auth::id())->where('status', 'completed')->count(),
            'upcoming' => Assignment::where('volunteer_id', Auth::id())
                ->where('status', 'accepted')
                ->where('scheduled_start', '>', now())->count(),
        ];

        return view('volunteer.tasks.index', compact('assignments', 'stats', 'status', 'priority'));
    }

    /**
     * Show task details
     */
    public function show(Assignment $assignment)
    {
        $this->authorize('view', $assignment);

        $assignment->load([
            'task.opportunity.organization',
            'assignedBy',
            'task.creator'
        ]);

        return view('volunteer.tasks.show', compact('assignment'));
    }

    /**
     * Accept task assignment
     */
    public function accept(Assignment $assignment)
    {
        $this->authorize('update', $assignment);

        if ($assignment->status !== 'pending') {
            return back()->withErrors(['error' => 'This assignment cannot be accepted.']);
        }

        DB::beginTransaction();
        try {
            $assignment->update([
                'status' => 'accepted',
                'accepted_at' => now(),
                'responded_at' => now()
            ]);

            // Create calendar event
            $assignment->task->createCalendarEvent(Auth::user(), $assignment);

            DB::commit();

            return back()->with('success', 'Task assignment accepted successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to accept assignment: ' . $e->getMessage()]);
        }
    }

    /**
     * Decline task assignment
     */
    public function decline(Request $request, Assignment $assignment)
    {
        $this->authorize('update', $assignment);

        if ($assignment->status !== 'pending') {
            return back()->withErrors(['error' => 'This assignment cannot be declined.']);
        }

        $validated = $request->validate([
            'decline_reason' => 'required|string|max:500'
        ]);

        DB::beginTransaction();
        try {
            $assignment->update([
                'status' => 'declined',
                'declined_at' => now(),
                'responded_at' => now(),
                'decline_reason' => $validated['decline_reason']
            ]);

            // Decrement volunteers assigned count
            $assignment->task->decrement('volunteers_assigned');

            DB::commit();

            return back()->with('success', 'Task assignment declined.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to decline assignment: ' . $e->getMessage()]);
        }
    }

    /**
     * Check in to task
     */
    public function checkIn(Request $request, Assignment $assignment)
    {
        $this->authorize('update', $assignment);

        if ($assignment->status !== 'accepted' || $assignment->checked_in_at) {
            return back()->withErrors(['error' => 'Cannot check in to this task.']);
        }

        $validated = $request->validate([
            'check_in_location' => 'nullable|string|max:255',
            'check_in_notes' => 'nullable|string|max:500'
        ]);

        $assignment->update([
            'checked_in_at' => now(),
            'actual_start' => now(),
            'check_in_location' => $validated['check_in_location'] ?? null,
            'check_in_notes' => $validated['check_in_notes'] ?? null
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Checked in successfully!',
            'checked_in_at' => $assignment->checked_in_at->format('Y-m-d H:i:s')
        ]);
    }

    /**
     * Check out from task
     */
    public function checkOut(Request $request, Assignment $assignment)
    {
        $this->authorize('update', $assignment);

        if (!$assignment->checked_in_at || $assignment->checked_out_at) {
            return back()->withErrors(['error' => 'Cannot check out from this task.']);
        }

        $validated = $request->validate([
            'check_out_location' => 'nullable|string|max:255',
            'check_out_notes' => 'nullable|string|max:500',
            'volunteer_feedback' => 'nullable|string|max:1000',
            'task_completed_successfully' => 'required|boolean'
        ]);

        DB::beginTransaction();
        try {
            $assignment->update([
                'checked_out_at' => now(),
                'actual_end' => now(),
                'status' => $validated['task_completed_successfully'] ? 'completed' : 'incomplete',
                'completed_at' => $validated['task_completed_successfully'] ? now() : null,
                'check_out_location' => $validated['check_out_location'] ?? null,
                'check_out_notes' => $validated['check_out_notes'] ?? null,
                'volunteer_feedback' => $validated['volunteer_feedback'] ?? null,
                'task_completed_successfully' => $validated['task_completed_successfully']
            ]);

            // Calculate actual hours worked
            if ($assignment->actual_start && $assignment->actual_end) {
                $actualMinutes = $assignment->actual_start->diffInMinutes($assignment->actual_end);
                $assignment->update(['break_minutes' => max(0, $actualMinutes - ($assignment->task->duration_minutes ?? 0))]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Checked out successfully!',
                'checked_out_at' => $assignment->checked_out_at->format('Y-m-d H:i:s')
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Browse available tasks for self-assignment
     */
    public function browse(Request $request)
    {
        $category = $request->get('category', 'all');
        $priority = $request->get('priority', 'all');
        $location = $request->get('location', 'all');

        $query = Task::where('status', 'active')
            ->where('allow_self_assignment', true)
            ->where('assignment_deadline', '>', now())
            ->whereColumn('volunteers_assigned', '<', 'volunteers_needed')
            ->whereHas('opportunity', function($q) {
                $q->where('status', 'published');
            })
            ->with(['opportunity.organization', 'assignments']);

        // Filter by user's accepted opportunities only
        $query->whereHas('opportunity.applications', function($q) {
            $q->where('volunteer_id', Auth::id())
              ->where('status', 'accepted');
        });

        if ($category !== 'all') {
            $query->whereHas('opportunity', function($q) use ($category) {
                $q->where('category', $category);
            });
        }

        if ($priority !== 'all') {
            $query->where('priority', $priority);
        }

        if ($location !== 'all') {
            $query->whereHas('opportunity', function($q) use ($location) {
                $q->where('district', $location);
            });
        }

        $availableTasks = $query->orderBy('start_datetime')->paginate(12);

        return view('volunteer.tasks.browse', compact('availableTasks', 'category', 'priority', 'location'));
    }

    /**
     * Self-assign to a task
     */
    public function selfAssign(Task $task)
    {
        if (!$task->allow_self_assignment || !$task->can_assign_volunteers) {
            return back()->withErrors(['error' => 'This task is not available for self-assignment.']);
        }

        // Check if volunteer is accepted for this opportunity
        $hasAcceptedApplication = Auth::user()->applications()
            ->where('opportunity_id', $task->opportunity_id)
            ->where('status', 'accepted')
            ->exists();

        if (!$hasAcceptedApplication) {
            return back()->withErrors(['error' => 'You must be accepted for this opportunity first.']);
        }

        // Check if already assigned
        $existingAssignment = Assignment::where('task_id', $task->id)
            ->where('volunteer_id', Auth::id())
            ->first();

        if ($existingAssignment) {
            return back()->withErrors(['error' => 'You are already assigned to this task.']);
        }

        DB::beginTransaction();
        try {
            $assignment = $task->assignVolunteer(Auth::user(), $task->creator, [
                'method' => 'self_assigned',
                'notes' => 'Self-assigned by volunteer'
            ]);

            DB::commit();

            return back()->with('success', 'Successfully assigned to task! Please check your tasks dashboard.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to assign to task: ' . $e->getMessage()]);
        }
    }
}
