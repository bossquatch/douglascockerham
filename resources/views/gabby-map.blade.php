<x-gabby.shell
    :snapshot="$snapshot"
    active="map"
    title="Polk County Operational Map"
    page-title="Gabby Map | Polk County Situational Awareness"
>
    <details class="map-snapshot-banner">
        <summary>
            <svg aria-hidden="true" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 10v6M12 7h.01"/></svg>
            <span>
                <strong>Configured snapshot map — not live tracking</strong>
                <small>Generalized public-scale operational context</small>
            </span>
            <span class="map-snapshot-banner__action">Safety and precision notes</span>
        </summary>
        <div class="map-snapshot-banner__body">
            <p>This geographic basemap shows current verified records at an appropriate public scale. Overlays are generalized and are not official GIS warning polygons, a traffic feed, a route planner, or location tracking.</p>
        </div>
    </details>

    <div class="map-workspace" data-gabby-map>
        <section class="map-filter-panel" aria-labelledby="map-filters-title">
            <div>
                <h2 id="map-filters-title">Map filters</h2>
                <p>Change the visible configured-snapshot records.</p>
            </div>

            <fieldset class="map-status-filter">
                <legend>Status</legend>
                <div>
                    @foreach (['all' => 'All', 'active' => 'Active', 'resolved' => 'Resolved'] as $value => $label)
                        <button
                            type="button"
                            aria-pressed="{{ $value === 'all' ? 'true' : 'false' }}"
                            data-map-status-filter="{{ $value }}"
                        >
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </fieldset>

            <label class="map-category-filter">
                <span>Category</span>
                <select data-map-category-filter>
                    @foreach ($map['categories'] as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </label>

            <p class="map-result-count" role="status" aria-live="polite">
                <strong data-map-result-count>{{ count($map['records']) }}</strong>
                <span data-map-result-label>records shown</span>
            </p>
        </section>

        <section class="operational-map-panel" aria-labelledby="operational-map-title">
            <div class="operational-map-heading">
                <div>
                    <h2 id="operational-map-title">Polk County locator</h2>
                    <p id="map-precision-note">Areas and locations are deliberately generalized or approximate. Use the official source links for authoritative detail.</p>
                </div>
                <span>Snapshot {{ $snapshot['status']['timestamp'] }}</span>
            </div>

            <div class="operational-map-canvas" role="group" aria-label="Interactive geographic Polk County locator" aria-describedby="map-precision-note">
                <div
                    class="gabby-leaflet-map"
                    data-leaflet-map
                    data-tile-url="{{ config('gabby.map.tile_url') }}"
                    data-attribution-label="{{ config('gabby.map.attribution_label') }}"
                    data-attribution-url="{{ config('gabby.map.attribution_url') }}"
                    data-max-zoom="{{ config('gabby.map.max_zoom') }}"
                    aria-label="Geographic map of generalized Gabby snapshot records in Polk County"
                ></div>
                @foreach ($map['records'] as $record)
                    @if ($record['visual'] === 'power')
                        <button
                            type="button"
                            class="map-aggregate-callout"
                            aria-label="Select provider-reported county outage aggregate: {{ $record['title'] }}"
                            aria-pressed="false"
                            data-map-item-control="{{ $record['id'] }}"
                            data-map-status="{{ $record['status'] }}"
                            data-map-category="{{ $record['category'] }}"
                        >
                            <svg aria-hidden="true" viewBox="0 0 24 24"><path d="m13 2-7 12h6l-1 8 7-12h-6z"/></svg>
                            <span>
                                <strong>{{ $record['title'] }}</strong>
                                <small>{{ $record['location_context'] }}</small>
                            </span>
                        </button>
                    @endif
                @endforeach
                <div class="map-no-results" hidden data-map-no-results>
                    <strong>No mapped records match these filters</strong>
                    <span>Choose another status or category.</span>
                </div>
                <div class="map-tile-fallback" hidden data-map-tile-fallback role="status">
                    <strong>Basemap tiles are unavailable</strong>
                    <span>The verified overlays and accessible record list remain available below.</span>
                </div>
                <noscript>
                    <p class="map-noscript">The geographic map requires JavaScript. All mapped records remain available in the accessible list below.</p>
                </noscript>
            </div>

            <div class="map-legend" aria-labelledby="map-legend-title">
                <h3 id="map-legend-title">Legend</h3>
                <ul>
                    <li><i class="map-legend__boundary" aria-hidden="true"></i><span>Polk County boundary <small>Supplied GeoJSON county outline</small></span></li>
                    <li><i class="map-legend__advisory" aria-hidden="true"></i><span>Verified advisory area <small>Broad county-level treatment</small></span></li>
                    <li><i class="map-legend__power" aria-hidden="true"></i><span>Provider outage aggregate <small>County total · locations not plotted</small></span></li>
                    <li><i class="map-legend__closure" aria-hidden="true"></i><span>Active road closure <small>Approximate corridor</small></span></li>
                    <li><i class="map-legend__resolved" aria-hidden="true"></i><span>Resolved utility notice <small>City-level marker</small></span></li>
                    <li><i class="map-legend__community" aria-hidden="true"></i><span>Unverified community awareness <small>Legend only · not plotted</small></span></li>
                    @if ($criticalFacilities['enabled'])
                        <li><i class="map-legend__facilities" aria-hidden="true"></i><span>Critical facilities <small>Supplied local-review point layer · not live status</small></span></li>
                    @endif
                </ul>
                <details class="map-metadata-disclosure">
                    <summary>Map data, precision, and provider notes</summary>
                    <div>
                        <p class="map-boundary-note" id="map-boundary-note">
                            <strong>County boundary:</strong> project-local copy of the user-supplied GeoJSON. Its source attributes identify Polk County, Florida, as Census GEO_ID 0500000US12105; the supplied file contains no source URL.
                        </p>
                        <p class="map-provider-note" id="map-provider-note">
                            Local-development basemap using
                            <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener noreferrer">OpenStreetMap data and attribution<span class="sr-only"> (opens in a new tab)</span></a>.
                            The public OSM tile service is not the production traffic-map plan; an approved provider and usage plan must be selected before public deployment.
                        </p>
                    </div>
                </details>
            </div>
        </section>

        <aside class="map-detail-panel" aria-labelledby="map-detail-panel-title">
            <div class="map-detail-panel__heading">
                <h2 id="map-detail-panel-title">Selected item details</h2>
                <p>Selection changes locally; the source snapshot is unchanged.</p>
            </div>

            <div class="map-detail-panel__content" aria-live="polite">
                @foreach ($map['records'] as $index => $record)
                    <article data-map-detail="{{ $record['id'] }}" @if ($index !== 0) hidden @endif>
                        <div class="map-detail-title">
                            <span class="map-detail-icon map-detail-icon--{{ $record['visual'] }}" aria-hidden="true">
                                @if ($record['visual'] === 'weather')
                                    <svg viewBox="0 0 24 24"><path d="M7 16h10a4 4 0 0 0 .4-7.98A6 6 0 0 0 6.2 6.8 4.7 4.7 0 0 0 7 16ZM9 19l-1 2M13 19l-1 2M17 19l-1 2"/></svg>
                                @elseif ($record['visual'] === 'power')
                                    <svg viewBox="0 0 24 24"><path d="m13 2-7 12h6l-1 8 7-12h-6z"/></svg>
                                @elseif ($record['visual'] === 'roads')
                                    <svg viewBox="0 0 24 24"><path d="M3 12h18M7 8l-4 4 4 4M17 8l4 4-4 4"/></svg>
                                @else
                                    <svg viewBox="0 0 24 24"><path d="M7 3h10v6a5 5 0 0 1-10 0zM5 3h14M9 18h6M12 14v4"/></svg>
                                @endif
                            </span>
                            <div>
                                <h3>{{ $record['title'] }}</h3>
                                <div class="map-detail-labels">
                                    <span class="status-label status-label--{{ $record['status'] === 'active' ? 'urgent' : 'resolved' }}">{{ $record['status_label'] }}</span>
                                    @foreach ($record['confidence_labels'] as $confidence)
                                        <span class="confidence-label">{{ $confidence }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <dl class="map-detail-facts">
                            <div>
                                <dt>Category</dt>
                                <dd>{{ $map['categories'][$record['category']] }}</dd>
                            </div>
                            <div>
                                <dt>Time context</dt>
                                <dd>{{ $record['time_context'] }}</dd>
                            </div>
                            <div>
                                <dt>Public scope</dt>
                                <dd>{{ $record['scope'] }}</dd>
                            </div>
                            <div>
                                <dt>Location treatment</dt>
                                <dd>{{ $record['location_context'] }}</dd>
                            </div>
                        </dl>

                        <div class="map-detail-summary">
                            <h4>Operational note</h4>
                            <p>{{ $record['map_summary'] }}</p>
                        </div>

                        <div class="map-detail-source">
                            <span>Source</span>
                            @if ($record['source_url'])
                                <x-gabby.source-link :href="$record['source_url']" :label="$record['source']" />
                            @else
                                <strong>{{ $record['source'] }}</strong>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </aside>

        @if ($criticalFacilities['enabled'])
            <section class="facility-layer-controls" aria-labelledby="facility-layer-controls-title" data-critical-facilities>
                <div>
                    <span class="facility-local-label">Local review layer</span>
                    <h2 id="facility-layer-controls-title">Polk County critical facilities</h2>
                    <p>
                        {{ number_format($criticalFacilities['count']) }} supplied point locations.
                        This reference layer is not live status and must remain disabled for production until explicitly approved.
                    </p>
                </div>
                <label class="facility-layer-toggle">
                    <input type="checkbox" checked data-facility-layer-toggle>
                    <span>Show facilities on map</span>
                </label>
                <label class="facility-category-filter">
                    <span>Facility category</span>
                    <select data-facility-category-filter>
                        <option value="all">All facility categories</option>
                        @foreach ($criticalFacilities['categories'] as $value => $category)
                            <option value="{{ $value }}">{{ $category['label'] }} ({{ number_format($category['count']) }})</option>
                        @endforeach
                    </select>
                </label>
                <p class="facility-layer-count" role="status" aria-live="polite">
                    <strong data-facility-visible-count>{{ number_format($criticalFacilities['count']) }}</strong>
                    <span>facilities in layer</span>
                </p>
            </section>
        @endif

        <section class="map-list-panel" aria-labelledby="map-list-title">
            <div class="map-list-panel__heading">
                <div>
                    <h2 id="map-list-title">Accessible list alternative</h2>
                    <p>Use Tab or the arrow keys to review visible records; press Enter or Space to select.</p>
                </div>
                <span><strong data-map-list-count>{{ count($map['records']) }}</strong> visible</span>
            </div>

            <ul data-map-record-list>
                @foreach ($map['records'] as $index => $record)
                    <li
                        data-map-record="{{ $record['id'] }}"
                        data-map-status="{{ $record['status'] }}"
                        data-map-category="{{ $record['category'] }}"
                        data-map-visual="{{ $record['visual'] }}"
                        data-map-geometry="{{ json_encode($record['geometry'], JSON_THROW_ON_ERROR) }}"
                        data-map-label="{{ $record['status_label'] }} {{ $map['categories'][$record['category']] }} record: {{ $record['title'] }}"
                    >
                        <button
                            type="button"
                            @class(['is-selected' => $index === 0])
                            aria-pressed="{{ $index === 0 ? 'true' : 'false' }}"
                            data-map-item-control="{{ $record['id'] }}"
                            data-map-list-control
                        >
                            <span class="map-list-status map-list-status--{{ $record['status'] }}" aria-hidden="true"></span>
                            <span class="map-list-copy">
                                <strong>{{ $record['title'] }}</strong>
                                <span>{{ $record['status_label'] }} · {{ $map['categories'][$record['category']] }} · {{ $record['scope'] }}</span>
                            </span>
                            <svg aria-hidden="true" viewBox="0 0 24 24"><path d="m9 5 7 7-7 7"/></svg>
                        </button>
                    </li>
                @endforeach
            </ul>
        </section>

        @if ($criticalFacilities['enabled'])
            <section class="facility-browser" aria-labelledby="facility-browser-title">
                <div class="facility-browser__heading">
                    <div>
                        <span class="facility-local-label">Local review only</span>
                        <h2 id="facility-browser-title">Critical-facility list alternative</h2>
                        <p>Search, filter, and select supplied facility points without using the map. No addresses, contacts, notes, identifiers, or hidden KMZ fields are included.</p>
                    </div>
                    <span><strong data-facility-list-total>{{ number_format($criticalFacilities['count']) }}</strong> matching</span>
                </div>

                <div class="facility-browser__controls">
                    <label>
                        <span>Search facility labels</span>
                        <input type="search" autocomplete="off" data-facility-search placeholder="Search supplied labels">
                    </label>
                    <p id="facility-list-help">Results are paged in groups of 25. Select a result for map and detail context.</p>
                </div>

                <div class="facility-browser__layout">
                    <div>
                        <ul class="facility-list" data-facility-list aria-describedby="facility-list-help"></ul>
                        <nav class="facility-pagination" aria-label="Critical-facility result pages">
                            <button type="button" data-facility-previous disabled>Previous</button>
                            <span data-facility-page-status>Page 1</span>
                            <button type="button" data-facility-next>Next</button>
                        </nav>
                    </div>
                    <article class="facility-detail" data-facility-detail aria-live="polite">
                        <span class="facility-local-label">Selected supplied point</span>
                        <h3 data-facility-detail-label>Select a facility from the list</h3>
                        <dl>
                            <div>
                                <dt>Category</dt>
                                <dd data-facility-detail-category>—</dd>
                            </div>
                            <div>
                                <dt>Facility type</dt>
                                <dd data-facility-detail-type>—</dd>
                            </div>
                            <div>
                                <dt>Location treatment</dt>
                                <dd>Point supplied in the authorized KMZ; no geocoding or added precision.</dd>
                            </div>
                        </dl>
                        <p>Reference location only. This layer does not indicate facility availability, operating condition, emergency status, capacity, or routing.</p>
                    </article>
                </div>

                <script type="application/json" data-facility-data>{!! json_encode(
                    [
                        'type' => 'FeatureCollection',
                        'features' => $criticalFacilities['features'],
                        'categories' => $criticalFacilities['categories'],
                    ],
                    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR,
                ) !!}</script>
            </section>
        @endif

        <section class="map-community-note" aria-labelledby="map-community-title">
            <span class="map-community-note__marker" aria-hidden="true"></span>
            <div>
                <h2 id="map-community-title">{{ $map['community']['label'] }} · Not plotted</h2>
                <p>{{ $map['community']['summary'] }}</p>
            </div>
        </section>
    </div>
</x-gabby.shell>
