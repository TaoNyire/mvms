<?php


use Illuminate\Console\Scheduling\Schedule;

return function (Schedule $schedule) {
    $schedule->command('app:auto-reject-unconfirmed-applications')->daily();
};