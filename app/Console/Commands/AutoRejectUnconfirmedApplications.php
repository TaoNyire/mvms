<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Application;
use Illuminate\Console\Command;

class AutoRejectUnconfirmedApplications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:auto-reject-unconfirmed-applications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-reject applications not confirmed within 2 days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $cutoff = Carbon::now()->subDays(2);
        $applications = Application::where('status', 'accepted')
            ->where('confirmation_status', 'pending')
            ->where('responded_at', '<', $cutoff)
            ->get();

        foreach ($applications as $application) {
            $application->status = 'rejected';
            $application->confirmation_status = 'rejected';
            $application->save();
            // Optionally notify the volunteer
        }

        $this->info('Auto-rejected ' . $applications->count() . ' applications.');
    }
    
}
