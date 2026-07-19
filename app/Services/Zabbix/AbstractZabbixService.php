<?php

namespace App\Services\Zabbix;

use App\Models\ZabbixConnection;

abstract class AbstractZabbixService
{
    public function __construct(
        protected readonly ZabbixApiClient $client,
    ) {
    }

    protected function authToken(ZabbixConnection $connection): string
    {
        return $this->client->login($connection);
    }

    protected function call(ZabbixConnection $connection, string $method, array $params = []): array
    {
        return $this->client->call($connection, $method, $params, $this->authToken($connection));
    }
}
