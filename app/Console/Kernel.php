<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {
        // Run the IMAP fetch every 5 minutes, avoid overlapping runs
        // Use a dedicated lightweight command 'emails:fetch' for production scheduling
        $schedule->command('emails:fetch')
             ->everyFiveMinutes()
             ->withoutOverlapping()
             ->runInBackground();

        // You can add additional scheduled tasks here
    }

    /**
     * Register the commands for the application.
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
