<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AdminUsersExport;

class AdminReportController extends Controller
{
    /**
     * Export all users (admins, organizations, volunteers) as CSV
     */
    public function exportUsers(Request $request)
    {
        $filename = 'mvms_users_export_' . now()->format('Ymd_His') . '.csv';
        return Excel::download(new AdminUsersExport, $filename);
    }
}
