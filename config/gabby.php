<?php

return [
    'handoff' => [
        'schema' => 'gabby.snapshot-handoff',
        'version' => 1,
        'path' => storage_path('app/private/gabby/handoff/latest.json'),
        'state_path' => storage_path('app/private/gabby/dashboard-snapshot.json'),
        'lock_path' => storage_path('app/private/gabby/sync.lock'),
        'timezone' => 'America/New_York',
        'max_age_minutes' => 180,
        'max_future_minutes' => 5,
        'max_generation_lag_minutes' => 15,
        'max_bytes' => 524288,
    ],

    'map' => [
        /*
         * OpenStreetMap's public tile service is suitable for this local
         * development view, not as a default production tile plan. A future
         * approved provider can be selected without changing the map UI.
         */
        'tile_url' => env('GABBY_MAP_TILE_URL', 'https://tile.openstreetmap.org/{z}/{x}/{y}.png'),
        'attribution_label' => env('GABBY_MAP_ATTRIBUTION_LABEL', 'OpenStreetMap contributors'),
        'attribution_url' => env('GABBY_MAP_ATTRIBUTION_URL', 'https://www.openstreetmap.org/copyright'),
        'max_zoom' => (int) env('GABBY_MAP_MAX_ZOOM', 19),
    ],

    /*
     * Critical facilities contain point locations supplied for local review.
     * The environment-aware default enables them only when APP_ENV is local.
     * Production and every other environment default to disabled; an explicit
     * true value is required to publish this layer elsewhere.
     */
    'critical_facilities' => [
        'enabled' => filter_var(
            env('GABBY_CRITICAL_FACILITIES_ENABLED', env('APP_ENV') === 'local'),
            FILTER_VALIDATE_BOOL,
        ),
        'path' => resource_path('data/gabby/critical_facilities.json'),
        'categories' => [
            'emergency-response' => ['label' => 'Emergency response', 'color' => '#bd321e'],
            'health-care' => ['label' => 'Health and care', 'color' => '#9d3f84'],
            'education' => ['label' => 'Education', 'color' => '#6b4fb3'],
            'transport-communications' => ['label' => 'Transportation and communications', 'color' => '#185a98'],
            'energy-utilities' => ['label' => 'Energy and utilities', 'color' => '#9a6500'],
            'government-public-safety' => ['label' => 'Government and public safety', 'color' => '#42566b'],
            'community-services' => ['label' => 'Community services', 'color' => '#187a55'],
            'housing-lodging' => ['label' => 'Housing and lodging', 'color' => '#8a4e2d'],
            'waste-environmental' => ['label' => 'Waste and environmental', 'color' => '#4f6f38'],
        ],
    ],

    'elections' => [
        /*
         * This versioned local path is a validation boundary. A later atomic
         * collector handoff can target another path without changing the view.
         */
        'path' => env(
            'GABBY_ELECTIONS_PATH',
            resource_path('data/gabby/elections_local_2026.json'),
        ),
    ],

    'links' => [
        'allowed_hosts' => [
            'duke-energy.com',
            'fl511.com',
            'www.fl511.com',
            'lakewalesfl.gov',
            'www.lakewalesfl.gov',
            'mywinterhaven.com',
            'www.mywinterhaven.com',
            'news.fl511.com',
            'nhc.noaa.gov',
            'www.nhc.noaa.gov',
            'polkelections.gov',
            'www.polkelections.gov',
            'polkfl.gov',
            'www.polkfl.gov',
            'weather.gov',
            'www.duke-energy.com',
            'www.kellyforpolk.com',
            'www.weather.gov',
            'www.voterfocus.com',
            'electomararroyo.com',
        ],
        'sources' => [
            'Official provider aggregate · Duke Energy Florida' => 'https://www.duke-energy.com/outagemaps',
            'Official record · NOAA/NWS active Florida alerts' => 'https://www.weather.gov/tbw/',
            'Official record · National Weather Service' => 'https://www.weather.gov/tbw/',
            'Official record · National Weather Service and National Hurricane Center' => 'https://www.weather.gov/tbw/',
            'Official record · National Hurricane Center' => 'https://www.nhc.noaa.gov/',
            'Official record · National Hurricane Center Atlantic products' => 'https://www.nhc.noaa.gov/',
            'Official record · City of Lake Wales' => 'https://www.lakewalesfl.gov/m/NewsFlash/Home/Detail/2121',
            'Official record · City of Lake Wales news' => 'https://www.lakewalesfl.gov/m/NewsFlash/Home/Detail/2121',
            'Official record · City of Winter Haven Utilities' => 'https://www.mywinterhaven.com/AlertCenter.aspx',
            'Official record · Winter Haven Water Department alerts' => 'https://www.mywinterhaven.com/AlertCenter.aspx',
            'Official record · Florida 511 official newsroom' => 'https://news.fl511.com/',
            'Official record · Polk County Supervisor of Elections' => 'https://www.polkelections.gov/',
        ],
        'official' => [
            'weather' => [
                'label' => 'National Weather Service Tampa Bay',
                'url' => 'https://www.weather.gov/tbw/',
            ],
            'hurricanes' => [
                'label' => 'National Hurricane Center',
                'url' => 'https://www.nhc.noaa.gov/',
            ],
            'transportation' => [
                'label' => 'Florida 511 alerts',
                'url' => 'https://www.fl511.com/List/Alerts',
            ],
            'elections' => [
                'label' => 'Polk County Supervisor of Elections',
                'url' => 'https://www.polkelections.gov/',
            ],
            'public_safety' => [
                'label' => 'Polk County Emergency Management',
                'url' => 'https://www.polkfl.gov/public-safety/emergency-management/',
            ],
            'utilities' => [
                'label' => 'Duke Energy official outage map',
                'url' => 'https://www.duke-energy.com/outagemaps',
            ],
        ],
        'url_labels' => [
            'https://www.weather.gov/safety/flood' => 'National Weather Service flood safety guidance',
            'https://www.lakewalesfl.gov/DocumentCenter/View/14829/SR-60-NOTICE-CSX-Closure' => 'Official SR 60 detour notice',
        ],
        'bare_domains' => [
            'FL511.com' => [
                'label' => 'FL511.com',
                'url' => 'https://www.fl511.com/',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Public Gabby snapshot
    |--------------------------------------------------------------------------
    |
    | This is a point-in-time, public-safe seed for the first dashboard release.
    | A future importer may replace GabbySnapshotService's config-backed
    | implementation while preserving this normalized, presentation-safe shape.
    |
    */
    'snapshot' => [
        'generated_at' => '2026-07-30T11:47:00-04:00',
        'status' => [
            'label' => 'Current snapshot',
            'state' => 'Public-source digest available',
            'timestamp' => 'July 30, 2026 at 11:47 AM EDT',
            'coverage' => 'Configured snapshot — not continuous monitoring',
        ],
        'priority' => [
            'level' => 'High priority · Through 1:30 PM EDT',
            'confidence' => 'Verified',
            'title' => 'Flood Advisory for Polk and Hillsborough counties',
            'summary' => 'Minor flooding is possible in low-lying and poor-drainage areas, and water may cover roads. Two to three inches had fallen, with another one to two inches possible. Polk-area locations include Lakeland, Lakeland Linder Airport, Medulla, Mulberry, Willow Oak, Winston, Lakeland Highlands, and Crystal Lake. Avoid flooded roads.',
            'source' => 'Official record · National Weather Service · Valid through 1:30 PM EDT',
            'source_url' => 'https://www.weather.gov/tbw/',
        ],
        'briefing' => [
            [
                'time' => 'Through 1:30 PM EDT',
                'label' => 'Verified · NWS',
                'tone' => 'urgent',
                'title' => 'Flood Advisory in effect for Polk and Hillsborough counties.',
                'summary' => 'Minor flooding is possible in low-lying and poor-drainage areas. Two to three inches had fallen, with another one to two inches possible. Water may cover roads; avoid flooded roads.',
                'source' => 'Official record · National Weather Service',
                'source_url' => 'https://www.weather.gov/tbw/',
            ],
            [
                'time' => 'Current',
                'label' => 'Verified',
                'tone' => 'urgent',
                'title' => 'SR 60 fully closed at the CSX crossing between Lake Wales and Bartow.',
                'summary' => 'Lake Wales advises detours.',
                'source' => 'Official record · City of Lake Wales',
                'source_url' => 'https://www.lakewalesfl.gov/m/NewsFlash/Home/Detail/2121',
            ],
            [
                'time' => 'Earlier',
                'label' => 'Resolved',
                'tone' => 'resolved',
                'title' => 'Precautionary boil-water notice rescinded.',
                'summary' => 'Affected addresses: 1800–1830 3rd Street Southeast, Winter Haven.',
                'source' => 'Official record · City of Winter Haven Utilities',
                'source_url' => 'https://www.mywinterhaven.com/AlertCenter.aspx',
            ],
            [
                'time' => 'Current',
                'label' => 'Official record',
                'tone' => 'official',
                'title' => 'No active tropical cyclone.',
                'summary' => 'No Atlantic tropical-cyclone formation is expected during the next seven days.',
                'source' => 'Official record · National Hurricane Center',
                'source_url' => 'https://www.nhc.noaa.gov/',
            ],
            [
                'time' => 'Upcoming',
                'label' => 'Official record',
                'tone' => 'official',
                'title' => 'Early voting is scheduled for August 8–15.',
                'summary' => 'Vote-by-mail ballots are being delivered.',
                'source' => 'Official record · Polk County Supervisor of Elections',
                'source_url' => 'https://www.polkelections.gov/',
            ],
        ],
        'source_health' => [
            'enabled' => 34,
            'processed' => 354,
            'failures' => 0,
            'with_current_items' => 15,
            'without_current_items' => 19,
            'items' => [
                ['label' => '354 source results', 'status' => 'Processed'],
                ['label' => '15 public sources', 'status' => 'Current items'],
                ['label' => '19 public sources', 'status' => 'No current items'],
                ['label' => 'Collection failures', 'status' => '0'],
            ],
        ],
        'weather' => [
            'label' => 'Official record',
            'items' => [
                'Flood Advisory through 1:30 PM EDT',
                'Two to three inches had fallen; another one to two inches is possible',
                'No active tropical cyclones',
                'No Atlantic formation expected during the next seven days',
            ],
        ],
        'elections' => [
            'label' => 'Official record',
            'items' => [
                'Early voting Aug 8–15',
                'Vote-by-mail ballots being delivered',
            ],
        ],
        'community' => [
            'label' => 'Unverified community signal',
            'title' => 'Special-needs tabletop exercise awareness lead',
            'summary' => 'One low-confidence, unverified X community-awareness lead references Polk Emergency Management’s special-needs tabletop exercise. No raw posts, identities, handles, or personal data are displayed.',
            'pattern' => 'No recurring general or election misinformation pattern found.',
            'coverage' => 'Bounded snapshot · No new official X Polk operational emergency; Bluesky had no current eligible item',
        ],
    ],
];
