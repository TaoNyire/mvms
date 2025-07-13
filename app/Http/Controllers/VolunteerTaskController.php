<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Application;
use App\Models\ApplicationTaskStatus;
use App\Models\SystemLog;
use Illuminate\Support\Facades\Auth;

class VolunteerTaskController extends Controller
{
    /**
     * Get volunteer's active tasks
     */
    public function getMyTasks(Request $request)
    {
        $user = Auth::user();

        $applications = Application::where('volunteer_id', $user->id)
            ->where('status', 'accepted')
            ->where('confirmation_status', 'confirmed')
            ->with(['opportunity.organization', 'taskStatus', 'task'])
            ->get();

        $tasks = $applications->map(function($application) {
            $taskStatus = $application->taskStatus;
            $task = $application->task;

            return [
                'id' => $application->id,
                'opportunity' => [
                    'id' => $application->opportunity->id,
                    'title' => $application->opportunity->title,
                    'description' => $application->opportunity->description,
                    'location' => $application->opportunity->location,
                    'start_date' => $application->opportunity->start_date,
                    'end_date' => $application->opportunity->end_date,
                    'organization' => $application->opportunity->organization->name ?? 'Unknown'
                ],
                'task' => $task ? [
                    'id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'start_date' => $task->start_date,
                    'end_date' => $task->end_date,
                    'status' => $task->status
                ] : null,
                'task_status' => $taskStatus ? [
                    'status' => $taskStatus->status,
                    'started_at' => $taskStatus->started_at,
                    'completed_at' => $taskStatus->completed_at,
                    'completion_notes' => $taskStatus->completion_notes,
                    'work_evidence' => $taskStatus->work_evidence
                ] : [
                    'status' => 'pending',
                    'started_at' => null,
                    'completed_at' => null,
                    'completion_notes' => null,
                    'work_evidence' => null
                ],
                'applied_at' => $application->created_at,
                'can_start' => !$taskStatus || $taskStatus->status === 'pending',
                'can_complete' => $taskStatus && $taskStatus->status === 'in_progress',
                'is_completed' => $taskStatus && $taskStatus->status === 'completed'
            ];
        });

        return response()->json([
            'tasks' => $tasks,
            'total_tasks' => $tasks->count(),
            'pending_tasks' => $tasks->where('task_status.status', 'pending')->count(),
            'in_progress_tasks' => $tasks->where('task_status.status', 'in_progress')->count(),
            'completed_tasks' => $tasks->where('task_status.status', 'completed')->count()
        ]);
    }

    /**
     * Start a task
     */
    public function startTask(Request $request, $applicationId)
    {
        $user = Auth::user();
        
        $application = Application::where('id', $applicationId)
            ->where('volunteer_id', $user->id)
            ->where('status', 'accepted')
            ->first();

        if (!$application) {
            return response()->json([
                'message' => 'Application not found or not authorized'
            ], 404);
        }

        // Check if task status already exists
        $taskStatus = ApplicationTaskStatus::where('application_id', $applicationId)->first();
        
        if ($taskStatus && $taskStatus->status !== 'pending') {
            return response()->json([
                'message' => 'Task has already been started or completed'
            ], 422);
        }

        if ($taskStatus) {
            $taskStatus->markAsStarted();
        } else {
            $taskStatus = ApplicationTaskStatus::create([
                'application_id' => $applicationId,
                'status' => 'in_progress',
                'started_at' => now()
            ]);
        }

        // Log the action
        SystemLog::logUserAction('start_task', 'Task', $applicationId, [
            'opportunity_title' => $application->opportunity->title,
            'organization' => $application->opportunity->organization->name ?? 'Unknown'
        ]);

        return response()->json([
            'message' => 'Task started successfully',
            'task_status' => $taskStatus
        ]);
    }

