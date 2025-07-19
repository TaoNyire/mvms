@extends('layouts.' . (auth()->user()->hasRole('volunteer') ? 'volunteer' : 'organization'))

@section('title', 'Announcement - MVMS')

@section('page-title', 'Announcements')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">{{ $announcement->title }}</h5>
                        <small class="text-muted">
                            By {{ $announcement->author->name }} • 
                            {{ $announcement->created_at->format('M d, Y g:i A') }}
                        </small>
                    </div>
                    <div>
                        @if(auth()->user()->hasRole('organization') && $announcement->author_id === auth()->id())
                            <div class="btn-group btn-group-sm me-2">
                                @if($announcement->is_pinned)
                                    <form action="{{ route('announcements.unpin', $announcement) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-warning btn-sm">
                                            <i class="bi bi-pin-angle-fill"></i> Unpin
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('announcements.pin', $announcement) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-pin-angle"></i> Pin
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @endif
                        <a href="{{ route('announcements.index') }}" class="btn btn-secondary btn-sm">
                            <i class="bi bi-arrow-left me-1"></i>Back to Announcements
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($announcement->is_pinned)
                        <div class="alert alert-info mb-3">
                            <i class="bi bi-pin-angle-fill me-2"></i>This announcement is pinned
                        </div>
                    @endif
                    
                    @if($announcement->priority === 'high')
                        <div class="alert alert-warning mb-3">
                            <i class="bi bi-exclamation-triangle me-2"></i>High Priority Announcement
                        </div>
                    @endif

                    @if($announcement->audience === 'my_volunteers')
                        <div class="alert alert-primary mb-3">
                            <i class="bi bi-people-fill me-2"></i>
                            @if(auth()->user()->hasRole('volunteer'))
                                This announcement is specifically for you as a volunteer with {{ $announcement->creator->organizationProfile->org_name ?? 'this organization' }}.
                            @else
                                This announcement is only visible to your organization's volunteers.
                            @endif
                        </div>
                    @endif

                    <div class="announcement-content">
                        {!! nl2br(e($announcement->content)) !!}
                    </div>

                    <div class="mt-3">
                        @if($announcement->category)
                            <span class="badge bg-secondary">{{ $announcement->category }}</span>
                        @endif

                        <span class="badge bg-info">
                            <i class="bi bi-people me-1"></i>
                            @if($announcement->audience === 'all')
                                All Users
                            @elseif($announcement->audience === 'volunteers')
                                All Volunteers
                            @elseif($announcement->audience === 'my_volunteers')
                                Organization's Volunteers
                            @elseif($announcement->audience === 'organizations')
                                Organizations Only
                            @elseif($announcement->audience === 'admins')
                                Administrators Only
                            @endif
                        </span>
                    </div>
                    
                    @if($announcement->expires_at)
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="bi bi-clock me-1"></i>
                                Expires: {{ $announcement->expires_at->format('M d, Y g:i A') }}
                            </small>
                        </div>
                    @endif
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <form action="{{ route('announcements.like', $announcement) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-link p-0 text-decoration-none">
                                    <i class="bi bi-heart{{ $announcement->isLikedBy(auth()->user()) ? '-fill text-danger' : '' }}"></i>
                                    {{ $announcement->likes_count ?? 0 }} 
                                    {{ ($announcement->likes_count ?? 0) === 1 ? 'Like' : 'Likes' }}
                                </button>
                            </form>
                        </div>
                        <div>
                            <small class="text-muted">
                                <i class="bi bi-eye me-1"></i>{{ $announcement->views_count ?? 0 }} views
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Related Announcements -->
            @if(isset($related_announcements) && $related_announcements->count() > 0)
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="mb-0">Related Announcements</h6>
                    </div>
                    <div class="card-body">
                        @foreach($related_announcements as $related)
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <div>
                                    <h6 class="mb-1">
                                        <a href="{{ route('announcements.show', $related) }}" class="text-decoration-none">
                                            {{ $related->title }}
                                        </a>
                                    </h6>
                                    <small class="text-muted">
                                        {{ $related->created_at->format('M d, Y') }} • {{ $related->author->name }}
                                    </small>
                                </div>
                                @if($related->is_pinned)
                                    <i class="bi bi-pin-angle-fill text-primary"></i>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-refresh likes count every 30 seconds
    setInterval(function() {
        fetch(`/announcements/{{ $announcement->id }}/stats`)
            .then(response => response.json())
            .then(data => {
                if (data.likes_count !== undefined) {
                    const likesElement = document.querySelector('.btn-link');
                    if (likesElement) {
                        const likesText = data.likes_count === 1 ? 'Like' : 'Likes';
                        likesElement.innerHTML = `<i class="bi bi-heart${data.user_liked ? '-fill text-danger' : ''}"></i> ${data.likes_count} ${likesText}`;
                    }
                }
            })
            .catch(error => console.log('Error updating stats:', error));
    }, 30000);
});
</script>
@endpush
@endsection
