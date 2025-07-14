@extends('layouts.volunteer')

@section('title', 'Browse Opportunities - MVMS')

@section('page-title', 'Browse Volunteer Opportunities')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>
                <i class="bi bi-search me-2"></i>Browse Volunteer Opportunities
            </h2>
            <p>Find meaningful volunteer opportunities that match your skills and interests.</p>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="{{ route('volunteer.opportunities.recommended') }}" class="btn btn-success">
                <i class="bi bi-star me-1"></i>Recommended for You
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0">
                <i class="bi bi-funnel me-2"></i>Filter Opportunities
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('volunteer.opportunities.index') }}" id="filterForm">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="{{ request('search') }}" placeholder="Search opportunities...">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="category" class="form-label">Category</label>
                        <select class="form-select" id="category" name="category">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>
                                    {{ $cat }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="district" class="form-label">District</label>
                        <select class="form-select" id="district" name="district">
                            <option value="">All Districts</option>
                            @foreach($districts as $dist)
                                <option value="{{ $dist }}" {{ request('district') === $dist ? 'selected' : '' }}>
                                    {{ $dist }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="region" class="form-label">Region</label>
                        <select class="form-select" id="region" name="region">
                            <option value="">All Regions</option>
                            @foreach($regions as $reg)
                                <option value="{{ $reg }}" {{ request('region') === $reg ? 'selected' : '' }}>
                                    {{ $reg }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="type" class="form-label">Type</label>
                        <select class="form-select" id="type" name="type">
                            <option value="">All Types</option>
                            <option value="one_time" {{ request('type') === 'one_time' ? 'selected' : '' }}>One-time</option>
                            <option value="recurring" {{ request('type') === 'recurring' ? 'selected' : '' }}>Recurring</option>
                            <option value="ongoing" {{ request('type') === 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="urgency" class="form-label">Urgency</label>
                        <select class="form-select" id="urgency" name="urgency">
                            <option value="">All Urgency Levels</option>
                            <option value="urgent" {{ request('urgency') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                            <option value="high" {{ request('urgency') === 'high' ? 'selected' : '' }}>High</option>
                            <option value="medium" {{ request('urgency') === 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="low" {{ request('urgency') === 'low' ? 'selected' : '' }}>Low</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="sort" class="form-label">Sort By</label>
                        <select class="form-select" id="sort" name="sort">
                            <option value="relevance" {{ request('sort') === 'relevance' ? 'selected' : '' }}>Relevance</option>
                            <option value="date" {{ request('sort') === 'date' ? 'selected' : '' }}>Start Date</option>
                            <option value="urgency" {{ request('sort') === 'urgency' ? 'selected' : '' }}>Urgency</option>
                            <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>Newest</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3 d-flex align-items-end">
                        <div class="w-100">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-search me-1"></i>Filter
                            </button>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="is_paid" name="is_paid" value="1" 
                                   {{ request('is_paid') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_paid">Paid Opportunities Only</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="provides_transport" name="provides_transport" value="1" 
                                   {{ request('provides_transport') ? 'checked' : '' }}>
                            <label class="form-check-label" for="provides_transport">Transportation Provided</label>
                        </div>
                        <a href="{{ route('volunteer.opportunities.index') }}" class="btn btn-outline-secondary btn-sm ms-3">
                            <i class="bi bi-x-circle me-1"></i>Clear Filters
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Results -->
    <div class="row">
        <div class="col-12 mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5>{{ $opportunities->total() }} Opportunities Found</h5>
                <small>Showing {{ $opportunities->firstItem() ?? 0 }} - {{ $opportunities->lastItem() ?? 0 }} of {{ $opportunities->total() }}</small>
            </div>
        </div>
    </div>

    <!-- Opportunities Grid -->
    @if($opportunities->count() > 0)
        <div class="row">
            @foreach($opportunities as $opportunity)
                <div class="col-lg-6 col-xl-4 mb-4">
                    <div class="card h-100 opportunity-card">
                        <div class="card-header d-flex justify-content-between align-items-start">
                            <div>
                                <span class="badge bg-secondary">{{ $opportunity->category }}</span>
                                @if($opportunity->urgency === 'urgent')
                                    <span class="badge bg-danger">Urgent</span>
                                @elseif($opportunity->urgency === 'high')
                                    <span class="badge bg-warning">High Priority</span>
                                @endif
                                @if($opportunity->is_paid)
                                    <span class="badge bg-success">Paid</span>
                                @endif
                            </div>
                            @if(isset($opportunity->match_score) && $opportunity->match_score > 0)
                                <div class="text-end">
                                    <small class="text-success fw-bold">{{ $opportunity->match_score }}% Match</small>
                                </div>
                            @endif
                        </div>
                        <div class="card-body">
                            <h6 class="card-title">{{ $opportunity->title }}</h6>
                            <p class="card-text">
                                <small class="text-muted">
                                    <i class="bi bi-building me-1"></i>{{ $opportunity->organization->name }}
                                </small>
                            </p>
                            <p class="card-text">{{ Str::limit($opportunity->description, 120) }}</p>
                            
                            <div class="mb-2">
                                <small>
                                    <i class="bi bi-geo-alt me-1"></i>{{ $opportunity->district }}, {{ $opportunity->region }}
                                </small>
                            </div>
                            <div class="mb-2">
                                <small>
                                    <i class="bi bi-calendar me-1"></i>{{ $opportunity->start_date->format('M j, Y') }}
                                    @if($opportunity->start_time)
                                        at {{ $opportunity->start_time->format('g:i A') }}
                                    @endif
                                </small>
                            </div>
                            <div class="mb-2">
                                <small>
                                    <i class="bi bi-people me-1"></i>{{ $opportunity->spots_remaining }} spots remaining
                                </small>
                            </div>
                            
                            @if($opportunity->required_skills && count($opportunity->required_skills) > 0)
                                <div class="mb-2">
                                    @foreach(array_slice($opportunity->required_skills, 0, 3) as $skill)
                                        <span class="badge bg-light text-dark me-1">{{ $skill }}</span>
                                    @endforeach
                                    @if(count($opportunity->required_skills) > 3)
                                        <span class="badge bg-light text-dark">+{{ count($opportunity->required_skills) - 3 }} more</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                        <div class="card-footer">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    @if(isset($userApplications[$opportunity->id]))
                                        @php $status = $userApplications[$opportunity->id]; @endphp
                                        @if($status === 'pending')
                                            <span class="badge bg-warning">Application Pending</span>
                                        @elseif($status === 'accepted')
                                            <span class="badge bg-success">Accepted</span>
                                        @elseif($status === 'rejected')
                                            <span class="badge bg-danger">Not Selected</span>
                                        @endif
                                    @else
                                        @if($opportunity->is_full)
                                            <span class="badge bg-secondary">Full</span>
                                        @elseif($opportunity->application_status !== 'Open for applications')
                                            <span class="badge bg-warning">{{ $opportunity->application_status }}</span>
                                        @endif
                                    @endif
                                </div>
                                <div>
                                    <a href="{{ route('volunteer.opportunities.show', $opportunity) }}" 
                                       class="btn btn-primary btn-sm">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            {{ $opportunities->appends(request()->query())->links() }}
        </div>
    @else
        <div class="text-center py-5">
            <i class="bi bi-search" style="font-size: 4rem; color: #dee2e6;"></i>
            <h4 class="mt-3">No Opportunities Found</h4>
            <p>Try adjusting your filters or search terms to find more opportunities.</p>
            <a href="{{ route('volunteer.opportunities.index') }}" class="btn btn-primary">
                <i class="bi bi-arrow-clockwise me-1"></i>Clear All Filters
            </a>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-submit form when filters change
    $('#category, #district, #region, #type, #urgency, #sort, #is_paid, #provides_transport').change(function() {
        $('#filterForm').submit();
    });
    
    // Opportunity card hover effects
    $('.opportunity-card').hover(
        function() {
            $(this).addClass('shadow-lg').css('transform', 'translateY(-2px)');
        },
        function() {
            $(this).removeClass('shadow-lg').css('transform', 'translateY(0)');
        }
    );
});
</script>
@endpush

@push('styles')
<style>
.opportunity-card {
    transition: all 0.3s ease;
    border: 1px solid #dee2e6;
}

.opportunity-card:hover {
    border-color: #28a745;
}
</style>
@endpush
