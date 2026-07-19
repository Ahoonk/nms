<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreZabbixConnectionRequest;
use App\Http\Requests\UpdateZabbixConnectionRequest;
use App\Models\Company;
use App\Models\ZabbixConnection;
use App\Repositories\Contracts\CompanyRepositoryInterface;
use App\Repositories\Contracts\ZabbixConnectionRepositoryInterface;
use App\Services\Audit\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ZabbixConnectionController extends Controller
{
    public function __construct(
        private readonly ZabbixConnectionRepositoryInterface $connections,
        private readonly CompanyRepositoryInterface $companies,
    ) {
        $this->authorizeResource(ZabbixConnection::class, 'zabbixConnection');
    }

    public function index(): Response
    {
        return Inertia::render('Admin/ZabbixConnections/Index', [
            'connections' => $this->connections->paginate(10),
            'companies' => $this->companies->all()->map(fn (Company $company) => [
                'id' => $company->id,
                'name' => $company->name,
            ])->values()->all(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/ZabbixConnections/Form', [
            'mode' => 'create',
            'action' => route('zabbix-connections.store'),
            'method' => 'post',
            'companies' => $this->companies->all()->map(fn (Company $company) => [
                'id' => $company->id,
                'name' => $company->name,
            ])->values()->all(),
        ]);
    }

    public function store(StoreZabbixConnectionRequest $request, ActivityLogService $activityLogs): RedirectResponse
    {
        $connection = $this->connections->create($request->validated());

        $activityLogs->record(
            $request,
            'zabbix_connection.created',
            $connection,
            'Created Zabbix connection ' . $connection->name,
            ['connection' => $connection->only(['id', 'company_id', 'name', 'base_url', 'status', 'is_default'])],
        );

        return redirect()
            ->route('zabbix-connections.index')
            ->with('success', 'Zabbix connection created successfully.');
    }

    public function show(ZabbixConnection $zabbixConnection): RedirectResponse
    {
        return redirect()->route('zabbix-connections.edit', $zabbixConnection);
    }

    public function edit(ZabbixConnection $zabbixConnection): Response
    {
        return Inertia::render('Admin/ZabbixConnections/Form', [
            'mode' => 'edit',
            'action' => route('zabbix-connections.update', $zabbixConnection),
            'method' => 'put',
            'connection' => $zabbixConnection->load('company'),
            'companies' => $this->companies->all()->map(fn (Company $company) => [
                'id' => $company->id,
                'name' => $company->name,
            ])->values()->all(),
        ]);
    }

    public function update(UpdateZabbixConnectionRequest $request, ZabbixConnection $zabbixConnection, ActivityLogService $activityLogs): RedirectResponse
    {
        $updated = $this->connections->update($zabbixConnection, $request->validated());

        $activityLogs->record(
            $request,
            'zabbix_connection.updated',
            $updated,
            'Updated Zabbix connection ' . $updated->name,
            ['changes' => $request->validated()],
        );

        return redirect()
            ->route('zabbix-connections.index')
            ->with('success', 'Zabbix connection updated successfully.');
    }

    public function destroy(Request $request, ZabbixConnection $zabbixConnection, ActivityLogService $activityLogs): RedirectResponse
    {
        $snapshot = $zabbixConnection->only(['id', 'company_id', 'name', 'base_url', 'status', 'is_default']);
        $this->connections->delete($zabbixConnection);

        $activityLogs->record(
            $request,
            'zabbix_connection.deleted',
            $zabbixConnection,
            'Deleted Zabbix connection ' . $zabbixConnection->name,
            ['connection' => $snapshot],
        );

        return redirect()
            ->route('zabbix-connections.index')
            ->with('success', 'Zabbix connection deleted successfully.');
    }
}
