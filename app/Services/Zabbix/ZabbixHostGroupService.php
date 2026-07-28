<?php

namespace App\Services\Zabbix;

use App\Models\ZabbixConnection;

class ZabbixHostGroupService extends AbstractZabbixService
{
    public function list(ZabbixConnection $connection, array $params = []): array
    {
        return $this->call($connection, 'hostgroup.get', $params);
    }
}
