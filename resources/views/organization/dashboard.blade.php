@extends('layouts.organization')

@section('title', 'Organization Dashboard - MVMS')

@section('page-title', 'Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="mb-2">Welcome back, {{ Auth::user()->organizationProfile->org_name ?? Auth::user()->name }}!</h4>
                            <p class="mb-0">Manage your volunteer opportunities and track applications from your dashboard.</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <i class="bi bi-building display-4 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="display-6 text-primary mb-2">
                        <i class="bi bi-briefcase"></i>
                    </div>
                    <h5 class="card-title">{{ $stats['total_opportunities'] ?? 0 }}</h5>
                    <p class="card-text text-muted">Total Opportunities</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="display-6 text-success mb-2">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <h5 class="card-title">{{ $stats['active_opportunities'] ?? 0 }}</h5>
                    <p class="card-text text-muted">Active Opportunities</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="display-6 text-info mb-2">
                        <i class="bi bi-people"></i>
                    </div>
                    <h5 class="card-title">{{ $stats['total_applications'] ?? 0 }}</h5>
                    <p class="card-text text-muted">Total Applications</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="display-6 text-warning mb-2">
                        <i class="bi bi-clock"></i>
                    </div>
                    <h5 class="card-title">{{ $stats['pending_applications'] ?? 0 }}</h5>
                    <p class="card-text text-muted">Pending Applications</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-lightning me-2"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('organization.opportunities.create') }}" class="btn btn-primary w-100">
                                <i class="bi bi-plus-circle me-1"></i>Create Opportunity
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('organization.opportunities.index') }}" class="btn btn-outline-primary w-100">
                                <i class="bi bi-briefcase me-1"></i>View Opportunities
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('organization.profile.show') }}" class="btn btn-outline-secondary w-100">
                                <i class="bi bi-person-badge me-1"></i>View Profile
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="#" class="btn btn-outline-info w-100">
                                <i class="bi bi-calendar me-1"></i>Calendar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Opportunities and Applications -->
    <div class="row">
        <!-- Recent Opportunities -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-briefcase me-2"></i>Recent Opportunities
                    </h5>
                    <a href="{{ route('organization.opportunities.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    @if(isset($recent_opportunities) && $recent_opportunities->count() > 0)
                        @foreach($recent_opportunities as $opportunity)
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <div>
                                    <h6 class="mb-1">
                                        <a href="{{ route('organization.opportunities.show', $opportunity) }}" class="text-decoration-none">
                                            {{ $opportunity->title }}
                                        </a>
                                    </h6>
                                    <small class="text-muted">
                                        <i class="bi bi-calendar me-1"></i>{{ $opportunity->created_at->format('M d, Y') }}
                                    </small>
                                </div>
                                <span class="badge bg-{{ $opportunity->status === 'active' ? 'success' : ($opportunity->status === 'closed' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($opportunity->status) }}
                                </span>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-briefcase display-4 text-muted"></i>
                            <h6 class="mt-3 text-muted">No Opportunities Yet</h6>
                            <p class="text-muted">Create your first volunteer opportunity to get started.</p>
                            <a href="{{ route('organization.opportunities.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-1"></i>Create Opportunity
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Applications -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-people me-2"></i>Recent Applications
                    </h5>
                </div>
                <div class="card-body">
                    @if(isset($recent_applications) && $recent_applications->count() > 0)
                        @foreach($recent_applications as $application)
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <div>
                                    <h6 class="mb-1">{{ $application->volunteer->volunteerProfile->full_name ?? $application->volunteer->name }}</h6>
                                    <small class="text-muted">
                                        Applied for: <a href="{{ route('organization.opportunities.show', $application->opportunity) }}" class="text-decoration-none">
                                            {{ $application->opportunity->title }}
                                        </a>
                                    </small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-{{ 
                                        $application->status === 'accepted' ? 'success' : 
                                        ($application->status === 'rejected' ? 'danger' : 'warning') 
                                    }}">
                                        {{ ucfirst($application->status) }}
                                    </span>
                                    <br>
                                    <small class="text-muted">{{ $application->created_at->format('M d') }}</small>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-inbox display-4 text-muted"></i>
                            <h6 class="mt-3 text-muted">No Applications Yet</h6>
                            <p class="text-muted">Applications will appear here when volunteers apply to your opportunities.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
