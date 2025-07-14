@extends('layouts.' . (auth()->user()->hasRole('volunteer') ? 'volunteer' : 'organization'))

@section('title', 'Messages - MVMS')

@section('page-title', 'Messages')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>
                <i class="bi bi-chat-dots me-2"></i>Messages
            </h2>
            <p>Communicate with volunteers and organizations.</p>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="{{ route('messages.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>New Message
            </a>
        </div>
    </div>

    <!-- Conversations List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-0">
                    @if($conversations->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($conversations as $conversation)
                                <a href="{{ route('messages.show', $conversation) }}" 
                                   class="list-group-item list-group-item-action conversation-item {{ $conversation->unread_count > 0 ? 'conversation-unread' : '' }}">
                                    <div class="d-flex align-items-start">
                                        <!-- Avatar -->
                                        <div class="conversation-avatar me-3">
                                            <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white" 
                                                 style="width: 50px; height: 50px;">
                                                @if($conversation->type === 'direct')
                                                    @php
                                                        $otherParticipant = $conversation->participants()->where('id', '!=', auth()->id())->first();
                                                    @endphp
                                                    @if($otherParticipant)
                                                        {{ strtoupper(substr($otherParticipant->name, 0, 2)) }}
                                                    @else
                                                        <i class="bi bi-person"></i>
                                                    @endif
                                                @else
                                                    <i class="bi bi-people"></i>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <!-- Conversation Content -->
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-start mb-1">
                                                <h6 class="mb-0 fw-bold">
                                                    @if($conversation->type === 'direct')
                                                        @php
                                                            $otherParticipant = $conversation->participants()->where('id', '!=', auth()->id())->first();
                                                        @endphp
                                                        {{ $otherParticipant ? $otherParticipant->name : 'Unknown User' }}
                                                    @else
                                                        {{ $conversation->title ?? 'Group Conversation' }}
                                                    @endif
                                                </h6>
                                                <div class="d-flex align-items-center">
                                                    @if($conversation->unread_count > 0)
                                                        <span class="badge bg-primary me-2">{{ $conversation->unread_count }}</span>
                                                    @endif
                                                    <small class="text-muted">
                                                        {{ $conversation->last_activity_at ? $conversation->last_activity_at->diffForHumans() : '' }}
                                                    </small>
                                                </div>
                                            </div>
                                            
                                            <!-- Last Message Preview -->
                                            @if($conversation->lastMessage)
                                                <p class="mb-0 text-muted">
                                                    @if($conversation->lastMessage->sender_id === auth()->id())
                                                        <span class="fw-bold">You:</span>
                                                    @else
                                                        <span class="fw-bold">{{ $conversation->lastMessage->sender->name }}:</span>
                                                    @endif
                                                    {{ $conversation->last_message_preview }}
                                                </p>
                                            @else
                                                <p class="mb-0 text-muted fst-italic">No messages yet</p>
                                            @endif
                                        </div>
                                        
                                        <!-- Unread Indicator -->
                                        @if($conversation->unread_count > 0)
                                            <div class="conversation-unread-dot"></div>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>
                        
                        <!-- Pagination -->
                        <div class="d-flex justify-content-center p-3">
                            {{ $conversations->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-chat-dots" style="font-size: 4rem; color: #dee2e6;"></i>
                            <h4 class="mt-3">No Conversations</h4>
                            <p class="text-muted">Start a conversation to communicate with others.</p>
                            <a href="{{ route('messages.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-1"></i>Start New Conversation
                            </a>
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
.conversation-item {
    border: none !important;
    border-bottom: 1px solid #dee2e6 !important;
    padding: 1rem;
    transition: all 0.3s ease;
    position: relative;
    text-decoration: none;
    color: inherit;
}

.conversation-item:hover {
    background-color: #f8f9fa;
    color: inherit;
    text-decoration: none;
}

.conversation-unread {
    background-color: #f0f8ff;
    border-left: 4px solid #007bff !important;
}

.conversation-unread-dot {
    width: 8px;
    height: 8px;
    background-color: #007bff;
    border-radius: 50%;
    position: absolute;
    top: 1rem;
    right: 1rem;
}

.conversation-avatar {
    flex-shrink: 0;
}

.conversation-item:last-child {
    border-bottom: none !important;
}
</style>
@endpush

@push('scripts')
<script>
// Auto-refresh conversations every 30 seconds
setInterval(function() {
    // You could implement real-time updates here
    // For now, we'll just refresh the page if there are new messages
}, 30000);

// Mark conversation as read when clicked
document.querySelectorAll('.conversation-item').forEach(item => {
    item.addEventListener('click', function() {
        const unreadDot = this.querySelector('.conversation-unread-dot');
        const unreadBadge = this.querySelector('.badge');
        
        if (unreadDot) {
            unreadDot.style.display = 'none';
        }
        if (unreadBadge) {
            unreadBadge.style.display = 'none';
        }
        
        this.classList.remove('conversation-unread');
    });
});
</script>
@endpush
