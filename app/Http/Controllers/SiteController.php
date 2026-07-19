<?php

namespace App\Http\Controllers;

use App\Enums\SiteStatus;
use App\Http\Requests\StoreSiteRequest;
use App\Http\Requests\UpdateSiteRequest;
use App\Models\Company;
use App\Models\Site;
use App\Repositories\Contracts\CompanyRepositoryInterface;
use App\Repositories\Contracts\SiteRepositoryInterface;
use App\Services\Audit\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class SiteController extends Controller
{
    public function __construct(
        private readonly SiteRepositoryInterface $sites,
        private readonly CompanyRepositoryInterface $companies,
    ) {
        $this->authorizeResource(Site::class, 'site');
    }

    public function index(Request $request): Response
    {
        $companyId = $request->user()?->isSuperAdmin() ? null : $request->user()?->company_id;

        return Inertia::render('Admin/Sites/Index', [
            'sites' => $this->sites->paginateByCompany($companyId, 10),
            'companies' => $this->companies->all()->map(fn (Company $company) => [
                'id' => $company->id,
                'name' => $company->name,
            ])->values()->all(),
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function create(Request $request): Response
    {
        $companyId = $request->user()?->isSuperAdmin() ? null : $request->user()?->company_id;

        return Inertia::render('Admin/Sites/Form', [
            'mode' => 'create',
            'action' => route('sites.store'),
            'method' => 'post',
            'companies' => $this->companies->all()->map(fn (Company $company) => [
                'id' => $company->id,
                'name' => $company->name,
            ])->values()->all(),
            'defaultCompanyId' => $companyId,
            'canChooseCompany' => $request->user()?->isSuperAdmin() ?? false,
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function store(StoreSiteRequest $request, ActivityLogService $activityLogs): RedirectResponse
    {
        $site = $this->sites->create($request->validated());

        $activityLogs->record(
            $request,
            'site.created',
            $site,
            'Created site ' . $site->name,
            ['site' => $site->only(['id', 'company_id', 'name', 'status'])],
        );

        return redirect()
            ->route('sites.index')
            ->with('success', 'Site created successfully.');
    }

    public function show(Site $site): RedirectResponse
    {
        return redirect()->route('sites.edit', $site);
    }

    public function edit(Request $request, Site $site): Response
    {
        return Inertia::render('Admin/Sites/Form', [
            'mode' => 'edit',
            'action' => route('sites.update', $site),
            'method' => 'put',
            'site' => $site->load('company'),
            'companies' => $this->companies->all()->map(fn (Company $company) => [
                'id' => $company->id,
                'name' => $company->name,
            ])->values()->all(),
            'canChooseCompany' => $request->user()?->isSuperAdmin() ?? false,
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function update(UpdateSiteRequest $request, Site $site, ActivityLogService $activityLogs): RedirectResponse
    {
        $updated = $this->sites->update($site, $request->validated());

        $activityLogs->record(
            $request,
            'site.updated',
            $updated,
            'Updated site ' . $updated->name,
            ['changes' => $request->validated()],
        );

        return redirect()
            ->route('sites.index')
            ->with('success', 'Site updated successfully.');
    }

    public function destroy(Request $request, Site $site, ActivityLogService $activityLogs): RedirectResponse
    {
        $snapshot = $site->only(['id', 'company_id', 'name', 'status']);
        $this->sites->delete($site);

        $activityLogs->record(
            $request,
            'site.deleted',
            $site,
            'Deleted site ' . $site->name,
            ['site' => $snapshot],
        );

        return redirect()
            ->route('sites.index')
            ->with('success', 'Site deleted successfully.');
    }

    private function statusOptions(): array
    {
        return collect(SiteStatus::cases())
            ->map(fn (SiteStatus $status) => [
                'label' => Str::headline($status->value),
                'value' => $status->value,
            ])
            ->values()
            ->all();
    }
}
