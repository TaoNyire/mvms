@extends('layouts.' . (auth()->user()->hasRole('volunteer') ? 'volunteer' : 'organization'))

@section('title', 'Conversation - MVMS')

@section('page-title', 'Messages')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">
                            <i class="bi bi-chat-dots me-2"></i>Conversation
                        </h5>
                        <small class="text-muted">
                            Participants: 
                            @foreach($participants as $participant)
                                {{ $participant->name }}{{ !$loop->last ? ', ' : '' }}
                            @endforeach
                        </small>
                    </div>
                    <a href="{{ route('messages.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i>Back to Messages
                    </a>
                </div>
                <div class="card-body p-0">
                    <!-- Messages Container -->
                    <div class="messages-container" style="height: 400px; overflow-y: auto; padding: 1rem;">
                        @if($messages->count() > 0)
                            @foreach($messages as $message)
                                <div class="message-item mb-3 {{ $message->sender_id === auth()->id() ? 'text-end' : '' }}">
                                    <div class="d-inline-block {{ $message->sender_id === auth()->id() ? 'bg-primary text-white' : 'bg-light' }} rounded p-3" style="max-width: 70%;">
                                        <div class="message-content">
                                            {{ $message->content }}
                                        </div>
                                        <div class="message-meta mt-2">
                                            <small class="{{ $message->sender_id === auth()->id() ? 'text-white-50' : 'text-muted' }}">
                                                {{ $message->sender->name }} • {{ $message->created_at->format('M d, Y g:i A') }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center py-4">
                                <i class="bi bi-chat-dots display-4 text-muted"></i>
                                <h6 class="mt-3 text-muted">No messages yet</h6>
                                <p class="text-muted">Start the conversation by sending a message below.</p>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Message Input -->
                    <div class="border-top p-3">
                        <form action="{{ route('messages.send', $conversation) }}" method="POST" id="messageForm">
                            @csrf
                            <div class="input-group">
                                <textarea class="form-control" name="content" id="messageContent" 
                                         placeholder="Type your message..." rows="2" required></textarea>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-send"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-scroll to bottom of messages
    const messagesContainer = document.querySelector('.messages-container');
    if (messagesContainer) {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
    
    // Handle form submission
    document.getElementById('messageForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const messageContent = document.getElementById('messageContent');
        
        if (!messageContent.value.trim()) {
            return;
        }
        
        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Add message to conversation
                const messagesContainer = document.querySelector('.messages-container');
                const messageHtml = `
                    <div class="message-item mb-3 text-end">
                        <div class="d-inline-block bg-primary text-white rounded p-3" style="max-width: 70%;">
                            <div class="message-content">
                                ${messageContent.value}
                            </div>
                            <div class="message-meta mt-2">
                                <small class="text-white-50">
                                    {{ auth()->user()->name }} • Just now
                                </small>
                            </div>
                        </div>
                    </div>
                `;
                messagesContainer.insertAdjacentHTML('beforeend', messageHtml);
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
                
                // Clear input
                messageContent.value = '';
            } else {
                alert('Error sending message');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error sending message');
        });
    });
    
    // Handle Enter key (Shift+Enter for new line, Enter to send)
    document.getElementById('messageContent').addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            document.getElementById('messageForm').dispatchEvent(new Event('submit'));
        }
    });
});
</script>
@endpush
@endsection
