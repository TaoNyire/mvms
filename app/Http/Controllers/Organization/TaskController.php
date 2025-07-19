<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Opportunity;
use App\Models\Assignment;
use App\Models\User;
use App\Services\TaskAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    protected $taskAssignmentService;

    public function __construct(TaskAssignmentService $taskAssignmentService)
    {
        $this->taskAssignmentService = $taskAssignmentService;
    }

    /**
     * Display tasks for an opportunity
     */
    public function index(Opportunity $opportunity)
    {
        $this->authorize('view', $opportunity);

        $tasks = $opportunity->tasks()
            ->with(['assignments.volunteer', 'creator'])
            ->orderBy('start_datetime')
            ->paginate(15);

        $stats = [
            'total_tasks' => $opportunity->tasks()->count(),
            'active_tasks' => $opportunity->tasks()->where('status', 'active')->count(),
            'completed_tasks' => $opportunity->tasks()->where('status', 'completed')->count(),
            'overdue_tasks' => $opportunity->tasks()->where('status', 'active')
                ->where('end_datetime', '<', now())->count(),
            'total_assignments' => Assignment::whereIn('task_id', 
                $opportunity->tasks()->pluck('id'))->count(),
            'pending_assignments' => Assignment::whereIn('task_id', 
                $opportunity->tasks()->pluck('id'))->where('status', 'pending')->count(),
        ];

        return view('organization.tasks.index', compact('opportunity', 'tasks', 'stats'));
    }

    /**
     * Show task creation form
     */
    public function create(Opportunity $opportunity)
    {
        $this->authorize('update', $opportunity);

        // Get accepted volunteers for this opportunity
        $availableVolunteers = User::whereHas('applications', function($query) use ($opportunity) {
            $query->where('opportunity_id', $opportunity->id)
                  ->where('status', 'accepted');
        })->with('volunteerProfile')->get();

        return view('organization.tasks.create', compact('opportunity', 'availableVolunteers'));
    }

    /**
     * Store a new task
     */
    public function store(Request $request, Opportunity $opportunity)
    {
        $this->authorize('update', $opportunity);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'instructions' => 'nullable|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'start_datetime' => 'required|date|after:now',
            'end_datetime' => 'required|date|after:start_datetime',
            'location_type' => 'required|in:on_site,remote,hybrid',
            'location_address' => 'required_if:location_type,on_site,hybrid|string|max:500',
            'location_instructions' => 'nullable|string',
            'volunteers_needed' => 'required|integer|min:1|max:50',
            'required_skills' => 'nullable|array',
            'special_requirements' => 'nullable|string',
            'assignment_type' => 'required|in:auto,manual,self_assign',
            'allow_self_assignment' => 'boolean',
            'assignment_deadline' => 'nullable|date|before:start_datetime',
            'requires_check_in' => 'boolean',
            'requires_check_out' => 'boolean',
            'estimated_hours' => 'nullable|numeric|min:0.5|max:24',
            'equipment_needed' => 'nullable|array',
            'safety_requirements' => 'nullable|string',
            'completion_checklist' => 'nullable|array',
            'is_recurring' => 'boolean',
            'recurring_pattern' => 'nullable|array',
            'recurring_end_date' => 'nullable|date|after:start_datetime',
        ]);

        DB::beginTransaction();
        try {
            $task = Task::create([
                'opportunity_id' => $opportunity->id,
                'created_by' => Auth::id(),
                'status' => 'active',
                'volunteers_assigned' => 0,
                ...$validated
            ]);

            // Auto-assign if requested and volunteers are available
            if ($validated['assignment_type'] === 'auto') {
                $this->taskAssignmentService->autoAssignVolunteersToTask($task);
            }

            DB::commit();

            return redirect()
                ->route('organization.opportunities.tasks.index', $opportunity)
                ->with('success', 'Task created successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to create task: ' . $e->getMessage()]);
        }
    }

    /**
     * Show task details
     */
    public function show(Opportunity $opportunity, Task $task)
    {
        $this->authorize('view', $opportunity);

        $task->load([
            'assignments.volunteer.volunteerProfile',
            'creator',
            'schedules',
            'calendarEvents'
        ]);

        $assignmentStats = [
            'total' => $task->assignments()->count(),
            'pending' => $task->assignments()->where('status', 'pending')->count(),
            'accepted' => $task->assignments()->where('status', 'accepted')->count(),
            'declined' => $task->assignments()->where('status', 'declined')->count(),
            'completed' => $task->assignments()->where('status', 'completed')->count(),
        ];

        return view('organization.tasks.show', compact('opportunity', 'task', 'assignmentStats'));
    }

    /**
     * Assign volunteers to task
     */
    public function assignVolunteers(Request $request, Opportunity $opportunity, Task $task)
    {
        $this->authorize('update', $opportunity);

        $validated = $request->validate([
            'volunteer_ids' => 'required|array',
            'volunteer_ids.*' => 'exists:users,id',
            'assignment_notes' => 'nullable|string',
            'override_conflicts' => 'boolean'
        ]);

        $results = [];
        $errors = [];

        foreach ($validated['volunteer_ids'] as $volunteerId) {
            try {
                $volunteer = User::findOrFail($volunteerId);
                $assignment = $task->assignVolunteer($volunteer, Auth::user(), [
                    'method' => 'manual',
                    'notes' => $validated['assignment_notes'] ?? null,
                    'override_conflicts' => $validated['override_conflicts'] ?? false
                ]);
                
                $results[] = "Successfully assigned {$volunteer->name}";
                
                // Send notification
                $volunteer->notify(new \App\Notifications\VolunteerAssignedToTaskNotification($assignment));
                
            } catch (\Exception $e) {
                $errors[] = "Failed to assign {$volunteer->name}: " . $e->getMessage();
            }
        }

        if (!empty($results)) {
            session()->flash('success', implode('<br>', $results));
        }
        if (!empty($errors)) {
            session()->flash('error', implode('<br>', $errors));
        }

        return back();
    }

    /**
     * Update task status
     */
    public function updateStatus(Request $request, Opportunity $opportunity, Task $task)
    {
        $this->authorize('update', $opportunity);

        $validated = $request->validate([
            'status' => 'required|in:active,paused,completed,cancelled',
            'completion_notes' => 'nullable|string'
        ]);

        $task->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Task status updated successfully'
        ]);
    }

    /**
     * Get task analytics
     */
    public function analytics(Opportunity $opportunity, Task $task)
    {
        $this->authorize('view', $opportunity);

        $analytics = [
            'completion_rate' => $task->assignments()->where('status', 'completed')->count() / 
                               max($task->assignments()->count(), 1) * 100,
            'average_response_time' => $task->assignments()
                ->whereNotNull('responded_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, assigned_at, responded_at)) as avg_hours')
                ->value('avg_hours') ?? 0,
            'volunteer_ratings' => $task->assignments()
                ->whereNotNull('performance_rating')
                ->avg('performance_rating') ?? 0,
            'check_in_rate' => $task->assignments()
                ->whereNotNull('checked_in_at')->count() / 
                max($task->assignments()->where('status', 'accepted')->count(), 1) * 100,
        ];

        return response()->json($analytics);
    }
}
