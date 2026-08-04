<?php

namespace App\Http\Controllers;

use App\Enums\CompanyStatus;
use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;
use App\Repositories\Contracts\CompanyRepositoryInterface;
use App\Services\Audit\ActivityLogService;
use App\Services\Monitoring\MonitoringService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CompanyController extends Controller
{
    public function __construct(
        private readonly CompanyRepositoryInterface $companies,
    ) {
        $this->authorizeResource(Company::class, 'company');
    }

    public function index(): Response
    {
        return Inertia::render('Admin/Companies/Index', [
            'companies' => $this->companies->paginate(10),
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Companies/Form', [
            'mode' => 'create',
            'action' => route('companies.store'),
            'method' => 'post',
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function store(
        StoreCompanyRequest $request,
        ActivityLogService $activityLogs,
        MonitoringService $monitoring,
    ): RedirectResponse
    {
        $company = $this->companies->create($request->validated());

        $activityLogs->record(
            $request,
            'company.created',
            $company,
            'Created company ' . $company->name,
            ['company' => $company->only(['id', 'name', 'email', 'status'])],
        );

        $monitoring->invalidateCache(null);
        $monitoring->invalidateCache($company->id);

        return redirect()
            ->route('companies.index')
            ->with('success', 'Company created successfully.');
    }

    public function show(Company $company): RedirectResponse
    {
        return redirect()->route('companies.edit', $company);
    }

    public function edit(Company $company): Response
    {
        return Inertia::render('Admin/Companies/Form', [
            'mode' => 'edit',
            'action' => route('companies.update', $company),
            'method' => 'put',
            'company' => $company,
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function update(
        UpdateCompanyRequest $request,
        Company $company,
        ActivityLogService $activityLogs,
        MonitoringService $monitoring,
    ): RedirectResponse
    {
        $updated = $this->companies->update($company, $request->validated());

        $activityLogs->record(
            $request,
            'company.updated',
            $updated,
            'Updated company ' . $updated->name,
            ['changes' => $request->validated()],
        );

        $monitoring->invalidateCache(null);
        $monitoring->invalidateCache($updated->id);

        return redirect()
            ->route('companies.index')
            ->with('success', 'Company updated successfully.');
    }

    public function destroy(
        Request $request,
        Company $company,
        ActivityLogService $activityLogs,
        MonitoringService $monitoring,
    ): RedirectResponse
    {
        $snapshot = $company->only(['id', 'name', 'email', 'status']);
        $this->companies->delete($company);

        $activityLogs->record(
            $request,
            'company.deleted',
            $company,
            'Deleted company ' . $company->name,
            ['company' => $snapshot],
        );

        $monitoring->invalidateCache(null);
        $monitoring->invalidateCache((int) $snapshot['id']);

        return redirect()
            ->route('companies.index')
            ->with('success', 'Company deleted successfully.');
    }

    private function statusOptions(): array
    {
        return collect(CompanyStatus::cases())
            ->map(fn (CompanyStatus $status) => [
                'label' => Str::headline($status->value),
                'value' => $status->value,
            ])
            ->values()
            ->all();
    }
}
