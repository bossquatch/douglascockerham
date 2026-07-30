<x-gabby.shell :snapshot="$snapshot" active="overview">
                <section class="priority-alert" id="overview" aria-labelledby="priority-title">
                    <div class="priority-alert__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M12 3 2.8 20h18.4zM12 9v5M12 17.2v.1"/></svg>
                    </div>
                    <div class="priority-alert__content">
                        <div class="priority-alert__meta">
                            <span>{{ $snapshot['priority']['level'] }}</span>
                            <span>{{ $snapshot['priority']['confidence'] }}</span>
                        </div>
                        <h2 id="priority-title">{{ $snapshot['priority']['title'] }}</h2>
                        <p><x-gabby.safe-summary :parts="$snapshot['priority']['_summary_parts']" /></p>
                        @if ($snapshot['priority']['_source_url'])
                            <x-gabby.source-link class="source-line" :href="$snapshot['priority']['_source_url']" :label="$snapshot['priority']['source']" />
                        @else
                            <span class="source-line">{{ $snapshot['priority']['source'] }}</span>
                        @endif
                    </div>
                </section>

                @foreach ($utilities as $index => $utility)
                    <section class="utility-alert" aria-labelledby="utility-alert-title-{{ $index }}">
                        <div class="utility-alert__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="m13 2-7 12h6l-1 8 7-12h-6z"/></svg>
                        </div>
                        <div class="utility-alert__content">
                            <div class="utility-alert__meta">
                                <span>{{ $utility['status_label'] }}</span>
                                <span>{{ $utility['label'] }}</span>
                            </div>
                            <h2 id="utility-alert-title-{{ $index }}">{{ $utility['title'] }}</h2>
                            <p>{{ $utility['summary'] }}</p>
                            <div class="utility-alert__source">
                                <span>{{ $utility['time_context'] }}</span>
                                @if ($utility['_source_url'])
                                    <x-gabby.source-link :href="$utility['_source_url']" :label="$utility['source']" />
                                @else
                                    <strong>{{ $utility['source'] }}</strong>
                                @endif
                            </div>
                        </div>
                    </section>
                @endforeach

                <div class="gabby-grid">
                    <div class="gabby-primary">
                        <section class="panel briefing-panel" id="briefing" aria-labelledby="briefing-title">
                            <div class="panel-heading">
                                <div>
                                    <h2 id="briefing-title">Briefing timeline</h2>
                                    <p>Official and reviewed public updates</p>
                                </div>
                                <span class="panel-heading__count">{{ count($snapshot['briefing']) }} items</span>
                            </div>

                            <div
                                class="briefing-scroll"
                                role="region"
                                aria-labelledby="briefing-title"
                                aria-describedby="briefing-scroll-help"
                                tabindex="0"
                                data-briefing-scroll
                            >
                                <p class="sr-only" id="briefing-scroll-help">Use the arrow keys, Page Up, or Page Down to review additional briefing items.</p>
                                <ol class="briefing-list">
                                    @foreach ($snapshot['briefing'] as $item)
                                        <li class="briefing-item briefing-item--{{ $item['tone'] }}">
                                            <div class="briefing-item__time">{{ $item['time'] }}</div>
                                            <div class="briefing-item__marker" aria-hidden="true"></div>
                                            <article>
                                                <span class="status-label status-label--{{ $item['tone'] }}">{{ $item['label'] }}</span>
                                                <h3>{{ $item['title'] }}</h3>
                                                <p><x-gabby.safe-summary :parts="$item['_summary_parts']" /></p>
                                                @if ($item['_source_url'])
                                                    <x-gabby.source-link class="source-line" :href="$item['_source_url']" :label="$item['source']" />
                                                @else
                                                    <span class="source-line">{{ $item['source'] }}</span>
                                                @endif
                                            </article>
                                        </li>
                                    @endforeach
                                </ol>
                            </div>
                        </section>

                        <section class="panel locator-panel" id="map" aria-labelledby="map-title">
                            <div class="panel-heading">
                                <div>
                                    <h2 id="map-title">Polk priority locator</h2>
                                    <p>Compact configured-snapshot map preview</p>
                                </div>
                                <span class="status-label status-label--official">Not live tracking</span>
                            </div>

                            <div class="overview-map-canvas" role="group" aria-label="Compact interactive Polk County snapshot map" aria-describedby="overview-map-note">
                                <div
                                    class="gabby-leaflet-map"
                                    data-overview-leaflet-map
                                    data-tile-url="{{ config('gabby.map.tile_url') }}"
                                    data-attribution-label="{{ config('gabby.map.attribution_label') }}"
                                    data-attribution-url="{{ config('gabby.map.attribution_url') }}"
                                    data-max-zoom="{{ config('gabby.map.max_zoom') }}"
                                    aria-label="Compact geographic map of generalized Gabby snapshot records in Polk County"
                                ></div>
                                <div class="map-tile-fallback" hidden data-overview-map-tile-fallback role="status">
                                    <strong>Basemap tiles are unavailable</strong>
                                    <span>The snapshot record list remains available below.</span>
                                </div>
                                <noscript>
                                    <p class="map-noscript">The compact geographic map requires JavaScript. The snapshot record list remains available below.</p>
                                </noscript>
                            </div>

                            @if ($criticalFacilities['enabled'])
                                <div class="overview-facilities-note">
                                    <span class="facility-local-label">Local review layer</span>
                                    <strong>{{ number_format($criticalFacilities['count']) }} supplied critical-facility points</strong>
                                    <small>Reference locations only; no live status, capacity, routing, contacts, or hidden KMZ fields.</small>
                                </div>
                                <script type="application/json" data-overview-facility-data>{!! json_encode(
                                    [
                                        'type' => 'FeatureCollection',
                                        'features' => $criticalFacilities['features'],
                                        'categories' => $criticalFacilities['categories'],
                                    ],
                                    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR,
                                ) !!}</script>
                            @endif

                            <ul class="overview-map-records" aria-label="Compact map record context">
                                @foreach ($map['records'] as $record)
                                    <li
                                        data-overview-map-record
                                        data-map-geometry="{{ json_encode($record['geometry'], JSON_THROW_ON_ERROR) }}"
                                        data-map-visual="{{ $record['visual'] }}"
                                        data-map-label="{{ $record['status_label'] }} {{ $map['categories'][$record['category']] }} record: {{ $record['title'] }}"
                                    >
                                        <i class="overview-map-records__status overview-map-records__status--{{ $record['visual'] }}" aria-hidden="true"></i>
                                        <span>
                                            <strong>{{ $record['title'] }}</strong>
                                            <small>{{ $record['scope'] }} · {{ $record['location_context'] }}</small>
                                        </span>
                                    </li>
                                @endforeach
                            </ul>

                            <div class="overview-map-footer" id="overview-map-note">
                                <p>
                                    County boundary is geographic context. Advisory areas and incident locations remain generalized, approximate, city-level, or explicitly not plotted. Confirm important actions with
                                    <x-gabby.source-link class="gabby-inline-link" :href="$snapshot['_official_links']['transportation']['url']" :label="$snapshot['_official_links']['transportation']['label']" />
                                    and
                                    <x-gabby.source-link class="gabby-inline-link" :href="$snapshot['_official_links']['public_safety']['url']" :label="$snapshot['_official_links']['public_safety']['label']" />.
                                </p>
                                <a class="gabby-external-link" href="{{ route('gabby.map') }}">
                                    View full map
                                    <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                                </a>
                            </div>
                        </section>

                        <section class="panel community-panel" id="community" aria-labelledby="community-title">
                            <div class="panel-heading">
                                <div>
                                    <h2 id="community-title">Community signals</h2>
                                    <p>Aggregated public reporting, separated from official records</p>
                                </div>
                                <span class="status-label status-label--community">Low confidence</span>
                            </div>

                            <div class="community-layout">
                                <article class="community-signal">
                                    <span class="status-label status-label--community">{{ $snapshot['community']['label'] }}</span>
                                    <h3>{{ $snapshot['community']['title'] }}</h3>
                                    <p>{{ $snapshot['community']['summary'] }}</p>
                                    <span class="source-line">{{ $snapshot['community']['coverage'] }}</span>
                                </article>
                                <article class="pattern-status">
                                    <svg aria-hidden="true" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="m8 12 2.5 2.5L16.5 9"/></svg>
                                    <div>
                                        <h3>{{ $snapshot['community']['pattern'] }}</h3>
                                        <p>No coordinated pattern has been identified in this bounded snapshot.</p>
                                    </div>
                                </article>
                            </div>
                        </section>
                    </div>

                    <aside class="gabby-secondary" aria-label="Snapshot summaries">
                        <section class="panel source-panel" id="sources" aria-labelledby="sources-title">
                            <div class="panel-heading">
                                <div>
                                    <h2 id="sources-title">Source health</h2>
                                    <p>Configured public-source digest</p>
                                </div>
                            </div>

                            <p class="source-total">
                                <svg aria-hidden="true" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="m8 12 2.5 2.5L16.5 9"/></svg>
                                <strong>{{ $snapshot['source_health']['enabled'] }}</strong>
                                <span>enabled public sources</span>
                            </p>

                            <dl class="source-list">
                                @foreach ($snapshot['source_health']['items'] as $source)
                                    <div>
                                        <dt>{{ $source['label'] }}</dt>
                                        <dd>{{ $source['status'] }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        </section>

                        <section class="panel summary-panel" aria-labelledby="weather-title">
                            <div class="summary-panel__icon summary-panel__icon--weather" aria-hidden="true">
                                <svg viewBox="0 0 24 24"><path d="M7 18h10a4 4 0 0 0 .5-7.97A6 6 0 0 0 6.1 8.7 4.7 4.7 0 0 0 7 18ZM12 2v2M4.9 4.9l1.4 1.4M19.1 4.9l-1.4 1.4"/></svg>
                            </div>
                            <div>
                                <span class="status-label status-label--official">{{ $snapshot['weather']['label'] }}</span>
                                <h2 id="weather-title">Weather</h2>
                                <ul class="check-list">
                                    @foreach ($snapshot['weather']['items'] as $item)
                                        <li>{{ $item }}</li>
                                    @endforeach
                                </ul>
                                <div class="panel-links" aria-label="Official weather sources">
                                    <x-gabby.source-link :href="$snapshot['_official_links']['weather']['url']" :label="$snapshot['_official_links']['weather']['label']" />
                                    <x-gabby.source-link :href="$snapshot['_official_links']['hurricanes']['url']" :label="$snapshot['_official_links']['hurricanes']['label']" />
                                </div>
                            </div>
                        </section>

                        <section class="panel summary-panel" id="elections" aria-labelledby="elections-title">
                            <div class="summary-panel__icon summary-panel__icon--elections" aria-hidden="true" data-election-summary-icon>
                                <svg viewBox="0 0 24 24">
                                    <path class="election-icon__ballot" d="M5 10.5h14v9H5zM8 10.5 9.2 4.5h5.6l1.2 6M9.5 7.5h5" />
                                    <path class="election-icon__check" d="m9 15 2 2 4-4" />
                                </svg>
                            </div>
                            <div>
                                <span class="status-label status-label--official">{{ $snapshot['elections']['label'] }}</span>
                                <h2 id="elections-title">Elections</h2>
                                <ul class="check-list">
                                    @foreach ($snapshot['elections']['items'] as $item)
                                        <li>{{ $item }}</li>
                                    @endforeach
                                </ul>
                                <div class="panel-links" aria-label="Official election source">
                                    <a class="source-link" href="{{ route('gabby.elections') }}">
                                        <span>Elections Watch</span>
                                        <svg aria-hidden="true" viewBox="0 0 24 24"><path d="m9 5 7 7-7 7"/></svg>
                                    </a>
                                    <x-gabby.source-link :href="$snapshot['_official_links']['elections']['url']" :label="$snapshot['_official_links']['elections']['label']" />
                                </div>
                            </div>
                        </section>

                        <section class="digest-disclaimer" aria-labelledby="disclaimer-title">
                            <svg aria-hidden="true" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 10v6M12 7h.01"/></svg>
                            <div>
                                <h2 id="disclaimer-title">Public-source digest</h2>
                                <p>
                                    Gabby summarizes configured public records and public reporting. It is not an emergency alerting system or a substitute for
                                    <x-gabby.source-link class="gabby-inline-link" :href="$snapshot['_official_links']['public_safety']['url']" label="official notifications" />.
                                </p>
                            </div>
                        </section>
                    </aside>
                </div>

</x-gabby.shell>
