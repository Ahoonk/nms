<?php

namespace App\Services\Dashboard;

use App\DTOs\Dashboard\DashboardSummaryData;
use App\Repositories\Contracts\CompanyRepositoryInterface;
use App\Repositories\Contracts\DeviceRepositoryInterface;
use App\Repositories\Contracts\SiteRepositoryInterface;

class DashboardSummaryService
{
    public function __construct(
        private readonly CompanyRepositoryInterface $companyRepository,
        private readonly SiteRepositoryInterface $siteRepository,
        private readonly DeviceRepositoryInterface $deviceRepository,
    ) {
    }

    public function forCurrentScope(?int $companyId = null): DashboardSummaryData
    {
        $devices = $this->deviceRepository->allByCompany($companyId);

        return new DashboardSummaryData(
            totalCompany: $companyId ? 1 : $this->companyRepository->all()->count(),
            totalSite: $this->siteRepository->allByCompany($companyId)->count(),
            totalDevice: $devices->count(),
            hostOnline: $devices->where('status', 'online')->count(),
            hostOffline: $devices->where('status', 'offline')->count(),
            problem: 0,
        );
    }
}
