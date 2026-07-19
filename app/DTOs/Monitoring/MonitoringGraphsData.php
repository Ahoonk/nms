<?php

namespace App\DTOs\Monitoring;

class MonitoringGraphsData
{
    public function __construct(
        public readonly array $connection,
        public readonly array $hosts,
        public readonly array $items,
        public readonly ?int $selectedHostId = null,
        public readonly array $pagination = [],
        public readonly array $meta = [],
    ) {
    }

    public function toArray(): array
    {
        return [
            'connection' => $this->connection,
            'hosts' => $this->hosts,
            'items' => $this->items,
            'selectedHostId' => $this->selectedHostId,
            'pagination' => $this->pagination,
            'meta' => $this->meta,
        ];
    }
}
