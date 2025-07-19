@extends('layouts.organization')

@section('title', 'View Opportunity - MVMS')

@section('page-title', 'Opportunity Details')

@section('content')
<div class="container-fluid">
    <!-- Opportunity Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">{{ $opportunity->title }}</h4>
                    <div>
                        <a href="{{ route('opportunities.edit', $opportunity) }}" class="btn btn-outline-primary btn-sm me-2">
                            <i class="bi bi-pencil me-1"></i>Edit
                        </a>
                        <a href="{{ route('opportunities.index') }}" class="btn btn-secondary btn-sm">
                            <i class="bi bi-arrow-left me-1"></i>Back to Opportunities
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h6 class="text-muted mb-2">Description</h6>
                            <p>{{ $opportunity->description }}</p>
                            
                            @if($opportunity->requirements)
                                <h6 class="text-muted mb-2 mt-4">Requirements</h6>
                                <p>{{ $opportunity->requirements }}</p>
                            @endif
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Opportunity Details</h6>
                                    
                                    <div class="mb-2">
                                        <small class="text-muted">Status:</small><br>
                                        <span class="badge bg-{{ $opportunity->status === 'active' ? 'success' : ($opportunity->status === 'closed' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($opportunity->status) }}
                                        </span>
                                    </div>
                                    
                                    <div class="mb-2">
                                        <small class="text-muted">Category:</small><br>
                                        <span class="badge bg-info">{{ $opportunity->category ?? 'General' }}</span>
                                    </div>
                                    
                                    @if($opportunity->location)
                                        <div class="mb-2">
                                            <small class="text-muted">Location:</small><br>
                                            <i class="bi bi-geo-alt me-1"></i>{{ $opportunity->location }}
                                        </div>
                                    @endif
                                    
                                    @if($opportunity->start_date)
                                        <div class="mb-2">
                                            <small class="text-muted">Start Date:</small><br>
                                            <i class="bi bi-calendar me-1"></i>{{ \Carbon\Carbon::parse($opportunity->start_date)->format('M d, Y') }}
                                        </div>
                                    @endif
                                    
                                    @if($opportunity->end_date)
                                        <div class="mb-2">
                                            <small class="text-muted">End Date:</small><br>
                                            <i class="bi bi-calendar me-1"></i>{{ \Carbon\Carbon::parse($opportunity->end_date)->format('M d, Y') }}
                                        </div>
                                    @endif
                                    
                                    <div class="mb-2">
                                        <small class="text-muted">Views:</small><br>
                                        <i class="bi bi-eye me-1"></i>{{ $opportunity->views_count ?? 0 }}
                                    </div>
                                    
                                    <div class="mb-2">
                                        <small class="text-muted">Applications:</small><br>
                                        <i class="bi bi-people me-1"></i>{{ $opportunity->applications->count() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Applications Section -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-people me-2"></i>Applications ({{ $opportunity->applications->count() }})
                    </h5>
                </div>
                <div class="card-body">
                    @if($opportunity->applications->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Volunteer</th>
                                        <th>Applied Date</th>
                                        <th>Status</th>
                                        <th>Contact</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($opportunity->applications as $application)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                                                        <i class="bi bi-person text-white"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0">{{ $application->volunteer->volunteerProfile->full_name ?? $application->volunteer->name }}</h6>
                                                        @if($application->volunteer->volunteerProfile)
                                                            <small class="text-muted">{{ $application->volunteer->volunteerProfile->district ?? 'Location not specified' }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $application->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <span class="badge bg-{{ 
                                                    $application->status === 'accepted' ? 'success' : 
                                                    ($application->status === 'rejected' ? 'danger' : 'warning') 
                                                }}">
                                                    {{ ucfirst($application->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($application->volunteer->volunteerProfile)
                                                    <small>{{ $application->volunteer->volunteerProfile->phone ?? $application->volunteer->email }}</small>
                                                @else
                                                    <small>{{ $application->volunteer->email }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($application->status === 'pending')
                                                    <div class="btn-group btn-group-sm">
                                                        <button type="button" class="btn btn-success btn-sm" 
                                                                onclick="updateApplicationStatus({{ $application->id }}, 'accepted')">
                                                            <i class="bi bi-check"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-danger btn-sm" 
                                                                onclick="updateApplicationStatus({{ $application->id }}, 'rejected')">
                                                            <i class="bi bi-x"></i>
                                                        </button>
                                                    </div>
                                                @else
                                                    <small class="text-muted">{{ ucfirst($application->status) }}</small>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-inbox display-4 text-muted"></i>
                            <h5 class="mt-3 text-muted">No Applications Yet</h5>
                            <p class="text-muted">No volunteers have applied for this opportunity yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function updateApplicationStatus(applicationId, status) {
    if (confirm(`Are you sure you want to ${status} this application?`)) {
        fetch(`/applications/${applicationId}/status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ status: status })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error updating application status');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating application status');
        });
    }
}
</script>
@endpush
@endsection
