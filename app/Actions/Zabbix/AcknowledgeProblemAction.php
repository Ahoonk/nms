<?php

namespace App\Actions\Zabbix;

use App\Services\Zabbix\ZabbixConnectionResolver;
use App\Services\Zabbix\ZabbixEventService;
use RuntimeException;

class AcknowledgeProblemAction
{
    public function __construct(
        private readonly ZabbixConnectionResolver $resolver,
        private readonly ZabbixEventService $events,
    ) {
    }

    public function execute(?int $companyId, int $eventId, ?string $message = null): array
    {
        $connection = $this->resolver->resolveOrFail($companyId);

        $params = [
            'eventids' => [(string) $eventId],
            'action' => filled($message) ? 6 : 2,
        ];

        if (filled($message)) {
            $params['message'] = $message;
        }

        $response = $this->events->acknowledge($connection, $params);

        if (! isset($response['result'])) {
            throw new RuntimeException('Unable to acknowledge the selected event.');
        }

        return $response;
    }
}
