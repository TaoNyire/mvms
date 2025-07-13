<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Opportunity;
use App\Models\Application;
use App\Notifications\ApplicationStatusNotification;

class CheckRecruitmentStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recruitment:check-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and close recruitment for opportunities that have reached their volunteer limit';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $activeOpportunities = Opportunity::where('status', 'active')->get();

        if ($activeOpportunities->isEmpty()) {
            $this->info('No active opportunities found.');
            return;
        }

        $closedCount = 0;

        foreach ($activeOpportunities as $opportunity) {
            $acceptedCount = $opportunity->getAcceptedVolunteersCount();

            if ($acceptedCount >= $opportunity->volunteers_needed) {
                // Close recruitment
                $opportunity->closeRecruitment();

                // Reject all pending applications
                $pendingApplications = Application::where('opportunity_id', $opportunity->id)
                    ->where('status', 'pending')
                    ->with('volunteer')
                    ->get();

                foreach ($pendingApplications as $application) {
                    $application->update([
                        'status' => 'rejected',
                        'responded_at' => now()
                    ]);

                    // Notify volunteer
                    $application->volunteer->notify(new ApplicationStatusNotification($application));
                }

                $closedCount++;
                $this->info("Closed recruitment for: {$opportunity->title} (ID: {$opportunity->id})");
                $this->info("  - Rejected {$pendingApplications->count()} pending applications");
            }
        }

        if ($closedCount === 0) {
            $this->info('No opportunities needed recruitment closure.');
        } else {
            $this->info("Successfully closed recruitment for {$closedCount} opportunities.");
        }
    }
}
