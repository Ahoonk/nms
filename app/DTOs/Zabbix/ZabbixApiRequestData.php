<?php

namespace App\DTOs\Zabbix;

readonly class ZabbixApiRequestData
{
    public function __construct(
        public string $method,
        public array $params = [],
        public ?string $auth = null,
        public int|string|null $id = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'jsonrpc' => '2.0',
            'method' => $this->method,
            'params' => $this->params,
            'auth' => $this->auth,
            'id' => $this->id ?? 1,
        ];
    }
}
