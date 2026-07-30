<?php

namespace App\Http\Controllers;

use App\Services\GabbyBriefingService;
use App\Services\GabbyCriticalFacilitiesService;
use App\Services\GabbyElectionsService;
use App\Services\GabbyMapService;
use App\Services\GabbySnapshotService;
use App\Services\GabbyUtilityStatusService;
use Illuminate\Contracts\View\View;

class GabbyDashboardController extends Controller
{
    public function overview(
        GabbySnapshotService $snapshots,
        GabbyUtilityStatusService $utilities,
        GabbyMapService $maps,
        GabbyCriticalFacilitiesService $criticalFacilities,
    ): View {
        $snapshot = $snapshots->current();

        return view('gabby', [
            'snapshot' => $snapshot,
            'utilities' => $utilities->fromSnapshot($snapshot),
            'map' => $maps->fromSnapshot($snapshot),
            'criticalFacilities' => $criticalFacilities->current(),
        ]);
    }

    public function briefing(
        GabbySnapshotService $snapshots,
        GabbyBriefingService $briefings,
    ): View {
        $snapshot = $snapshots->current();

        return view('gabby-briefing', [
            'snapshot' => $snapshot,
            'briefing' => $briefings->fromSnapshot($snapshot),
        ]);
    }

    public function map(
        GabbySnapshotService $snapshots,
        GabbyMapService $maps,
        GabbyCriticalFacilitiesService $criticalFacilities,
    ): View {
        $snapshot = $snapshots->current();

        return view('gabby-map', [
            'snapshot' => $snapshot,
            'map' => $maps->fromSnapshot($snapshot),
            'criticalFacilities' => $criticalFacilities->current(),
        ]);
    }

    public function elections(
        GabbySnapshotService $snapshots,
        GabbyElectionsService $elections,
    ): View {
        return view('gabby-elections', [
            'snapshot' => $snapshots->current(),
            'elections' => $elections->current(),
        ]);
    }
}
