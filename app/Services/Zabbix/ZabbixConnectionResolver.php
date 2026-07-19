<?php

namespace App\Services\Zabbix;

use App\Models\ZabbixConnection;
use App\Repositories\Contracts\ZabbixConnectionRepositoryInterface;

class ZabbixConnectionResolver
{
    public function __construct(
        private readonly ZabbixConnectionRepositoryInterface $connections,
    ) {
    }

    public function resolve(?int $companyId = null): ?ZabbixConnection
    {
        if ($companyId !== null) {
            $connection = $this->connections->defaultForCompany($companyId)
                ?? $this->connections->activeForCompany($companyId)->first();

            if ($connection) {
                return $connection;
            }
        }

        return $this->connections->activeDefault()
            ?? $this->connections->active()->first();
    }

    public function resolveOrFail(?int $companyId = null): ZabbixConnection
    {
        $connection = $this->resolve($companyId);

        if (! $connection) {
            abort(503, 'No active Zabbix connection configured.');
        }

        return $connection;
    }
}
