<?php

namespace App\DTOs\Zabbix;

use App\Models\ZabbixConnection;

readonly class ZabbixConnectionData
{
    public function __construct(
        public ?int $companyId,
        public string $name,
        public string $baseUrl,
        public ?string $username = null,
        public ?string $password = null,
        public ?string $apiToken = null,
        public int $timeoutSeconds = 30,
        public bool $isDefault = false,
    ) {
    }

    public static function fromModel(ZabbixConnection $connection): self
    {
        return new self(
            companyId: $connection->company_id,
            name: $connection->name,
            baseUrl: $connection->base_url,
            username: $connection->username,
            password: $connection->password,
            apiToken: $connection->api_token,
            timeoutSeconds: $connection->timeout_seconds,
            isDefault: $connection->is_default,
        );
    }
}
