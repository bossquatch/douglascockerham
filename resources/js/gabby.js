import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import polkCountyBoundary from '../data/gabby/polk_county.json';

const shell = document.querySelector('[data-gabby-shell]');
const navToggle = document.querySelector('[data-nav-toggle]');
const themeToggle = document.querySelector('[data-theme-toggle]');
const briefingScroll = document.querySelector('[data-briefing-scroll]');
const briefingFilters = document.querySelector('[data-briefing-filters]');
const gabbyMap = document.querySelector('[data-gabby-map]');
const navLinks = document.querySelectorAll('.gabby-nav a');
const appearanceStorageKey = 'flux.appearance';

const applyAppearance = (appearance) => {
    if (window.Flux?.applyAppearance) {
        window.Flux.applyAppearance(appearance);

        return;
    }

    document.documentElement.classList.toggle(
        'dark',
        appearance === 'dark'
            || (appearance === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches),
    );
};

const storedAppearance = () => {
    try {
        return window.localStorage.getItem(appearanceStorageKey);
    } catch {
        return null;
    }
};

if (themeToggle) {
    const toggleAppearance = () => {
        const nextAppearance = document.documentElement.classList.contains('dark') ? 'light' : 'dark';

        applyAppearance(nextAppearance);
    };

    themeToggle.addEventListener('click', toggleAppearance);

    themeToggle.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            toggleAppearance();
        }
    });

    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
        if (!storedAppearance()) {
            applyAppearance('system');
        }
    });

    window.addEventListener('storage', (event) => {
        if (event.key === appearanceStorageKey) {
            applyAppearance(event.newValue || 'system');
        }
    });
}

if (briefingScroll) {
    const mobileBriefing = window.matchMedia('(max-width: 820px)');
    const syncBriefingTabStop = () => {
        briefingScroll.tabIndex = mobileBriefing.matches ? -1 : 0;
    };

    syncBriefingTabStop();
    mobileBriefing.addEventListener('change', syncBriefingTabStop);

    briefingScroll.addEventListener('keydown', (event) => {
        if (event.target !== briefingScroll || mobileBriefing.matches) {
            return;
        }

        const pageStep = briefingScroll.clientHeight * 0.85;
        const movements = {
            ArrowDown: 48,
            ArrowUp: -48,
            PageDown: pageStep,
            PageUp: -pageStep,
        };

        if (event.key in movements) {
            event.preventDefault();
            briefingScroll.scrollBy({ top: movements[event.key], behavior: 'smooth' });
        } else if (event.key === 'Home') {
            event.preventDefault();
            briefingScroll.scrollTo({ top: 0, behavior: 'smooth' });
        } else if (event.key === 'End') {
            event.preventDefault();
            briefingScroll.scrollTo({ top: briefingScroll.scrollHeight, behavior: 'smooth' });
        }
    });
}

if (briefingFilters) {
    const statusButtons = Array.from(briefingFilters.querySelectorAll('[data-briefing-status-filter]'));
    const categorySelect = briefingFilters.querySelector('[data-briefing-category-filter]');
    const briefingItems = Array.from(document.querySelectorAll('[data-briefing-item]'));
    const resultCount = briefingFilters.querySelector('[data-briefing-result-count]');
    const resultLabel = briefingFilters.querySelector('[data-briefing-result-label]');
    const emptyState = document.querySelector('[data-briefing-empty]');
    let selectedStatus = 'all';

    const applyBriefingFilters = () => {
        const selectedCategory = categorySelect?.value || 'all';
        let visibleItems = 0;

        briefingItems.forEach((item) => {
            const statusMatches = selectedStatus === 'all' || item.dataset.briefingStatus === selectedStatus;
            const categoryMatches = selectedCategory === 'all' || item.dataset.briefingCategory === selectedCategory;
            const visible = statusMatches && categoryMatches;

            item.hidden = ! visible;
            visibleItems += visible ? 1 : 0;
        });

        if (resultCount) {
            resultCount.textContent = String(visibleItems);
        }

        if (resultLabel) {
            resultLabel.textContent = visibleItems === 1 ? 'item shown' : 'items shown';
        }

        if (emptyState) {
            emptyState.hidden = visibleItems !== 0;
        }
    };

    const selectStatus = (button) => {
        selectedStatus = button.dataset.briefingStatusFilter || 'all';

        statusButtons.forEach((item) => {
            item.setAttribute('aria-pressed', String(item === button));
        });

        applyBriefingFilters();
    };

    statusButtons.forEach((button) => {
        button.addEventListener('click', () => {
            selectStatus(button);
        });

        button.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                selectStatus(button);
            }
        });
    });

    categorySelect?.addEventListener('change', applyBriefingFilters);
}

const electionFilters = document.querySelector('[data-election-filters]');

