# Gabby snapshot handoff

The Gabby collector owns collection scheduling. After a successful collection, it may update the local Laravel dashboard by writing one public-safe JSON handoff and invoking:

```sh
cd /Users/douglascockerham/Projects/douglascockerham
php artisan gabby:sync-snapshot --no-interaction
```

The command is idempotent. It exits successfully when an identical snapshot is already current and exits nonzero without changing the dashboard when the handoff is invalid.

## Artifact location and atomic publication

The collector publishes to:

`/Users/douglascockerham/Projects/douglascockerham/storage/app/private/gabby/handoff/latest.json`

The collector must create the parent directory with user-only permissions, write a temporary file in that same directory, finish and close the file, set user-only file permissions, and atomically rename it to `latest.json`. It must invoke the Artisan command only after that rename succeeds. It must not write directly into Laravel's accepted-state file.

Laravel validates the handoff and atomically promotes it to its ignored, private last-known-good state:

`storage/app/private/gabby/dashboard-snapshot.json`

## Version 1 contract

The root JSON object requires:

- `schema`: exactly `gabby.snapshot-handoff`
- `version`: integer `1`
- `data_mode`: exactly `live`; sample, fixture, demo, and test data are rejected
- `generated_at`: RFC 3339 with an explicit numeric UTC offset
- `collection`: collection status, completeness, timestamps, and reconciled source-health totals
- `privacy`: all required public-safety and neutrality attestations
- `snapshot`: the normalized public dashboard data

The collection object requires:

```json
{
    "status": "succeeded",
    "complete": true,
    "started_at": "2026-07-30T11:43:00-04:00",
    "completed_at": "2026-07-30T11:46:00-04:00",
    "source_health": {
        "enabled": 34,
        "processed": 354,
        "failures": 0,
        "with_current_items": 15,
        "without_current_items": 19
    }
}
```

All timestamps must be ordered, collection completion must be within 15 minutes of artifact generation, generation cannot be more than five minutes in the future, and a new incoming handoff cannot be older than three hours or older than the accepted snapshot. Source-health counts must reconcile, `processed` must be at least `enabled`, and `failures` must be zero.

The privacy object requires these exact values:

```json
{
    "public_safe": true,
    "contains_raw_posts": false,
    "contains_handles": false,
    "contains_pii": false,
    "politically_neutral": true,
    "contains_political_scoring": false,
    "contains_endorsements": false,
    "contains_donor_targeting": false
}
```

The `snapshot` object uses the same presentation-safe shape as `config/gabby.php`:

- `generated_at`
- `status`: `label`, `state`, `timestamp`, `coverage`
- `priority`: `level`, `confidence`, `title`, `summary`, `source`
- `briefing`: one or more official items with `time`, `label`, `tone`, `title`, `summary`, `source`
- `source_health`: the same five numeric totals as `collection.source_health`, plus presentation `items`
- `weather` and `elections`: official-record `label` and nonempty `items`
- optional `utilities`: validated provider-reported county outage aggregates
- `community`: unverified `label`, `title`, `summary`, `pattern`, `coverage`

`snapshot.generated_at` must exactly equal the root `generated_at`. `snapshot.status.timestamp` must be the matching Eastern display value, formatted like `July 30, 2026 at 11:47 AM EDT`.

The four `snapshot.source_health.items` entries are deterministic and ordered: processed results, sources with current items, sources with no current items, and collection failures. Their displayed values must exactly match the numeric source-health totals.

Priority and briefing sources must retain `Official record` attribution, community reporting must remain explicitly `Unverified`, and the artifact must not contain raw posts, social handles, email addresses, telephone numbers, identity fields, PII, political scoring, endorsements, or donor-targeting fields.

Priority and briefing items may include an optional `source_url`. Laravel accepts only HTTPS URLs on the configured allowlist of official government and public-safety hosts. Unapproved explicit source URLs reject the handoff. URLs found inside summary text are never rendered directly: approved official URLs become descriptive external links, while unapproved URLs are omitted from public presentation. If no approved URL or configured exact source-label mapping exists, the source remains attributed text rather than receiving a fabricated link.

### Optional provider outage aggregates

Verified county-level outage totals belong in `snapshot.utilities`, not in raw
provider payloads:

```json
{
    "label": "Provider-reported aggregate",
    "items": [
        {
            "kind": "county_outage_aggregate",
            "provider": "Duke Energy Florida",
            "scope": "Polk County",
            "customers_without_power": 1161,
            "customers_served": 152464,
            "percent_without_power": 0.76,
            "updated_at": "2026-07-30T17:21:48.305Z",
            "published_etr": "2026-07-30 17:15:00",
            "source": "Official provider aggregate · Duke Energy Florida",
            "source_url": "https://www.duke-energy.com/outagemaps"
        }
    ]
}
```

Laravel reconciles the percentage with the integer totals, requires the
provider update to be no later than handoff generation, validates the official
HTTPS source allowlist, and rejects unsupported provider fields. Account,
customer, address, premise, outage-ID, coordinate, latitude, longitude, raw
provider, and other individual-level fields must never be included. The
provider-published ETR string is retained without inventing a timezone.

## Failure behavior

The command locks locally so overlapping hooks cannot update simultaneously. It validates the complete artifact before writing anything and uses an atomic same-directory rename for promotion. Invalid JSON, wrong versions, failed or partial collection status, stale timestamps, unreconciled source totals, sample data, missing content, privacy failures, or a conflicting artifact at the same timestamp all leave the last-known-good dashboard snapshot unchanged.