    /**
     * Complete a task
     */
    public function completeTask(Request $request, $applicationId)
    {
        $validated = $request->validate([
            'completion_notes' => 'required|string|max:1000',
            'work_evidence' => 'nullable|array',
            'work_evidence.*' => 'string' // URLs or file paths
        ]);

        $user = Auth::user();
        
        $application = Application::where('id', $applicationId)
            ->where('volunteer_id', $user->id)
            ->where('status', 'accepted')
            ->first();

        if (!$application) {
            return response()->json([
                'message' => 'Application not found or not authorized'
            ], 404);
        }

        $taskStatus = ApplicationTaskStatus::where('application_id', $applicationId)->first();
        
        if (!$taskStatus || $taskStatus->status !== 'in_progress') {
            return response()->json([
                'message' => 'Task must be started before it can be completed'
            ], 422);
        }

        $taskStatus->markAsCompleted(
            $validated['completion_notes'],
            $validated['work_evidence'] ?? null
        );

        // Log the action
        SystemLog::logUserAction('complete_task', 'Task', $applicationId, [
            'opportunity_title' => $application->opportunity->title,
            'organization' => $application->opportunity->organization->name ?? 'Unknown',
            'completion_notes' => $validated['completion_notes']
        ]);

        return response()->json([
            'message' => 'Task completed successfully',
            'task_status' => $taskStatus
        ]);
    }

    /**
     * Quit a task
     */
    public function quitTask(Request $request, $applicationId)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $user = Auth::user();
        
        $application = Application::where('id', $applicationId)
            ->where('volunteer_id', $user->id)
            ->where('status', 'accepted')
            ->first();

        if (!$application) {
            return response()->json([
                'message' => 'Application not found or not authorized'
            ], 404);
        }

        $taskStatus = ApplicationTaskStatus::where('application_id', $applicationId)->first();
        
        if (!$taskStatus || $taskStatus->status === 'completed') {
            return response()->json([
                'message' => 'Cannot quit a completed task'
            ], 422);
        }

        if ($taskStatus) {
            $taskStatus->markAsQuit($validated['reason']);
        } else {
            ApplicationTaskStatus::create([
                'application_id' => $applicationId,
                'status' => 'quit',
                'completed_at' => now(),
                'completion_notes' => $validated['reason']
            ]);
        }

        // Log the action
        SystemLog::logUserAction('quit_task', 'Task', $applicationId, [
            'opportunity_title' => $application->opportunity->title,
            'organization' => $application->opportunity->organization->name ?? 'Unknown',
            'reason' => $validated['reason']
        ]);

        return response()->json([
            'message' => 'Task quit successfully'
        ]);
    }

    /**
     * Update task progress
     */
    public function updateProgress(Request $request, $applicationId)
    {
        $validated = $request->validate([
            'progress_notes' => 'required|string|max:1000',
            'work_evidence' => 'nullable|array',
            'work_evidence.*' => 'string'
        ]);

        $user = Auth::user();
        
        $application = Application::where('id', $applicationId)
            ->where('volunteer_id', $user->id)
            ->where('status', 'accepted')
            ->first();

        if (!$application) {
            return response()->json([
                'message' => 'Application not found or not authorized'
            ], 404);
        }

        $taskStatus = ApplicationTaskStatus::where('application_id', $applicationId)->first();
        
        if (!$taskStatus || $taskStatus->status !== 'in_progress') {
            return response()->json([
                'message' => 'Task must be in progress to update'
            ], 422);
        }

        $taskStatus->update([
            'completion_notes' => $validated['progress_notes'],
            'work_evidence' => $validated['work_evidence'] ?? $taskStatus->work_evidence
        ]);

        // Log the action
        SystemLog::logUserAction('update_task_progress', 'Task', $applicationId, [
            'opportunity_title' => $application->opportunity->title,
            'organization' => $application->opportunity->organization->name ?? 'Unknown'
        ]);

        return response()->json([
            'message' => 'Task progress updated successfully',
            'task_status' => $taskStatus
        ]);
    }
}
