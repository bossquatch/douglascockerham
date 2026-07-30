<x-gabby.shell
    :snapshot="$snapshot"
    active="briefing"
    title="Polk County Operational Briefing"
    page-title="Gabby Briefing | Polk County Situational Awareness"
>
    <section class="briefing-page-header" aria-labelledby="operational-briefing-title">
        <div>
            <span class="briefing-page-header__eyebrow">Detailed operational view</span>
            <h2 id="operational-briefing-title">Operational briefing</h2>
            <p>Active verified priorities appear first, followed by resolved and informational records from the current configured snapshot.</p>
        </div>
        <div class="briefing-page-header__stamp">
            <span>Snapshot as of</span>
            <time datetime="{{ $snapshot['generated_at'] }}">{{ $snapshot['status']['timestamp'] }}</time>
        </div>
    </section>

    <section class="briefing-filter-panel" aria-labelledby="briefing-filters-title" data-briefing-filters>
        <div class="briefing-filter-panel__heading">
            <div>
                <h2 id="briefing-filters-title">Filter the briefing</h2>
                <p>Filters change only this local view and do not alter the source snapshot.</p>
            </div>
            <p class="briefing-results" role="status" aria-live="polite">
                <strong data-briefing-result-count>{{ count($briefing['items']) }}</strong>
                <span data-briefing-result-label>items shown</span>
            </p>
        </div>

        <div class="briefing-filter-controls">
            <fieldset class="briefing-status-filter">
                <legend>Status</legend>
                <div class="briefing-filter-buttons">
                    @foreach (['all' => 'All', 'active' => 'Active', 'resolved' => 'Resolved', 'informational' => 'Informational'] as $value => $label)
                        <button
                            type="button"
                            aria-pressed="{{ $value === 'all' ? 'true' : 'false' }}"
                            data-briefing-status-filter="{{ $value }}"
                        >
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </fieldset>

            <label class="briefing-category-filter">
                <span>Category</span>
                <select data-briefing-category-filter>
                    @foreach ($briefing['categories'] as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </label>
        </div>
    </section>

    <section class="operational-briefing" aria-labelledby="briefing-items-title">
        <h2 class="sr-only" id="briefing-items-title">Current operational briefing items</h2>

        <ol class="operational-briefing__list" data-operational-briefing-list>
            @foreach ($briefing['items'] as $item)
                <li
                    class="operational-item operational-item--{{ $item['tone'] }}"
                    data-briefing-item
                    data-briefing-status="{{ $item['status'] }}"
                    data-briefing-category="{{ $item['category'] }}"
                >
                    <article>
                        <div class="operational-item__rail" aria-hidden="true">
                            <span></span>
                        </div>

                        <div class="operational-item__body">
                            <div class="operational-item__topline">
                                <div class="operational-item__labels">
                                    <span class="status-label status-label--{{ $item['tone'] }}">{{ $item['status_label'] }}</span>
                                    @foreach ($item['confidence_labels'] as $confidence)
                                        <span @class([
                                            'confidence-label',
                                            'confidence-label--community' => $confidence === 'Unverified community signal',
                                        ])>{{ $confidence }}</span>
                                    @endforeach
                                    <span class="category-label">{{ $briefing['categories'][$item['category']] }}</span>
                                </div>

                                <div class="operational-item__time">
                                    <strong>{{ $item['time_context'] }}</strong>
                                    <span>{{ $item['snapshot_context'] }}</span>
                                </div>
                            </div>

                            <h3>{{ $item['title'] }}</h3>
                            <p class="operational-item__summary"><x-gabby.safe-summary :parts="$item['_summary_parts']" /></p>

                            <div class="operational-item__source">
                                <span>Source</span>
                                @if ($item['_source_url'])
                                    <x-gabby.source-link :href="$item['_source_url']" :label="$item['source']" />
                                @else
                                    <strong>{{ $item['source'] }}</strong>
                                @endif
                            </div>
                        </div>
                    </article>
                </li>
            @endforeach
        </ol>

        <div class="briefing-empty" hidden data-briefing-empty>
            <h3>No items match these filters</h3>
            <p>Choose another status or category to review the current configured snapshot.</p>
        </div>
    </section>

    <section class="briefing-disclosure" aria-labelledby="briefing-disclosure-title">
        <svg aria-hidden="true" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 10v6M12 7h.01"/></svg>
        <div>
            <h2 id="briefing-disclosure-title">Coverage and confirmation</h2>
            <p>
                This is a configured public-source digest, not exhaustive or real-time alerting. Reported coverage and unverified community signals are separated from official records. Important actions require confirmation through
                <x-gabby.source-link class="gabby-inline-link" :href="$snapshot['_official_links']['public_safety']['url']" label="official agency notifications" />.
            </p>
        </div>
    </section>
</x-gabby.shell>
