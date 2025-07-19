@extends('layouts.organization')

@section('title', 'Reports - MVMS')

@section('page-title', 'Reports & Analytics')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 position-relative overflow-hidden" style="background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); min-height: 120px;">
                <div class="card-body text-white p-4 position-relative">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center">
                                <div class="bg-white bg-opacity-20 rounded-circle p-3 me-3">
                                    <i class="bi bi-graph-up" style="font-size: 1.5rem;"></i>
                                </div>
                                <div>
                                    <h1 class="mb-1 fw-bold" style="font-size: 1.8rem;">Reports & Analytics</h1>
                                    <p class="mb-0 opacity-90">Generate detailed reports for your organization</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="text-center bg-white bg-opacity-10 rounded-3 p-3">
                                <div class="h5 fw-bold mb-1">{{ now()->format('M Y') }}</div>
                                <small class="opacity-75 text-uppercase fw-semibold">Current Period</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="bi bi-funnel me-2"></i>Report Filters
                    </h5>
                    <form id="reportFilters">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="month" class="form-label fw-semibold">Month</label>
                                <select class="form-select" id="month" name="month">
                                    @foreach($availableMonths as $month)
                                        <option value="{{ $month['value'] }}" {{ $month['value'] == $currentMonth ? 'selected' : '' }}>
                                            {{ $month['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="year" class="form-label fw-semibold">Year</label>
                                <select class="form-select" id="year" name="year">
                                    @foreach($availableYears as $year)
                                        <option value="{{ $year['value'] }}" {{ $year['value'] == $currentYear ? 'selected' : '' }}>
                                            {{ $year['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="reportType" class="form-label fw-semibold">Report Type</label>
                                <select class="form-select" id="reportType" name="reportType">
                                    <option value="volunteers">Recruited Volunteers</option>
                                    <option value="completed">Completed Tasks</option>
                                    <option value="failed">Failed Tasks</option>
                                    <option value="comprehensive">Comprehensive Report</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="button" class="btn btn-primary me-2" id="previewBtn">
                                    <i class="bi bi-eye me-1"></i>Preview
                                </button>
                                <button type="button" class="btn btn-success" id="generatePdfBtn">
                                    <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Preview -->
    <div class="row" id="reportPreview" style="display: none;">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-bar-chart me-2"></i>Report Preview
                    </h5>
                </div>
                <div class="card-body">
                    <div id="previewContent">
                        <!-- Preview content will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="bg-primary bg-opacity-10 rounded-circle p-3 d-inline-flex mb-3">
                        <i class="bi bi-people text-primary" style="font-size: 1.5rem;"></i>
                    </div>
                    <h5 class="fw-bold">Recruited Volunteers</h5>
                    <p class="text-muted mb-0">Monthly volunteer recruitment reports</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="bg-success bg-opacity-10 rounded-circle p-3 d-inline-flex mb-3">
                        <i class="bi bi-check-circle text-success" style="font-size: 1.5rem;"></i>
                    </div>
                    <h5 class="fw-bold">Completed Tasks</h5>
                    <p class="text-muted mb-0">Successfully completed task reports</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="bg-danger bg-opacity-10 rounded-circle p-3 d-inline-flex mb-3">
                        <i class="bi bi-x-circle text-danger" style="font-size: 1.5rem;"></i>
                    </div>
                    <h5 class="fw-bold">Failed Tasks</h5>
                    <p class="text-muted mb-0">Cancelled or unsuccessful task reports</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="bg-info bg-opacity-10 rounded-circle p-3 d-inline-flex mb-3">
                        <i class="bi bi-file-earmark-text text-info" style="font-size: 1.5rem;"></i>
                    </div>
                    <h5 class="fw-bold">Comprehensive</h5>
                    <p class="text-muted mb-0">Complete monthly activity reports</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Spinner -->
    <div class="text-center" id="loadingSpinner" style="display: none;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2 text-muted">Generating report...</p>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const previewBtn = document.getElementById('previewBtn');
    const generatePdfBtn = document.getElementById('generatePdfBtn');
    const reportPreview = document.getElementById('reportPreview');
    const previewContent = document.getElementById('previewContent');
    const loadingSpinner = document.getElementById('loadingSpinner');

    // Preview report
    previewBtn.addEventListener('click', function() {
        const month = document.getElementById('month').value;
        const year = document.getElementById('year').value;
        const type = document.getElementById('reportType').value;

        loadingSpinner.style.display = 'block';
        reportPreview.style.display = 'none';

        fetch(`{{ route('organization.reports.preview') }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ month, year, type })
        })
        .then(response => response.json())
        .then(data => {
            loadingSpinner.style.display = 'none';
            if (data.errors) {
                alert('Error: ' + Object.values(data.errors).join(', '));
                return;
            }
            
            displayPreview(data, type);
            reportPreview.style.display = 'block';
        })
        .catch(error => {
            loadingSpinner.style.display = 'none';
            console.error('Error:', error);
            alert('An error occurred while generating the preview.');
        });
    });

    // Generate PDF
    generatePdfBtn.addEventListener('click', function() {
        const month = document.getElementById('month').value;
        const year = document.getElementById('year').value;
        const type = document.getElementById('reportType').value;

        let url;
        switch(type) {
            case 'volunteers':
                url = `{{ route('organization.reports.volunteers') }}`;
                break;
            case 'completed':
                url = `{{ route('organization.reports.completed') }}`;
                break;
            case 'failed':
                url = `{{ route('organization.reports.failed') }}`;
                break;
            case 'comprehensive':
                url = `{{ route('organization.reports.comprehensive') }}`;
                break;
        }

        const params = new URLSearchParams({ month, year, format: 'pdf' });
        window.open(`${url}?${params}`, '_blank');
    });

    function displayPreview(data, type) {
        let html = `
            <div class="row mb-4">
                <div class="col-12">
                    <h4>${getReportTitle(type)} - ${data.period}</h4>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h3>${data.statistics.recruited_volunteers}</h3>
                            <p class="mb-0">Recruited Volunteers</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h3>${data.statistics.completed_tasks}</h3>
                            <p class="mb-0">Completed Tasks</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body text-center">
                            <h3>${data.statistics.failed_tasks}</h3>
                            <p class="mb-0">Failed Tasks</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h3>${data.statistics.success_rate}%</h3>
                            <p class="mb-0">Success Rate</p>
                        </div>
                    </div>
                </div>
            </div>
        `;

        if (type === 'comprehensive') {
            html += `
                <div class="alert alert-info">
                    <h5>Report Summary</h5>
                    <ul class="mb-0">
                        <li>Recruited Volunteers: ${data.volunteers_count}</li>
                        <li>Completed Tasks: ${data.completed_count}</li>
                        <li>Failed Tasks: ${data.failed_count}</li>
                    </ul>
                </div>
            `;
        } else {
            html += `
                <div class="alert alert-info">
                    <strong>Total Records:</strong> ${data.count}
                </div>
            `;
        }

        previewContent.innerHTML = html;
    }

    function getReportTitle(type) {
        switch(type) {
            case 'volunteers': return 'Recruited Volunteers Report';
            case 'completed': return 'Completed Tasks Report';
            case 'failed': return 'Failed Tasks Report';
            case 'comprehensive': return 'Comprehensive Report';
            default: return 'Report';
        }
    }
});
</script>
@endpush
@endsection
