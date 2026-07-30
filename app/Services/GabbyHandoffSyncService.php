<?php

namespace App\Services;

use App\Exceptions\InvalidGabbyHandoff;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class GabbyHandoffSyncService
{
    public function __construct(
        private GabbyHandoffValidator $validator,
    ) {}

    /**
     * @return array{status: 'updated'|'unchanged', generated_at: string}
     */
    public function sync(): array
    {
        $handoffPath = $this->configuredPath('path');
        $statePath = $this->configuredPath('state_path');
        $lockPath = $this->configuredPath('lock_path');

        $this->ensureDirectory(dirname($statePath));
        $this->ensureDirectory(dirname($lockPath));

        $lock = fopen($lockPath, 'c');

        if ($lock === false) {
            throw new RuntimeException('The Gabby sync lock could not be opened.');
        }

        @chmod($lockPath, 0600);

        if (! flock($lock, LOCK_EX | LOCK_NB)) {
            fclose($lock);

            throw new RuntimeException('Another Gabby snapshot sync is already running.');
        }

        try {
            $incoming = $this->validator->load($handoffPath);
            $incomingTimestamp = CarbonImmutable::parse($incoming['generated_at']);
            $incomingJson = $this->validator->canonicalJson($incoming);
            $current = null;
            $stateNeedsRepair = is_link($statePath);

            if (is_file($statePath) && ! is_link($statePath)) {
                try {
                    $current = $this->validator->load($statePath, false);
                } catch (InvalidGabbyHandoff $exception) {
                    $stateNeedsRepair = true;
                    Log::warning('Stored Gabby snapshot is invalid and will be replaced only by a valid handoff.', [
                        'reason' => $exception->getMessage(),
                    ]);
                }
            }

            if (is_array($current)) {
                $currentTimestamp = CarbonImmutable::parse($current['generated_at']);

                if ($incomingTimestamp->lessThan($currentTimestamp)) {
                    throw new InvalidGabbyHandoff('The Gabby handoff is older than the last accepted snapshot.');
                }

                if ($incomingTimestamp->equalTo($currentTimestamp)) {
                    if (hash_equals(
                        hash('sha256', $this->validator->canonicalJson($current)),
                        hash('sha256', $incomingJson),
                    )) {
                        return [
                            'status' => 'unchanged',
                            'generated_at' => $incoming['generated_at'],
                        ];
                    }

                    throw new InvalidGabbyHandoff('The Gabby handoff conflicts with the accepted snapshot at the same timestamp.');
                }
            } else {
                $seed = config('gabby.snapshot');

                if (is_array($seed) && isset($seed['generated_at']) && is_string($seed['generated_at'])) {
                    $seedTimestamp = CarbonImmutable::parse($seed['generated_at']);

                    if ($incomingTimestamp->lessThan($seedTimestamp)) {
                        throw new InvalidGabbyHandoff('The Gabby handoff is older than the configured last-known-good snapshot.');
                    }

                    if (! $stateNeedsRepair && $incomingTimestamp->equalTo($seedTimestamp) && $incoming['snapshot'] === $seed) {
                        return [
                            'status' => 'unchanged',
                            'generated_at' => $incoming['generated_at'],
                        ];
                    }
                }
            }

            $this->atomicWrite($statePath, $incomingJson);

            return [
                'status' => 'updated',
                'generated_at' => $incoming['generated_at'],
            ];
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    private function configuredPath(string $key): string
    {
        $path = config("gabby.handoff.$key");

        if (! is_string($path) || $path === '') {
            throw new RuntimeException("Gabby handoff $key is not configured.");
        }

        return $path;
    }

    private function ensureDirectory(string $path): void
    {
        if (is_dir($path)) {
            return;
        }

        if (! mkdir($path, 0700, true) && ! is_dir($path)) {
            throw new RuntimeException('The Gabby private storage directory could not be created.');
        }
    }

    private function atomicWrite(string $path, string $contents): void
    {
        $temporaryPath = $path.'.tmp.'.bin2hex(random_bytes(8));

        try {
            if (file_put_contents($temporaryPath, $contents, LOCK_EX) !== strlen($contents)) {
                throw new RuntimeException('The Gabby snapshot could not be written completely.');
            }

            @chmod($temporaryPath, 0600);

            if (! rename($temporaryPath, $path)) {
                throw new RuntimeException('The Gabby snapshot could not be promoted atomically.');
            }
        } finally {
            if (is_file($temporaryPath)) {
                @unlink($temporaryPath);
            }
        }
    }
}
