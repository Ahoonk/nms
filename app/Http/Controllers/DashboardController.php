<?php

namespace App\Http\Controllers;

use App\Services\Dashboard\DashboardSummaryService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request, DashboardSummaryService $summaryService): Response
    {
        $summary = $summaryService->forCurrentScope($request->user()?->company_id);

        return Inertia::render('Dashboard', [
            'summary' => $summary->toArray(),
            'summaryCards' => $summary->cards(),
        ]);
    }
}