if (electionFilters) {
    const raceSelect = electionFilters.querySelector('[data-election-race-filter]');
    const platformButtons = Array.from(electionFilters.querySelectorAll('[data-election-platform-filter]'));
    const races = Array.from(document.querySelectorAll('[data-election-race]'));
    const resultCount = electionFilters.querySelector('[data-election-result-count]');
    const resultLabel = electionFilters.querySelector('[data-election-result-label]');
    const emptyState = document.querySelector('[data-election-empty]');
    let selectedPlatform = 'all';

    const applyElectionFilters = () => {
        const selectedRace = raceSelect?.value || 'all';
        let visibleCandidates = 0;

        races.forEach((race) => {
            const raceMatches = selectedRace === 'all' || race.dataset.electionRace === selectedRace;
            let visibleInRace = 0;

            race.querySelectorAll('[data-election-candidate]').forEach((candidate) => {
                const platformMatches = selectedPlatform === 'all'
                    || candidate.dataset.electionPlatform === selectedPlatform;
                const visible = raceMatches && platformMatches;

                candidate.hidden = ! visible;
                visibleInRace += visible ? 1 : 0;
                visibleCandidates += visible ? 1 : 0;
            });

            race.hidden = visibleInRace === 0;
        });

        if (resultCount) {
            resultCount.textContent = String(visibleCandidates);
        }

        if (resultLabel) {
            resultLabel.textContent = visibleCandidates === 1 ? 'candidate shown' : 'candidates shown';
        }

        if (emptyState) {
            emptyState.hidden = visibleCandidates !== 0;
        }
    };

    const selectPlatform = (button) => {
        selectedPlatform = button.dataset.electionPlatformFilter || 'all';

        platformButtons.forEach((item) => {
            item.setAttribute('aria-pressed', String(item === button));
        });

        applyElectionFilters();
    };

    platformButtons.forEach((button) => {
        button.addEventListener('click', () => selectPlatform(button));
        button.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                selectPlatform(button);
            }
        });
    });

    raceSelect?.addEventListener('change', applyElectionFilters);
    applyElectionFilters();
}

