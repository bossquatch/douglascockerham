<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use RuntimeException;

class GabbyElectionsService
{
    public function __construct(
        private GabbySourceLinkService $links,
    ) {}

    /**
     * Validate and present the structured, aggregate-only elections watch.
     *
     * @return array<string, mixed>
     */
    public function current(): array
    {
        $path = config('gabby.elections.path');

        if (! is_string($path) || ! is_file($path) || ! is_readable($path)) {
            throw new RuntimeException('Gabby elections data is unavailable.');
        }

        $contents = file_get_contents($path);

        if ($contents === false || strlen($contents) > 524_288) {
            throw new RuntimeException('Gabby elections data failed size validation.');
        }

        $payload = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        $this->validateRoot($payload);

        $deadlines = array_map(
            fn (array $deadline): array => $this->deadline($deadline),
            $payload['deadlines'],
        );
        $raceIds = [];
        $candidateCount = 0;
        $platformAvailable = 0;
        $financeAvailable = 0;
        $sourceUrls = collect($deadlines)->pluck('source_url')->filter()->all();
        $races = [];

        foreach ($payload['races'] as $race) {
            $normalized = $this->race($race);

            if (isset($raceIds[$normalized['id']])) {
                throw new RuntimeException('Gabby election race IDs must be unique.');
            }

            $raceIds[$normalized['id']] = true;
            $candidateCount += count($normalized['candidates']);
            $platformAvailable += collect($normalized['candidates'])
                ->where('platform.status', 'available')
                ->count();
            $financeAvailable += collect($normalized['candidates'])
                ->whereNotNull('finance')
                ->count();
            $sourceUrls[] = $normalized['source_url'];

            foreach ($normalized['candidates'] as $candidate) {
                $sourceUrls[] = $candidate['record']['source_url'];
                $sourceUrls[] = $candidate['platform']['source_url'];
                $sourceUrls[] = $candidate['finance']['source_url'] ?? null;
            }

            $races[] = $normalized;
        }

        foreach ($payload['upstream_access'] as $access) {
            $this->assertKeys($access, ['source_id', 'status', 'method', 'scope']);

            foreach ($access as $value) {
                $this->text($value, 240);
            }
        }

        return [
            'schema' => $payload['schema'],
            'version' => $payload['version'],
            'generated_at' => $payload['generated_at'],
            'generated_label' => CarbonImmutable::parse($payload['generated_at'])
                ->setTimezone('America/New_York')
                ->format('F j, Y \a\t g:i A T'),
            'scope' => $payload['scope'],
            'deadlines' => $deadlines,
            'races' => $races,
            'stats' => [
                'races' => count($races),
                'candidates' => $candidateCount,
                'platform_available' => $platformAvailable,
                'platform_gaps' => $candidateCount - $platformAvailable,
                'finance_available' => $financeAvailable,
                'unique_sources' => count(array_unique(array_filter($sourceUrls))),
            ],
        ];
    }

    private function validateRoot(mixed $payload): void
    {
        if (! is_array($payload)) {
            throw new RuntimeException('Gabby elections data must be a JSON object.');
        }

        $this->assertKeys($payload, [
            'schema',
            'version',
            'generated_at',
            'scope',
            'display_order',
            'policy',
            'deadlines',
            'races',
            'upstream_access',
        ]);

        if (
            $payload['schema'] !== 'gabby.elections-local'
            || $payload['version'] !== 1
            || $payload['display_order'] !== 'race_then_candidate_name_alphabetical'
            || ! is_array($payload['deadlines'])
            || ! array_is_list($payload['deadlines'])
            || ! is_array($payload['races'])
            || ! array_is_list($payload['races'])
            || ! is_array($payload['upstream_access'])
            || ! array_is_list($payload['upstream_access'])
            || count($payload['deadlines']) === 0
            || count($payload['races']) === 0
        ) {
            throw new RuntimeException('Gabby elections schema or required collections are invalid.');
        }

        $generatedAt = CarbonImmutable::parse($payload['generated_at']);

        if ($generatedAt->greaterThan(now()->addMinutes(5))) {
            throw new RuntimeException('Gabby elections data has a future generation timestamp.');
        }

        $this->text($payload['scope'], 400);
        $this->assertKeys($payload['policy'], [
            'purpose',
            'provenance_labels',
            'candidate_statements',
            'finance',
            'prohibited',
        ]);

        if (
            ! is_array($payload['policy']['provenance_labels'])
            || ! array_is_list($payload['policy']['provenance_labels'])
            || ! str_contains($payload['policy']['prohibited'], 'No candidate scoring')
        ) {
            throw new RuntimeException('Gabby elections neutrality policy is invalid.');
        }

        foreach (['purpose', 'candidate_statements', 'finance', 'prohibited'] as $key) {
            $this->text($payload['policy'][$key], 500);
        }

        foreach ($payload['policy']['provenance_labels'] as $label) {
            $this->text($label, 80);
        }
    }

