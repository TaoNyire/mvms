# MVMS Report Generation Module

## Overview
A comprehensive report generation module for organizations in the Malawi Volunteer Management System (MVMS). This module allows organizations to generate detailed monthly reports in PDF format for volunteer recruitment, task completion, and task failures.

## Features

### 📊 Report Types
1. **Monthly Recruited Volunteers Report**
   - Lists all volunteers recruited (accepted applications) in a specific month
   - Includes volunteer details, contact information, skills, and assignment status
   - Shows opportunity details and recruitment timeline

2. **Monthly Completed Tasks Report**
   - Details all successfully completed tasks in a specific month
   - Includes task information, volunteer performance, ratings, and duration
   - Shows completion timeline and performance metrics

3. **Monthly Failed Tasks Report**
   - Lists all failed, cancelled, or declined tasks in a specific month
   - Categorizes failure reasons (cancelled, no-show, declined, unsuccessful)
   - Provides failure analysis and insights

4. **Comprehensive Monthly Report**
   - Combines all three reports into a single document
   - Includes executive summary and key insights
   - Provides recommendations based on performance metrics

### 🎨 Features
- **Interactive Preview**: AJAX-powered preview before PDF generation
- **Professional PDF Styling**: Clean, branded PDF templates with statistics
- **Filtering Options**: Filter by month and year
- **Statistics Dashboard**: Key metrics and performance indicators
- **Responsive Design**: Mobile-friendly interface
- **Export Functionality**: Direct PDF download

## Installation & Setup

### 1. Dependencies
The module uses the following packages:
- `barryvdh/laravel-dompdf` - PDF generation
- Laravel's built-in authentication and authorization

### 2. Files Created
```
app/
├── Http/Controllers/ReportController.php
└── Services/ReportService.php

resources/views/organization/reports/
├── index.blade.php
└── pdf/
    ├── recruited-volunteers.blade.php
    ├── completed-tasks.blade.php
    ├── failed-tasks.blade.php
    └── comprehensive.blade.php

database/seeders/
└── ReportTestDataSeeder.php
```

### 3. Routes Added
```php
// Organization Reports
Route::get('/organization/reports', [ReportController::class, 'index'])->name('organization.reports.index');
Route::post('/organization/reports/preview', [ReportController::class, 'preview'])->name('organization.reports.preview');
Route::get('/organization/reports/volunteers', [ReportController::class, 'monthlyRecruitedVolunteers'])->name('organization.reports.volunteers');
Route::get('/organization/reports/completed-tasks', [ReportController::class, 'monthlyCompletedTasks'])->name('organization.reports.completed');
Route::get('/organization/reports/failed-tasks', [ReportController::class, 'monthlyFailedTasks'])->name('organization.reports.failed');
Route::get('/organization/reports/comprehensive', [ReportController::class, 'monthlyComprehensiveReport'])->name('organization.reports.comprehensive');
```

## Usage

### 1. Access Reports
1. Login as an organization user
2. Navigate to "Generate Reports" in the sidebar
3. Select desired month, year, and report type
4. Click "Preview" to see data summary or "PDF" to download

### 2. API Endpoints

#### Preview Report Data
```http
POST /organization/reports/preview
Content-Type: application/json

{
    "month": 12,
    "year": 2024,
    "type": "volunteers"
}
```

#### Generate PDF Reports
```http
GET /organization/reports/volunteers?month=12&year=2024&format=pdf
GET /organization/reports/completed-tasks?month=12&year=2024&format=pdf
GET /organization/reports/failed-tasks?month=12&year=2024&format=pdf
GET /organization/reports/comprehensive?month=12&year=2024&format=pdf
```

### 3. Testing with Sample Data
Run the test data seeder to populate sample data:
```bash
php artisan db:seed --class=ReportTestDataSeeder
```

This creates:
- 1 test organization (org@test.com / password)
- 10 test volunteers (volunteer1@test.com to volunteer10@test.com / password)
- Sample opportunities, applications, tasks, and assignments

## Technical Details

### Database Queries
The module efficiently queries the following relationships:
- `Applications` → `Opportunities` → `Organization`
- `Assignments` → `Tasks` → `Opportunities` → `Organization`
- `Users` → `VolunteerProfiles`

### Performance Considerations
- Uses eager loading to prevent N+1 queries
- Implements proper indexing on date fields
- Limits preview data for better performance
- Uses efficient groupBy operations for statistics

### Security
- Protected by organization role middleware
- CSRF protection on all forms
- Input validation on all parameters
- Organization-scoped data access only

## Customization

### PDF Styling
Modify the PDF templates in `resources/views/organization/reports/pdf/` to customize:
- Colors and branding
- Layout and typography
- Additional data fields
- Statistical calculations

### Report Logic
Extend `ReportService.php` to:
- Add new report types
- Modify data filtering logic
- Include additional statistics
- Change date range calculations

### UI Components
Customize `index.blade.php` to:
- Add new filter options
- Modify preview functionality
- Change report type options
- Update styling and layout

## Troubleshooting

### Common Issues
1. **PDF Generation Fails**: Ensure DomPDF package is properly installed
2. **No Data in Reports**: Check if organization has completed applications/tasks
3. **Permission Errors**: Verify user has organization role and profile is complete
4. **Styling Issues**: Check CSS compatibility with DomPDF limitations

### Debug Mode
Enable debug mode in the controller to see detailed error messages:
```php
$pdf = PDF::loadView('view', $data)->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);
```

## Future Enhancements
- Export to Excel format
- Email report scheduling
- Multi-month date ranges
- Advanced filtering options
- Chart and graph integration
- Report templates customization
- Automated report generation

## Support
For issues or feature requests, please refer to the main MVMS documentation or contact the development team.