if (gabbyMap) {
    const statusButtons = Array.from(gabbyMap.querySelectorAll('[data-map-status-filter]'));
    const categorySelect = gabbyMap.querySelector('[data-map-category-filter]');
    const records = Array.from(gabbyMap.querySelectorAll('[data-map-record]'));
    const controls = Array.from(gabbyMap.querySelectorAll('[data-map-item-control]'));
    const listControls = Array.from(gabbyMap.querySelectorAll('[data-map-list-control]'));
    const details = Array.from(gabbyMap.querySelectorAll('[data-map-detail]'));
    const resultCount = gabbyMap.querySelector('[data-map-result-count]');
    const resultLabel = gabbyMap.querySelector('[data-map-result-label]');
    const listCount = gabbyMap.querySelector('[data-map-list-count]');
    const noResults = gabbyMap.querySelector('[data-map-no-results]');
    const mapElement = gabbyMap.querySelector('[data-leaflet-map]');
    const tileFallback = gabbyMap.querySelector('[data-map-tile-fallback]');
    const facilityDataElement = gabbyMap.querySelector('[data-facility-data]');
    const facilityLayerToggle = gabbyMap.querySelector('[data-facility-layer-toggle]');
    const facilityCategoryFilter = gabbyMap.querySelector('[data-facility-category-filter]');
    const facilitySearch = gabbyMap.querySelector('[data-facility-search]');
    const facilityList = gabbyMap.querySelector('[data-facility-list]');
    const facilityListTotal = gabbyMap.querySelector('[data-facility-list-total]');
    const facilityVisibleCount = gabbyMap.querySelector('[data-facility-visible-count]');
    const facilityPrevious = gabbyMap.querySelector('[data-facility-previous]');
    const facilityNext = gabbyMap.querySelector('[data-facility-next]');
    const facilityPageStatus = gabbyMap.querySelector('[data-facility-page-status]');
    const facilityDetailLabel = gabbyMap.querySelector('[data-facility-detail-label]');
    const facilityDetailCategory = gabbyMap.querySelector('[data-facility-detail-category]');
    const facilityDetailType = gabbyMap.querySelector('[data-facility-detail-type]');
    let selectedStatus = 'all';
    let selectedId = records[0]?.dataset.mapRecord || null;
    let syncLayerSelection = () => {};
    let syncLayerVisibility = () => {};
    let syncFacilityMap = () => {};
    let selectFacilityOnMap = () => {};
    let selectedFacilityId = null;
    let facilityPage = 0;
    const facilityPageSize = 25;
    let facilityPayload = null;

    if (facilityDataElement) {
        try {
            const parsed = JSON.parse(facilityDataElement.textContent || '{}');

            if (
                parsed?.type !== 'FeatureCollection'
                || ! Array.isArray(parsed.features)
                || typeof parsed.categories !== 'object'
                || parsed.categories === null
            ) {
                throw new Error('Invalid critical-facilities payload.');
            }

            facilityPayload = parsed;
        } catch {
            facilityDataElement.closest('.facility-browser')?.setAttribute('hidden', '');
            gabbyMap.querySelector('[data-critical-facilities]')?.setAttribute('hidden', '');
        }
    }

    const facilityMatches = () => {
        if (! facilityPayload) {
            return [];
        }

        const selectedCategory = facilityCategoryFilter?.value || 'all';
        const search = (facilitySearch?.value || '').trim().toLocaleLowerCase();

        return facilityPayload.features.filter((feature) => {
            const properties = feature?.properties;
            const coordinates = feature?.geometry?.coordinates;

            if (
                feature?.type !== 'Feature'
                || feature?.geometry?.type !== 'Point'
                || ! Array.isArray(coordinates)
                || coordinates.length !== 2
                || ! coordinates.every((coordinate) => Number.isFinite(coordinate))
                || typeof properties?.id !== 'string'
                || typeof properties?.label !== 'string'
                || typeof properties?.category !== 'string'
                || typeof properties?.type !== 'string'
                || ! facilityPayload.categories[properties.category]
            ) {
                return false;
            }

            const categoryMatches = selectedCategory === 'all' || properties.category === selectedCategory;
            const searchMatches = search === ''
                || properties.label.toLocaleLowerCase().includes(search)
                || properties.type.toLocaleLowerCase().includes(search);

            return categoryMatches && searchMatches;
        });
    };

    const selectFacility = (feature, focusMap = false) => {
        if (! feature || ! facilityPayload) {
            return;
        }

        selectedFacilityId = feature.properties.id;
        const category = facilityPayload.categories[feature.properties.category];

        if (facilityDetailLabel) {
            facilityDetailLabel.textContent = feature.properties.label;
        }

        if (facilityDetailCategory) {
            facilityDetailCategory.textContent = category.label;
        }

        if (facilityDetailType) {
            facilityDetailType.textContent = feature.properties.type;
        }

        facilityList?.querySelectorAll('[data-facility-id]').forEach((button) => {
            const selected = button.dataset.facilityId === selectedFacilityId;

            button.classList.toggle('is-selected', selected);
            button.setAttribute('aria-pressed', String(selected));
        });

        selectFacilityOnMap(feature, focusMap);
    };

    const renderFacilityList = () => {
        const matches = facilityMatches();
        const pageCount = Math.max(1, Math.ceil(matches.length / facilityPageSize));

        facilityPage = Math.min(facilityPage, pageCount - 1);

        if (facilityListTotal) {
            facilityListTotal.textContent = matches.length.toLocaleString();
        }

        if (facilityVisibleCount) {
            facilityVisibleCount.textContent = facilityLayerToggle?.checked === false
                ? '0'
                : matches.length.toLocaleString();
        }

        if (facilityPageStatus) {
            facilityPageStatus.textContent = matches.length === 0
                ? 'No matching facilities'
                : `Page ${facilityPage + 1} of ${pageCount}`;
        }

        if (facilityPrevious) {
            facilityPrevious.disabled = facilityPage === 0;
        }

        if (facilityNext) {
            facilityNext.disabled = facilityPage >= pageCount - 1 || matches.length === 0;
        }

        if (facilityList) {
            facilityList.replaceChildren();
            const fragment = document.createDocumentFragment();
            const start = facilityPage * facilityPageSize;

            matches.slice(start, start + facilityPageSize).forEach((feature) => {
                const category = facilityPayload.categories[feature.properties.category];
                const item = document.createElement('li');
                const button = document.createElement('button');
                const marker = document.createElement('i');
                const copy = document.createElement('span');
                const title = document.createElement('strong');
                const meta = document.createElement('small');

                button.type = 'button';
                button.dataset.facilityId = feature.properties.id;
                button.setAttribute('aria-pressed', String(feature.properties.id === selectedFacilityId));
                button.classList.toggle('is-selected', feature.properties.id === selectedFacilityId);
                marker.setAttribute('aria-hidden', 'true');
                marker.style.setProperty('--facility-color', category.color);
                title.textContent = feature.properties.label;
                meta.textContent = `${category.label} · ${feature.properties.type}`;
                copy.append(title, meta);
                button.append(marker, copy);
                button.addEventListener('click', () => selectFacility(feature, true));
                item.append(button);
                fragment.append(item);
            });

            if (matches.length === 0) {
                const empty = document.createElement('li');

                empty.className = 'facility-list__empty';
                empty.textContent = 'No supplied facilities match this search and category.';
                fragment.append(empty);
            }

            facilityList.append(fragment);
        }

        if (! selectedFacilityId || ! matches.some((feature) => feature.properties.id === selectedFacilityId)) {
            selectedFacilityId = matches[0]?.properties.id || null;

            if (matches[0]) {
                selectFacility(matches[0]);
            }
        }

        syncFacilityMap(matches, facilityLayerToggle?.checked !== false);
    };

    const selectMapItem = (id) => {
        selectedId = id;

        controls.forEach((control) => {
            const selected = control.dataset.mapItemControl === id;

            control.classList.toggle('is-selected', selected);
            control.setAttribute('aria-pressed', String(selected));
        });

        details.forEach((detail) => {
            detail.hidden = detail.dataset.mapDetail !== id;
        });

        syncLayerSelection(id);
    };

    const applyMapFilters = () => {
        const category = categorySelect?.value || 'all';
        const visibleIds = [];

        records.forEach((record) => {
            const statusMatches = selectedStatus === 'all' || record.dataset.mapStatus === selectedStatus;
            const categoryMatches = category === 'all' || record.dataset.mapCategory === category;
            const visible = statusMatches && categoryMatches;

            record.hidden = ! visible;

            if (visible) {
                visibleIds.push(record.dataset.mapRecord);
            }
        });

        controls.forEach((control) => {
            const visible = visibleIds.includes(control.dataset.mapItemControl);

            control.hidden = ! visible;
        });

        syncLayerVisibility(visibleIds);

        if (resultCount) {
            resultCount.textContent = String(visibleIds.length);
        }

        if (resultLabel) {
            resultLabel.textContent = visibleIds.length === 1 ? 'record shown' : 'records shown';
        }

        if (listCount) {
            listCount.textContent = String(visibleIds.length);
        }

        if (noResults) {
            noResults.hidden = visibleIds.length !== 0;
        }

        if (visibleIds.length === 0) {
            selectedId = null;
            details.forEach((detail) => {
                detail.hidden = true;
            });
            syncLayerSelection(null);

            return;
        }

        if (! selectedId || ! visibleIds.includes(selectedId)) {
            selectMapItem(visibleIds[0]);
        }
    };

    const selectStatus = (button) => {
        selectedStatus = button.dataset.mapStatusFilter || 'all';

        statusButtons.forEach((item) => {
            item.setAttribute('aria-pressed', String(item === button));
        });

        applyMapFilters();
    };

    statusButtons.forEach((button) => {
        button.addEventListener('click', () => {
            selectStatus(button);
        });

        button.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                selectStatus(button);
            }
        });
    });

    categorySelect?.addEventListener('change', applyMapFilters);

    controls.forEach((control) => {
        control.addEventListener('click', () => {
            selectMapItem(control.dataset.mapItemControl);
        });

        control.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                selectMapItem(control.dataset.mapItemControl);
            }
        });
    });

    listControls.forEach((control) => {
        control.addEventListener('keydown', (event) => {
            if (! ['ArrowDown', 'ArrowUp', 'Home', 'End'].includes(event.key)) {
                return;
            }

            const visibleControls = listControls.filter((item) => ! item.closest('[data-map-record]')?.hidden);
            const currentIndex = visibleControls.indexOf(control);
            const nextIndex = event.key === 'Home'
                ? 0
                : event.key === 'End'
                    ? visibleControls.length - 1
                    : event.key === 'ArrowDown'
                        ? Math.min(currentIndex + 1, visibleControls.length - 1)
                        : Math.max(currentIndex - 1, 0);
            const nextControl = visibleControls[nextIndex];

            if (nextControl) {
                event.preventDefault();
                nextControl.focus();
                selectMapItem(nextControl.dataset.mapItemControl);
            }
        });
    });

    if (mapElement) {
        const parseCoordinate = (value) => {
            if (
                ! Array.isArray(value)
                || value.length !== 2
                || ! value.every((coordinate) => Number.isFinite(coordinate))
                || Math.abs(value[0]) > 90
                || Math.abs(value[1]) > 180
            ) {
                throw new Error('Invalid public map coordinate.');
            }

            return value;
        };

        const parseCoordinates = (values) => {
            if (! Array.isArray(values) || values.length < 2) {
                throw new Error('Invalid public map geometry.');
            }

            return values.map(parseCoordinate);
        };

        const safeHttpsUrl = (value) => {
            const url = new URL(value);

            if (url.protocol !== 'https:') {
                throw new Error('Gabby map providers must use HTTPS.');
            }

            return url;
        };

        try {
            const tileTemplate = mapElement.dataset.tileUrl || '';

            if (! ['{z}', '{x}', '{y}'].every((token) => tileTemplate.includes(token))) {
                throw new Error('The Gabby tile template is incomplete.');
            }

            safeHttpsUrl(
                tileTemplate
                    .replace('{s}', 'a')
                    .replace('{z}', '0')
                    .replace('{x}', '0')
                    .replace('{y}', '0'),
            );

            const attributionUrl = safeHttpsUrl(mapElement.dataset.attributionUrl || '');
            const attributionLink = document.createElement('a');
            attributionLink.href = attributionUrl.toString();
            attributionLink.target = '_blank';
            attributionLink.rel = 'noopener noreferrer';
            attributionLink.textContent = mapElement.dataset.attributionLabel || 'Map contributors';

            const configuredMaxZoom = Number.parseInt(mapElement.dataset.maxZoom || '19', 10);
            const maxZoom = Number.isFinite(configuredMaxZoom)
                ? Math.min(Math.max(configuredMaxZoom, 10), 22)
                : 19;
            const compactMobileMap = window.matchMedia('(max-width: 640px)').matches;
            const map = L.map(mapElement, {
                attributionControl: true,
                boxZoom: ! compactMobileMap,
                doubleClickZoom: ! compactMobileMap,
                dragging: ! compactMobileMap,
                keyboard: true,
                maxBounds: [
                    [27.1, -82.5],
                    [28.8, -80.8],
                ],
                maxBoundsViscosity: 0.7,
                scrollWheelZoom: false,
                touchZoom: ! compactMobileMap,
                zoomControl: true,
            }).setView([27.95, -81.72], 9);
            const tileLayer = L.tileLayer(tileTemplate, {
                attribution: `&copy; ${attributionLink.outerHTML}`,
                detectRetina: false,
                keepBuffer: 1,
                maxZoom,
                minZoom: 7,
                updateWhenIdle: true,
            }).addTo(map);
            const boundaryFeature = polkCountyBoundary?.features?.[0];
            const boundaryGeometryType = boundaryFeature?.geometry?.type;

            if (
                polkCountyBoundary?.type !== 'FeatureCollection'
                || polkCountyBoundary.features.length !== 1
                || ! ['Polygon', 'MultiPolygon'].includes(boundaryGeometryType)
                || boundaryFeature?.properties?.STATE !== '12'
                || boundaryFeature?.properties?.COUNTY !== '105'
                || boundaryFeature?.properties?.NAME !== 'Polk'
            ) {
                throw new Error('The project-local Polk County boundary is invalid.');
            }

            const countyBoundaryPane = map.createPane('countyBoundary');
            countyBoundaryPane.style.zIndex = '320';
            countyBoundaryPane.style.pointerEvents = 'none';

            const countyBoundaryLayer = L.geoJSON(polkCountyBoundary, {
                interactive: false,
                pane: 'countyBoundary',
                style: {
                    className: 'gabby-county-boundary',
                    color: '#334f68',
                    fillColor: '#185a98',
                    fillOpacity: 0.04,
                    opacity: 0.95,
                    weight: 2,
                },
            }).addTo(map);

            map.fitBounds(countyBoundaryLayer.getBounds(), {
                maxZoom: 9,
                padding: [20, 20],
            });

            const operationalLayers = L.layerGroup().addTo(map);
            const layersById = new Map();
            let tileErrors = 0;

            tileLayer.on('tileerror', () => {
                tileErrors += 1;

                if (tileErrors >= 2 && tileFallback) {
                    tileFallback.hidden = false;
                }
            });

            tileLayer.on('load', () => {
                tileErrors = 0;

                if (tileFallback) {
                    tileFallback.hidden = true;
                }
            });

            const markerIcon = (visual) => L.divIcon({
                className: `gabby-map-marker gabby-map-marker--${visual}`,
                html: '<span aria-hidden="true"></span>',
                iconAnchor: [18, 18],
                iconSize: [36, 36],
            });

            records.forEach((record) => {
                const geometry = JSON.parse(record.dataset.mapGeometry || '{}');
                const id = record.dataset.mapRecord;
                const visual = record.dataset.mapVisual;
                const label = record.dataset.mapLabel || 'Select map record';
                let focus;
                let shape;

                if (geometry.type === 'generalized_bounds') {
                    const bounds = parseCoordinates(geometry.bounds);
                    focus = parseCoordinate(geometry.focus);
                    shape = L.rectangle(bounds, {
                        className: 'gabby-map-shape gabby-map-shape--weather',
                        color: '#185a98',
                        dashArray: '10 7',
                        fillColor: '#2b7ccc',
                        fillOpacity: 0.2,
                        interactive: true,
                        opacity: 0.95,
                        weight: 3,
                    });
                } else if (geometry.type === 'approximate_corridor') {
                    const path = parseCoordinates(geometry.path);
                    focus = parseCoordinate(geometry.focus);
                    shape = L.polyline(path, {
                        className: 'gabby-map-shape gabby-map-shape--roads',
                        color: '#bd321e',
                        dashArray: '12 7',
                        interactive: true,
                        opacity: 0.95,
                        weight: 7,
                    });
                } else if (geometry.type === 'city_point') {
                    focus = parseCoordinate(geometry.point);
                } else if (geometry.type === 'county_aggregate') {
                    return;
                } else {
                    throw new Error('Unsupported public map geometry.');
                }

                const marker = L.marker(focus, {
                    alt: label,
                    icon: markerIcon(visual),
                    keyboard: true,
                    riseOnHover: true,
                    title: label,
                });
                const group = L.layerGroup(shape ? [shape, marker] : [marker]);

                shape?.on('click', () => selectMapItem(id));
                marker.on('click', () => selectMapItem(id));
                group.addTo(operationalLayers);
                layersById.set(id, { group, marker, shape, visual });
            });

            syncLayerSelection = (id) => {
                layersById.forEach((layer, layerId) => {
                    const selected = layerId === id;
                    const markerElement = layer.marker.getElement();

                    markerElement?.classList.toggle('is-selected', selected);
                    markerElement?.setAttribute('aria-pressed', String(selected));

                    if (layer.shape) {
                        layer.shape.setStyle({
                            fillOpacity: layer.visual === 'weather' ? (selected ? 0.3 : 0.2) : undefined,
                            opacity: selected ? 1 : 0.9,
                            weight: layer.visual === 'roads'
                                ? (selected ? 10 : 7)
                                : (selected ? 5 : 3),
                        });
                    }
                });
            };

            syncLayerVisibility = (visibleIds) => {
                layersById.forEach((layer, id) => {
                    if (visibleIds.includes(id)) {
                        if (! operationalLayers.hasLayer(layer.group)) {
                            operationalLayers.addLayer(layer.group);
                        }
                    } else if (operationalLayers.hasLayer(layer.group)) {
                        operationalLayers.removeLayer(layer.group);
                    }
                });

                window.requestAnimationFrame(() => syncLayerSelection(selectedId));
            };

            if (facilityPayload) {
                const facilityPane = map.createPane('criticalFacilities');
                facilityPane.style.zIndex = '360';
                const facilityRenderer = L.canvas({ padding: 0.4, pane: 'criticalFacilities' });
                const facilityLayers = L.layerGroup().addTo(map);
                let facilityPoints = null;
                let selectedFacilityMarker = null;

                const facilityPointStyle = (feature) => {
                    const category = facilityPayload.categories[feature.properties.category];

                    return {
                        color: '#ffffff',
                        fillColor: category.color,
                        fillOpacity: 0.82,
                        opacity: 0.95,
                        pane: 'criticalFacilities',
                        radius: 4,
                        renderer: facilityRenderer,
                        weight: 1,
                    };
                };

                syncFacilityMap = (features, visible) => {
                    facilityLayers.clearLayers();
                    facilityPoints = null;
                    selectedFacilityMarker = null;

                    if (! visible || features.length === 0) {
                        return;
                    }

                    facilityPoints = L.geoJSON({
                        type: 'FeatureCollection',
                        features,
                    }, {
                        pointToLayer: (feature, latlng) => L.circleMarker(latlng, facilityPointStyle(feature)),
                        onEachFeature: (feature, layer) => {
                            layer.on('click', () => selectFacility(feature));
                            layer.bindTooltip(feature.properties.label, {
                                direction: 'top',
                                opacity: 0.95,
                            });
                        },
                    }).addTo(facilityLayers);

                    const selected = features.find((feature) => feature.properties.id === selectedFacilityId);

                    if (selected) {
                        selectFacilityOnMap(selected, false);
                    }
                };

                selectFacilityOnMap = (feature, focusMap) => {
                    selectedFacilityMarker?.removeFrom(facilityLayers);

                    if (facilityLayerToggle?.checked === false) {
                        selectedFacilityMarker = null;

                        return;
                    }

                    const [longitude, latitude] = feature.geometry.coordinates;
                    const category = facilityPayload.categories[feature.properties.category];

                    selectedFacilityMarker = L.circleMarker([latitude, longitude], {
                        color: '#ffffff',
                        fillColor: category.color,
                        fillOpacity: 1,
                        interactive: false,
                        opacity: 1,
                        pane: 'criticalFacilities',
                        radius: 9,
                        renderer: facilityRenderer,
                        weight: 3,
                    }).addTo(facilityLayers);

                    if (focusMap) {
                        map.panTo([latitude, longitude], { animate: true });
                    }
                };
            }

            const refreshMapSize = () => map.invalidateSize({ pan: false });
            const resizeObserver = new ResizeObserver(refreshMapSize);

            resizeObserver.observe(mapElement);
            window.requestAnimationFrame(refreshMapSize);
            syncLayerSelection(selectedId);
        } catch {
            mapElement.classList.add('is-unavailable');
            mapElement.setAttribute('aria-label', 'Geographic basemap unavailable');

            if (tileFallback) {
                tileFallback.hidden = false;
            }
        }
    }

    applyMapFilters();

    facilityLayerToggle?.addEventListener('change', renderFacilityList);
    facilityCategoryFilter?.addEventListener('change', () => {
        facilityPage = 0;
        renderFacilityList();
    });
    facilitySearch?.addEventListener('input', () => {
        facilityPage = 0;
        renderFacilityList();
    });
    facilityPrevious?.addEventListener('click', () => {
        facilityPage = Math.max(0, facilityPage - 1);
        renderFacilityList();
        facilityList?.querySelector('button')?.focus();
    });
    facilityNext?.addEventListener('click', () => {
        facilityPage += 1;
        renderFacilityList();
        facilityList?.querySelector('button')?.focus();
    });

    if (facilityPayload) {
        renderFacilityList();
    }
}

