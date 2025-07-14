<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CalendarEvent;
use App\Models\Assignment;
use App\Models\Task;
use App\Models\Schedule;

class CalendarController extends Controller
{
    /**
     * Display calendar view for volunteers
     */
    public function volunteerCalendar(Request $request)
    {
        $user = Auth::user();

        // Get date range from request or default to current month
        $start = $request->input('start', now()->startOfMonth()->toDateString());
        $end = $request->input('end', now()->endOfMonth()->toDateString());

        // Get assignments
        $assignments = Assignment::where('volunteer_id', $user->id)
            ->whereIn('status', ['pending', 'accepted', 'completed'])
            ->whereBetween('scheduled_start', [$start, $end])
            ->with(['task.opportunity'])
            ->get();

        // Get calendar events
        $calendarEvents = CalendarEvent::where('user_id', $user->id)
            ->whereBetween('start_datetime', [$start, $end])
            ->get();

        // Get availability schedules
        $schedules = Schedule::where('user_id', $user->id)
            ->where('type', 'availability')
            ->whereBetween('start_datetime', [$start, $end])
            ->get();

        $events = collect();

        // Add assignments to events
        foreach ($assignments as $assignment) {
            $events->push([
                'id' => 'assignment_' . $assignment->id,
                'title' => $assignment->task->title,
                'start' => $assignment->scheduled_start->toISOString(),
                'end' => $assignment->scheduled_end->toISOString(),
                'color' => $this->getAssignmentColor($assignment->status),
                'url' => route('assignments.show', $assignment),
                'type' => 'assignment',
                'status' => $assignment->status,
                'extendedProps' => [
                    'opportunity' => $assignment->task->opportunity->title,
                    'location' => $assignment->task->location_address,
                    'priority' => $assignment->task->priority,
                    'description' => $assignment->task->description,
                ]
            ]);
        }

        // Add calendar events
        foreach ($calendarEvents as $event) {
            $events->push([
                'id' => 'event_' . $event->id,
                'title' => $event->title,
                'start' => $event->start_datetime->toISOString(),
                'end' => $event->end_datetime->toISOString(),
                'color' => $event->color,
                'type' => $event->type,
                'allDay' => $event->is_all_day,
                'extendedProps' => [
                    'description' => $event->description,
                    'location' => $event->location,
                ]
            ]);
        }

        // Add availability schedules
        foreach ($schedules as $schedule) {
            $events->push([
                'id' => 'schedule_' . $schedule->id,
                'title' => $schedule->title,
                'start' => $schedule->start_datetime->toISOString(),
                'end' => $schedule->end_datetime->toISOString(),
                'color' => '#e9ecef',
                'type' => 'availability',
                'display' => 'background',
                'extendedProps' => [
                    'availability_type' => $schedule->availability_type,
                ]
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json($events);
        }

        return view('volunteer.calendar', compact('events'));
    }

    /**
     * Display calendar view for organizations
     */
    public function organizationCalendar(Request $request)
    {
        $user = Auth::user();

        // Get date range from request or default to current month
        $start = $request->input('start', now()->startOfMonth()->toDateString());
        $end = $request->input('end', now()->endOfMonth()->toDateString());

        // Get tasks for organization's opportunities
        $tasks = Task::whereHas('opportunity', function($query) use ($user) {
                $query->where('organization_id', $user->id);
            })
            ->whereBetween('start_datetime', [$start, $end])
            ->with(['assignments.volunteer', 'opportunity'])
            ->get();

        $events = collect();

        // Add tasks to events
        foreach ($tasks as $task) {
            $events->push([
                'id' => 'task_' . $task->id,
                'title' => $task->title,
                'start' => $task->start_datetime->toISOString(),
                'end' => $task->end_datetime->toISOString(),
                'color' => $this->getTaskColor($task->status, $task->priority),
                'url' => route('tasks.show', [$task->opportunity, $task]),
                'type' => 'task',
                'extendedProps' => [
                    'opportunity' => $task->opportunity->title,
                    'volunteers_needed' => $task->volunteers_needed,
                    'volunteers_assigned' => $task->volunteers_assigned,
                    'status' => $task->status,
                    'priority' => $task->priority,
                    'location' => $task->location_address,
                ]
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json($events);
        }

        return view('organization.calendar', compact('events'));
    }

    /**
     * Check for scheduling conflicts
     */
    public function checkConflicts(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'volunteer_id' => 'required|exists:users,id',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
        ];

        $validatedData = $request->validate($rules);

        $conflicts = [];

        // Check existing assignments
        $existingAssignments = Assignment::where('volunteer_id', $validatedData['volunteer_id'])
            ->where('status', 'accepted')
            ->where(function($query) use ($validatedData) {
                $query->whereBetween('scheduled_start', [$validatedData['start_datetime'], $validatedData['end_datetime']])
                      ->orWhereBetween('scheduled_end', [$validatedData['start_datetime'], $validatedData['end_datetime']])
                      ->orWhere(function($q) use ($validatedData) {
                          $q->where('scheduled_start', '<=', $validatedData['start_datetime'])
                            ->where('scheduled_end', '>=', $validatedData['end_datetime']);
                      });
            })
            ->with('task')
            ->get();

        foreach ($existingAssignments as $assignment) {
            $conflicts[] = [
                'type' => 'assignment',
                'title' => $assignment->task->title,
                'start' => $assignment->scheduled_start,
                'end' => $assignment->scheduled_end,
                'severity' => 'high'
            ];
        }

        // Check volunteer availability
        $unavailableSchedules = Schedule::where('user_id', $validatedData['volunteer_id'])
            ->where('type', 'unavailability')
            ->where(function($query) use ($validatedData) {
                $query->whereBetween('start_datetime', [$validatedData['start_datetime'], $validatedData['end_datetime']])
                      ->orWhereBetween('end_datetime', [$validatedData['start_datetime'], $validatedData['end_datetime']])
                      ->orWhere(function($q) use ($validatedData) {
                          $q->where('start_datetime', '<=', $validatedData['start_datetime'])
                            ->where('end_datetime', '>=', $validatedData['end_datetime']);
                      });
            })
            ->get();

        foreach ($unavailableSchedules as $schedule) {
            $conflicts[] = [
                'type' => 'unavailable',
                'title' => $schedule->title,
                'start' => $schedule->start_datetime,
                'end' => $schedule->end_datetime,
                'severity' => 'medium'
            ];
        }

        return response()->json([
            'has_conflicts' => count($conflicts) > 0,
            'conflicts' => $conflicts
        ]);
    }

    /**
     * Get available volunteers for a time slot
     */
    public function getAvailableVolunteers(Request $request)
    {
        $rules = [
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
            'required_skills' => 'nullable|array',
            'location_district' => 'nullable|string',
        ];

        $validatedData = $request->validate($rules);

        // Get volunteers who are not assigned during this time
        $busyVolunteerIds = Assignment::where('status', 'accepted')
            ->where(function($query) use ($validatedData) {
                $query->whereBetween('scheduled_start', [$validatedData['start_datetime'], $validatedData['end_datetime']])
                      ->orWhereBetween('scheduled_end', [$validatedData['start_datetime'], $validatedData['end_datetime']])
                      ->orWhere(function($q) use ($validatedData) {
                          $q->where('scheduled_start', '<=', $validatedData['start_datetime'])
                            ->where('scheduled_end', '>=', $validatedData['end_datetime']);
                      });
            })
            ->pluck('volunteer_id');

        // Get volunteers who marked themselves as unavailable
        $unavailableVolunteerIds = Schedule::where('type', 'unavailability')
            ->where(function($query) use ($validatedData) {
                $query->whereBetween('start_datetime', [$validatedData['start_datetime'], $validatedData['end_datetime']])
                      ->orWhereBetween('end_datetime', [$validatedData['start_datetime'], $validatedData['end_datetime']])
                      ->orWhere(function($q) use ($validatedData) {
                          $q->where('start_datetime', '<=', $validatedData['start_datetime'])
                            ->where('end_datetime', '>=', $validatedData['end_datetime']);
                      });
            })
            ->pluck('user_id');

        $excludeIds = $busyVolunteerIds->merge($unavailableVolunteerIds)->unique();

        // Build query for available volunteers
        $query = \App\Models\User::whereHas('roles', function($q) {
                $q->where('name', 'volunteer');
            })
            ->whereNotIn('id', $excludeIds)
            ->with('volunteerProfile');

        // Filter by skills if provided
        if (!empty($validatedData['required_skills'])) {
            $query->whereHas('volunteerProfile', function($q) use ($validatedData) {
                foreach ($validatedData['required_skills'] as $skill) {
                    $q->whereJsonContains('skills', $skill);
                }
            });
        }

        // Filter by location if provided
        if (!empty($validatedData['location_district'])) {
            $query->whereHas('volunteerProfile', function($q) use ($validatedData) {
                $q->where('district', $validatedData['location_district'])
                  ->orWhere('can_travel', true);
            });
        }

        $volunteers = $query->get();

        return response()->json($volunteers);
    }

    /**
     * Get assignment color based on status
     */
    private function getAssignmentColor($status): string
    {
        return match($status) {
            'pending' => '#ffc107',
            'accepted' => '#28a745',
            'completed' => '#17a2b8',
            'declined' => '#dc3545',
            'cancelled' => '#6c757d',
            'no_show' => '#fd7e14',
            default => '#007bff'
        };
    }

    /**
     * Get task color based on status and priority
     */
    private function getTaskColor($status, $priority): string
    {
        if ($status === 'completed') return '#17a2b8';
        if ($status === 'cancelled') return '#6c757d';

        return match($priority) {
            'urgent' => '#dc3545',
            'high' => '#fd7e14',
            'medium' => '#ffc107',
            'low' => '#28a745',
            default => '#007bff'
        };
    }
}
