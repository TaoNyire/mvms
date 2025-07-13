<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Opportunity;
use App\Models\Application;
use App\Models\ApplicationTaskStatus;
use App\Models\Task;
use App\Models\Skill;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Services\TaskAssignmentService;

class OpportunityController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        return $user->opportunities()->with('skills')->get();
    }

    public function show(Request $request, $id)
    {
        try {
            $user = $request->user();

            // Find the opportunity with relationships
            $opportunity = Opportunity::with(['skills', 'organization'])
                ->withCount('applications')
                ->findOrFail($id);

            // Check if user has permission to view this opportunity
            // Organizations can only view their own opportunities
            if ($user->hasRole('organization') && $opportunity->organization_id !== $user->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            return response()->json([
                'data' => $opportunity
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Opportunity not found',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'location' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'volunteers_needed' => 'required|integer|min:1',
            'status' => 'nullable|string|in:active,recruitment_closed,in_progress,completed,cancelled',
            'skills' => 'nullable|array',
            'skills.*' => 'integer|exists:skills,id',
            'tasks' => 'nullable|array',
            'tasks.*.title' => 'required|string',
            'tasks.*.description' => 'nullable|string',
            'tasks.*.start_date' => 'required|date',
            'tasks.*.end_date' => 'required|date|after_or_equal:tasks.*.start_date',
            'tasks.*.status' => 'nullable|string|in:pending,in_progress,completed,cancelled',
        ]);

        $data['organization_id'] = $request->user()->id;
        $data['status'] = $data['status'] ?? 'active'; // Default to active

        DB::beginTransaction();
        try {
            $opportunity = Opportunity::create($data);

            // Sync skills if provided
            if (!empty($data['skills'])) {
                $opportunity->skills()->sync($data['skills']);
            }

            // Create tasks if provided
            if (!empty($data['tasks'])) {
                foreach ($data['tasks'] as $taskData) {
                    $taskData['opportunity_id'] = $opportunity->id;
                    $taskData['status'] = $taskData['status'] ?? 'active';
                    $task = Task::create($taskData);

                    // Automatically assign task to accepted and confirmed volunteers
                    $taskAssignmentService = new TaskAssignmentService();
                    $taskAssignmentService->autoAssignTasksToAllVolunteers($opportunity->id);
                }
            }

            DB::commit();
            return response()->json($opportunity->load(['skills', 'tasks']), 201);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'error' => 'Failed to create opportunity',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Opportunity $opportunity)
    {
        // Ensure that the opportunity belongs to this org
        if ($opportunity->organization_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'title' => 'sometimes|required|string',
            'description' => 'sometimes|required|string',
            'location' => 'sometimes|required|string',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'volunteers_needed' => 'sometimes|required|integer|min:1',
            'status' => 'sometimes|string|in:active,recruitment_closed,in_progress,completed,cancelled',
            'skills' => 'nullable|array',
            'skills.*' => 'integer|exists:skills,id',
        ]);

        DB::beginTransaction();
        try {
            $opportunity->update($data);

            if (isset($data['skills'])) {
                $opportunity->skills()->sync($data['skills']);
            }

            // Check if we need to auto-close recruitment
            $this->checkAndUpdateRecruitmentStatus($opportunity);

            DB::commit();
            return response()->json($opportunity->load(['skills', 'tasks']));
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'error' => 'Failed to update opportunity',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, Opportunity $opportunity)
    {
        if ($opportunity->organization_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $opportunity->delete();
        return response()->json(['message' => 'Opportunity deleted']);
    }

    /**
     * Get applications for a specific opportunity
     */
    public function getApplications(Request $request, $id)
    {
        try {
            $user = $request->user();

            // Find the opportunity
            $opportunity = Opportunity::findOrFail($id);

            // Check if user has permission to view applications for this opportunity
            if ($user->hasRole('organization') && $opportunity->organization_id !== $user->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Get applications with volunteer details
            $applications = $opportunity->applications()
                ->with(['volunteer', 'volunteer.volunteerProfile'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function($application) {
                    return [
                        'id' => $application->id,
                        'status' => $application->status,
                        'applied_at' => $application->applied_at,
                        'responded_at' => $application->responded_at,
                        'feedback_rating' => $application->feedback_rating,
                        'feedback_comment' => $application->feedback_comment,
                        'volunteer' => [
                            'id' => $application->volunteer->id,
                            'name' => $application->volunteer->name,
                            'email' => $application->volunteer->email,
                            'profile' => $application->volunteer->volunteerProfile
                        ]
                    ];
                });

            return response()->json([
                'data' => $applications
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to get applications',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin: Get all opportunities with pagination and filtering
     */
    public function adminIndex(Request $request)
    {
        $query = Opportunity::with(['organization', 'skills'])
            ->withCount('applications')
            ->orderBy('created_at', 'desc');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%')
                  ->orWhere('location', 'like', '%' . $request->search . '%');
            });
        }

        $opportunities = $query->paginate(20);
        return response()->json($opportunities);
    }

    /**
     * Update opportunity status
     */
    public function updateStatus(Request $request, Opportunity $opportunity)
    {
        // Ensure that the opportunity belongs to this org
        if ($opportunity->organization_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'status' => 'required|string|in:active,recruitment_closed,in_progress,completed,cancelled',
            'reason' => 'nullable|string'
        ]);

        $oldStatus = $opportunity->status;
        $opportunity->update(['status' => $data['status']]);

        // Log status change if needed
        if ($oldStatus !== $data['status']) {
            // You can add logging here if you have a status change log table
        }

        return response()->json([
            'message' => 'Status updated successfully',
            'opportunity' => $opportunity->load(['skills', 'tasks'])
        ]);
    }

    /**
     * Get opportunity tasks
     */
    public function getTasks(Request $request, Opportunity $opportunity)
    {
        // Check permissions
        if ($request->user()->hasRole('organization') && $opportunity->organization_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $tasks = $opportunity->tasks()
            ->with(['applications.user'])
            ->orderBy('start_date')
            ->get();

        return response()->json(['data' => $tasks]);
    }

    /**
     * Create a new task for an opportunity
     */
    public function createTask(Request $request, Opportunity $opportunity)
    {
        // Ensure that the opportunity belongs to this org
        if ($opportunity->organization_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'nullable|string|in:active,completed,cancelled',
        ]);

        $data['opportunity_id'] = $opportunity->id;
        $data['status'] = $data['status'] ?? 'active';

        $task = Task::create($data);

        // Automatically assign task to accepted and confirmed volunteers
        $taskAssignmentService = new TaskAssignmentService();
        $taskAssignmentService->autoAssignTasksToAllVolunteers($task->opportunity_id);

        return response()->json([
            'message' => 'Task created successfully',
            'task' => $task->load(['applications.volunteer'])
        ], 201);
    }

    /**
     * Check and update recruitment status based on accepted applications
     */
    private function checkAndUpdateRecruitmentStatus(Opportunity $opportunity)
    {
        // Only check if opportunity is currently active
        if ($opportunity->status !== 'active') {
            return;
        }

        $acceptedApplicationsCount = $opportunity->applications()
            ->where('status', 'accepted')
            ->count();

        // Auto-close recruitment if we have enough volunteers
        if ($acceptedApplicationsCount >= $opportunity->volunteers_needed) {
            $opportunity->update(['status' => 'recruitment_closed']);
        }
    }

    /**
     * Get opportunity statistics
     */
    public function getStatistics(Request $request, Opportunity $opportunity)
    {
        // Check permissions
        if ($request->user()->hasRole('organization') && $opportunity->organization_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $stats = [
            'total_applications' => $opportunity->applications()->count(),
            'pending_applications' => $opportunity->applications()->where('status', 'pending')->count(),
            'accepted_applications' => $opportunity->applications()->where('status', 'accepted')->count(),
            'rejected_applications' => $opportunity->applications()->where('status', 'rejected')->count(),
            'total_tasks' => $opportunity->tasks()->count(),
            'pending_tasks' => $opportunity->tasks()->where('status', 'pending')->count(),
            'in_progress_tasks' => $opportunity->tasks()->where('status', 'in_progress')->count(),
            'completed_tasks' => $opportunity->tasks()->where('status', 'completed')->count(),
            'volunteers_needed' => $opportunity->volunteers_needed,
            'recruitment_progress' => $opportunity->volunteers_needed > 0
                ? round(($opportunity->applications()->where('status', 'accepted')->count() / $opportunity->volunteers_needed) * 100, 1)
                : 0,
        ];

        return response()->json(['data' => $stats]);
    }

    /**
     * Bulk update opportunity statuses (Admin only)
     */
    public function bulkUpdateStatus(Request $request)
    {
        $data = $request->validate([
            'opportunity_ids' => 'required|array',
            'opportunity_ids.*' => 'integer|exists:opportunities,id',
            'status' => 'required|string|in:active,recruitment_closed,in_progress,completed,cancelled',
        ]);

        $updated = Opportunity::whereIn('id', $data['opportunity_ids'])
            ->update(['status' => $data['status']]);

        return response()->json([
            'message' => "Updated {$updated} opportunities",
            'updated_count' => $updated
        ]);
    }

    /**
     * Get opportunities with advanced filtering
     */
    public function getFiltered(Request $request)
    {
        $query = Opportunity::with(['organization', 'skills'])
            ->withCount(['applications', 'tasks']);

        // Status filter
        if ($request->status) {
            if (is_array($request->status)) {
                $query->whereIn('status', $request->status);
            } else {
                $query->where('status', $request->status);
            }
        }

        // Date range filter
        if ($request->start_date) {
            $query->where('start_date', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->where('end_date', '<=', $request->end_date);
        }

        // Location filter
        if ($request->location) {
            $query->where('location', 'like', "%{$request->location}%");
        }

        // Skills filter
        if ($request->skills && is_array($request->skills)) {
            $query->whereHas('skills', function($q) use ($request) {
                $q->whereIn('skills.id', $request->skills);
            });
        }

        // Organization filter (for admin)
        if ($request->organization_id && $request->user()->hasRole('admin')) {
            $query->where('organization_id', $request->organization_id);
        } elseif ($request->user()->hasRole('organization')) {
            // Organizations can only see their own opportunities
            $query->where('organization_id', $request->user()->id);
        }

        // Search filter
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%")
                  ->orWhere('location', 'like', "%{$request->search}%");
            });
        }

        // Sorting
        $sortBy = $request->sort_by ?? 'created_at';
        $sortOrder = $request->sort_order ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        $opportunities = $query->paginate($request->per_page ?? 15);
        return response()->json($opportunities);
    }


}