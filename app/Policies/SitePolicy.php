<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Enums\RoleName;
use App\Models\Site;
use App\Models\User;

class SitePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin()
            || $user->can(PermissionName::SiteManage->value)
            || $user->hasRole(RoleName::CompanyAdmin->value);
    }

    public function view(User $user, Site $site): bool
    {
        return $user->isSuperAdmin()
            || $user->company_id === $site->company_id
            || $user->can(PermissionName::SiteManage->value);
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin()
            || $user->can(PermissionName::SiteManage->value)
            || $user->hasRole(RoleName::CompanyAdmin->value);
    }

    public function update(User $user, Site $site): bool
    {
        return $user->isSuperAdmin()
            || ($user->company_id === $site->company_id && $user->can(PermissionName::SiteManage->value));
    }

    public function delete(User $user, Site $site): bool
    {
        return $this->update($user, $site);
    }
}
