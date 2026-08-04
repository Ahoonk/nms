<?php

namespace App\Http\Controllers;

use App\Enums\DeviceStatus;
use App\Services\Dashboard\DashboardSummaryService;
use App\Services\Monitoring\MonitoringService;
use App\Repositories\Contracts\DeviceRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(
        Request $request,
        DashboardSummaryService $summaryService,
        MonitoringService $monitoring,
        DeviceRepositoryInterface $devices,
    ): Response {
        $companyId = $request->user()?->company_id;
        $summary = $summaryService->forCurrentScope($companyId);
        $availability = $monitoring->availability($companyId, [], 1, 250);
        $events = $monitoring->events($request->user()?->company_id, [], 1, 5);
        $telemetry = $monitoring->dashboardTelemetry($request->user()?->company_id);
        $telemetryCards = $telemetry['cards'] ?? [];
        $companyAvailability = $this->companyAvailability(
            $devices->allByCompany($companyId),
            collect($availability->items ?? [])
        );

        return Inertia::render('Dashboard', [
            'summary' => $summary->toArray(),
            'summaryCards' => $summary->cards(),
            'availabilityPreview' => $availability->toArray(),
            'companyAvailability' => $companyAvailability,
            'latestEvents' => $events->toArray(),
            'telemetryCards' => $telemetryCards,
        ]);
    }

    private function companyAvailability(Collection $devices, Collection $zabbixRows): array
    {
        $lookup = [];

        foreach ($zabbixRows as $row) {
            $device = Arr::get($row, 'device');

            if (! is_array($device)) {
                continue;
            }

            $payload = [
                'name' => Arr::get($device, 'hostname', Arr::get($row, 'name', '-')),
                'host' => Arr::get($row, 'host', Arr::get($device, 'hostname', '-')),
                'ip' => Arr::get($device, 'ip', Arr::get($row, 'ip')),
                'availability' => Arr::get($row, 'availability', 'Unknown'),
                'availability_class' => Arr::get($row, 'availability_class', 'bg-slate-500/10 text-slate-700 dark:text-slate-300'),
                'status' => Arr::get($row, 'status', 'Unknown'),
                'status_class' => Arr::get($row, 'status_class', 'bg-slate-500/10 text-slate-700 dark:text-slate-300'),
            ];

            if (filled(Arr::get($device, 'id'))) {
                $lookup['device:' . Arr::get($device, 'id')] = $payload;
            }

            if (filled(Arr::get($device, 'hostname'))) {
                $lookup['host:' . $this->normalizeLookupKey((string) Arr::get($device, 'hostname'))] = $payload;
            }

            if (filled(Arr::get($device, 'ip'))) {
                $lookup['ip:' . $this->normalizeLookupKey((string) Arr::get($device, 'ip'))] = $payload;
            }
        }

        return $devices
            ->groupBy(fn ($device) => $device->site?->company?->name ?? 'Global')
            ->map(function (Collection $companyDevices, string $companyName) use ($lookup): array {
                $hosts = $companyDevices->map(function ($device) use ($lookup): array {
                    $fallback = $this->availabilityFromDeviceStatus($device->status);
                    $matched = $lookup['device:' . $device->id]
                        ?? $lookup['host:' . $this->normalizeLookupKey((string) $device->hostname)]
                        ?? ($device->ip ? ($lookup['ip:' . $this->normalizeLookupKey((string) $device->ip)] ?? null) : null)
                        ?? null;

                    $availability = $matched['availability'] ?? $fallback['availability'];
                    $availabilityClass = $matched['availability_class'] ?? $fallback['availability_class'];

                    return [
                        'device_id' => $device->id,
                        'name' => $device->hostname,
                        'host' => $device->hostname,
                        'site_name' => $device->site?->name ?? '-',
                        'ip' => $device->ip,
                        'status' => $this->deviceStatusLabel($device->status),
                        'status_class' => $this->deviceStatusClass($device->status),
                        'availability' => $availability,
                        'availability_class' => $availabilityClass,
                    ];
                })->values();

                $online = $hosts->filter(fn (array $host): bool => ($host['availability'] ?? '') === 'Online')->count();
                $offline = $hosts->filter(fn (array $host): bool => ($host['availability'] ?? '') === 'Offline')->count();
                $unknown = $hosts->filter(fn (array $host): bool => ! in_array($host['availability'] ?? '', ['Online', 'Offline'], true))->count();

                return [
                    'name' => $companyName,
                    'hosts' => $hosts->all(),
                    'onlineHosts' => $hosts->filter(fn (array $host): bool => ($host['availability'] ?? '') === 'Online')->values()->all(),
                    'offlineHosts' => $hosts->filter(fn (array $host): bool => ($host['availability'] ?? '') === 'Offline')->values()->all(),
                    'unknownHosts' => $hosts->filter(fn (array $host): bool => ! in_array($host['availability'] ?? '', ['Online', 'Offline'], true))->values()->all(),
                    'onlineCount' => $online,
                    'offlineCount' => $offline,
                    'unknownCount' => $unknown,
                    'totalCount' => $hosts->count(),
                ];
            })
            ->sortBy('name')
            ->values()
            ->all();
    }

    private function availabilityFromDeviceStatus(mixed $status): array
    {
        $value = $status instanceof DeviceStatus ? $status->value : (string) $status;

        return match ($value) {
            DeviceStatus::Online->value => [
                'availability' => 'Online',
                'availability_class' => 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300',
            ],
            DeviceStatus::Offline->value => [
                'availability' => 'Offline',
                'availability_class' => 'bg-rose-500/10 text-rose-700 dark:text-rose-300',
            ],
            DeviceStatus::Maintenance->value => [
                'availability' => 'Maintenance',
                'availability_class' => 'bg-amber-500/10 text-amber-700 dark:text-amber-300',
            ],
            default => [
                'availability' => 'Unknown',
                'availability_class' => 'bg-slate-500/10 text-slate-700 dark:text-slate-300',
            ],
        };
    }

    private function deviceStatusLabel(mixed $status): string
    {
        $value = $status instanceof DeviceStatus ? $status->value : (string) $status;

        return Str::headline($value ?: 'unknown');
    }

    private function deviceStatusClass(mixed $status): string
    {
        return match ($status instanceof DeviceStatus ? $status->value : (string) $status) {
            DeviceStatus::Online->value => 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300',
            DeviceStatus::Offline->value => 'bg-rose-500/10 text-rose-700 dark:text-rose-300',
            DeviceStatus::Maintenance->value => 'bg-amber-500/10 text-amber-700 dark:text-amber-300',
            default => 'bg-slate-500/10 text-slate-700 dark:text-slate-300',
        };
    }

    private function normalizeLookupKey(string $value): string
    {
        return mb_strtolower(preg_replace('/[^a-z0-9]+/i', '', trim($value)) ?? '');
    }
}
