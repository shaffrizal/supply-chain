<?php

namespace App\Console\Commands;

use App\Services\WorldBankBatchSyncService;
use Illuminate\Console\Command;
use Throwable;

class SyncWorldBankIndicators extends Command
{
    protected $signature = 'worldbank:sync {--fresh : Ignore the six-hour provider cache}';
    protected $description = 'Synchronize the latest World Bank indicators for every country';

    public function handle(WorldBankBatchSyncService $sync): int
    {
        $this->info('Synchronizing seven World Bank indicators for the global country dataset...');

        try {
            $result = $sync->sync(
                $this->option('fresh'),
                fn (string $name, int $count) => $this->line("  <info>✓</info> $name: $count countries")
            );
        } catch (Throwable $exception) {
            $this->error('World Bank synchronization failed: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->newLine();
        $this->info("Synchronization complete: {$result['countries']} database countries updated.");

        foreach ($result['errors'] ?? [] as $indicator => $message) {
            $this->warn("$indicator was skipped: $message");
        }

        return self::SUCCESS;
    }
}
