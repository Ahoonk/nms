<?php

namespace App\Services\Zabbix;

use App\Models\ZabbixConnection;

class ZabbixAuthService extends AbstractZabbixService
{
    public function login(ZabbixConnection $connection): string
    {
        return $this->authToken($connection);
    }
}
