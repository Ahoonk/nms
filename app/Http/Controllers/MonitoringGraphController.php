<?php

namespace App\Http\Controllers;

use App\Models\ZabbixConnection;
use App\Services\Zabbix\ZabbixSession;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Response;

class MonitoringGraphController extends Controller
{
    public function __construct(
        protected HttpFactory $http,
        protected ZabbixSession $session
    ) {
    }

    public function show(int $graph)
    {
        $connection = ZabbixConnection::where('status', 'active')->firstOrFail();

        $cookie = $this->session->cookie($connection);

        $response = $this->http
            ->withoutVerifying()
            ->withHeaders([
                'Cookie' => $cookie,
            ])
            ->get(
                rtrim($connection->base_url, '/') .
                "/chart2.php?graphid={$graph}&from=now-24h&to=now"
            );

        if (! $response->successful()) {
            abort(404);
        }

        return response(
            $response->body(),
            200,
            [
                'Content-Type' => 'image/png',
                'Cache-Control' => 'public,max-age=60',
            ]
        );
    }
}