    /**
     * @param  array<string, mixed>  $deadline
     * @return array<string, mixed>
     */
    private function deadline(array $deadline): array
    {
        $this->assertKeys(
            $deadline,
            ['event', 'election_date', 'vote_by_mail_request_deadline', 'label', 'source_name', 'source_url', 'source_date'],
            ['registration_and_party_change_deadline', 'registration_deadline', 'early_voting'],
        );

        if ($deadline['label'] !== 'Official election record') {
            throw new RuntimeException('Election deadlines must use official-record attribution.');
        }

        foreach (['event', 'label', 'source_name', 'source_date'] as $key) {
            $this->text($deadline[$key], 180);
        }

        $electionDate = $this->date($deadline['election_date']);
        $registration = $deadline['registration_and_party_change_deadline']
            ?? $deadline['registration_deadline']
            ?? null;
        $mailDeadline = CarbonImmutable::parse($deadline['vote_by_mail_request_deadline']);

        return [
            'event' => $deadline['event'],
            'election_date' => $electionDate->toDateString(),
            'election_label' => $electionDate->format('F j, Y'),
            'registration_label' => $registration
                ? $this->date($registration)->format('F j, Y')
                : null,
            'vote_by_mail_label' => $mailDeadline
                ->setTimezone('America/New_York')
                ->format('F j, Y \a\t g:i A T'),
            'early_voting' => isset($deadline['early_voting'])
                ? $this->text($deadline['early_voting'], 160)
                : null,
            'source' => $deadline['source_name'],
            'source_date' => $deadline['source_date'],
            'source_url' => $this->requiredSafeUrl($deadline['source_url']),
        ];
    }

    /**
     * @param  array<string, mixed>  $race
     * @return array<string, mixed>
     */
    private function race(array $race): array
    {
        $this->assertKeys($race, [
            'race_id',
            'office',
            'district',
            'election_date',
            'race_status',
            'source',
            'candidates',
        ]);

        if (
            ! is_string($race['race_id'])
            || ! preg_match('/^[a-z0-9-]{8,100}$/', $race['race_id'])
            || ! is_array($race['source'])
            || ! is_array($race['candidates'])
            || ! array_is_list($race['candidates'])
            || count($race['candidates']) === 0
        ) {
            throw new RuntimeException('A Gabby election race is invalid.');
        }

        foreach (['office', 'district', 'race_status'] as $key) {
            $this->text($race[$key], 180);
        }

        $this->assertKeys($race['source'], [
            'label',
            'name',
            'url',
            'source_date',
            'audited_at',
        ]);

        if ($race['source']['label'] !== 'Official election record') {
            throw new RuntimeException('Election races must use official-record attribution.');
        }

        foreach (['label', 'name', 'source_date'] as $key) {
            $this->text($race['source'][$key], 220);
        }

        CarbonImmutable::parse($race['source']['audited_at']);
        $electionDate = $this->date($race['election_date']);
        $candidates = array_map(
            fn (array $candidate): array => $this->candidate($candidate),
            $race['candidates'],
        );
        usort(
            $candidates,
            fn (array $left, array $right): int => strcasecmp($left['name'], $right['name']),
        );

        return [
            'id' => $race['race_id'],
            'office' => $race['office'],
            'district' => $race['district'],
            'label' => trim($race['office'].' · '.$race['district'], ' ·'),
            'election_date' => $electionDate->toDateString(),
            'election_label' => $electionDate->format('F j, Y'),
            'status' => $race['race_status'],
            'source' => $race['source']['name'],
            'source_date' => $race['source']['source_date'],
            'source_url' => $this->requiredSafeUrl($race['source']['url']),
            'candidates' => $candidates,
        ];
    }

