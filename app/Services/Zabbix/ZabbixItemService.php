<?php

namespace App\Services\Zabbix;

use App\Models\ZabbixConnection;

class ZabbixItemService extends AbstractZabbixService
{
    public function list(ZabbixConnection $connection, array $params = []): array
    {
        return $this->call($connection, 'item.get', $params);
    }
}
