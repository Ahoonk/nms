<?php

namespace App\Http\Controllers;

use App\Actions\Zabbix\AcknowledgeProblemAction;
use App\Http\Requests\AcknowledgeProblemRequest;
use App\Services\Audit\ActivityLogService;
use App\Services\Monitoring\MonitoringService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class MonitoringController extends Controller
{
    public function overview(Request $request, MonitoringService $monitoring): Response
    {
        return Inertia::render('Monitoring/Overview', $monitoring->overview($request->user()?->company_id)->toArray());
    }

    public function problems(Request $request, MonitoringService $monitoring): Response
    {
        $filters = $this->monitoringFilters($request);
        $page = max(1, $request->integer('page', 1));
        $perPage = max(1, min(100, $request->integer('per_page', 25)));

        return Inertia::render('Monitoring/Problems', array_merge(
            $monitoring->problems($request->user()?->company_id, $filters, $page, $perPage)->toArray(),
            ['filters' => $filters],
        ));
    }

    public function events(Request $request, MonitoringService $monitoring): Response
    {
        $filters = $this->monitoringFilters($request);
        $page = max(1, $request->integer('page', 1));
        $perPage = max(1, min(100, $request->integer('per_page', 25)));

        return Inertia::render('Monitoring/Events', array_merge(
            $monitoring->events($request->user()?->company_id, $filters, $page, $perPage)->toArray(),
            ['filters' => $filters],
        ));
    }

    public function hosts(Request $request, MonitoringService $monitoring): Response
    {
        $filters = $this->monitoringFilters($request);
        $page = max(1, $request->integer('page', 1));
        $perPage = max(1, min(100, $request->integer('per_page', 25)));

        return Inertia::render('Monitoring/Hosts', array_merge(
            $monitoring->hosts($request->user()?->company_id, $filters, $page, $perPage)->toArray(),
            ['filters' => $filters],
        ));
    }

    public function availability(Request $request, MonitoringService $monitoring): Response
    {
        $filters = $this->monitoringFilters($request);
        $page = max(1, $request->integer('page', 1));
        $perPage = max(1, min(100, $request->integer('per_page', 25)));

        return Inertia::render('Monitoring/Availability', array_merge(
            $monitoring->availability($request->user()?->company_id, $filters, $page, $perPage)->toArray(),
            ['filters' => $filters],
        ));
    }

    public function graphs(Request $request, MonitoringService $monitoring): Response
    {
        $hostId = $request->integer('host_id') ?: null;
        $filters = $this->monitoringFilters($request);
        $page = max(1, $request->integer('page', 1));
        $perPage = max(1, min(100, $request->integer('per_page', 20)));

        return Inertia::render('Monitoring/Graphs', array_merge(
            $monitoring->graphs($request->user()?->company_id, $hostId, $filters, $page, $perPage)->toArray(),
            ['filters' => $filters],
        ));
    }

    public function acknowledgeProblem(
        AcknowledgeProblemRequest $request,
        AcknowledgeProblemAction $action,
        ActivityLogService $activityLogs,
    ): RedirectResponse {
        try {
            $action->execute(
                $request->user()?->company_id,
                $request->validated('event_id'),
                $request->validated('message'),
            );
            $activityLogs->record(
                $request,
                'problem.acknowledged',
                null,
                'Acknowledged Zabbix event ' . $request->validated('event_id'),
                [
                    'event_id' => $request->validated('event_id'),
                    'message' => $request->validated('message'),
                ],
            );
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Problem acknowledged successfully.');
    }

    private function monitoringFilters(Request $request): array
    {
        return [
            'search' => trim($request->string('search')->toString()),
            'status' => trim($request->string('status')->toString()),
            'severity' => trim($request->string('severity')->toString()),
            'availability' => trim($request->string('availability')->toString()),
            'type' => trim($request->string('type')->toString()),
            'device_type' => trim($request->string('device_type')->toString()),
            'site_id' => $request->integer('site_id') ?: null,
        ];
    }
}
