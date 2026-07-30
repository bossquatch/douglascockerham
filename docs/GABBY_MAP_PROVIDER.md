# Gabby map provider

The local `/gabby/map` view uses Leaflet with the public OpenStreetMap tile
service by default. It requests only the tiles visible in the current map view,
shows required attribution, does not request user location, and does not add
analytics or tracking.

The public OpenStreetMap tile service is not a production traffic-map plan.
Before public deployment, select an approved tile provider or self-hosted plan,
confirm its usage and attribution terms, and configure:

```dotenv
GABBY_MAP_TILE_URL=https://approved-provider.example/{z}/{x}/{y}.png
GABBY_MAP_ATTRIBUTION_LABEL="Approved map provider"
GABBY_MAP_ATTRIBUTION_URL=https://approved-provider.example/attribution
GABBY_MAP_MAX_ZOOM=19
```

The configured tile URL and attribution URL must use HTTPS. If a Content
Security Policy is added later, its `img-src` directive must explicitly allow
the selected tile origin. Traffic, routing, geolocation, and user tracking are
intentionally not part of this implementation.

The Flood Advisory overlay is a generalized area, the SR 60 closure is an
approximate operational corridor, and the resolved Winter Haven notice is a
city-level marker. None are authoritative GIS geometry.

The Polk County outline is loaded from
`resources/data/gabby/polk_county.json`. The supplied GeoJSON attributes
identify Polk County, Florida (`STATE=12`, `COUNTY=105`, Census
`GEO_ID=0500000US12105`); no source URL was present in the file. The boundary
is a separate geographic context layer and does not replace or imply the shape
of any advisory or incident.

The Overview page uses the same basemap, boundary, and snapshot-derived overlay
rules in a compact preview. The full `/gabby/map` page owns filtering and item
details. Provider outage aggregates remain county-level text records and are
never turned into inferred outage points, polygons, customer locations, or
routes.

## Critical-facilities deployment gate

The user-supplied critical-facilities point layer is available for local review
only. Laravel loads and sends its minimized data only when
`GABBY_CRITICAL_FACILITIES_ENABLED=true`. The configuration default enables it
only for `APP_ENV=local`; production and all other environments default to
disabled.

Before any public deployment, set
`GABBY_CRITICAL_FACILITIES_ENABLED=false` and clear Laravel's generated
configuration cache. Enabling the layer in a non-local environment requires an
explicit environment override and must follow a separate location-data review.
When disabled, the facility payload, controls, legend entry, list, and map
points are absent from both `/gabby` and `/gabby/map`.
