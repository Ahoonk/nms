<?php

namespace App\Providers;

use App\Enums\PermissionName;
use App\Models\Company;
use App\Models\User;
use App\Repositories\Contracts\CompanyRepositoryInterface;
use App\Repositories\Contracts\DeviceRepositoryInterface;
use App\Repositories\Contracts\SiteRepositoryInterface;
use App\Repositories\Contracts\ZabbixConnectionRepositoryInterface;
use App\Repositories\Eloquent\CompanyRepository;
use App\Repositories\Eloquent\DeviceRepository;
use App\Repositories\Eloquent\SiteRepository;
use App\Repositories\Eloquent\ZabbixConnectionRepository;
use App\Services\Zabbix\ZabbixApiClient;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CompanyRepositoryInterface::class, CompanyRepository::class);
        $this->app->bind(SiteRepositoryInterface::class, SiteRepository::class);
        $this->app->bind(DeviceRepositoryInterface::class, DeviceRepository::class);
        $this->app->bind(ZabbixConnectionRepositoryInterface::class, ZabbixConnectionRepository::class);

        $this->app->singleton(ZabbixApiClient::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        Gate::before(function (User $user) {
            return $user->isSuperAdmin() ? true : null;
        });

        Gate::define('view-dashboard', function (User $user): bool {
            return $user->can(PermissionName::DashboardView->value);
        });

        Gate::define('manage-companies', function (User $user): bool {
            return $user->can(PermissionName::CompanyManage->value);
        });

        Gate::define('view-monitoring', function (User $user): bool {
            return $user->can(PermissionName::MonitoringView->value);
        });

        Gate::define('acknowledge-problem', function (User $user): bool {
            return $user->can(PermissionName::ProblemAcknowledge->value);
        });

        Gate::define('access-company', function (User $user, Company $company): bool {
            return $user->company_id === null || $user->company_id === $company->id;
        });
    }
}
