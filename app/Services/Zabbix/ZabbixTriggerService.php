<?php

namespace App\Services\Zabbix;

use App\Models\ZabbixConnection;

class ZabbixTriggerService extends AbstractZabbixService
{
    public function list(ZabbixConnection $connection, array $params = []): array
    {
        return $this->call($connection, 'trigger.get', $params);
    }
}
