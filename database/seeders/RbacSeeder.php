<?php

namespace Database\Seeders;

use App\Enums\PermissionName;
use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RbacSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = collect(PermissionName::cases())
            ->map(fn (PermissionName $permission) => Permission::findOrCreate($permission->value))
            ->values();

        $rolePermissions = [
            RoleName::SuperAdmin->value => $permissions->pluck('name')->all(),
            RoleName::CompanyAdmin->value => [
                PermissionName::DashboardView->value,
                PermissionName::CompanyManage->value,
                PermissionName::UserManage->value,
                PermissionName::SiteManage->value,
                PermissionName::DeviceManage->value,
                PermissionName::InventoryManage->value,
                PermissionName::MonitoringView->value,
                PermissionName::ProblemAcknowledge->value,
                PermissionName::GraphView->value,
                PermissionName::ReportView->value,
            ],
            RoleName::Operator->value => [
                PermissionName::DashboardView->value,
                PermissionName::MonitoringView->value,
                PermissionName::ProblemAcknowledge->value,
                PermissionName::GraphView->value,
            ],
            RoleName::Viewer->value => [
                PermissionName::DashboardView->value,
                PermissionName::MonitoringView->value,
                PermissionName::GraphView->value,
                PermissionName::ReportView->value,
            ],
        ];

        foreach ($rolePermissions as $roleName => $permissionNames) {
            $role = Role::findOrCreate($roleName);
            $role->syncPermissions($permissionNames);
        }

        $superAdmin = User::query()->firstOrCreate(
            ['email' => 'superadmin@nms.local'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'status' => 'active',
            ],
        );

        $superAdmin->assignRole(RoleName::SuperAdmin->value);
    }
}
