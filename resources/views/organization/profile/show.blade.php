@extends('layouts.organization')

@section('title', 'Organization Profile - MVMS')

@section('page-title', 'Organization Profile')

@section('content')
<div class="container-fluid">
    <!-- Profile Status Alert -->
    @if($profile->status === 'pending')
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <h6><i class="bi bi-clock me-2"></i>Profile Under Review</h6>
            <p class="mb-0">Your organization profile is currently under review by our administrators. You will be notified once your profile is approved and you can access the full dashboard.</p>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @elseif($profile->status === 'rejected')
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h6><i class="bi bi-x-circle me-2"></i>Profile Rejected</h6>
            <p class="mb-0">Your organization profile has been rejected. Please contact support for more information or to resubmit your application.</p>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @elseif($profile->status === 'approved')
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <h6><i class="bi bi-check-circle me-2"></i>Profile Approved</h6>
            <p class="mb-0">Your organization profile has been approved! You can now access the full dashboard.</p>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Profile Information -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-building me-2"></i>Organization Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Organization Name:</strong></td>
                                    <td>{{ $profile->org_name ?? 'Not provided' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Type:</strong></td>
                                    <td>{{ $profile->org_type ?? 'Not specified' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Sector:</strong></td>
                                    <td>{{ $profile->sector ?? 'Not specified' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Registration Number:</strong></td>
                                    <td>{{ $profile->registration_number ?? 'Not provided' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>{{ $profile->email ?? 'Not provided' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Phone:</strong></td>
                                    <td>{{ $profile->phone ?? 'Not provided' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>District:</strong></td>
                                    <td>{{ $profile->district ?? 'Not provided' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Region:</strong></td>
                                    <td>{{ $profile->region ?? 'Not provided' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    @if($profile->description)
                        <div class="mt-3">
                            <h6>Description</h6>
                            <p>{{ $profile->description }}</p>
                        </div>
                    @endif
                    
                    @if($profile->mission)
                        <div class="mt-3">
                            <h6>Mission</h6>
                            <p>{{ $profile->mission }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle me-2"></i>Profile Status
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <div class="mb-3">
                            <span class="badge bg-{{ 
                                $profile->status === 'approved' ? 'success' : 
                                ($profile->status === 'rejected' ? 'danger' : 'warning') 
                            }} fs-6">
                                {{ ucfirst($profile->status) }}
                            </span>
                        </div>
                        
                        <div class="progress mb-3" style="height: 20px;">
                            <div class="progress-bar" role="progressbar" 
                                 style="width: {{ $profile->completion_percentage }}%">
                                {{ $profile->completion_percentage }}% Complete
                            </div>
                        </div>
                        
                        @if($profile->status === 'approved')
                            <a href="{{ route('organization.dashboard') }}" class="btn btn-primary">
                                <i class="bi bi-speedometer2 me-1"></i>Go to Dashboard
                            </a>
                        @else
                            <p class="text-muted">
                                <small>Please wait for admin approval to access the dashboard.</small>
                            </p>
                        @endif
                    </div>
                </div>
            </div>
            
            @if($profile->contact_person_name)
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-person me-2"></i>Contact Person
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-1"><strong>{{ $profile->contact_person_name }}</strong></p>
                        @if($profile->contact_person_title)
                            <p class="mb-1 text-muted">{{ $profile->contact_person_title }}</p>
                        @endif
                        @if($profile->contact_person_email)
                            <p class="mb-1">{{ $profile->contact_person_email }}</p>
                        @endif
                        @if($profile->contact_person_phone)
                            <p class="mb-0">{{ $profile->contact_person_phone }}</p>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
