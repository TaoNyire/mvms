@extends('layouts.admin')

@section('title', 'System Logs - Admin Panel')

@section('page-title', 'System Logs')

@section('breadcrumb')
    <li class="breadcrumb-item active">Logs</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-file-text me-2"></i>System Logs
                    </h5>
                </div>
                <div class="card-body text-center py-5">
                    <i class="bi bi-file-text" style="font-size: 4rem; color: #dee2e6;"></i>
                    <h4 class="mt-3">System Logs</h4>
                    <p class="text-muted">System logging and audit trail functionality will be implemented here.</p>
                    <p class="text-muted">This will include:</p>
                    <ul class="list-unstyled text-muted">
                        <li>• Application logs</li>
                        <li>• User activity logs</li>
                        <li>• Admin action logs</li>
                        <li>• Error logs</li>
                        <li>• Security logs</li>
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
