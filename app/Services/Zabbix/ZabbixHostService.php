<?php

namespace App\Services\Zabbix;

use App\Models\ZabbixConnection;

class ZabbixHostService extends AbstractZabbixService
{
    public function list(ZabbixConnection $connection, array $params = []): array
    {
        return $this->call($connection, 'host.get', $params);
    }
}
