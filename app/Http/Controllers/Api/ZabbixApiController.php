<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Zabbix\ZabbixConnectionResolver;
use App\Services\Zabbix\ZabbixHostService;
use App\Services\Zabbix\ZabbixProblemService;
use Illuminate\Http\JsonResponse;

class ZabbixApiController extends Controller
{
    public function __construct(
        protected ZabbixConnectionResolver $resolver,
        protected ZabbixHostService $hostService,
        protected ZabbixProblemService $problemService,
    ) {}

    public function hosts(): JsonResponse
    {
        $connection = $this->resolver->resolveOrFail();

        $response = $this->hostService->list($connection, [
            'output' => [
                'hostid',
                'host',
                'name',
            ],
        ]);

        return response()->json($response['result']);
    }

    public function problems(): JsonResponse
    {
        $connection = $this->resolver->resolveOrFail();

        $response = $this->problemService->list($connection);

        return response()->json($response['result']);
    }
}
