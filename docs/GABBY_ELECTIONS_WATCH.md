# Gabby Elections Watch

The public `/gabby/elections` view reads a versioned, structured collector
artifact through `GabbyElectionsService`.

## Current local source

- Supplied collector file:
  `/Users/douglascockerham/Documents/Codex/2026-07-30/situational-awareness-gatherer/config/elections_local_2026.json`
- Project-local copy:
  `resources/data/gabby/elections_local_2026.json`
- SHA-256 (source and project copy):
  `ecc57d2b00260c58eceb6cecb1030e9f4a22708ee26f23bd75cbea3e11a49bb2`
- Contract: `gabby.elections-local`, version `1`
- Generated: `2026-07-30T18:06:20Z`

The current artifact contains 2 official election-date records, 7 races, and
15 official candidate records. Candidate-authored platform sources were
validated for 2 candidates; 13 candidates retain an explicit `Not yet
verified` coverage gap. Aggregate filed finance is available for 4 candidates.
The presented data references 10 unique approved source URLs.

## Validation and privacy boundary

The service rejects unsupported schema/version values, missing or extra fields,
future generation timestamps, invalid dates, duplicate race IDs, unsupported
platform/finance statuses, unsafe text, non-HTTPS or unapproved hosts, negative
or implausibly large finance amounts, and any nested fields outside the
documented contract. This strict unknown-field rejection prevents a future
artifact from silently introducing donor rows, addresses, contacts, social
accounts, or targeting fields.

Only candidate-level aggregate monetary contributions, in-kind contributions,
and expenditures are presented, together with the filing report, filed date,
period-through date, and official source. Candidate platform summaries are
shown only when the artifact contains an approved candidate-authored source;
they are labeled `Candidate statement` and are not treated as independently
verified facts.

## Future collector handoff

The default path is the project-local copy above. A future atomic collector
handoff can point Laravel at a stable local artifact without changing the
dashboard:

```dotenv
GABBY_ELECTIONS_PATH=/absolute/local/path/elections_local_2026.json
```

Every replacement must remain schema version 1 and pass the same validation
before it is rendered. A failed or unsupported replacement raises a validation
error instead of partially presenting unreviewed fields. The collector should
write a complete temporary file and atomically rename it into the configured
path.
