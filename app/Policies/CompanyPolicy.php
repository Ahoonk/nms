<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Enums\RoleName;
use App\Models\Company;
use App\Models\User;

class CompanyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::CompanyManage->value);
    }

    public function view(User $user, Company $company): bool
    {
        return $user->isSuperAdmin()
            || $user->can(PermissionName::CompanyManage->value)
            || $user->company_id === $company->id;
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionName::CompanyManage->value);
    }

    public function update(User $user, Company $company): bool
    {
        return $user->isSuperAdmin()
            || (
                $user->hasRole(RoleName::CompanyAdmin->value)
                && $user->company_id === $company->id
            );
    }

    public function delete(User $user, Company $company): bool
    {
        return $user->isSuperAdmin();
    }
}
