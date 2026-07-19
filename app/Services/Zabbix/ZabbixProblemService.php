<?php

namespace App\Services\Zabbix;

use App\Models\ZabbixConnection;

class ZabbixProblemService extends AbstractZabbixService
{
    public function list(ZabbixConnection $connection, array $params = []): array
    {
        return $this->call($connection, 'problem.get', $params);
    }
}
