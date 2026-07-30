<?php

namespace App\Services;

use App\Exceptions\InvalidGabbyHandoff;
use Carbon\CarbonImmutable;
use JsonException;
use Throwable;

class GabbyHandoffValidator
{
    public function __construct(
        private GabbySourceLinkService $links,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function load(string $path, bool $enforceFreshness = true): array
    {
        if (is_link($path) || ! is_file($path)) {
            throw new InvalidGabbyHandoff('The Gabby handoff is missing or is not a regular file.');
        }

        $maxBytes = (int) config('gabby.handoff.max_bytes', 524288);
        $size = filesize($path);

        if ($size === false || $size < 2 || $size > $maxBytes) {
            throw new InvalidGabbyHandoff('The Gabby handoff has an invalid file size.');
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new InvalidGabbyHandoff('The Gabby handoff could not be read.');
        }

        try {
            $artifact = json_decode($contents, true, 64, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidGabbyHandoff('The Gabby handoff is not valid JSON.', previous: $exception);
        }

        if (! is_array($artifact) || array_is_list($artifact)) {
            throw new InvalidGabbyHandoff('The Gabby handoff root must be a JSON object.');
        }

        return $this->validate($artifact, $enforceFreshness);
    }

    /**
     * @param  array<string, mixed>  $artifact
     * @return array<string, mixed>
     */
    public function validate(array $artifact, bool $enforceFreshness = true): array
    {
        $this->assertSame(
            config('gabby.handoff.schema'),
            $artifact['schema'] ?? null,
            'schema',
        );
        $this->assertSame(
            (int) config('gabby.handoff.version'),
            $artifact['version'] ?? null,
            'version',
        );
        $this->assertSame('live', $artifact['data_mode'] ?? null, 'data_mode');

        $generatedAt = $this->date($artifact['generated_at'] ?? null, 'generated_at');
        $collection = $this->object($artifact['collection'] ?? null, 'collection');
        $this->assertSame('succeeded', $collection['status'] ?? null, 'collection.status');
        $this->assertSame(true, $collection['complete'] ?? null, 'collection.complete');

        $startedAt = $this->date($collection['started_at'] ?? null, 'collection.started_at');
        $completedAt = $this->date($collection['completed_at'] ?? null, 'collection.completed_at');

        if ($startedAt->greaterThan($completedAt) || $completedAt->greaterThan($generatedAt)) {
            throw new InvalidGabbyHandoff('The Gabby handoff collection timestamps are out of order.');
        }

        $maxGenerationLag = (int) config('gabby.handoff.max_generation_lag_minutes', 15);

        if ($generatedAt->timestamp - $completedAt->timestamp > $maxGenerationLag * 60) {
            throw new InvalidGabbyHandoff('The Gabby handoff was generated too long after collection completed.');
        }

        if ($enforceFreshness) {
            $now = CarbonImmutable::now('UTC');
            $maxFuture = (int) config('gabby.handoff.max_future_minutes', 5);
            $maxAge = (int) config('gabby.handoff.max_age_minutes', 180);

            if ($generatedAt->timestamp > $now->addMinutes($maxFuture)->timestamp) {
                throw new InvalidGabbyHandoff('The Gabby handoff timestamp is in the future.');
            }

            if ($generatedAt->timestamp < $now->subMinutes($maxAge)->timestamp) {
                throw new InvalidGabbyHandoff('The Gabby handoff is stale.');
            }
        }

        $sourceHealth = $this->object($collection['source_health'] ?? null, 'collection.source_health');
        $enabled = $this->positiveInteger($sourceHealth['enabled'] ?? null, 'collection.source_health.enabled');
        $processed = $this->positiveInteger($sourceHealth['processed'] ?? null, 'collection.source_health.processed');
        $failures = $this->nonNegativeInteger($sourceHealth['failures'] ?? null, 'collection.source_health.failures');
        $withCurrentItems = $this->nonNegativeInteger($sourceHealth['with_current_items'] ?? null, 'collection.source_health.with_current_items');
        $withoutCurrentItems = $this->nonNegativeInteger($sourceHealth['without_current_items'] ?? null, 'collection.source_health.without_current_items');

        if ($failures !== 0) {
            throw new InvalidGabbyHandoff('The Gabby handoff reports collection failures.');
        }

        if ($withCurrentItems + $withoutCurrentItems !== $enabled || $processed < $enabled) {
            throw new InvalidGabbyHandoff('The Gabby handoff source-health totals do not reconcile.');
        }

        $privacy = $this->object($artifact['privacy'] ?? null, 'privacy');
        $requiredPrivacy = [
            'public_safe' => true,
            'contains_raw_posts' => false,
            'contains_handles' => false,
            'contains_pii' => false,
            'politically_neutral' => true,
            'contains_political_scoring' => false,
            'contains_endorsements' => false,
            'contains_donor_targeting' => false,
        ];

        foreach ($requiredPrivacy as $key => $expected) {
            $this->assertSame($expected, $privacy[$key] ?? null, "privacy.$key");
        }

        $snapshot = $this->object($artifact['snapshot'] ?? null, 'snapshot');
        $this->validateSnapshot($snapshot, $generatedAt, [
            'enabled' => $enabled,
            'processed' => $processed,
            'failures' => $failures,
            'with_current_items' => $withCurrentItems,
            'without_current_items' => $withoutCurrentItems,
        ]);
        $this->rejectUnsafeFields($artifact, 'handoff');

        return $artifact;
    }

    /**
     * @param  array<string, mixed>  $snapshot
     * @param  array<string, int>  $sourceHealth
     */
    private function validateSnapshot(array $snapshot, CarbonImmutable $generatedAt, array $sourceHealth): void
    {
        $this->assertSame($generatedAt->format(DATE_ATOM), $snapshot['generated_at'] ?? null, 'snapshot.generated_at');

        $status = $this->object($snapshot['status'] ?? null, 'snapshot.status');
        $this->text($status['label'] ?? null, 'snapshot.status.label', 80);
        $this->text($status['state'] ?? null, 'snapshot.status.state', 160);
        $displayTimestamp = $this->text($status['timestamp'] ?? null, 'snapshot.status.timestamp', 100);
        $this->text($status['coverage'] ?? null, 'snapshot.status.coverage', 200);

        $timezone = (string) config('gabby.handoff.timezone', 'America/New_York');
        $expectedTimestamp = $generatedAt->setTimezone($timezone)->format('F j, Y \a\t g:i A T');

        if ($displayTimestamp !== $expectedTimestamp) {
            throw new InvalidGabbyHandoff('The displayed Gabby snapshot timestamp does not match generated_at.');
        }

        $priority = $this->object($snapshot['priority'] ?? null, 'snapshot.priority');
        $this->text($priority['level'] ?? null, 'snapshot.priority.level', 100);
        $this->assertSame('Verified', $priority['confidence'] ?? null, 'snapshot.priority.confidence');
        $this->text($priority['title'] ?? null, 'snapshot.priority.title', 300);
        $this->text($priority['summary'] ?? null, 'snapshot.priority.summary', 2000);
        $prioritySource = $this->text($priority['source'] ?? null, 'snapshot.priority.source', 300);
        $this->optionalSafeUrl($priority['source_url'] ?? null, 'snapshot.priority.source_url');

        if (! str_contains($prioritySource, 'Official record')) {
            throw new InvalidGabbyHandoff('The priority item must include official-record attribution.');
        }

        $briefing = $this->list($snapshot['briefing'] ?? null, 'snapshot.briefing', 1, 20);

        foreach ($briefing as $index => $item) {
            $item = $this->object($item, "snapshot.briefing.$index");
            $this->text($item['time'] ?? null, "snapshot.briefing.$index.time", 100);
            $this->text($item['label'] ?? null, "snapshot.briefing.$index.label", 100);
            $tone = $this->text($item['tone'] ?? null, "snapshot.briefing.$index.tone", 20);

            if (! in_array($tone, ['urgent', 'resolved', 'official'], true)) {
                throw new InvalidGabbyHandoff("snapshot.briefing.$index.tone is not allowed.");
            }

            $this->text($item['title'] ?? null, "snapshot.briefing.$index.title", 300);
            $this->text($item['summary'] ?? null, "snapshot.briefing.$index.summary", 1200);
            $source = $this->text($item['source'] ?? null, "snapshot.briefing.$index.source", 300);
            $this->optionalSafeUrl($item['source_url'] ?? null, "snapshot.briefing.$index.source_url");

            if (! str_contains($source, 'Official record')) {
                throw new InvalidGabbyHandoff("snapshot.briefing.$index must include official-record attribution.");
            }
        }

        $snapshotHealth = $this->object($snapshot['source_health'] ?? null, 'snapshot.source_health');

        foreach ($sourceHealth as $key => $expected) {
            $this->assertSame($expected, $snapshotHealth[$key] ?? null, "snapshot.source_health.$key");
        }

        $healthItems = $this->list($snapshotHealth['items'] ?? null, 'snapshot.source_health.items', 4, 4);
        $expectedHealthItems = [
            ['label' => $sourceHealth['processed'].' source results', 'status' => 'Processed'],
            ['label' => $sourceHealth['with_current_items'].' public sources', 'status' => 'Current items'],
            ['label' => $sourceHealth['without_current_items'].' public sources', 'status' => 'No current items'],
            ['label' => 'Collection failures', 'status' => (string) $sourceHealth['failures']],
        ];

        foreach ($healthItems as $index => $item) {
            $item = $this->object($item, "snapshot.source_health.items.$index");
            $this->assertSame($expectedHealthItems[$index]['label'], $item['label'] ?? null, "snapshot.source_health.items.$index.label");
            $this->assertSame($expectedHealthItems[$index]['status'], $item['status'] ?? null, "snapshot.source_health.items.$index.status");
        }

        $this->validateOfficialSummary($snapshot['weather'] ?? null, 'snapshot.weather');
        $this->validateOfficialSummary($snapshot['elections'] ?? null, 'snapshot.elections');

        if (array_key_exists('utilities', $snapshot)) {
            $this->validateUtilities($snapshot['utilities'], $generatedAt);
        }

        $community = $this->object($snapshot['community'] ?? null, 'snapshot.community');
        $communityLabel = $this->text($community['label'] ?? null, 'snapshot.community.label', 120);

        if (! str_contains($communityLabel, 'Unverified')) {
            throw new InvalidGabbyHandoff('Community reporting must remain explicitly unverified.');
        }

        $this->text($community['title'] ?? null, 'snapshot.community.title', 300);
        $this->text($community['summary'] ?? null, 'snapshot.community.summary', 1200);
        $this->text($community['pattern'] ?? null, 'snapshot.community.pattern', 300);
        $this->text($community['coverage'] ?? null, 'snapshot.community.coverage', 500);
    }

    private function validateOfficialSummary(mixed $value, string $path): void
    {
        $summary = $this->object($value, $path);
        $label = $this->text($summary['label'] ?? null, "$path.label", 100);

        if (! str_contains($label, 'Official record')) {
            throw new InvalidGabbyHandoff("$path must include official-record attribution.");
        }

        $items = $this->list($summary['items'] ?? null, "$path.items", 1, 20);

        foreach ($items as $index => $item) {
            $this->text($item, "$path.items.$index", 500);
        }
    }

    private function validateUtilities(mixed $value, CarbonImmutable $generatedAt): void
    {
        $utilities = $this->object($value, 'snapshot.utilities');
        $label = $this->text($utilities['label'] ?? null, 'snapshot.utilities.label', 100);

        if (! str_contains($label, 'Provider-reported aggregate')) {
            throw new InvalidGabbyHandoff('snapshot.utilities must identify provider-reported aggregate data.');
        }

        $items = $this->list($utilities['items'] ?? null, 'snapshot.utilities.items', 1, 20);
        $allowedKeys = [
            'kind',
            'provider',
            'scope',
            'customers_without_power',
            'customers_served',
            'percent_without_power',
            'updated_at',
            'published_etr',
            'source',
            'source_url',
        ];

        foreach ($items as $index => $item) {
            $path = "snapshot.utilities.items.$index";
            $item = $this->object($item, $path);
            $unexpectedKeys = array_diff(array_keys($item), $allowedKeys);

            if ($unexpectedKeys !== []) {
                throw new InvalidGabbyHandoff("$path contains unsupported provider fields.");
            }

            $this->assertSame('county_outage_aggregate', $item['kind'] ?? null, "$path.kind");
            $this->text($item['provider'] ?? null, "$path.provider", 120);
            $this->assertSame('Polk County', $item['scope'] ?? null, "$path.scope");
            $withoutPower = $this->nonNegativeInteger(
                $item['customers_without_power'] ?? null,
                "$path.customers_without_power",
            );
            $served = $this->positiveInteger($item['customers_served'] ?? null, "$path.customers_served");

            if ($withoutPower > $served) {
                throw new InvalidGabbyHandoff("$path outage totals do not reconcile.");
            }

            $percentage = $item['percent_without_power'] ?? null;

            if ((! is_float($percentage) && ! is_int($percentage))
                || $percentage < 0
                || $percentage > 100
                || abs((float) $percentage - round(($withoutPower / $served) * 100, 2)) > 0.005) {
                throw new InvalidGabbyHandoff("$path.percent_without_power does not match the provider totals.");
            }

            $updatedAt = $this->providerDate($item['updated_at'] ?? null, "$path.updated_at");

            if ($updatedAt->greaterThan($generatedAt)) {
                throw new InvalidGabbyHandoff("$path.updated_at is later than the handoff generation time.");
            }

            $publishedEtr = $this->text($item['published_etr'] ?? null, "$path.published_etr", 19);

            if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $publishedEtr) !== 1) {
                throw new InvalidGabbyHandoff("$path.published_etr must use the provider timestamp format.");
            }

            $source = $this->text($item['source'] ?? null, "$path.source", 200);

            if (! str_contains($source, 'Official provider aggregate')) {
                throw new InvalidGabbyHandoff("$path must retain official provider-aggregate attribution.");
            }

            $this->optionalSafeUrl($item['source_url'] ?? null, "$path.source_url");
        }
    }

