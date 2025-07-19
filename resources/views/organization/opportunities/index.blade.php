@extends('layouts.organization')

@section('title', 'My Opportunities - MVMS')

@section('page-title', 'Manage Opportunities')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>
                <i class="bi bi-briefcase me-2"></i>My Volunteer Opportunities
            </h2>
            <p>Create and manage volunteer opportunities for your organization.</p>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="{{ route('opportunities.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>Create New Opportunity
            </a>
        </div>
    </div>



    <!-- Opportunities List -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-list me-2"></i>Opportunities List
            </h5>
        </div>
        <div class="card-body">
            @if($opportunities->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Start Date</th>
                                <th>Volunteers</th>
                                <th>Applications</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($opportunities as $opportunity)
                            <tr>
                                <td>
                                    <div>
                                        <strong>{{ $opportunity->title }}</strong>
                                        @if($opportunity->urgency === 'urgent')
                                            <span class="badge bg-danger ms-1">Urgent</span>
                                        @elseif($opportunity->urgency === 'high')
                                            <span class="badge bg-warning ms-1">High Priority</span>
                                        @endif
                                        <br>
                                        <small>{{ $opportunity->district }}, {{ $opportunity->region }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $opportunity->category }}</span>
                                </td>
                                <td>
                                    @if($opportunity->status === 'published')
                                        <span class="badge bg-success">Published</span>
                                    @elseif($opportunity->status === 'draft')
                                        <span class="badge bg-warning">Draft</span>
                                    @elseif($opportunity->status === 'paused')
                                        <span class="badge bg-info">Paused</span>
                                    @elseif($opportunity->status === 'completed')
                                        <span class="badge bg-dark">Completed</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($opportunity->status) }}</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $opportunity->start_date->format('M j, Y') }}
                                    @if($opportunity->start_time)
                                        <br><small>{{ $opportunity->start_time->format('g:i A') }}</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress me-2" style="width: 60px; height: 8px;">
                                            @php
                                                $percentage = $opportunity->volunteers_needed > 0 
                                                    ? ($opportunity->volunteers_recruited / $opportunity->volunteers_needed) * 100 
                                                    : 0;
                                            @endphp
                                            <div class="progress-bar bg-success" style="width: {{ $percentage }}%"></div>
                                        </div>
                                        <small>{{ $opportunity->volunteers_recruited }}/{{ $opportunity->volunteers_needed }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ $opportunity->applications_count }}</span>
                                    @if($opportunity->pendingApplications->count() > 0)
                                        <br><small class="text-warning">{{ $opportunity->pendingApplications->count() }} pending</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('opportunities.show', $opportunity) }}" 
                                           class="btn btn-sm btn-outline-primary" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('organization.opportunities.tasks.index', $opportunity) }}"
                                           class="btn btn-sm btn-outline-info" title="Manage Tasks">
                                            <i class="bi bi-list-task"></i>
                                        </a>
                                        <a href="{{ route('opportunities.edit', $opportunity) }}"
                                           class="btn btn-sm btn-outline-secondary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        @if($opportunity->status === 'draft')
                                            <form method="POST" action="{{ route('opportunities.publish', $opportunity) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success" title="Publish">
                                                    <i class="bi bi-check-circle"></i>
                                                </button>
                                            </form>
                                        @elseif($opportunity->status === 'published')
                                            <form method="POST" action="{{ route('opportunities.pause', $opportunity) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-warning" title="Pause">
                                                    <i class="bi bi-pause-circle"></i>
                                                </button>
                                            </form>
                                        @endif
                                        @if($opportunity->acceptedApplications->count() === 0)
                                            <form method="POST" action="{{ route('opportunities.destroy', $opportunity) }}" 
                                                  class="d-inline" onsubmit="return confirm('Are you sure you want to delete this opportunity?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($opportunities->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $opportunities->links() }}
                    </div>
                @endif
            @else
                <div class="text-center py-5">
                    <i class="bi bi-briefcase" style="font-size: 4rem; color: #dee2e6;"></i>
                    <h4 class="mt-3">No Opportunities Yet</h4>
                    <p>You haven't created any volunteer opportunities yet.</p>
                    <a href="{{ route('opportunities.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i>Create Your First Opportunity
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-refresh page every 5 minutes to update application counts
    setTimeout(function() {
        location.reload();
    }, 300000); // 5 minutes
});
</script>
@endpush
