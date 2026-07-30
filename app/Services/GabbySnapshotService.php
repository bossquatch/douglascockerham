<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class GabbySnapshotService
{
    public function __construct(
        private GabbyHandoffValidator $validator,
        private GabbySourceLinkService $links,
    ) {}

    /**
     * Return the public-safe snapshot used by the dashboard.
     *
     * This intentionally small boundary is the integration seam for Gabby's
     * future import/API adapter. The view should continue to consume this
     * normalized shape rather than knowing where the source data lives.
     *
     * @return array<string, mixed>
     */
    public function current(): array
    {
        $statePath = config('gabby.handoff.state_path');

        if (is_string($statePath) && is_file($statePath) && ! is_link($statePath)) {
            try {
                $artifact = $this->validator->load($statePath, false);

                return $this->links->present($artifact['snapshot']);
            } catch (Throwable $exception) {
                Log::warning('Stored Gabby snapshot was rejected; using the configured last-known-good snapshot.', [
                    'reason' => $exception->getMessage(),
                ]);
            }
        }

        $snapshot = config('gabby.snapshot');

        if (! is_array($snapshot)) {
            throw new RuntimeException('Gabby snapshot configuration is missing.');
        }

        return $this->links->present($snapshot);
    }
}
