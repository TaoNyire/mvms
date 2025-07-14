@extends('layouts.volunteer')

@section('title', 'My Applications - MVMS')

@section('page-title', 'My Applications')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>
                <i class="bi bi-file-earmark-text me-2"></i>My Applications
            </h2>
            <p>Track the status of your volunteer opportunity applications.</p>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="{{ route('volunteer.opportunities.index') }}" class="btn btn-primary">
                <i class="bi bi-search me-1"></i>Find More Opportunities
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Total Applications</h6>
                            <h3 class="mb-0">{{ $applications->total() }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-file-earmark-text" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Pending</h6>
                            <h3 class="mb-0">{{ $applications->where('status', 'pending')->count() }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-clock" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Accepted</h6>
                            <h3 class="mb-0">{{ $applications->where('status', 'accepted')->count() }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-check-circle" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Completed</h6>
                            <h3 class="mb-0">{{ $applications->where('status', 'completed')->count() }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-award" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Applications List -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-list me-2"></i>Application History
            </h5>
        </div>
        <div class="card-body">
            @if($applications->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Opportunity</th>
                                <th>Organization</th>
                                <th>Applied Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($applications as $application)
                            <tr>
                                <td>
                                    <div>
                                        <strong>{{ $application->opportunity->title }}</strong>
                                        @if($application->opportunity->urgency === 'urgent')
                                            <span class="badge bg-danger ms-1">Urgent</span>
                                        @elseif($application->opportunity->urgency === 'high')
                                            <span class="badge bg-warning ms-1">High Priority</span>
                                        @endif
                                        <br>
                                        <small class="text-muted">
                                            <i class="bi bi-geo-alt me-1"></i>{{ $application->opportunity->district }}, {{ $application->opportunity->region }}
                                        </small>
                                        <br>
                                        <small class="text-muted">
                                            <i class="bi bi-calendar me-1"></i>{{ $application->opportunity->start_date->format('M j, Y') }}
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $application->opportunity->organization->name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $application->opportunity->category }}</small>
                                    </div>
                                </td>
                                <td>
                                    {{ $application->applied_at->format('M j, Y') }}
                                    <br>
                                    <small class="text-muted">{{ $application->applied_at->diffForHumans() }}</small>
                                </td>
                                <td>
                                    @if($application->status === 'pending')
                                        <span class="badge bg-warning">
                                            <i class="bi bi-clock me-1"></i>Pending Review
                                        </span>
                                    @elseif($application->status === 'accepted')
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle me-1"></i>Accepted
                                        </span>
                                        @if($application->accepted_at)
                                            <br><small class="text-muted">{{ $application->accepted_at->format('M j, Y') }}</small>
                                        @endif
                                    @elseif($application->status === 'rejected')
                                        <span class="badge bg-danger">
                                            <i class="bi bi-x-circle me-1"></i>Not Selected
                                        </span>
                                        @if($application->rejected_at)
                                            <br><small class="text-muted">{{ $application->rejected_at->format('M j, Y') }}</small>
                                        @endif
                                    @elseif($application->status === 'withdrawn')
                                        <span class="badge bg-secondary">
                                            <i class="bi bi-arrow-left-circle me-1"></i>Withdrawn
                                        </span>
                                    @elseif($application->status === 'completed')
                                        <span class="badge bg-info">
                                            <i class="bi bi-award me-1"></i>Completed
                                        </span>
                                        @if($application->completed_at)
                                            <br><small class="text-muted">{{ $application->completed_at->format('M j, Y') }}</small>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('volunteer.opportunities.show', $application->opportunity) }}" 
                                           class="btn btn-sm btn-outline-primary" title="View Opportunity">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('applications.show', $application) }}" 
                                           class="btn btn-sm btn-outline-info" title="View Application">
                                            <i class="bi bi-file-earmark-text"></i>
                                        </a>
                                        @if($application->status === 'pending' && $application->can_be_withdrawn)
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="withdrawApplication({{ $application->id }})" title="Withdraw">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $applications->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-file-earmark-text" style="font-size: 4rem; color: #dee2e6;"></i>
                    <h4 class="mt-3">No Applications Yet</h4>
                    <p>You haven't applied for any volunteer opportunities yet.</p>
                    <a href="{{ route('volunteer.opportunities.index') }}" class="btn btn-primary">
                        <i class="bi bi-search me-1"></i>Browse Opportunities
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Withdraw Application Modal -->
<div class="modal fade" id="withdrawModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Withdraw Application</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to withdraw this application?</p>
                <p class="text-muted small">This action cannot be undone. You will need to reapply if you change your mind.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="withdrawForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Withdraw Application</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function withdrawApplication(applicationId) {
    const form = document.getElementById('withdrawForm');
    form.action = `/volunteer/applications/${applicationId}/withdraw`;
    
    const modal = new bootstrap.Modal(document.getElementById('withdrawModal'));
    modal.show();
}

$(document).ready(function() {
    // Auto-refresh page every 2 minutes to update application statuses
    setTimeout(function() {
        location.reload();
    }, 120000); // 2 minutes
});
</script>
@endpush
