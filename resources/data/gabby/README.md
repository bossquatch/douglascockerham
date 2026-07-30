# Gabby map data

## Loaded boundary

`polk_county.json` is the project-local copy used by `/gabby/map`.

- Original supplied path:
  `/Users/douglascockerham/Projects/geo-json/polk_county.json`
- SHA-256:
  `1b8fa4e63c778926e1ab74ceb90e7726aa629feefd87f0a35736fae4970f3019`
- GeoJSON: one `Polygon` feature, one closed ring, 99 coordinates
- Extent: `-82.106206,27.643238` to `-81.132457,28.361868`
- Supplied attributes: `NAME=Polk`, `STATE=12`, `COUNTY=105`,
  `GEO_ID=0500000US12105`

The supplied file contains no explicit publisher, license, or source URL.
Those attributes are preserved without adding an unsupported attribution.

## Local-review critical facilities

`critical_facilities.json` is a minimized conversion of the user-supplied
`PC_Crit_Fac.kmz`.

- Original supplied path:
  `/Users/douglascockerham/Downloads/Polk County Critical Facilities/PC_Crit_Fac.kmz`
- Original KMZ SHA-256:
  `42d43cb5d0469d8563facfa5e1cd758f2e8f6dd7a1c671dea07ca7b6b0d6cb74`
- Project-local minimized JSON SHA-256:
  `59479f77c385eab46a09a3d51481e34afca8b1b3245e1587780138d76b62e88a`
- Source geometry: 2,339 KML 2.2 WGS84 point placemarks
- Public-map contract: 2,339 point features in nine normalized categories

The conversion retains only the visible placemark label, `FACILITY_TYPE`,
normalized category, and supplied point coordinate. It discards description
HTML, addresses, contacts, notes, parcel/system identifiers, facility status,
capacity fields, attachments, and every other source field. Rebuild the
minimized asset with:

```sh
php tools/gabby-convert-critical-facilities.php input.kml resources/data/gabby/critical_facilities.json
```

The public presentation is controlled by
`GABBY_CRITICAL_FACILITIES_ENABLED`. Its environment-aware default is `true`
only when `APP_ENV=local`; production and every other environment default to
`false`. Before any live deployment, explicitly set:

```dotenv
GABBY_CRITICAL_FACILITIES_ENABLED=false
```

Then clear Laravel's generated configuration cache. Do not enable the layer
outside local review until the location-level dataset has received explicit
deployment approval.

## Future reference only

`reference/counties.json` is a dormant, UTF-8-normalized copy of the second
supplied file. It is not imported by JavaScript, rendered by Leaflet, or
included in the normal Gabby page payload.

- Original supplied path:
  `/Users/douglascockerham/Projects/geo-json/counties.json`
- Original supplied SHA-256:
  `ac500b2beb49b6418166b6b312db856b10ba0dcf1ce6b58dc98f206704adaa08`
- Project-local normalized SHA-256:
  `fb51f2f5345fa40222f1ae4ae7d5608c75da630d8cbae359a954d2bcf08ac12b`
- GeoJSON: 3,221 `Polygon`/`MultiPolygon` county-equivalent features
- Florida subset: 67 features where `properties.STATE == "12"`
- All 3,498 polygon rings are closed and coordinate values are in valid
  longitude/latitude ranges.

Despite its supplied description, the file is a nationwide county dataset,
not a Florida-only dataset. Any future Florida view must filter to
`properties.STATE == "12"` before use. The file carries Census-style county
attributes but no explicit publisher, license, or source URL. The source file
also contained legacy ISO-8859-1 characters and was not strict UTF-8 JSON; the
project-local copy was transcoded to UTF-8 without changing its features,
geometry, or properties.

## Elections Watch source

`elections_local_2026.json` is an exact project-local copy of the collector's
structured `gabby.elections-local` version 1 artifact.

- Supplied path:
  `/Users/douglascockerham/Documents/Codex/2026-07-30/situational-awareness-gatherer/config/elections_local_2026.json`
- Source and project-local SHA-256:
  `ecc57d2b00260c58eceb6cecb1030e9f4a22708ee26f23bd75cbea3e11a49bb2`

The file is never rendered directly. `GabbyElectionsService` applies a strict
schema, field, provenance, date, amount, and URL-host allowlist before
presentation. See `docs/GABBY_ELECTIONS_WATCH.md`.
