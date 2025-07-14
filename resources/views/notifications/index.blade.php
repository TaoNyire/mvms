@extends('layouts.' . (auth()->user()->hasRole('volunteer') ? 'volunteer' : 'organization'))

@section('title', 'Notifications - MVMS')

@section('page-title', 'Notifications')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>
                <i class="bi bi-bell me-2"></i>Notifications
            </h2>
            <p>Stay updated with your volunteer activities and communications.</p>
        </div>
        <div class="col-md-4 text-md-end">
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-primary" onclick="markAllAsRead()">
                    <i class="bi bi-check-all me-1"></i>Mark All Read
                </button>
                <a href="{{ route('notifications.preferences') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-gear me-1"></i>Settings
                </a>
            </div>
        </div>
    </div>

    <!-- Notification Tabs -->
    <div class="row mb-3">
        <div class="col-12">
            <ul class="nav nav-tabs" id="notificationTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab">
                        All <span class="badge bg-secondary ms-1">{{ $counts['all'] }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="unread-tab" data-bs-toggle="tab" data-bs-target="#unread" type="button" role="tab">
                        Unread <span class="badge bg-primary ms-1">{{ $counts['unread'] }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="read-tab" data-bs-toggle="tab" data-bs-target="#read" type="button" role="tab">
                        Read <span class="badge bg-success ms-1">{{ $counts['read'] }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="archived-tab" data-bs-toggle="tab" data-bs-target="#archived" type="button" role="tab">
                        Archived <span class="badge bg-info ms-1">{{ $counts['archived'] }}</span>
                    </button>
                </li>
            </ul>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-0">
                    @if($notifications->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($notifications as $notification)
                                <div class="list-group-item notification-item {{ $notification->is_read ? '' : 'notification-unread' }}" 
                                     data-notification-id="{{ $notification->id }}">
                                    <div class="d-flex align-items-start">
                                        <!-- Notification Icon -->
                                        <div class="notification-icon me-3">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center" 
                                                 style="width: 40px; height: 40px; background-color: {{ $notification->color }}20; color: {{ $notification->color }};">
                                                <i class="{{ $notification->icon ?? 'bi-info-circle' }}"></i>
                                            </div>
                                        </div>
                                        
                                        <!-- Notification Content -->
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-start mb-1">
                                                <h6 class="mb-0 fw-bold">{{ $notification->title }}</h6>
                                                <div class="d-flex align-items-center">
                                                    @if($notification->priority === 'urgent')
                                                        <span class="badge bg-danger me-2">Urgent</span>
                                                    @elseif($notification->priority === 'high')
                                                        <span class="badge bg-warning me-2">High</span>
                                                    @endif
                                                    <small class="text-muted">{{ $notification->time_ago }}</small>
                                                </div>
                                            </div>
                                            <p class="mb-2 text-muted">{{ $notification->message }}</p>
                                            
                                            <!-- Action Buttons -->
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    @if($notification->action_url)
                                                        <a href="{{ $notification->action_url }}" class="btn btn-sm btn-outline-primary">
                                                            {{ $notification->action_text ?? 'View' }}
                                                        </a>
                                                    @endif
                                                </div>
                                                <div class="btn-group" role="group">
                                                    @if(!$notification->is_read)
                                                        <button type="button" class="btn btn-sm btn-outline-success" 
                                                                onclick="markAsRead({{ $notification->id }})" title="Mark as Read">
                                                            <i class="bi bi-check"></i>
                                                        </button>
                                                    @else
                                                        <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                                onclick="markAsUnread({{ $notification->id }})" title="Mark as Unread">
                                                            <i class="bi bi-arrow-counterclockwise"></i>
                                                        </button>
                                                    @endif
                                                    
                                                    @if(!$notification->is_archived)
                                                        <button type="button" class="btn btn-sm btn-outline-info" 
                                                                onclick="archiveNotification({{ $notification->id }})" title="Archive">
                                                            <i class="bi bi-archive"></i>
                                                        </button>
                                                    @else
                                                        <button type="button" class="btn btn-sm btn-outline-warning" 
                                                                onclick="unarchiveNotification({{ $notification->id }})" title="Unarchive">
                                                            <i class="bi bi-arrow-up-square"></i>
                                                        </button>
                                                    @endif
                                                    
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            onclick="deleteNotification({{ $notification->id }})" title="Delete">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Unread Indicator -->
                                        @if(!$notification->is_read)
                                            <div class="notification-unread-dot"></div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <!-- Pagination -->
                        <div class="d-flex justify-content-center p-3">
                            {{ $notifications->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-bell-slash" style="font-size: 4rem; color: #dee2e6;"></i>
                            <h4 class="mt-3">No Notifications</h4>
                            <p class="text-muted">You're all caught up! No notifications to show.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.notification-item {
    border: none !important;
    border-bottom: 1px solid #dee2e6 !important;
    padding: 1rem;
    transition: all 0.3s ease;
    position: relative;
}

.notification-item:hover {
    background-color: #f8f9fa;
}

.notification-unread {
    background-color: #f0f8ff;
    border-left: 4px solid #007bff !important;
}

.notification-unread-dot {
    width: 8px;
    height: 8px;
    background-color: #007bff;
    border-radius: 50%;
    position: absolute;
    top: 1rem;
    right: 1rem;
}

.notification-icon {
    flex-shrink: 0;
}

.nav-tabs .nav-link {
    border: none;
    border-bottom: 2px solid transparent;
    color: #6c757d;
}

.nav-tabs .nav-link.active {
    border-bottom-color: #007bff;
    color: #007bff;
    background: none;
}

.nav-tabs .nav-link:hover {
    border-bottom-color: #007bff;
    color: #007bff;
}
</style>
@endpush

@push('scripts')
<script>
function markAsRead(notificationId) {
    fetch(`/notifications/${notificationId}/read`, {
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

function markAsUnread(notificationId) {
    fetch(`/notifications/${notificationId}/unread`, {
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

function archiveNotification(notificationId) {
    fetch(`/notifications/${notificationId}/archive`, {
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

function unarchiveNotification(notificationId) {
    fetch(`/notifications/${notificationId}/unarchive`, {
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

function deleteNotification(notificationId) {
    if (confirm('Are you sure you want to delete this notification?')) {
        fetch(`/notifications/${notificationId}`, {
            method: 'DELETE',
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
}

function markAllAsRead() {
    fetch('/notifications/mark-all-read', {
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

// Tab functionality
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');
    
    if (status) {
        const tab = document.getElementById(`${status}-tab`);
        if (tab) {
            tab.click();
        }
    }
    
    // Update URL when tab changes
    document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tab => {
        tab.addEventListener('shown.bs.tab', function(e) {
            const target = e.target.getAttribute('data-bs-target').substring(1);
            if (target !== 'all') {
                const url = new URL(window.location);
                url.searchParams.set('status', target);
                window.history.pushState({}, '', url);
            } else {
                const url = new URL(window.location);
                url.searchParams.delete('status');
                window.history.pushState({}, '', url);
            }
        });
    });
});
</script>
@endpush
