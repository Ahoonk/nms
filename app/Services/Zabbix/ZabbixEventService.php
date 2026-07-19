<?php

namespace App\Services\Zabbix;

use App\Models\ZabbixConnection;

class ZabbixEventService extends AbstractZabbixService
{
    public function list(ZabbixConnection $connection, array $params = []): array
    {
        return $this->call($connection, 'event.get', $params);
    }

    public function acknowledge(ZabbixConnection $connection, array $params = []): array
    {
        return $this->call($connection, 'event.acknowledge', $params);
    }
}
