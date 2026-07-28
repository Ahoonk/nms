<?php

namespace App\Http\Controllers;

use App\Enums\DeviceStatus;
use App\Enums\DeviceType;
use App\Http\Requests\StoreDeviceRequest;
use App\Http\Requests\UpdateDeviceRequest;
use App\Models\Device;
use App\Models\Site;
use App\Models\ZabbixConnection;
use App\Repositories\Contracts\DeviceRepositoryInterface;
use App\Repositories\Contracts\SiteRepositoryInterface;
use App\Services\Audit\ActivityLogService;
use App\Services\Zabbix\ZabbixHostGroupService;
use App\Services\Zabbix\ZabbixHostService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class DeviceController extends Controller
{
    public function __construct(
        private readonly DeviceRepositoryInterface $devices,
        private readonly SiteRepositoryInterface $sites,
        private readonly ZabbixHostService $zabbixHosts,
        private readonly ZabbixHostGroupService $hostGroups,
    ) {
        $this->authorizeResource(Device::class, 'device');
    }

    public function index(Request $request): Response
    {
        $companyId = $request->user()?->isSuperAdmin() ? null : $request->user()?->company_id;

        return Inertia::render('Admin/Devices/Index', [
            'devices' => $this->devices->paginateByCompany($companyId, 10),
            'sites' => $this->siteOptions($companyId),
            'deviceTypeOptions' => $this->deviceTypeOptions(),
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function create(Request $request): Response
    {
        $companyId = $request->user()?->isSuperAdmin() ? null : $request->user()?->company_id;

        return Inertia::render('Admin/Devices/Form', [
            'mode' => 'create',
            'action' => route('devices.store'),
            'method' => 'post',
            'sites' => $this->siteOptions($companyId),
            'deviceTypeOptions' => $this->deviceTypeOptions(),
            'statusOptions' => $this->statusOptions(),
            'zabbixHosts' => $this->zabbixHosts(),
            'hostGroups' => $this->hostGroupOptions(),
        ]);
    }

    public function store(StoreDeviceRequest $request, ActivityLogService $activityLogs): RedirectResponse
    {
        $device = $this->devices->create($request->validated());

        $activityLogs->record(
            $request,
            'device.created',
            $device,
            'Created device ' . $device->hostname,
            ['device' => $device->only(['id', 'site_id', 'device_type', 'hostname', 'ip', 'status', 'zabbix_host_id'])],
        );

        return redirect()
            ->route('devices.index')
            ->with('success', 'Device created successfully.');
    }

    public function show(Device $device): RedirectResponse
    {
        return redirect()->route('devices.edit', $device);
    }

    public function edit(Request $request, Device $device): Response
    {
        $companyId = $request->user()?->isSuperAdmin() ? null : $request->user()?->company_id;

        return Inertia::render('Admin/Devices/Form', [
            'mode' => 'edit',
            'action' => route('devices.update', $device),
            'method' => 'put',
            'device' => $device->load('site.company'),
            'sites' => $this->siteOptions($companyId),
            'deviceTypeOptions' => $this->deviceTypeOptions(),
            'statusOptions' => $this->statusOptions(),
            'zabbixHosts' => $this->zabbixHosts(),
            'hostGroups' => $this->hostGroupOptions(),
        ]);
    }

private function hostGroupOptions(): array
{
    $connection = ZabbixConnection::first();

    if (! $connection) {
        return [];
    }

    try {

        $response = $this->hostGroups->list($connection, [
            'output' => ['groupid', 'name'],
            'sortfield' => 'name',
        ]);

        //dd($response);

        return collect($response['result'] ?? [])
            ->map(fn ($group) => [
                'id' => $group['groupid'],
                'name' => $group['name'],
            ])
            ->values()
            ->all();

    } catch (\Throwable $e) {
   // dd($e->getMessage());
    }
}

    public function update(UpdateDeviceRequest $request, Device $device, ActivityLogService $activityLogs): RedirectResponse
    {
        $updated = $this->devices->update($device, $request->validated());

        $activityLogs->record(
            $request,
            'device.updated',
            $updated,
            'Updated device ' . $updated->hostname,
            ['changes' => $request->validated()],
        );

        return redirect()
            ->route('devices.index')
            ->with('success', 'Device updated successfully.');
    }

    public function destroy(Request $request, Device $device, ActivityLogService $activityLogs): RedirectResponse
    {
        $snapshot = $device->only(['id', 'site_id', 'device_type', 'hostname', 'ip', 'status', 'zabbix_host_id']);
        $this->devices->delete($device);

        $activityLogs->record(
            $request,
            'device.deleted',
            $device,
            'Deleted device ' . $device->hostname,
            ['device' => $snapshot],
        );

        return redirect()
            ->route('devices.index')
            ->with('success', 'Device deleted successfully.');
    }

    private function siteOptions(?int $companyId): array
    {
        return $this->sites->allByCompany($companyId)
            ->map(fn (Site $site) => [
                'id' => $site->id,
                'name' => $site->name,
                'company_name' => $site->company?->name,
            ])
            ->values()
            ->all();
    }

    private function deviceTypeOptions(): array
    {
        return collect(DeviceType::cases())
            ->map(fn (DeviceType $type) => [
                'label' => Str::headline(str_replace('_', ' ', $type->value)),
                'value' => $type->value,
            ])
            ->values()
            ->all();
    }

    private function statusOptions(): array
    {
        return collect(DeviceStatus::cases())
            ->map(fn (DeviceStatus $status) => [
                'label' => Str::headline($status->value),
                'value' => $status->value,
            ])
            ->values()
            ->all();
    }

private function zabbixHosts(): array
{
    $connection = ZabbixConnection::first();

    if (! $connection) {
        return [];
    }

    try {
        $response = $this->zabbixHosts->list($connection, [
            'output' => ['hostid', 'host', 'name'],
            'selectHostGroups' => ['groupid', 'name'],
            'sortfield' => 'host',
        ]);

        return collect($response['result'] ?? [])
            ->map(function ($host) {

                return [
                    'hostid' => $host['hostid'],
                    'host' => $host['host'],
                    'name' => $host['name'],
                    'hostgroup' => collect($host['hostgroups'])
                        ->pluck('name')
                        ->join(', '),
                ];

            })
            ->all();

    } catch (\Throwable $e) {
report($e);

   // dd($e->getMessage());
    }
}
}
