<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Printer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class SyncPrinters extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'print:sync-printers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize printers with the system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting printer synchronization...');

        try {
            // Get printers from the system
            $systemPrinters = $this->getSystemPrinters();

            if (empty($systemPrinters)) {
                $this->warn('No printers found in the system.');
                return;
            }

            $this->info("Found " . count($systemPrinters) . " printers in the system.");

            // Sync each printer
            foreach ($systemPrinters as $systemPrinter) {
                $this->syncPrinter($systemPrinter);
            }

            // Deactivate printers not found in the system
            $this->deactivateMissingPrinters($systemPrinters);

            $this->info('Printer synchronization completed successfully.');

        } catch (\Exception $e) {
            Log::error('Error during printer synchronization: ' . $e->getMessage());
            $this->error('An error occurred while synchronizing printers.');
        }
    }

    /**
     * Get printers from the system
     *
     * @return array
     */
    protected function getSystemPrinters()
    {
        try {
            // Example: Get printers from CUPS (Common UNIX Printing System)
            // You may need to adjust this based on your actual printer system
            $response = Http::get('http://localhost:631/printers');
            
            if ($response->successful()) {
                return $response->json();
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Error getting system printers: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Sync a single printer with the database
     *
     * @param array $systemPrinter
     * @return void
     */
    protected function syncPrinter(array $systemPrinter)
    {
        try {
            $this->info("Syncing printer: {$systemPrinter['name']}");

            $printer = Printer::updateOrCreate(
                ['system_id' => $systemPrinter['id']],
                [
                    'name' => $systemPrinter['name'],
                    'model' => $systemPrinter['model'] ?? null,
                    'location' => $systemPrinter['location'] ?? null,
                    'status' => $systemPrinter['status'] ?? 'unknown',
                    'is_active' => true,
                    'last_sync' => now(),
                ]
            );

            $this->info("Printer {$printer->name} synchronized successfully.");

        } catch (\Exception $e) {
            Log::error("Error syncing printer {$systemPrinter['name']}: " . $e->getMessage());
            $this->error("Failed to sync printer {$systemPrinter['name']}");
        }
    }

    /**
     * Deactivate printers that are no longer in the system
     *
     * @param array $systemPrinters
     * @return void
     */
    protected function deactivateMissingPrinters(array $systemPrinters)
    {
        try {
            $systemPrinterIds = array_column($systemPrinters, 'id');
            
            $deactivated = Printer::whereNotIn('system_id', $systemPrinterIds)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            if ($deactivated > 0) {
                $this->info("Deactivated {$deactivated} printers that are no longer in the system.");
            }

        } catch (\Exception $e) {
            Log::error('Error deactivating missing printers: ' . $e->getMessage());
            $this->error('Failed to deactivate missing printers');
        }
    }
}
