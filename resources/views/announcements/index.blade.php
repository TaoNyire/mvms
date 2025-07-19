@extends('layouts.' . (auth()->user()->hasRole('volunteer') ? 'volunteer' : 'organization'))

@section('title', 'Announcements - MVMS')

@section('page-title', 'Announcements')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>
                <i class="bi bi-megaphone me-2"></i>Announcements
            </h2>
            <p>Stay informed with the latest news and updates.</p>
        </div>
        <div class="col-md-4 text-md-end">
            @if(auth()->user()->hasRole('organization'))
                <a href="{{ route('announcements.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i>New Announcement
                </a>
            @endif
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body py-2">
                    <form method="GET" class="row g-3 align-items-center">
                        <div class="col-md-3">
                            <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="all" {{ request('type') === 'all' ? 'selected' : '' }}>All Types</option>
                                <option value="general" {{ request('type') === 'general' ? 'selected' : '' }}>General</option>
                                <option value="urgent" {{ request('type') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                                <option value="event" {{ request('type') === 'event' ? 'selected' : '' }}>Event</option>
                                <option value="policy" {{ request('type') === 'policy' ? 'selected' : '' }}>Policy</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="priority" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="all" {{ request('priority') === 'all' ? 'selected' : '' }}>All Priorities</option>
                                <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                                <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                                <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                            </select>
                        </div>
                        <div class="col-md-6 text-end">
                            <small class="text-muted">{{ $announcements->total() }} announcements found</small>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Announcements List -->
    <div class="row">
        <div class="col-12">
            @if($announcements->count() > 0)
                @foreach($announcements as $announcement)
                    <div class="card mb-3 announcement-card {{ $announcement->is_pinned ? 'announcement-pinned' : '' }} {{ $announcement->is_featured ? 'announcement-featured' : '' }}">
                        <div class="card-body">
                            <div class="d-flex align-items-start">
                                <!-- Announcement Icon -->
                                <div class="announcement-icon me-3">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center" 
                                         style="width: 50px; height: 50px; background-color: {{ $announcement->color }}20; color: {{ $announcement->color }};">
                                        <i class="{{ $announcement->icon ?? 'bi-megaphone' }}" style="font-size: 1.2rem;"></i>
                                    </div>
                                </div>
                                
                                <!-- Announcement Content -->
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h5 class="mb-1">
                                                @if($announcement->is_pinned)
                                                    <i class="bi bi-pin-angle text-warning me-1" title="Pinned"></i>
                                                @endif
                                                @if($announcement->is_featured)
                                                    <i class="bi bi-star-fill text-warning me-1" title="Featured"></i>
                                                @endif
                                                {{ $announcement->title }}
                                            </h5>
                                            <div class="d-flex align-items-center mb-2">
                                                <span class="badge bg-{{ $announcement->priority === 'urgent' ? 'danger' : ($announcement->priority === 'high' ? 'warning' : 'primary') }} me-2">
                                                    {{ ucfirst($announcement->priority) }}
                                                </span>
                                                <span class="badge bg-secondary me-2">{{ ucfirst($announcement->type) }}</span>

                                                @if($announcement->audience === 'my_volunteers')
                                                <span class="badge bg-info me-2">
                                                    <i class="bi bi-people-fill me-1"></i>
                                                    @if(auth()->user()->hasRole('volunteer'))
                                                        For You
                                                    @else
                                                        Your Volunteers
                                                    @endif
                                                </span>
                                                @endif

                                                <small class="text-muted">
                                                    by {{ $announcement->creator->name }} • {{ $announcement->time_ago }}
                                                </small>
                                            </div>
                                        </div>
                                        <div class="announcement-actions">
                                            @if(auth()->user()->hasRole('organization') && $announcement->created_by === auth()->id())
                                                <div class="btn-group" role="group">
                                                    @if($announcement->is_pinned)
                                                        <button type="button" class="btn btn-sm btn-outline-warning" 
                                                                onclick="unpinAnnouncement({{ $announcement->id }})" title="Unpin">
                                                            <i class="bi bi-pin-angle-fill"></i>
                                                        </button>
                                                    @else
                                                        <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                                onclick="pinAnnouncement({{ $announcement->id }})" title="Pin">
                                                            <i class="bi bi-pin-angle"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <!-- Announcement Content -->
                                    <div class="announcement-content mb-3">
                                        <p class="mb-0">{{ $announcement->excerpt }}</p>
                                    </div>
                                    
                                    <!-- Announcement Footer -->
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="announcement-stats">
                                            <button type="button" class="btn btn-sm btn-outline-primary me-2" 
                                                    onclick="toggleLike({{ $announcement->id }})" 
                                                    id="like-btn-{{ $announcement->id }}">
                                                <i class="bi bi-heart{{ $announcement->hasBeenLikedBy(auth()->user()) ? '-fill text-danger' : '' }}"></i>
                                                <span id="like-count-{{ $announcement->id }}">{{ $announcement->likes_count }}</span>
                                            </button>
                                            <small class="text-muted">
                                                <i class="bi bi-eye me-1"></i>{{ $announcement->views_count }} views
                                            </small>
                                        </div>
                                        <div>
                                            <a href="{{ route('announcements.show', $announcement) }}" class="btn btn-sm btn-primary">
                                                Read More <i class="bi bi-arrow-right ms-1"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
                
                <!-- Pagination -->
                @if($announcements->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $announcements->links() }}
                    </div>
                @endif
            @else
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-megaphone" style="font-size: 4rem; color: #dee2e6;"></i>
                        <h4 class="mt-3">No Announcements</h4>
                        <p class="text-muted">There are no announcements to display at this time.</p>
                        @if(auth()->user()->hasRole('organization'))
                            <a href="{{ route('announcements.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-1"></i>Create First Announcement
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.announcement-card {
    border: 1px solid #dee2e6;
    transition: all 0.3s ease;
}

.announcement-card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.announcement-pinned {
    border-left: 4px solid #ffc107;
    background-color: #fffbf0;
}

.announcement-featured {
    border-left: 4px solid #28a745;
    background-color: #f8fff9;
}

.announcement-icon {
    flex-shrink: 0;
}

.announcement-content {
    line-height: 1.6;
}

.announcement-stats .btn {
    border: none;
    background: none;
    color: #6c757d;
}

.announcement-stats .btn:hover {
    color: #007bff;
}

.announcement-actions .btn {
    opacity: 0.7;
}

.announcement-actions .btn:hover {
    opacity: 1;
}
</style>
@endpush

@push('scripts')
<script>
function toggleLike(announcementId) {
    fetch(`/announcements/${announcementId}/like`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const btn = document.getElementById(`like-btn-${announcementId}`);
            const icon = btn.querySelector('i');
            const count = document.getElementById(`like-count-${announcementId}`);
            
            if (data.liked) {
                icon.className = 'bi bi-heart-fill text-danger';
            } else {
                icon.className = 'bi bi-heart';
            }
            
            count.textContent = data.likes_count;
        }
    })
    .catch(error => console.error('Error:', error));
}

function pinAnnouncement(announcementId) {
    fetch(`/announcements/${announcementId}/pin`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}

function unpinAnnouncement(announcementId) {
    fetch(`/announcements/${announcementId}/unpin`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}
</script>
@endpush
