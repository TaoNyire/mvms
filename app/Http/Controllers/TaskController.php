<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\Opportunity;
use App\Models\Application;

class TaskController extends Controller
{
    /**
     * Get all tasks for an opportunity (Organization view)
     */
    public function index(Request $request, $opportunityId)
    {
        $user = $request->user();

        $opportunity = Opportunity::where('id', $opportunityId)
                                 ->where('organization_id', $user->id)
                                 ->firstOrFail();

        $tasks = $opportunity->tasks()
                           ->with(['applications.volunteer.volunteerProfile'])
                           ->orderBy('start_date')
                           ->get();

        return response()->json($tasks);
    }

    /**
     * Create a new task for an opportunity
     */
    public function store(Request $request, $opportunityId)
    {
        $user = $request->user();

        $opportunity = Opportunity::where('id', $opportunityId)
                                 ->where('organization_id', $user->id)
                                 ->firstOrFail();

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $task = $opportunity->tasks()->create($data);

        return response()->json($task->load('opportunity'), 201);
    }

    /**
     * Update a task
     */
    public function update(Request $request, $taskId)
    {
        $user = $request->user();

        $task = Task::whereHas('opportunity', function($query) use ($user) {
                    $query->where('organization_id', $user->id);
                })->findOrFail($taskId);

        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after_or_equal:start_date',
            'status' => 'sometimes|required|in:active,completed,cancelled',
        ]);

        $task->update($data);

        return response()->json($task->load('opportunity'));
    }

    /**
     * Assign volunteers to a task
     */
    public function assignVolunteers(Request $request, $taskId)
    {
        $user = $request->user();

        $task = Task::whereHas('opportunity', function($query) use ($user) {
                    $query->where('organization_id', $user->id);
                })->findOrFail($taskId);

        $data = $request->validate([
            'application_ids' => 'required|array',
            'application_ids.*' => 'integer|exists:applications,id',
        ]);

        // Verify applications belong to this opportunity and are accepted
        $applications = Application::whereIn('id', $data['application_ids'])
                                  ->where('opportunity_id', $task->opportunity_id)
                                  ->where('status', 'accepted')
                                  ->where('confirmation_status', 'confirmed')
                                  ->get();

        if ($applications->count() !== count($data['application_ids'])) {
            return response()->json(['error' => 'Some applications are invalid'], 422);
        }

        // Assign applications to task
        foreach ($applications as $application) {
            $application->update(['task_id' => $task->id]);

            // Notify volunteer about task assignment
            $application->volunteer->notify(new \App\Notifications\VolunteerAssignedToTaskNotification($task));
        }

        $task->update(['assigned_volunteers' => $applications->count()]);

        return response()->json([
            'message' => 'Volunteers assigned successfully',
            'task' => $task->load(['applications.volunteer.volunteerProfile'])
        ]);
    }

    /**
     * Complete a task manually
     */
    public function complete(Request $request, $taskId)
    {
        $user = $request->user();

        $task = Task::whereHas('opportunity', function($query) use ($user) {
                    $query->where('organization_id', $user->id);
                })->findOrFail($taskId);

        $data = $request->validate([
            'completion_notes' => 'nullable|string',
        ]);

        $task->markAsCompleted($data['completion_notes'] ?? null);

        // Notify all volunteers working on this task
        $this->notifyVolunteersTaskCompleted($task);

        return response()->json([
            'message' => 'Task completed successfully',
            'task' => $task
        ]);
    }

    /**
     * Reassign volunteers from one task to another
     */
    public function reassignVolunteers(Request $request, $taskId)
    {
        $user = $request->user();

        $task = Task::whereHas('opportunity', function($query) use ($user) {
                    $query->where('organization_id', $user->id);
                })->findOrFail($taskId);

        $data = $request->validate([
            'new_task_id' => 'required|integer|exists:tasks,id',
            'application_ids' => 'required|array',
            'application_ids.*' => 'integer|exists:applications,id',
        ]);

        // Verify new task belongs to same organization
        $newTask = Task::whereHas('opportunity', function($query) use ($user) {
                       $query->where('organization_id', $user->id);
                   })->findOrFail($data['new_task_id']);

        // Verify applications belong to current task
        $applications = Application::whereIn('id', $data['application_ids'])
                                  ->where('task_id', $task->id)
                                  ->get();

        if ($applications->count() !== count($data['application_ids'])) {
            return response()->json(['error' => 'Some applications are invalid'], 422);
        }

        // Reassign applications
        foreach ($applications as $application) {
            $application->update(['task_id' => $newTask->id]);
        }

        // Update volunteer counts
        $task->update(['assigned_volunteers' => $task->applications()->count()]);
        $newTask->update(['assigned_volunteers' => $newTask->applications()->count()]);

        return response()->json([
            'message' => 'Volunteers reassigned successfully',
            'from_task' => $task->load('applications.volunteer.volunteerProfile'),
            'to_task' => $newTask->load('applications.volunteer.volunteerProfile')
        ]);
    }

    /**
     * Get task progress for organization - shows all volunteers assigned to a task with their progress
     */
    public function getTaskProgress(Request $request, $taskId)
    {
        $user = $request->user();

        $task = Task::whereHas('opportunity', function($query) use ($user) {
                    $query->where('organization_id', $user->id);
                })->findOrFail($taskId);

        // Get all applications assigned to this task with their progress
        $volunteers = $task->applications()
            ->where('status', 'accepted')
            ->where('confirmation_status', 'confirmed')
            ->with([
                'volunteer.volunteerProfile',
                'taskStatus'
            ])
            ->get()
            ->map(function($application) {
                $taskStatus = $application->taskStatus;
                return [
                    'application_id' => $application->id,
                    'volunteer' => [
                        'id' => $application->volunteer->id,
                        'name' => $application->volunteer->name,
                        'email' => $application->volunteer->email,
                        'profile' => $application->volunteer->volunteerProfile
                    ],
                    'task_status' => [
                        'status' => $taskStatus->status ?? 'pending',
                        'started_at' => $taskStatus->started_at ?? null,
                        'completed_at' => $taskStatus->completed_at ?? null,
                        'completion_notes' => $taskStatus->completion_notes ?? null,
                        'work_evidence' => $taskStatus->work_evidence ?? null,
                        'duration_hours' => $taskStatus->duration_hours ?? null
                    ],
                    'applied_at' => $application->applied_at,
                    'responded_at' => $application->responded_at
                ];
            });

        return response()->json([
            'task' => $task,
            'volunteers' => $volunteers,
            'total_volunteers' => $volunteers->count(),
            'progress_summary' => [
                'pending' => $volunteers->where('task_status.status', 'pending')->count(),
                'in_progress' => $volunteers->where('task_status.status', 'in_progress')->count(),
                'completed' => $volunteers->where('task_status.status', 'completed')->count(),
                'quit' => $volunteers->where('task_status.status', 'quit')->count()
            ]
        ]);
    }

    /**
     * Get all volunteers assigned to tasks for a specific opportunity (Organization view)
     */
    public function getOpportunityVolunteersProgress(Request $request, $opportunityId)
    {
        $user = $request->user();

        $opportunity = Opportunity::where('id', $opportunityId)
                                 ->where('organization_id', $user->id)
                                 ->firstOrFail();

        // Get all applications for this opportunity with task assignments
        $volunteers = Application::where('opportunity_id', $opportunityId)
            ->where('status', 'accepted')
            ->where('confirmation_status', 'confirmed')
            ->whereNotNull('task_id')
            ->with([
                'volunteer.volunteerProfile',
                'task',
                'taskStatus'
            ])
            ->get()
            ->map(function($application) {
                $taskStatus = $application->taskStatus;
                return [
                    'application_id' => $application->id,
                    'volunteer' => [
                        'id' => $application->volunteer->id,
                        'name' => $application->volunteer->name,
                        'email' => $application->volunteer->email,
                        'profile' => $application->volunteer->volunteerProfile
                    ],
                    'task' => [
                        'id' => $application->task->id,
                        'title' => $application->task->title,
                        'description' => $application->task->description,
                        'start_date' => $application->task->start_date,
                        'end_date' => $application->task->end_date,
                        'status' => $application->task->status
                    ],
                    'task_status' => [
                        'status' => $taskStatus->status ?? 'pending',
                        'started_at' => $taskStatus->started_at ?? null,
                        'completed_at' => $taskStatus->completed_at ?? null,
                        'completion_notes' => $taskStatus->completion_notes ?? null,
                        'work_evidence' => $taskStatus->work_evidence ?? null,
                        'duration_hours' => $taskStatus->duration_hours ?? null
                    ],
                    'applied_at' => $application->applied_at,
                    'responded_at' => $application->responded_at
                ];
            });

        return response()->json([
            'opportunity' => $opportunity,
            'volunteers' => $volunteers,
            'total_volunteers' => $volunteers->count(),
            'progress_summary' => [
                'pending' => $volunteers->where('task_status.status', 'pending')->count(),
                'in_progress' => $volunteers->where('task_status.status', 'in_progress')->count(),
                'completed' => $volunteers->where('task_status.status', 'completed')->count(),
                'quit' => $volunteers->where('task_status.status', 'quit')->count()
            ]
        ]);
    }

    /**
     * Update volunteer task status (for organization to mark progress)
     */
    public function updateVolunteerTaskStatus(Request $request, $applicationId)
    {
        $user = $request->user();

        $data = $request->validate([
            'status' => 'required|in:pending,in_progress,completed,quit',
            'completion_notes' => 'nullable|string',
            'work_evidence' => 'nullable|array'
        ]);

        // Find the application and verify organization ownership
        $application = Application::whereHas('opportunity', function($query) use ($user) {
                            $query->where('organization_id', $user->id);
                        })
                        ->where('id', $applicationId)
                        ->where('status', 'accepted')
                        ->where('confirmation_status', 'confirmed')
                        ->whereNotNull('task_id')
                        ->with(['volunteer', 'task', 'taskStatus'])
                        ->firstOrFail();

        // Get or create task status record
        $taskStatus = $application->taskStatus;
        if (!$taskStatus) {
            $taskStatus = \App\Models\ApplicationTaskStatus::create([
                'application_id' => $application->id,
                'status' => 'pending'
            ]);
        }

        // Update the status based on the new status
        switch ($data['status']) {
            case 'in_progress':
                $taskStatus->markAsStarted();
                break;
            case 'completed':
                $taskStatus->markAsCompleted(
                    $data['completion_notes'] ?? null,
                    $data['work_evidence'] ?? null
                );
                break;
            case 'quit':
                $taskStatus->markAsQuit($data['completion_notes'] ?? null);
                break;
            default:
                $taskStatus->update(['status' => $data['status']]);
        }

        // Notify volunteer about status change
        $application->volunteer->notify(new \App\Notifications\TaskStatusUpdateNotification($taskStatus));

        return response()->json([
            'message' => 'Task status updated successfully',
            'application' => $application->load(['volunteer.volunteerProfile', 'task', 'taskStatus'])
        ]);
    }

    /**
     * Get volunteers currently working on tasks for an organization
     */
    public function getCurrentVolunteers(Request $request)
    {
        $user = $request->user();

        $volunteers = Application::whereHas('opportunity', function($query) use ($user) {
                                     $query->where('organization_id', $user->id);
                                 })
                                 ->where('status', 'accepted')
                                 ->where('confirmation_status', 'confirmed')
                                 ->whereNotNull('task_id')
                                 ->with([
                                     'volunteer.volunteerProfile',
                                     'task',
                                     'opportunity',
                                     'taskStatus'
                                 ])
                                 ->get();

        return response()->json($volunteers);
    }

    /**
     * Get recently employed volunteers (last 30 days)
     */
    public function getRecentlyEmployedVolunteers(Request $request)
    {
        $user = $request->user();

        $volunteers = Application::whereHas('opportunity', function($query) use ($user) {
                                     $query->where('organization_id', $user->id);
                                 })
                                 ->where('status', 'accepted')
                                 ->where('confirmation_status', 'confirmed')
                                 ->where('confirmed_at', '>=', now()->subDays(30))
                                 ->with([
                                     'volunteer.volunteerProfile',
                                     'task',
                                     'opportunity',
                                     'taskStatus'
                                 ])
                                 ->orderBy('confirmed_at', 'desc')
                                 ->get();

        return response()->json($volunteers);
    }

    /**
     * Get volunteers with their assigned tasks and status for organization
     */
    public function getVolunteersWithTasks(Request $request)
    {
        try {
            $user = $request->user();
            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 10);
            $search = $request->get('search', '');
            $taskStatus = $request->get('task_status', 'all'); // all, pending, in_progress, completed
            $opportunityId = $request->get('opportunity_id', null);

            if (!$user) {
                return response()->json([
                    'error' => 'User not authenticated',
                    'data' => [],
                    'total' => 0
                ], 401);
            }

            $query = Application::whereHas('opportunity', function($q) use ($user) {
                $q->where('organization_id', $user->id);
            })
            ->where('status', 'accepted')
            ->where('confirmation_status', 'confirmed')
            ->with([
                'volunteer.volunteerProfile.skills',
                'task',
                'opportunity',
                'taskStatus'
            ]);

            // Filter by opportunity if specified
            if ($opportunityId) {
                $query->where('opportunity_id', $opportunityId);
            }

            // Apply search filter
            if (!empty($search)) {
                $query->whereHas('volunteer', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                })->orWhereHas('task', function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%");
                });
            }

            // Apply task status filter
            if ($taskStatus !== 'all') {
                if ($taskStatus === 'pending') {
                    $query->where(function($q) {
                        $q->whereNull('task_id')
                          ->orWhereDoesntHave('taskStatus')
                          ->orWhereHas('taskStatus', function($subQ) {
                              $subQ->where('status', 'pending');
                          });
                    });
                } else {
                    $query->whereHas('taskStatus', function($q) use ($taskStatus) {
                        $q->where('status', $taskStatus);
                    });
                }
            }

            $total = $query->count();
            $volunteers = $query->orderBy('confirmed_at', 'desc')
                               ->skip(($page - 1) * $perPage)
                               ->take($perPage)
                               ->get()
                               ->map(function($application) {
                                   $volunteer = $application->volunteer;
                                   $opportunity = $application->opportunity;
                                   $taskStatus = $application->taskStatus;
                                   $task = $application->task;

                                   return [
                                       'application_id' => $application->id,
                                       'volunteer' => [
                                           'id' => $volunteer->id,
                                           'name' => $volunteer->name,
                                           'email' => $volunteer->email,
                                           'profile' => $volunteer->volunteerProfile ? [
                                               'bio' => $volunteer->volunteerProfile->bio,
                                               'location' => $volunteer->volunteerProfile->location,
                                               'district' => $volunteer->volunteerProfile->district,
                                               'region' => $volunteer->volunteerProfile->region,
                                               'availability' => $volunteer->volunteerProfile->availability,
                                           ] : null,
                                           'skills' => $volunteer->volunteerProfile && $volunteer->volunteerProfile->skills ?
                                               $volunteer->volunteerProfile->skills->map(function($skill) {
                                                   return [
                                                       'id' => $skill->id,
                                                       'name' => $skill->name,
                                                       'category' => $skill->category ?? 'General',
                                                   ];
                                               })->toArray() : [],
                                       ],
                                       'opportunity' => [
                                           'id' => $opportunity->id,
                                           'title' => $opportunity->title,
                                           'location' => $opportunity->location,
                                           'start_date' => $opportunity->start_date,
                                           'end_date' => $opportunity->end_date,
                                           'status' => $opportunity->status,
                                       ],
                                       'task' => $task ? [
                                           'id' => $task->id,
                                           'title' => $task->title,
                                           'description' => $task->description,
                                           'start_date' => $task->start_date,
                                           'end_date' => $task->end_date,
                                           'status' => $task->status,
                                       ] : null,
                                       'task_status' => $taskStatus ? [
                                           'id' => $taskStatus->id,
                                           'status' => $taskStatus->status,
                                           'started_at' => $taskStatus->started_at,
                                           'completed_at' => $taskStatus->completed_at,
                                           'completion_notes' => $taskStatus->completion_notes,
                                           'work_evidence' => $taskStatus->work_evidence,
                                       ] : [
                                           'status' => 'pending',
                                           'started_at' => null,
                                           'completed_at' => null,
                                           'completion_notes' => null,
                                           'work_evidence' => null,
                                       ],
                                       'progress' => $this->calculateTaskProgress($taskStatus, $task),
                                       'joined_at' => $application->confirmed_at,
                                       'application_date' => $application->applied_at,
                                   ];
                               });

            return response()->json([
                'success' => true,
                'data' => $volunteers,
                'total' => $total,
                'current_page' => $page,
                'per_page' => $perPage,
                'last_page' => ceil($total / $perPage),
                'from' => ($page - 1) * $perPage + 1,
                'to' => min($page * $perPage, $total),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch volunteers with tasks',
                'message' => $e->getMessage(),
                'data' => [],
                'total' => 0
            ], 500);
        }
    }

    /**
     * Get detailed information about a specific volunteer's task assignment
     */
    public function getVolunteerTaskDetails(Request $request, $applicationId)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'error' => 'User not authenticated'
                ], 401);
            }

            // Verify that this application belongs to the organization
            $application = Application::whereHas('opportunity', function($q) use ($user) {
                $q->where('organization_id', $user->id);
            })
            ->where('id', $applicationId)
            ->where('status', 'accepted')
            ->where('confirmation_status', 'confirmed')
            ->with([
                'volunteer.volunteerProfile.skills',
                'task',
                'opportunity',
                'taskStatus',
                'feedback'
            ])
            ->first();

            if (!$application) {
                return response()->json([
                    'error' => 'Application not found or not associated with your organization'
                ], 404);
            }

            $volunteer = $application->volunteer;
            $opportunity = $application->opportunity;
            $taskStatus = $application->taskStatus;
            $task = $application->task;

            $detailsData = [
                'application_id' => $application->id,
                'volunteer' => [
                    'id' => $volunteer->id,
                    'name' => $volunteer->name,
                    'email' => $volunteer->email,
                    'status' => $volunteer->status,
                    'profile' => $volunteer->volunteerProfile ? [
                        'bio' => $volunteer->volunteerProfile->bio,
                        'location' => $volunteer->volunteerProfile->location,
                        'district' => $volunteer->volunteerProfile->district,
                        'region' => $volunteer->volunteerProfile->region,
                        'availability' => $volunteer->volunteerProfile->availability,
                        'cv_url' => $volunteer->volunteerProfile->cv_url,
                        'qualifications_url' => $volunteer->volunteerProfile->qualifications_url,
                    ] : null,
                    'skills' => $volunteer->volunteerProfile && $volunteer->volunteerProfile->skills ?
                        $volunteer->volunteerProfile->skills->map(function($skill) {
                            return [
                                'id' => $skill->id,
                                'name' => $skill->name,
                                'category' => $skill->category ?? 'General',
                                'description' => $skill->description ?? '',
                            ];
                        })->toArray() : [],
                ],
                'opportunity' => [
                    'id' => $opportunity->id,
                    'title' => $opportunity->title,
                    'description' => $opportunity->description,
                    'location' => $opportunity->location,
                    'start_date' => $opportunity->start_date,
                    'end_date' => $opportunity->end_date,
                    'status' => $opportunity->status,
                    'volunteers_needed' => $opportunity->volunteers_needed,
                ],
                'task' => $task ? [
                    'id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'start_date' => $task->start_date,
                    'end_date' => $task->end_date,
                    'status' => $task->status,
                    'assigned_volunteers' => $task->assigned_volunteers,
                    'completion_notes' => $task->completion_notes,
                    'completed_at' => $task->completed_at,
                ] : null,
                'task_status' => $taskStatus ? [
                    'id' => $taskStatus->id,
                    'status' => $taskStatus->status,
                    'started_at' => $taskStatus->started_at,
                    'completed_at' => $taskStatus->completed_at,
                    'completion_notes' => $taskStatus->completion_notes,
                    'work_evidence' => $taskStatus->work_evidence,
                ] : [
                    'status' => 'pending',
                    'started_at' => null,
                    'completed_at' => null,
                    'completion_notes' => null,
                    'work_evidence' => null,
                ],
                'application_details' => [
                    'applied_at' => $application->applied_at,
                    'responded_at' => $application->responded_at,
                    'confirmed_at' => $application->confirmed_at,
                    'status' => $application->status,
                    'confirmation_status' => $application->confirmation_status,
                    'feedback_rating' => $application->feedback_rating,
                    'feedback_comment' => $application->feedback_comment,
                ],
                'feedback' => $application->feedback ? [
                    'id' => $application->feedback->id,
                    'rating' => $application->feedback->rating,
                    'comment' => $application->feedback->comment,
                    'feedback_type' => $application->feedback->feedback_type,
                    'created_at' => $application->feedback->created_at,
                ] : null,
                'progress' => $this->calculateTaskProgress($taskStatus, $task),
                'timeline' => $this->generateTaskTimeline($application),
            ];

            return response()->json([
                'success' => true,
                'data' => $detailsData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch volunteer task details',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate task progress based on task status and dates
     */
    private function calculateTaskProgress($taskStatus, $task)
    {
        if (!$taskStatus) {
            return 0;
        }

        switch ($taskStatus->status) {
            case 'completed':
                return 100;
            case 'in_progress':
                // Calculate based on time elapsed if dates are available
                if ($taskStatus->started_at && $task && $task->start_date && $task->end_date) {
                    $totalDuration = strtotime($task->end_date) - strtotime($task->start_date);
                    $elapsed = time() - strtotime($taskStatus->started_at);
                    $progress = min(90, max(10, ($elapsed / $totalDuration) * 100));
                    return round($progress);
                }
                return 50; // Default for in-progress
            case 'pending':
            default:
                return 0;
        }
    }

    /**
     * Generate timeline for task progress
     */
    private function generateTaskTimeline($application)
    {
        $timeline = [];

        // Application submitted
        $timeline[] = [
            'event' => 'Application Submitted',
            'date' => $application->applied_at,
            'status' => 'completed',
            'description' => 'Volunteer applied for the opportunity'
        ];

        // Application accepted
        if ($application->responded_at) {
            $timeline[] = [
                'event' => 'Application Accepted',
                'date' => $application->responded_at,
                'status' => 'completed',
                'description' => 'Organization accepted the application'
            ];
        }

        // Application confirmed
        if ($application->confirmed_at) {
            $timeline[] = [
                'event' => 'Application Confirmed',
                'date' => $application->confirmed_at,
                'status' => 'completed',
                'description' => 'Volunteer confirmed participation'
            ];
        }

        // Task assigned
        if ($application->task) {
            $timeline[] = [
                'event' => 'Task Assigned',
                'date' => $application->task->created_at,
                'status' => 'completed',
                'description' => 'Task "' . $application->task->title . '" was assigned'
            ];
        }

        // Task started
        if ($application->taskStatus && $application->taskStatus->started_at) {
            $timeline[] = [
                'event' => 'Task Started',
                'date' => $application->taskStatus->started_at,
                'status' => 'completed',
                'description' => 'Volunteer started working on the task'
            ];
        }

        // Task completed
        if ($application->taskStatus && $application->taskStatus->completed_at) {
            $timeline[] = [
                'event' => 'Task Completed',
                'date' => $application->taskStatus->completed_at,
                'status' => 'completed',
                'description' => 'Task was completed successfully'
            ];
        }

        return $timeline;
    }

    /**
     * Private method to notify volunteers when task is completed
     */
    private function notifyVolunteersTaskCompleted(Task $task)
    {
        $applications = $task->applications()
                            ->where('status', 'accepted')
                            ->where('confirmation_status', 'confirmed')
                            ->with('volunteer')
                            ->get();

        foreach ($applications as $application) {
            // You can create a specific notification for task completion
            // For now, we'll use a simple notification
            $application->volunteer->notify(new \App\Notifications\TaskCompletedNotification($task));
        }
    }
}
