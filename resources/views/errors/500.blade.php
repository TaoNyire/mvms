@extends('layouts.organization')

@section('title', 'Server Error - MVMS')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-exclamation-triangle display-1 text-danger mb-3"></i>
                    <h2 class="mb-3">Server Error</h2>
                    <p class="text-muted mb-4">Something went wrong on our end. Please try again later.</p>
                    
                    @if(isset($error) && config('app.debug'))
                        <div class="alert alert-danger text-start">
                            <strong>Debug Information:</strong><br>
                            {{ $error }}
                        </div>
                    @endif
                    
                    <div class="d-flex justify-content-center gap-2">
                        <a href="{{ route('organization.dashboard') }}" class="btn btn-primary">
                            <i class="bi bi-house me-2"></i>Go to Dashboard
                        </a>
                        <button onclick="history.back()" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Go Back
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
