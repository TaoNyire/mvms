<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Generate PDF from view
     */
    private function generatePdf($view, $data, $filename)
    {
        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $html = view($view, $data)->render();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return response($dompdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Show the reports dashboard
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get current month and year for default values
        $currentMonth = now()->month;
        $currentYear = now()->year;
        
        // Get available months and years for dropdown
        $availableMonths = collect(range(1, 12))->map(function($month) {
            return [
                'value' => $month,
                'label' => Carbon::create(null, $month, 1)->format('F')
            ];
        });
        
        $availableYears = collect(range(now()->year - 2, now()->year))->map(function($year) {
            return [
                'value' => $year,
                'label' => $year
            ];
        });

        return view('organization.reports.index', compact(
            'currentMonth',
            'currentYear',
            'availableMonths',
            'availableYears'
        ));
    }

    /**
     * Generate monthly recruited volunteers report
     */
    public function monthlyRecruitedVolunteers(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:' . (now()->year + 1),
            'format' => 'in:pdf,json'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();
        $month = $request->input('month');
        $year = $request->input('year');
        $format = $request->input('format', 'json');

        $volunteers = $this->reportService->getMonthlyRecruitedVolunteers($user->id, $month, $year);
        $statistics = $this->reportService->getReportStatistics($user->id, $month, $year);

        $data = [
            'title' => 'Monthly Recruited Volunteers Report',
            'organization' => $user->organizationProfile,
            'period' => Carbon::create($year, $month, 1)->format('F Y'),
            'generated_at' => now(),
            'statistics' => $statistics,
            'volunteers' => $volunteers,
            'total_count' => $volunteers->count()
        ];

        if ($format === 'pdf') {
            $filename = "recruited-volunteers-{$year}-{$month}.pdf";
            return $this->generatePdf('organization.reports.pdf.recruited-volunteers', $data, $filename);
        }

        return response()->json($data);
    }

    /**
     * Generate monthly completed tasks report
     */
    public function monthlyCompletedTasks(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:' . (now()->year + 1),
            'format' => 'in:pdf,json'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();
        $month = $request->input('month');
        $year = $request->input('year');
        $format = $request->input('format', 'json');

        $tasks = $this->reportService->getMonthlyCompletedTasks($user->id, $month, $year);
        $statistics = $this->reportService->getReportStatistics($user->id, $month, $year);

        $data = [
            'title' => 'Monthly Completed Tasks Report',
            'organization' => $user->organizationProfile,
            'period' => Carbon::create($year, $month, 1)->format('F Y'),
            'generated_at' => now(),
            'statistics' => $statistics,
            'tasks' => $tasks,
            'total_count' => $tasks->count()
        ];

        if ($format === 'pdf') {
            $filename = "completed-tasks-{$year}-{$month}.pdf";
            return $this->generatePdf('organization.reports.pdf.completed-tasks', $data, $filename);
        }

        return response()->json($data);
    }

    /**
     * Generate monthly failed tasks report
     */
    public function monthlyFailedTasks(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:' . (now()->year + 1),
            'format' => 'in:pdf,json'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();
        $month = $request->input('month');
        $year = $request->input('year');
        $format = $request->input('format', 'json');

        $tasks = $this->reportService->getMonthlyFailedTasks($user->id, $month, $year);
        $statistics = $this->reportService->getReportStatistics($user->id, $month, $year);

        $data = [
            'title' => 'Monthly Failed Tasks Report',
            'organization' => $user->organizationProfile,
            'period' => Carbon::create($year, $month, 1)->format('F Y'),
            'generated_at' => now(),
            'statistics' => $statistics,
            'tasks' => $tasks,
            'total_count' => $tasks->count()
        ];

        if ($format === 'pdf') {
            $filename = "failed-tasks-{$year}-{$month}.pdf";
            return $this->generatePdf('organization.reports.pdf.failed-tasks', $data, $filename);
        }

        return response()->json($data);
    }

    /**
     * Generate comprehensive monthly report (all reports combined)
     */
    public function monthlyComprehensiveReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:' . (now()->year + 1),
            'format' => 'in:pdf,json'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();
        $month = $request->input('month');
        $year = $request->input('year');
        $format = $request->input('format', 'json');

        $volunteers = $this->reportService->getMonthlyRecruitedVolunteers($user->id, $month, $year);
        $completedTasks = $this->reportService->getMonthlyCompletedTasks($user->id, $month, $year);
        $failedTasks = $this->reportService->getMonthlyFailedTasks($user->id, $month, $year);
        $statistics = $this->reportService->getReportStatistics($user->id, $month, $year);

        $data = [
            'title' => 'Monthly Comprehensive Report',
            'organization' => $user->organizationProfile,
            'period' => Carbon::create($year, $month, 1)->format('F Y'),
            'generated_at' => now(),
            'statistics' => $statistics,
            'volunteers' => $volunteers,
            'completed_tasks' => $completedTasks,
            'failed_tasks' => $failedTasks,
            'volunteers_count' => $volunteers->count(),
            'completed_tasks_count' => $completedTasks->count(),
            'failed_tasks_count' => $failedTasks->count()
        ];

        if ($format === 'pdf') {
            $filename = "comprehensive-report-{$year}-{$month}.pdf";
            return $this->generatePdf('organization.reports.pdf.comprehensive', $data, $filename);
        }

        return response()->json($data);
    }

    /**
     * Get report preview data (for AJAX requests)
     */
    public function preview(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:' . (now()->year + 1),
            'type' => 'required|in:volunteers,completed,failed,comprehensive'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();
        $month = $request->input('month');
        $year = $request->input('year');
        $type = $request->input('type');

        $statistics = $this->reportService->getReportStatistics($user->id, $month, $year);

        $data = [
            'statistics' => $statistics,
            'period' => Carbon::create($year, $month, 1)->format('F Y')
        ];

        switch ($type) {
            case 'volunteers':
                $data['count'] = $this->reportService->getMonthlyRecruitedVolunteers($user->id, $month, $year)->count();
                break;
            case 'completed':
                $data['count'] = $this->reportService->getMonthlyCompletedTasks($user->id, $month, $year)->count();
                break;
            case 'failed':
                $data['count'] = $this->reportService->getMonthlyFailedTasks($user->id, $month, $year)->count();
                break;
            case 'comprehensive':
                $data['volunteers_count'] = $this->reportService->getMonthlyRecruitedVolunteers($user->id, $month, $year)->count();
                $data['completed_count'] = $this->reportService->getMonthlyCompletedTasks($user->id, $month, $year)->count();
                $data['failed_count'] = $this->reportService->getMonthlyFailedTasks($user->id, $month, $year)->count();
                break;
        }

        return response()->json($data);
    }
}
