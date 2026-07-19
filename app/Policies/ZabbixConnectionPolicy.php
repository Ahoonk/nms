<?php

namespace App\Policies;

use App\Enums\RoleName;
use App\Models\User;
use App\Models\ZabbixConnection;

class ZabbixConnectionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function view(User $user, ZabbixConnection $zabbixConnection): bool
    {
        return $user->isSuperAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, ZabbixConnection $zabbixConnection): bool
    {
        return $user->isSuperAdmin();
    }

    public function delete(User $user, ZabbixConnection $zabbixConnection): bool
    {
        return $user->isSuperAdmin();
    }
}
