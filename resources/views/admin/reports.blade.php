@extends('layouts.admin')

@section('title', 'Reports & Analytics - Admin Panel')

@section('page-title', 'Reports & Analytics')

@section('breadcrumb')
    <li class="breadcrumb-item active">Reports</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-graph-up me-2"></i>Reports & Analytics
                    </h5>
                </div>
                <div class="card-body text-center py-5">
                    <i class="bi bi-graph-up" style="font-size: 4rem; color: #dee2e6;"></i>
                    <h4 class="mt-3">Reports & Analytics</h4>
                    <p class="text-muted">Comprehensive reporting and analytics functionality will be implemented here.</p>
                    <p class="text-muted">This will include:</p>
                    <ul class="list-unstyled text-muted">
                        <li>• User registration trends</li>
                        <li>• Organization approval statistics</li>
                        <li>• Volunteer activity reports</li>
                        <li>• System usage analytics</li>
                        <li>• Performance metrics</li>
                    </ul>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">
                        <i class="bi bi-arrow-left me-1"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
