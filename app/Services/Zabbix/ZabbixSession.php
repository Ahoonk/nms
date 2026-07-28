<?php

namespace App\Services\Zabbix;

use App\Models\ZabbixConnection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ZabbixSession
{
    public function cookie(ZabbixConnection $connection): string
    {
        return Cache::remember(
            "zabbix-cookie-{$connection->id}",
            now()->addMinutes(20),
            function () use ($connection) {

                $response = Http::asForm()
                    ->timeout($connection->timeout_seconds)
                    ->withoutVerifying()
                    ->post(
                        rtrim($connection->base_url, '/') . '/index.php',
                        [
                            'name'     => $connection->username,
                            'password' => $connection->password,
                            'enter'    => 'Sign in',
                        ]
                    );

                if (! $response->successful()) {
                    throw new RuntimeException('Unable to login to Zabbix.');
                }

                foreach ($response->cookies() as $cookie) {
                    if ($cookie->getName() === 'zbx_session') {
                        return 'zbx_session=' . $cookie->getValue();
                    }
                }

                throw new RuntimeException('Zabbix session cookie not found.');
            }
        );
    }

    public function clear(ZabbixConnection $connection): void
    {
        Cache::forget("zabbix-cookie-{$connection->id}");
    }
}
