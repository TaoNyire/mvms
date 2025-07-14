@extends('layouts.volunteer')

@section('title', 'My Profile - MVMS')

@section('page-title', 'My Volunteer Profile')

@section('content')
<div class="container-fluid">
    <!-- Profile Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 bg-gradient" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                <div class="card-body text-white p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-2">
                                <i class="bi bi-person-check me-2"></i>{{ $profile->full_name ?? Auth::user()->name }}
                            </h2>
                            <p class="mb-0 opacity-90">
                                <i class="bi bi-shield-check me-2"></i>
                                Profile Status: 
                                @if($profile->is_complete)
                                    <span class="badge bg-light text-success">Complete</span>
                                @else
                                    <span class="badge bg-warning text-dark">{{ $profile->completion_percentage }}% Complete</span>
                                @endif
                            </p>
                            @if($profile->is_verified)
                                <p class="mb-0 opacity-90">
                                    <i class="bi bi-patch-check me-2"></i>Verified Volunteer
                                </p>
                            @endif
                        </div>
                        <div class="col-md-4 text-md-end">
                            <a href="{{ route('volunteer.profile.edit') }}" class="btn btn-light">
                                <i class="bi bi-pencil me-1"></i>Edit Profile
                            </a>
                            <a href="{{ route('volunteer.dashboard') }}" class="btn btn-outline-light ms-2">
                                <i class="bi bi-arrow-left me-1"></i>Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(!$profile->is_complete)
    <!-- Completion Warning -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-warning">
                <h5 class="alert-heading">
                    <i class="bi bi-exclamation-triangle me-2"></i>Profile Incomplete
                </h5>
                <p class="mb-2">Your profile is {{ $profile->completion_percentage }}% complete. Complete your profile to access all volunteer opportunities.</p>
                <a href="{{ route('volunteer.profile.create') }}" class="btn btn-warning">
                    <i class="bi bi-plus-circle me-1"></i>Complete Profile
                </a>
            </div>
        </div>
    </div>
    @endif

    <!-- Profile Information -->
    <div class="row">
        <!-- Basic Information -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-person me-2"></i>Basic Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-6 mb-3">
                            <strong>Full Name:</strong><br>
                            <span>{{ $profile->full_name ?? 'Not provided' }}</span>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <strong>Date of Birth:</strong><br>
                            <span>{{ $profile->date_of_birth ? $profile->date_of_birth->format('F j, Y') : 'Not provided' }}</span>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <strong>Gender:</strong><br>
                            <span>{{ $profile->gender ? ucfirst(str_replace('_', ' ', $profile->gender)) : 'Not provided' }}</span>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <strong>Phone:</strong><br>
                            <span>{{ $profile->phone ?? 'Not provided' }}</span>
                        </div>
                        <div class="col-12 mb-3">
                            <strong>About:</strong><br>
                            <span>{{ $profile->bio ?? 'Not provided' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Location Information -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-geo-alt me-2"></i>Location Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <strong>Address:</strong><br>
                            <span>{{ $profile->physical_address ?? 'Not provided' }}</span>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <strong>District:</strong><br>
                            <span>{{ $profile->district ?? 'Not provided' }}</span>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <strong>Region:</strong><br>
                            <span>{{ $profile->region ?? 'Not provided' }}</span>
                        </div>
                        <div class="col-12 mb-3">
                            <strong>Emergency Contact:</strong><br>
                            <span>
                                {{ $profile->emergency_contact_name ?? 'Not provided' }}
                                @if($profile->emergency_contact_phone)
                                    <br><small>{{ $profile->emergency_contact_phone }}</small>
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Skills and Education -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-award me-2"></i>Skills & Education
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Skills:</strong><br>
                        @if($profile->skills && count($profile->skills) > 0)
                            @foreach($profile->skills as $skill)
                                <span class="badge bg-primary me-1 mb-1">{{ $skill }}</span>
                            @endforeach
                        @else
                            <span>No skills listed</span>
                        @endif
                    </div>
                    <div class="mb-3">
                        <strong>Education Level:</strong><br>
                        <span>{{ $profile->education_level ?? 'Not provided' }}</span>
                    </div>
                    @if($profile->field_of_study)
                    <div class="mb-3">
                        <strong>Field of Study:</strong><br>
                        <span>{{ $profile->field_of_study }}</span>
                    </div>
                    @endif
                    @if($profile->experience_description)
                    <div class="mb-3">
                        <strong>Experience:</strong><br>
                        <span>{{ $profile->experience_description }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Availability -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-calendar-event me-2"></i>Availability
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Available Days:</strong><br>
                        @if($profile->available_days && count($profile->available_days) > 0)
                            @foreach($profile->available_days as $day)
                                <span class="badge bg-success me-1 mb-1">{{ ucfirst($day) }}</span>
                            @endforeach
                        @else
                            <span>Not specified</span>
                        @endif
                    </div>
                    <div class="mb-3">
                        <strong>Availability Type:</strong><br>
                        <span>{{ $profile->availability_type ? ucfirst(str_replace('_', ' ', $profile->availability_type)) : 'Not specified' }}</span>
                    </div>
                    @if($profile->available_time_start && $profile->available_time_end)
                    <div class="mb-3">
                        <strong>Time Range:</strong><br>
                        <span>{{ $profile->available_time_start }} - {{ $profile->available_time_end }}</span>
                    </div>
                    @endif
                    <div class="mb-3">
                        <strong>Can Travel:</strong><br>
                        <span>
                            {{ $profile->can_travel ? 'Yes' : 'No' }}
                            @if($profile->can_travel && $profile->max_travel_distance)
                                (up to {{ $profile->max_travel_distance }} km)
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Documents -->
        @if($profile->cv_path || $profile->id_document_path || ($profile->certificates && count($profile->certificates) > 0))
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-file-earmark-text me-2"></i>Documents
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @if($profile->cv_path)
                        <div class="col-md-4 mb-3">
                            <div class="border rounded p-3 text-center">
                                <i class="bi bi-file-earmark-pdf text-danger" style="font-size: 2rem;"></i>
                                <h6 class="mt-2">CV/Resume</h6>
                                <a href="{{ $profile->cv_url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-download me-1"></i>Download
                                </a>
                            </div>
                        </div>
                        @endif
                        @if($profile->id_document_path)
                        <div class="col-md-4 mb-3">
                            <div class="border rounded p-3 text-center">
                                <i class="bi bi-file-earmark-image text-info" style="font-size: 2rem;"></i>
                                <h6 class="mt-2">ID Document</h6>
                                <a href="{{ $profile->id_document_url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-download me-1"></i>Download
                                </a>
                            </div>
                        </div>
                        @endif
                        @if($profile->certificates && count($profile->certificates) > 0)
                        <div class="col-md-4 mb-3">
                            <div class="border rounded p-3 text-center">
                                <i class="bi bi-award text-warning" style="font-size: 2rem;"></i>
                                <h6 class="mt-2">Certificates</h6>
                                <small>{{ count($profile->certificates) }} file(s)</small>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Motivation -->
        @if($profile->motivation)
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-heart me-2"></i>Motivation
                    </h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $profile->motivation }}</p>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
