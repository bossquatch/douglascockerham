<?php

declare(strict_types=1);

/**
 * Convert the user-supplied critical-facilities KMZ/KML export into Gabby's
 * deliberately small public-map contract.
 *
 * Only the visible KML placemark name, FACILITY_TYPE, and point coordinate are
 * retained. Description HTML, addresses, contacts, notes, identifiers, and
 * every other source field are discarded.
 */
if ($argc !== 3) {
    fwrite(STDERR, "Usage: php tools/gabby-convert-critical-facilities.php input.kml output.json\n");
    exit(2);
}

[$script, $inputPath, $outputPath] = $argv;

if (! is_file($inputPath) || ! is_readable($inputPath)) {
    fwrite(STDERR, "Input KML is not readable.\n");
    exit(2);
}

$source = file_get_contents($inputPath);

if ($source === false || str_contains(strtoupper($source), '<!DOCTYPE') || str_contains(strtoupper($source), '<!ENTITY')) {
    fwrite(STDERR, "Input KML is unreadable or contains unsupported declarations.\n");
    exit(2);
}

libxml_use_internal_errors(true);

$document = new DOMDocument;

if (! $document->loadXML($source, LIBXML_NONET | LIBXML_COMPACT)) {
    fwrite(STDERR, "Input is not well-formed KML XML.\n");
    exit(2);
}

$xpath = new DOMXPath($document);
$xpath->registerNamespace('k', 'http://www.opengis.net/kml/2.2');

$placemarks = $xpath->query('//k:Placemark');

if ($placemarks === false || $placemarks->length === 0) {
    fwrite(STDERR, "Input KML contains no placemarks.\n");
    exit(2);
}

$categoryLabels = [
    'emergency-response' => 'Emergency response',
    'health-care' => 'Health and care',
    'education' => 'Education',
    'transport-communications' => 'Transportation and communications',
    'energy-utilities' => 'Energy and utilities',
    'government-public-safety' => 'Government and public safety',
    'community-services' => 'Community services',
    'housing-lodging' => 'Housing and lodging',
    'waste-environmental' => 'Waste and environmental',
];

$categoryByType = [];

$assign = static function (string $category, array $types) use (&$categoryByType): void {
    foreach ($types as $type) {
        $categoryByType[$type] = $category;
    }
};

$assign('emergency-response', [
    'CALL CENTER',
    'DISASTER RECOVERY CENTER',
    'DISASTER RECOVERY CENTER-MOBILE',
    'EMERGENCY MEDICAL SERVICE',
    'EMERGENCY OPERATIONS CENTER',
    'FIRE STATION',
    'LOGISTICAL STAGING AREA',
    'RELIEF AGENCY',
    'SHELTER',
]);
$assign('health-care', [
    'ADULT FAMILY CARE HOME',
    'AMBULATORY SURGICAL CENTER',
    'ASSISTED LIVING FACILITY',
    'BIRTH CENTER',
    'BLOOD BANK',
    'CRISIS STABILIZATION UNIT',
    'END-STAGE RENAL DISEASE',
    'HOSPICE',
    'HOSPITAL',
    'HOSPITAL - ACUTE CARE',
    'HOSPITAL - TRAUMA',
    'PUBLIC HEALTH OFFICE',
    'RESIDENTIAL TREATMENT FACILITY',
    'RURAL HEALTH CLINIC',
    'SKILLED NURSING FACILITY',
]);
$assign('education', [
    'COLLEGE',
    'DAY CARE',
    'PRIVATE SCHOOL',
    'PUBLIC SCHOOL',
]);
$assign('transport-communications', [
    'AIRPORT',
    'AIRPORT2',
    'BOAT RAMP',
    'BRIDGE',
    'BUS TERMINAL',
    'HELIPORT/HELIPAD',
    'MARINA',
    'RADIO COMMUNICATIONS TOWER',
    'RAIL FACILITY',
    'REST AREA',
]);
$assign('energy-utilities', [
    'CBP',
    'DW',
    'ELECTRIC POWER PLANT',
    'ELECTRIC SUBSTATION',
    'IW',
]);
$assign('government-public-safety', [
    'JUVENILE CORRECTIONAL INSTITUTION',
    'LAW ENFORCEMENT',
    'LOCAL CORRECTIONAL INSTITUTION',
    'LOCAL GOVERNMENT FACILITY',
    'NATIONAL GUARD',
    'STATE GOVERNMENT FACILITY',
]);
$assign('community-services', [
    'ATTRACTION',
    'COMMUNITY CENTER',
    'FAITH-BASED FACILITY',
    'FOOD BANK',
    'LIBRARY',
    'STADIUM',
]);
$assign('housing-lodging', [
    'MH/RV PARK',
    'MOBILE HOME PARK',
    'RV PARK',
]);
$assign('waste-environmental', [
    'DISASTER DEBRIS MANAGEMENT SITE',
    'SOLID WASTE FACILITY',
]);

