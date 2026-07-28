<?php

namespace App\Http\Controllers;

use App\Services\Dashboard\DashboardSummaryService;
use App\Services\Monitoring\MonitoringService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(
        Request $request,
        DashboardSummaryService $summaryService,
        MonitoringService $monitoring,
    ): Response {
        $summary = $summaryService->forCurrentScope($request->user()?->company_id);
        $availability = $monitoring->availability($request->user()?->company_id, [], 1, 5);
        $featuredGraphs = $monitoring->featuredGraphs($request->user()?->company_id);

        return Inertia::render('Dashboard', [
            'summary' => $summary->toArray(),
            'summaryCards' => $summary->cards(),
            'availabilityPreview' => $availability->toArray(),
            'featuredGraphs' => $featuredGraphs,
        ]);
    }
}
