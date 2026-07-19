<?php

namespace App\Services\Zabbix;

use App\Models\ZabbixConnection;

class ZabbixHistoryService extends AbstractZabbixService
{
    public function list(ZabbixConnection $connection, array $params = []): array
    {
        return $this->call($connection, 'history.get', $params);
    }
}