const overviewMapElement = document.querySelector('[data-overview-leaflet-map]');

if (overviewMapElement) {
    const overviewRecords = Array.from(document.querySelectorAll('[data-overview-map-record]'));
    const overviewTileFallback = document.querySelector('[data-overview-map-tile-fallback]');
    const parseCoordinate = (value) => {
        if (
            ! Array.isArray(value)
            || value.length !== 2
            || ! value.every((coordinate) => Number.isFinite(coordinate))
            || Math.abs(value[0]) > 90
            || Math.abs(value[1]) > 180
        ) {
            throw new Error('Invalid overview map coordinate.');
        }

        return value;
    };
    const parseCoordinates = (values) => {
        if (! Array.isArray(values) || values.length < 2) {
            throw new Error('Invalid overview map geometry.');
        }

        return values.map(parseCoordinate);
    };

    try {
        const tileTemplate = overviewMapElement.dataset.tileUrl || '';

        if (! ['{z}', '{x}', '{y}'].every((token) => tileTemplate.includes(token))) {
            throw new Error('The Gabby overview tile template is incomplete.');
        }

        const tilePreviewUrl = new URL(
            tileTemplate
                .replace('{s}', 'a')
                .replace('{z}', '0')
                .replace('{x}', '0')
                .replace('{y}', '0'),
        );
        const attributionUrl = new URL(overviewMapElement.dataset.attributionUrl || '');

        if (tilePreviewUrl.protocol !== 'https:' || attributionUrl.protocol !== 'https:') {
            throw new Error('Gabby map providers must use HTTPS.');
        }

        const attributionLink = document.createElement('a');
        attributionLink.href = attributionUrl.toString();
        attributionLink.target = '_blank';
        attributionLink.rel = 'noopener noreferrer';
        attributionLink.textContent = overviewMapElement.dataset.attributionLabel || 'Map contributors';

        const configuredMaxZoom = Number.parseInt(overviewMapElement.dataset.maxZoom || '19', 10);
        const maxZoom = Number.isFinite(configuredMaxZoom)
            ? Math.min(Math.max(configuredMaxZoom, 10), 22)
            : 19;
        const compactMobile = window.matchMedia('(max-width: 640px)').matches;
        const map = L.map(overviewMapElement, {
            attributionControl: true,
            doubleClickZoom: ! compactMobile,
            dragging: ! compactMobile,
            keyboard: true,
            maxBounds: [
                [27.1, -82.5],
                [28.8, -80.8],
            ],
            maxBoundsViscosity: 0.7,
            scrollWheelZoom: false,
            touchZoom: ! compactMobile,
            zoomControl: false,
        }).setView([27.95, -81.72], 9);
        const tileLayer = L.tileLayer(tileTemplate, {
            attribution: `&copy; ${attributionLink.outerHTML}`,
            detectRetina: false,
            keepBuffer: 1,
            maxZoom,
            minZoom: 7,
            updateWhenIdle: true,
        }).addTo(map);
        const boundaryFeature = polkCountyBoundary?.features?.[0];

        if (
            polkCountyBoundary?.type !== 'FeatureCollection'
            || polkCountyBoundary.features.length !== 1
            || ! ['Polygon', 'MultiPolygon'].includes(boundaryFeature?.geometry?.type)
            || boundaryFeature?.properties?.STATE !== '12'
            || boundaryFeature?.properties?.COUNTY !== '105'
            || boundaryFeature?.properties?.NAME !== 'Polk'
        ) {
            throw new Error('The project-local Polk County boundary is invalid.');
        }

        const countyBoundaryPane = map.createPane('overviewCountyBoundary');
        countyBoundaryPane.style.zIndex = '320';
        countyBoundaryPane.style.pointerEvents = 'none';

        const countyBoundaryLayer = L.geoJSON(polkCountyBoundary, {
            interactive: false,
            pane: 'overviewCountyBoundary',
            style: {
                className: 'gabby-county-boundary',
                color: '#334f68',
                fillColor: '#185a98',
                fillOpacity: 0.04,
                opacity: 0.95,
                weight: 2,
            },
        }).addTo(map);
        let tileErrors = 0;

        tileLayer.on('tileerror', () => {
            tileErrors += 1;

            if (tileErrors >= 2 && overviewTileFallback) {
                overviewTileFallback.hidden = false;
            }
        });

        tileLayer.on('load', () => {
            tileErrors = 0;

            if (overviewTileFallback) {
                overviewTileFallback.hidden = true;
            }
        });

        const markerIcon = (visual) => L.divIcon({
            className: `gabby-map-marker gabby-map-marker--${visual} overview-map-marker`,
            html: '<span aria-hidden="true"></span>',
            iconAnchor: [15, 15],
            iconSize: [30, 30],
        });

        overviewRecords.forEach((record) => {
            const geometry = JSON.parse(record.dataset.mapGeometry || '{}');
            const visual = record.dataset.mapVisual;
            const label = record.dataset.mapLabel || 'Map record';
            let focus;
            let shape;

            if (geometry.type === 'generalized_bounds') {
                shape = L.rectangle(parseCoordinates(geometry.bounds), {
                    className: 'gabby-map-shape gabby-map-shape--weather',
                    color: '#185a98',
                    dashArray: '10 7',
                    fillColor: '#2b7ccc',
                    fillOpacity: 0.2,
                    interactive: false,
                    opacity: 0.95,
                    weight: 3,
                });
                focus = parseCoordinate(geometry.focus);
            } else if (geometry.type === 'approximate_corridor') {
                shape = L.polyline(parseCoordinates(geometry.path), {
                    className: 'gabby-map-shape gabby-map-shape--roads',
                    color: '#bd321e',
                    dashArray: '12 7',
                    interactive: false,
                    opacity: 0.95,
                    weight: 7,
                });
                focus = parseCoordinate(geometry.focus);
            } else if (geometry.type === 'city_point') {
                focus = parseCoordinate(geometry.point);
            } else if (geometry.type === 'county_aggregate') {
                return;
            } else {
                throw new Error('Unsupported overview map geometry.');
            }

            shape?.addTo(map);
            L.marker(focus, {
                alt: label,
                icon: markerIcon(visual),
                keyboard: true,
                title: label,
            }).addTo(map);
        });

        const overviewFacilityDataElement = document.querySelector('[data-overview-facility-data]');

        if (overviewFacilityDataElement) {
            const facilityPayload = JSON.parse(overviewFacilityDataElement.textContent || '{}');

            if (
                facilityPayload?.type !== 'FeatureCollection'
                || ! Array.isArray(facilityPayload.features)
                || typeof facilityPayload.categories !== 'object'
                || facilityPayload.categories === null
            ) {
                throw new Error('Invalid overview critical-facilities payload.');
            }

            const facilityPane = map.createPane('overviewCriticalFacilities');
            facilityPane.style.zIndex = '360';
            facilityPane.style.pointerEvents = 'none';
            const facilityRenderer = L.canvas({ padding: 0.2, pane: 'overviewCriticalFacilities' });

            L.geoJSON(facilityPayload, {
                interactive: false,
                pointToLayer: (feature, latlng) => {
                    const category = facilityPayload.categories[feature?.properties?.category];
                    const coordinates = feature?.geometry?.coordinates;

                    if (
                        feature?.geometry?.type !== 'Point'
                        || ! category
                        || ! Array.isArray(coordinates)
                        || coordinates.length !== 2
                        || ! coordinates.every((coordinate) => Number.isFinite(coordinate))
                    ) {
                        throw new Error('Invalid overview critical-facility point.');
                    }

                    return L.circleMarker(latlng, {
                        color: '#ffffff',
                        fillColor: category.color,
                        fillOpacity: 0.68,
                        interactive: false,
                        opacity: 0.75,
                        pane: 'overviewCriticalFacilities',
                        radius: 2.4,
                        renderer: facilityRenderer,
                        weight: 0.6,
                    });
                },
            }).addTo(map);
        }

        map.fitBounds(countyBoundaryLayer.getBounds(), {
            maxZoom: 9,
            padding: [14, 14],
        });

        const refreshMapSize = () => map.invalidateSize({ pan: false });
        const resizeObserver = new ResizeObserver(refreshMapSize);

        resizeObserver.observe(overviewMapElement);
        window.requestAnimationFrame(refreshMapSize);
    } catch {
        overviewMapElement.classList.add('is-unavailable');
        overviewMapElement.setAttribute('aria-label', 'Compact geographic basemap unavailable');

        if (overviewTileFallback) {
            overviewTileFallback.hidden = false;
        }
    }
}

if (shell && navToggle) {
    navToggle.addEventListener('click', () => {
        const isOpen = shell.classList.toggle('is-nav-open');
        navToggle.setAttribute('aria-expanded', String(isOpen));
    });

    navLinks.forEach((link) => {
        link.addEventListener('click', () => {
            shell.classList.remove('is-nav-open');
            navToggle.setAttribute('aria-expanded', 'false');

            navLinks.forEach((item) => {
                item.classList.remove('is-active');
                item.removeAttribute('aria-current');
            });

            link.classList.add('is-active');
            link.setAttribute('aria-current', 'page');
        });
    });
}
