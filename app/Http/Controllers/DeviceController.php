<?php

namespace App\Http\Controllers;

use App\Enums\DeviceStatus;
use App\Enums\DeviceType;
use App\Http\Requests\StoreDeviceRequest;
use App\Http\Requests\UpdateDeviceRequest;
use App\Models\Device;
use App\Models\Site;
use App\Repositories\Contracts\DeviceRepositoryInterface;
use App\Repositories\Contracts\SiteRepositoryInterface;
use App\Services\Audit\ActivityLogService;
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
        ]);
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
}
