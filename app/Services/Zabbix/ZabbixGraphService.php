<?php

namespace App\Services\Zabbix;

use App\Models\ZabbixConnection;

class ZabbixGraphService extends AbstractZabbixService
{
    public function list(ZabbixConnection $connection, array $params = []): array
    {
        return $this->call($connection, 'graph.get', $params);
    }
}
