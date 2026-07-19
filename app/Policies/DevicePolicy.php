<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Enums\RoleName;
use App\Models\Device;
use App\Models\User;

class DevicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin()
            || $user->can(PermissionName::DeviceManage->value)
            || $user->hasRole(RoleName::CompanyAdmin->value);
    }

    public function view(User $user, Device $device): bool
    {
        return $user->isSuperAdmin()
            || $user->company_id === $device->site?->company_id
            || $user->can(PermissionName::DeviceManage->value);
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin()
            || $user->can(PermissionName::DeviceManage->value)
            || $user->hasRole(RoleName::CompanyAdmin->value);
    }

    public function update(User $user, Device $device): bool
    {
        return $user->isSuperAdmin()
            || ($user->company_id === $device->site?->company_id && $user->can(PermissionName::DeviceManage->value));
    }

    public function delete(User $user, Device $device): bool
    {
        return $this->update($user, $device);
    }
}
