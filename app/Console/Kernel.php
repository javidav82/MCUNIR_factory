<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\ProcessPrintJobs;
use App\Console\Commands\CleanupPrintJobs;
use App\Console\Commands\GeneratePrintReports;
use App\Console\Commands\NotifyPrintStatus;
use App\Console\Commands\ValidatePrintDocuments;
use App\Console\Commands\BackupPrintData;
use App\Console\Commands\SyncPrinters;
use App\Console\Commands\MonitorPrintQueue;
use App\Console\Commands\ArchivePrintJobs;
use App\Console\Commands\GeneratePrintStatistics;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        ProcessPrintJobs::class,
        CleanupPrintJobs::class,
        GeneratePrintReports::class,
        NotifyPrintStatus::class,
        ValidatePrintDocuments::class,
        BackupPrintData::class,
        SyncPrinters::class,
        MonitorPrintQueue::class,
        ArchivePrintJobs::class,
        GeneratePrintStatistics::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
