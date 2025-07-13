<?php

use Illuminate\Console\Scheduling\Schedule;

return function (Schedule $schedule) {
    // Auto-reject unconfirmed applications (existing)
    $schedule->command('app:auto-reject-unconfirmed-applications')->daily();

    // Auto-complete expired tasks
    $schedule->command('tasks:auto-complete-expired')->daily();

    // Check and close recruitment when volunteer limit is reached
    $schedule->command('recruitment:check-status')->hourly();
};