    private function rejectUnsafeFields(mixed $value, string $path = 'snapshot'): void
    {
        if (is_string($value)) {
            $patterns = [
                '/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}\b/i',
                '/(^|\s)@[A-Za-z0-9_]{1,30}\b/',
                '/\b(?:\+?1[-.\s]?)?\(?\d{3}\)?[-.\s]\d{3}[-.\s]\d{4}\b/',
            ];

            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $value) === 1) {
                    throw new InvalidGabbyHandoff("$path contains disallowed identifying data.");
                }
            }

            return;
        }

        if (! is_array($value)) {
            return;
        }

        $blockedKeys = [
            'account_id',
            'account_number',
            'address',
            'addresses',
            'author',
            'authors',
            'donor',
            'donor_targeting',
            'donors',
            'email',
            'emails',
            'endorsement',
            'endorsements',
            'coordinates',
            'customer_id',
            'handle',
            'handles',
            'identities',
            'latitude',
            'longitude',
            'outage_id',
            'phone',
            'phones',
            'pii',
            'political_score',
            'political_scoring',
            'post_text',
            'premise_id',
            'raw_post',
            'raw_posts',
            'service_address',
            'user_id',
            'username',
            'usernames',
        ];

        foreach ($value as $key => $child) {
            $normalizedKey = strtolower(preg_replace('/[^a-z0-9]+/i', '_', (string) $key) ?? '');

            if (in_array($normalizedKey, $blockedKeys, true)) {
                throw new InvalidGabbyHandoff("$path contains the disallowed field $key.");
            }

            $this->rejectUnsafeFields($child, "$path.$key");
        }
    }

    /**
     * @param  array<string, mixed>  $artifact
     */
    public function canonicalJson(array $artifact): string
    {
        try {
            return json_encode(
                $this->canonicalize($artifact),
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
            ).PHP_EOL;
        } catch (JsonException $exception) {
            throw new InvalidGabbyHandoff('The Gabby handoff cannot be encoded safely.', previous: $exception);
        }
    }

    private function canonicalize(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        if (array_is_list($value)) {
            return array_map(fn (mixed $item): mixed => $this->canonicalize($item), $value);
        }

        ksort($value, SORT_STRING);

        foreach ($value as $key => $item) {
            $value[$key] = $this->canonicalize($item);
        }

        return $value;
    }

    private function date(mixed $value, string $path): CarbonImmutable
    {
        if (! is_string($value) || preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}$/', $value) !== 1) {
            throw new InvalidGabbyHandoff("$path must be an RFC 3339 timestamp with an explicit offset.");
        }

        try {
            return CarbonImmutable::parse($value);
        } catch (Throwable $exception) {
            throw new InvalidGabbyHandoff("$path is not a valid timestamp.", previous: $exception);
        }
    }

    private function providerDate(mixed $value, string $path): CarbonImmutable
    {
        if (! is_string($value)
            || preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d{1,6})?(?:Z|[+-]\d{2}:\d{2})$/', $value) !== 1) {
            throw new InvalidGabbyHandoff("$path must be an RFC 3339 provider timestamp.");
        }

        try {
            return CarbonImmutable::parse($value);
        } catch (Throwable $exception) {
            throw new InvalidGabbyHandoff("$path is not a valid provider timestamp.", previous: $exception);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function object(mixed $value, string $path): array
    {
        if (! is_array($value) || array_is_list($value)) {
            throw new InvalidGabbyHandoff("$path must be an object.");
        }

        return $value;
    }

    /**
     * @return array<int, mixed>
     */
    private function list(mixed $value, string $path, int $minimum, int $maximum): array
    {
        if (! is_array($value) || ! array_is_list($value) || count($value) < $minimum || count($value) > $maximum) {
            throw new InvalidGabbyHandoff("$path must contain between $minimum and $maximum items.");
        }

        return $value;
    }

    private function text(mixed $value, string $path, int $maximum): string
    {
        if (! is_string($value) || trim($value) === '' || mb_strlen($value) > $maximum) {
            throw new InvalidGabbyHandoff("$path must be non-empty text no longer than $maximum characters.");
        }

        return $value;
    }

    private function positiveInteger(mixed $value, string $path): int
    {
        if (! is_int($value) || $value < 1) {
            throw new InvalidGabbyHandoff("$path must be a positive integer.");
        }

        return $value;
    }

    private function nonNegativeInteger(mixed $value, string $path): int
    {
        if (! is_int($value) || $value < 0) {
            throw new InvalidGabbyHandoff("$path must be a non-negative integer.");
        }

        return $value;
    }

    private function assertSame(mixed $expected, mixed $actual, string $path): void
    {
        if ($actual !== $expected) {
            throw new InvalidGabbyHandoff("$path has an invalid value.");
        }
    }

    private function optionalSafeUrl(mixed $value, string $path): void
    {
        if ($value !== null && $this->links->safeUrl($value) === null) {
            throw new InvalidGabbyHandoff("$path is not an approved HTTPS source URL.");
        }
    }
}
