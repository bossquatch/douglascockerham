<?php

namespace App\Console\Commands;

use App\Exceptions\InvalidGabbyHandoff;
use App\Services\GabbyHandoffSyncService;
use Illuminate\Console\Command;
use RuntimeException;
use Throwable;

class SyncGabbySnapshot extends Command
{
    protected $signature = 'gabby:sync-snapshot';

    protected $description = 'Validate and promote the latest local Gabby collector handoff';

    public function handle(GabbyHandoffSyncService $sync): int
    {
        try {
            $result = $sync->sync();
        } catch (InvalidGabbyHandoff $exception) {
            $this->error('Gabby snapshot was not updated: '.$exception->getMessage());

            return self::FAILURE;
        } catch (RuntimeException $exception) {
            $this->error('Gabby snapshot was not updated: '.$exception->getMessage());

            return self::FAILURE;
        } catch (Throwable $exception) {
            report($exception);
            $this->error('Gabby snapshot was not updated because the local sync failed.');

            return self::FAILURE;
        }

        if ($result['status'] === 'unchanged') {
            $this->info("Gabby snapshot is already current ({$result['generated_at']}).");
        } else {
            $this->info("Gabby snapshot updated ({$result['generated_at']}).");
        }

        return self::SUCCESS;
    }
}