$normalizeText = static function (string $value, int $maxLength): string {
    $value = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $value = preg_replace('/\s+/u', ' ', trim($value)) ?? '';

    if (
        $value === ''
        || ! mb_check_encoding($value, 'UTF-8')
        || mb_strlen($value, 'UTF-8') > $maxLength
        || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', $value)
    ) {
        throw new RuntimeException('A public label or type failed validation.');
    }

    return $value;
};

$features = [];
$counts = array_fill_keys(array_keys($categoryLabels), 0);

foreach ($placemarks as $index => $placemark) {
    $points = $xpath->query('.//k:Point', $placemark);
    $otherGeometry = $xpath->query('.//k:LineString | .//k:Polygon | .//k:MultiGeometry', $placemark);

    if ($points === false || $points->length !== 1 || $otherGeometry === false || $otherGeometry->length !== 0) {
        throw new RuntimeException('Every critical-facility placemark must contain exactly one point.');
    }

    $label = $normalizeText((string) $xpath->evaluate('string(k:name)', $placemark), 120);
    $coordinateText = trim((string) $xpath->evaluate('string(.//k:Point/k:coordinates)', $placemark));
    $coordinateParts = preg_split('/\s*,\s*/', $coordinateText);

    if (
        ! is_array($coordinateParts)
        || count($coordinateParts) < 2
        || ! is_numeric($coordinateParts[0])
        || ! is_numeric($coordinateParts[1])
    ) {
        throw new RuntimeException('A critical-facility point has an invalid coordinate.');
    }

    $longitude = (float) $coordinateParts[0];
    $latitude = (float) $coordinateParts[1];

    if ($longitude < -82.2 || $longitude > -81.0 || $latitude < 27.5 || $latitude > 28.5) {
        throw new RuntimeException('A critical-facility point falls outside the Polk County review envelope.');
    }

    $description = (string) $xpath->evaluate('string(k:description)', $placemark);
    $descriptionDocument = new DOMDocument;

    if (! @$descriptionDocument->loadHTML($description, LIBXML_NONET | LIBXML_COMPACT)) {
        throw new RuntimeException('A placemark description is not parseable.');
    }

    $descriptionXPath = new DOMXPath($descriptionDocument);
    $facilityType = null;

    foreach ($descriptionXPath->query('//tr[td]') ?: [] as $row) {
        $cells = $descriptionXPath->query('./td', $row);

        if ($cells === false || $cells->length !== 2) {
            continue;
        }

        if (trim($cells->item(0)?->textContent ?? '') === 'FACILITY_TYPE') {
            $facilityType = $normalizeText($cells->item(1)?->textContent ?? '', 80);
            break;
        }
    }

    if ($facilityType === null || ! isset($categoryByType[$facilityType])) {
        throw new RuntimeException('A facility type is missing or has no approved public category.');
    }

    $category = $categoryByType[$facilityType];
    $counts[$category]++;

    $features[] = [
        'type' => 'Feature',
        'id' => sprintf('facility-%04d', $index + 1),
        'properties' => [
            'id' => sprintf('facility-%04d', $index + 1),
            'label' => $label,
            'category' => $category,
            'type' => $facilityType,
        ],
        'geometry' => [
            'type' => 'Point',
            'coordinates' => [
                round($longitude, 8),
                round($latitude, 8),
            ],
        ],
    ];
}

$payload = [
    'schema' => 'gabby.critical-facilities',
    'version' => 1,
    'type' => 'FeatureCollection',
    'metadata' => [
        'source_filename' => 'PC_Crit_Fac.kmz',
        'source_sha256' => '42d43cb5d0469d8563facfa5e1cd758f2e8f6dd7a1c671dea07ca7b6b0d6cb74',
        'coordinate_reference' => 'KML 2.2 WGS84 longitude,latitude',
        'field_policy' => 'Visible placemark label, facility type, normalized category, and point coordinate only.',
        'count' => count($features),
        'category_counts' => array_filter($counts),
        'category_labels' => $categoryLabels,
    ],
    'features' => $features,
];

$encoded = json_encode(
    $payload,
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
).PHP_EOL;

if (file_put_contents($outputPath, $encoded, LOCK_EX) === false) {
    fwrite(STDERR, "Unable to write converted critical-facilities data.\n");
    exit(2);
}

fwrite(STDOUT, sprintf(
    "Converted %d point facilities across %d public categories.\n",
    count($features),
    count(array_filter($counts)),
));
