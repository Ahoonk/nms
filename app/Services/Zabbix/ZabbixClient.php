<?php

namespace App\Services\Zabbix;

use App\Models\ZabbixConnection;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class ZabbixClient
{
    public function connection(?int $connectionId = null): ZabbixConnection
    {
        return $connectionId
            ? ZabbixConnection::findOrFail($connectionId)
            : ZabbixConnection::where('status', 'active')->firstOrFail();
    }

    public function http(ZabbixConnection $connection): PendingRequest
    {
        return Http::timeout($connection->timeout_seconds)
            ->withoutVerifying()
            ->baseUrl(rtrim($connection->base_url, '/'));
    }
}
