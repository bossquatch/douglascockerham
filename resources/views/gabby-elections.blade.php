<x-gabby.shell
    :snapshot="$snapshot"
    active="elections"
    title="Polk County Elections Watch"
    page-title="Gabby Elections Watch | Polk County"
>
    <section class="elections-watch-header" aria-labelledby="elections-watch-title">
        <div>
            <span class="elections-watch-header__eyebrow">Validated public election records</span>
            <h2 id="elections-watch-title">Elections Watch</h2>
            <p>{{ $elections['scope'] }}</p>
        </div>
        <div class="elections-watch-header__stamp">
            <span>Collector data generated</span>
            <time datetime="{{ $elections['generated_at'] }}">{{ $elections['generated_label'] }}</time>
        </div>
    </section>

    <section class="elections-neutrality" aria-labelledby="elections-neutrality-title">
        <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M12 3 5 6v5c0 4.6 2.9 8 7 10 4.1-2 7-5.4 7-10V6zM12 8v5M12 16h.01"/></svg>
        <div>
            <h2 id="elections-neutrality-title">Neutral public-information digest</h2>
            <p>
                Candidate platform material is labeled as an attributed <strong>Candidate statement</strong>, not an independently verified fact.
                Filed finance is an aggregate snapshot for the stated filing period and source date. Gabby does not rank or endorse candidates,
                infer positions, profile voters, display donor rows, or provide targeting information.
            </p>
        </div>
    </section>

    <section class="election-deadlines" aria-labelledby="election-deadlines-title">
        <div class="election-section-heading">
            <div>
                <h2 id="election-deadlines-title">Upcoming election dates</h2>
                <p>Official Polk County election records; confirm dates with the linked source.</p>
            </div>
            <span>{{ count($elections['deadlines']) }} election records</span>
        </div>

        <div class="election-deadline-grid">
            @foreach ($elections['deadlines'] as $deadline)
                <article>
                    <span class="status-label status-label--official">Official election record</span>
                    <h3>{{ $deadline['event'] }}</h3>
                    <dl>
                        <div>
                            <dt>Election day</dt>
                            <dd><time datetime="{{ $deadline['election_date'] }}">{{ $deadline['election_label'] }}</time></dd>
                        </div>
                        @if ($deadline['registration_label'])
                            <div>
                                <dt>Registration deadline</dt>
                                <dd>{{ $deadline['registration_label'] }}</dd>
                            </div>
                        @endif
                        <div>
                            <dt>Vote-by-mail request deadline</dt>
                            <dd>{{ $deadline['vote_by_mail_label'] }}</dd>
                        </div>
                        @if ($deadline['early_voting'])
                            <div>
                                <dt>Early voting</dt>
                                <dd>{{ $deadline['early_voting'] }}</dd>
                            </div>
                        @endif
                    </dl>
                    <p>
                        <x-gabby.source-link :href="$deadline['source_url']" :label="$deadline['source']" />
                        <span>{{ $deadline['source_date'] }}</span>
                    </p>
                </article>
            @endforeach
        </div>
    </section>

    <section class="election-filter-panel" aria-labelledby="election-filter-title" data-election-filters>
        <div>
            <h2 id="election-filter-title">Race and coverage filters</h2>
            <p>Filters change this local view only; source data and ordering remain unchanged.</p>
        </div>

        <label class="election-race-filter">
            <span>Race or office</span>
            <select data-election-race-filter>
                <option value="all">All races and offices</option>
                @foreach ($elections['races'] as $race)
                    <option value="{{ $race['id'] }}">{{ $race['label'] }}</option>
                @endforeach
            </select>
        </label>

        <fieldset class="election-platform-filter">
            <legend>Platform coverage</legend>
            <div>
                @foreach (['all' => 'All candidates', 'available' => 'Statement available', 'gap' => 'Not yet verified'] as $value => $label)
                    <button
                        type="button"
                        aria-pressed="{{ $value === 'all' ? 'true' : 'false' }}"
                        data-election-platform-filter="{{ $value }}"
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </fieldset>

        <p class="election-filter-results" role="status" aria-live="polite">
            <strong data-election-result-count>{{ $elections['stats']['candidates'] }}</strong>
            <span data-election-result-label>candidates shown</span>
        </p>
    </section>

    <div class="election-watch-stats" aria-label="Elections Watch coverage summary">
        <div><strong>{{ $elections['stats']['races'] }}</strong><span>races</span></div>
        <div><strong>{{ $elections['stats']['candidates'] }}</strong><span>official candidate records</span></div>
        <div><strong>{{ $elections['stats']['platform_available'] }}</strong><span>candidate statements verified to a source</span></div>
        <div><strong>{{ $elections['stats']['platform_gaps'] }}</strong><span>platform coverage gaps</span></div>
        <div><strong>{{ $elections['stats']['finance_available'] }}</strong><span>filed finance aggregates</span></div>
    </div>

    <div class="election-race-list" data-election-race-list>
        @foreach ($elections['races'] as $race)
            <section
                class="election-race"
                aria-labelledby="election-race-{{ $race['id'] }}"
                data-election-race="{{ $race['id'] }}"
            >
                <header>
                    <div>
                        <span class="status-label status-label--official">Official election record</span>
                        <h2 id="election-race-{{ $race['id'] }}">{{ $race['label'] }}</h2>
                        <p>{{ $race['status'] }} · Election date {{ $race['election_label'] }}</p>
                    </div>
                    <div>
                        <x-gabby.source-link :href="$race['source_url']" :label="$race['source']" />
                        <span>{{ $race['source_date'] }}</span>
                    </div>
                </header>

                <div class="candidate-grid">
                    @foreach ($race['candidates'] as $candidate)
                        <article
                            class="candidate-card"
                            data-election-candidate
                            data-election-platform="{{ $candidate['platform']['status'] }}"
                        >
                            <div class="candidate-card__header">
                                <div>
                                    <span class="candidate-card__office">{{ $race['label'] }}</span>
                                    <h3>{{ $candidate['name'] }}</h3>
                                    @if ($candidate['party_ballot_label'])
                                        <span class="candidate-card__ballot-label">Ballot label: {{ $candidate['party_ballot_label'] }}</span>
                                    @endif
                                </div>
                                <span class="status-label status-label--official">Official record</span>
                            </div>

                            <p class="candidate-record-source">
                                <x-gabby.source-link :href="$candidate['record']['source_url']" :label="$candidate['record']['label']" />
                                <span>{{ $candidate['record']['source_date'] }}</span>
                            </p>

                            <section @class([
                                'candidate-platform',
                                'candidate-platform--gap' => $candidate['platform']['status'] === 'gap',
                            ]) aria-labelledby="candidate-platform-{{ $race['id'] }}-{{ $loop->index }}">
                                <div>
                                    <span @class([
                                        'status-label',
                                        'status-label--official' => $candidate['platform']['status'] === 'available',
                                        'status-label--community' => $candidate['platform']['status'] === 'gap',
                                    ])>{{ $candidate['platform']['status_label'] }}</span>
                                    <h4 id="candidate-platform-{{ $race['id'] }}-{{ $loop->index }}">Platform coverage</h4>
                                </div>

                                @if ($candidate['platform']['status'] === 'available')
                                    <ul>
                                        @foreach ($candidate['platform']['summary'] as $statement)
                                            <li>{{ $statement }}</li>
                                        @endforeach
                                    </ul>
                                    <p>{{ $candidate['platform']['note'] }}</p>
                                    <p class="candidate-platform__source">
                                        <x-gabby.source-link :href="$candidate['platform']['source_url']" :label="$candidate['platform']['source']" />
                                        <span>{{ $candidate['platform']['source_date'] }}</span>
                                    </p>
                                @else
                                    <p class="candidate-platform__gap">{{ $candidate['platform']['note'] }}</p>
                                    <span class="candidate-platform__date">Coverage checked {{ $candidate['platform']['source_date'] }}</span>
                                @endif
                            </section>

                            <section class="candidate-finance" aria-labelledby="candidate-finance-{{ $race['id'] }}-{{ $loop->index }}">
                                <div>
                                    <span class="status-label status-label--official">
                                        {{ $candidate['finance'] ? $candidate['finance']['label'] : 'Finance not included' }}
                                    </span>
                                    <h4 id="candidate-finance-{{ $race['id'] }}-{{ $loop->index }}">Campaign finance</h4>
                                </div>

                                @if ($candidate['finance'])
                                    <dl>
                                        <div>
                                            <dt>Filed contributions</dt>
                                            <dd>${{ number_format($candidate['finance']['monetary_contributions'], 2) }}</dd>
                                        </div>
                                        <div>
                                            <dt>In-kind</dt>
                                            <dd>${{ number_format($candidate['finance']['in_kind_contributions'], 2) }}</dd>
                                        </div>
                                        <div>
                                            <dt>Expenditures</dt>
                                            <dd>${{ number_format($candidate['finance']['expenditures'], 2) }}</dd>
                                        </div>
                                        <div>
                                            <dt>Report / filed</dt>
                                            <dd>{{ $candidate['finance']['latest_report'] }} · {{ $candidate['finance']['filed_date'] }}</dd>
                                        </div>
                                        <div>
                                            <dt>Period through</dt>
                                            <dd>{{ $candidate['finance']['period_through'] }}</dd>
                                        </div>
                                    </dl>
                                    <p>{{ $candidate['finance']['note'] }} No individual donor rows are displayed.</p>
                                    <p class="candidate-finance__source">
                                        <x-gabby.source-link :href="$candidate['finance']['source_url']" :label="$candidate['finance']['source']" />
                                    </p>
                                @else
                                    <p class="candidate-finance__gap">No validated aggregate finance record is included in this collector snapshot.</p>
                                @endif
                            </section>
                        </article>
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>

    <section class="election-filter-empty" hidden data-election-empty>
        <h2>No candidates match these filters</h2>
        <p>Choose another race or platform-coverage setting.</p>
    </section>
</x-gabby.shell>