    /**
     * @param  array<string, mixed>  $candidate
     * @return array<string, mixed>
     */
    private function candidate(array $candidate): array
    {
        $this->assertKeys(
            $candidate,
            ['name', 'candidate_record', 'platform'],
            ['party_ballot_label', 'finance'],
        );
        $name = $this->text($candidate['name'], 120);
        $party = isset($candidate['party_ballot_label'])
            ? $this->text($candidate['party_ballot_label'], 30)
            : null;

        $record = $candidate['candidate_record'];

        if (! is_array($record)) {
            throw new RuntimeException('A candidate record is invalid.');
        }

        $this->assertKeys($record, ['label', 'source_url', 'source_date']);

        if ($record['label'] !== 'Official election record') {
            throw new RuntimeException('Candidate names must use official-record attribution.');
        }

        return [
            'name' => $name,
            'party_ballot_label' => $party,
            'record' => [
                'label' => $record['label'],
                'source_date' => $this->text($record['source_date'], 80),
                'source_url' => $this->requiredSafeUrl($record['source_url']),
            ],
            'platform' => $this->platform($candidate['platform']),
            'finance' => isset($candidate['finance']) ? $this->finance($candidate['finance']) : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function platform(mixed $platform): array
    {
        if (! is_array($platform)) {
            throw new RuntimeException('A candidate platform record is invalid.');
        }

        $status = $platform['status'] ?? null;

        if ($status === 'available') {
            $this->assertKeys($platform, [
                'status',
                'label',
                'source_name',
                'source_url',
                'source_date',
                'summary',
            ]);

            if (
                $platform['label'] !== 'Candidate statement'
                || ! is_array($platform['summary'])
                || ! array_is_list($platform['summary'])
                || count($platform['summary']) === 0
                || count($platform['summary']) > 10
            ) {
                throw new RuntimeException('An available candidate statement is invalid.');
            }

            return [
                'status' => 'available',
                'status_label' => 'Candidate statement',
                'source' => $this->text($platform['source_name'], 200),
                'source_date' => $this->text($platform['source_date'], 80),
                'source_url' => $this->requiredSafeUrl($platform['source_url']),
                'summary' => array_map(
                    fn (mixed $item): string => $this->text($item, 300),
                    $platform['summary'],
                ),
                'note' => 'Attributed campaign material; not independently verified by Gabby.',
            ];
        }

        if (! in_array($status, ['coverage_gap', 'not_yet_audited'], true)) {
            throw new RuntimeException('A candidate platform status is unsupported.');
        }

        $this->assertKeys($platform, ['status', 'label', 'source_date', 'note']);

        if ($platform['label'] !== 'Candidate statement') {
            throw new RuntimeException('A candidate coverage gap has invalid attribution.');
        }

        return [
            'status' => 'gap',
            'status_label' => 'Not yet verified',
            'source' => null,
            'source_date' => $this->text($platform['source_date'], 80),
            'source_url' => null,
            'summary' => [],
            'note' => $this->text($platform['note'], 300),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function finance(mixed $finance): array
    {
        if (! is_array($finance)) {
            throw new RuntimeException('A candidate finance record is invalid.');
        }

        $this->assertKeys($finance, [
            'status',
            'label',
            'source_name',
            'source_url',
            'latest_report',
            'filed_date',
            'period_through',
            'monetary_contributions',
            'in_kind_contributions',
            'expenditures',
            'note',
        ]);

        if ($finance['status'] !== 'manual_official_snapshot' || $finance['label'] !== 'Official election record') {
            throw new RuntimeException('A candidate finance record has invalid provenance.');
        }

        foreach (['monetary_contributions', 'in_kind_contributions', 'expenditures'] as $key) {
            if (! is_numeric($finance[$key]) || $finance[$key] < 0 || $finance[$key] > 1_000_000_000) {
                throw new RuntimeException('A candidate finance aggregate is invalid.');
            }
        }

        return [
            'label' => 'Filed finance aggregate',
            'source' => $this->text($finance['source_name'], 220),
            'source_url' => $this->requiredSafeUrl($finance['source_url']),
            'latest_report' => $this->text($finance['latest_report'], 30),
            'filed_date' => $this->date($finance['filed_date'])->format('F j, Y'),
            'period_through' => $this->date($finance['period_through'])->format('F j, Y'),
            'monetary_contributions' => (float) $finance['monetary_contributions'],
            'in_kind_contributions' => (float) $finance['in_kind_contributions'],
            'expenditures' => (float) $finance['expenditures'],
            'note' => $this->text($finance['note'], 240),
        ];
    }

    /**
     * @param  array<string, mixed>  $value
     * @param  array<int, string>  $required
     * @param  array<int, string>  $optional
     */
    private function assertKeys(array $value, array $required, array $optional = []): void
    {
        $keys = array_keys($value);

        if (
            array_diff($required, $keys) !== []
            || array_diff($keys, [...$required, ...$optional]) !== []
        ) {
            throw new RuntimeException('Gabby elections data contains missing or unsupported fields.');
        }
    }

    private function requiredSafeUrl(mixed $url): string
    {
        $safe = $this->links->safeUrl($url);

        if ($safe === null) {
            throw new RuntimeException('Gabby elections data contains an unapproved URL.');
        }

        return $safe;
    }

    private function text(mixed $value, int $maxLength): string
    {
        if (
            ! is_string($value)
            || trim($value) === ''
            || mb_strlen($value, 'UTF-8') > $maxLength
            || ! mb_check_encoding($value, 'UTF-8')
            || preg_match('/[<>\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', $value)
        ) {
            throw new RuntimeException('Gabby elections data contains unsafe text.');
        }

        return trim($value);
    }

    private function date(mixed $value): CarbonImmutable
    {
        if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            throw new RuntimeException('Gabby elections data contains an invalid date.');
        }

        return CarbonImmutable::createFromFormat('!Y-m-d', $value);
    }
}
