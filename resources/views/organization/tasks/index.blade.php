@extends('layouts.organization')

@section('title', 'Tasks - MVMS')

@section('page-title', 'Task Management')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-list-task me-2"></i>Task Management
                    </h5>
                    <a href="{{ route('organization.tasks.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i>Create New Task
                    </a>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">Manage and assign tasks to your volunteers.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tasks List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">All Tasks</h6>
                </div>
                <div class="card-body">
                    @if(isset($tasks) && $tasks->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Task</th>
                                        <th>Opportunity</th>
                                        <th>Assigned To</th>
                                        <th>Due Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tasks as $task)
                                        <tr>
                                            <td>
                                                <h6 class="mb-1">{{ $task->title }}</h6>
                                                <small class="text-muted">{{ Str::limit($task->description, 50) }}</small>
                                            </td>
                                            <td>
                                                @if($task->opportunity)
                                                    <a href="{{ route('organization.opportunities.show', $task->opportunity) }}" class="text-decoration-none">
                                                        {{ $task->opportunity->title }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">No opportunity</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($task->volunteer)
                                                    {{ $task->volunteer->volunteerProfile->full_name ?? $task->volunteer->name }}
                                                @else
                                                    <span class="text-muted">Unassigned</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($task->due_date)
                                                    {{ \Carbon\Carbon::parse($task->due_date)->format('M d, Y') }}
                                                @else
                                                    <span class="text-muted">No due date</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ 
                                                    $task->status === 'completed' ? 'success' : 
                                                    ($task->status === 'in_progress' ? 'warning' : 'secondary') 
                                                }}">
                                                    {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('organization.tasks.show', $task) }}" class="btn btn-outline-primary btn-sm">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="{{ route('organization.tasks.edit', $task) }}" class="btn btn-outline-secondary btn-sm">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        @if(method_exists($tasks, 'links'))
                            <div class="d-flex justify-content-center">
                                {{ $tasks->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-list-task display-4 text-muted"></i>
                            <h5 class="mt-3 text-muted">No Tasks Yet</h5>
                            <p class="text-muted">Create your first task to get started with task management.</p>
                            <a href="{{ route('organization.tasks.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-1"></i>Create First Task
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
