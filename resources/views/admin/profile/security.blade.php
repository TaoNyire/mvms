@extends('layouts.admin')

@section('title', 'Security Settings - Admin Profile')

@section('page-title', 'Security Settings')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Security Overview -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="bi bi-shield-check me-2"></i>Security Status
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="bg-success bg-opacity-10 rounded-circle p-3 d-inline-flex mb-3">
                            <i class="bi bi-shield-check text-success" style="font-size: 2rem;"></i>
                        </div>
                        <h6 class="text-success">Account Secure</h6>
                        <p class="text-muted small">Your account security is up to date</p>
                    </div>

                    <div class="security-metrics">
                        <div class="d-flex justify-content-between align-items-center mb-3 p-2 bg-light rounded">
                            <span class="text-muted">Password Strength</span>
                            <span class="badge bg-success">Strong</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3 p-2 bg-light rounded">
                            <span class="text-muted">Two-Factor Auth</span>
                            <span class="badge bg-warning">Disabled</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3 p-2 bg-light rounded">
                            <span class="text-muted">Active Sessions</span>
                            <span class="badge bg-info">{{ count($security_info['active_sessions']) }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3 p-2 bg-light rounded">
                            <span class="text-muted">Recent Logins</span>
                            <span class="badge bg-primary">{{ count($security_info['recent_logins']) }}</span>
                        </div>
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <a href="{{ route('admin.profile.change-password') }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-key me-1"></i>Change Password
                        </a>
                        <a href="{{ route('admin.profile.show') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left me-1"></i>Back to Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Login Activity -->
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history me-2"></i>Recent Login Activity
                    </h5>
                </div>
                <div class="card-body">
                    @if(count($security_info['recent_logins']) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date & Time</th>
                                        <th>IP Address</th>
                                        <th>Location</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($security_info['recent_logins'] as $login)
                                        <tr>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span>{{ $login['timestamp']->format('M j, Y') }}</span>
                                                    <small class="text-muted">{{ $login['timestamp']->format('g:i A') }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <code class="bg-light px-2 py-1 rounded">{{ $login['ip'] }}</code>
                                            </td>
                                            <td>
                                                <i class="bi bi-geo-alt me-1 text-muted"></i>{{ $login['location'] }}
                                            </td>
                                            <td>
                                                <span class="badge bg-success">Successful</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-clock-history text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-2">No recent login activity</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Active Sessions -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="bi bi-laptop me-2"></i>Active Sessions
                    </h5>
                </div>
                <div class="card-body">
                    @if(count($security_info['active_sessions']) > 0)
                        @foreach($security_info['active_sessions'] as $session)
                            <div class="d-flex justify-content-between align-items-center p-3 border rounded mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                                        <i class="bi bi-laptop text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">{{ $session['device'] }}</h6>
                                        <div class="text-muted small">
                                            <i class="bi bi-geo-alt me-1"></i>{{ $session['ip'] }}
                                            <span class="mx-2">•</span>
                                            <i class="bi bi-clock me-1"></i>Last active {{ $session['last_activity']->diffForHumans() }}
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <span class="badge bg-success">Current Session</span>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-laptop text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-2">No active sessions</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Security Events -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="bi bi-shield-exclamation me-2"></i>Security Events
                    </h5>
                </div>
                <div class="card-body">
                    @if(count($security_info['security_events']) > 0)
                        <div class="timeline">
                            @foreach($security_info['security_events'] as $event)
                                <div class="timeline-item d-flex mb-3">
                                    <div class="timeline-marker bg-info rounded-circle me-3" style="width: 12px; height: 12px; margin-top: 6px;"></div>
                                    <div class="timeline-content">
                                        <p class="mb-1">
                                            <strong>{{ $event['event'] }}</strong>
                                        </p>
                                        <small class="text-muted">{{ $event['timestamp']->diffForHumans() }}</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-shield-exclamation text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-2">No security events</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Login Statistics -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="bi bi-graph-up me-2"></i>Login Statistics
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-4">
                        <div class="col-4">
                            <div class="border-end">
                                <h4 class="text-success mb-1">{{ $security_info['login_history']->sum('successful') }}</h4>
                                <small class="text-muted">Successful</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border-end">
                                <h4 class="text-danger mb-1">{{ $security_info['login_history']->sum('failed') }}</h4>
                                <small class="text-muted">Failed</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <h4 class="text-primary mb-1">{{ $security_info['login_history']->count() }}</h4>
                            <small class="text-muted">Days</small>
                        </div>
                    </div>

                    <div class="login-chart">
                        @foreach($security_info['login_history'] as $day)
                            <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
                                <span class="text-muted">{{ $day['date']->format('M j') }}</span>
                                <div>
                                    <span class="badge bg-success me-1">{{ $day['successful'] }}</span>
                                    @if($day['failed'] > 0)
                                        <span class="badge bg-danger">{{ $day['failed'] }}</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Security Recommendations -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="bi bi-lightbulb me-2"></i>Security Recommendations
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="d-flex">
                                <div class="bg-warning bg-opacity-10 rounded-circle p-2 me-3 flex-shrink-0">
                                    <i class="bi bi-shield-plus text-warning"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Enable Two-Factor Authentication</h6>
                                    <p class="text-muted small mb-2">Add an extra layer of security to your account</p>
                                    <button class="btn btn-outline-warning btn-sm" disabled>
                                        <i class="bi bi-shield-plus me-1"></i>Enable 2FA (Coming Soon)
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex">
                                <div class="bg-info bg-opacity-10 rounded-circle p-2 me-3 flex-shrink-0">
                                    <i class="bi bi-key text-info"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Regular Password Updates</h6>
                                    <p class="text-muted small mb-2">Change your password every 3-6 months</p>
                                    <a href="{{ route('admin.profile.change-password') }}" class="btn btn-outline-info btn-sm">
                                        <i class="bi bi-key me-1"></i>Change Password
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex">
                                <div class="bg-success bg-opacity-10 rounded-circle p-2 me-3 flex-shrink-0">
                                    <i class="bi bi-laptop text-success"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Monitor Active Sessions</h6>
                                    <p class="text-muted small mb-2">Regularly review your active sessions</p>
                                    <span class="badge bg-success">Currently Monitoring</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex">
                                <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3 flex-shrink-0">
                                    <i class="bi bi-bell text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Security Notifications</h6>
                                    <p class="text-muted small mb-2">Stay informed about security events</p>
                                    <span class="badge bg-primary">Enabled</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline-item {
    position: relative;
}

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: 5px;
    top: 18px;
    width: 2px;
    height: calc(100% + 12px);
    background-color: #e9ecef;
}
</style>
@endsection
