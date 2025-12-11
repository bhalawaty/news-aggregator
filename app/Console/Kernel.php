<?php


namespace App\Console;

use App\Jobs\FetchSourceJob;
use App\Models\Source;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $schedule->call(function () {
            $sources = Source::all();
            foreach ($sources as $source) {
                FetchSourceJob::dispatch($source);
            }
        })->everyFiveMinutes()
        ->withoutOverlapping()
        ->onOneServer();
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
