<?php

namespace App\DTOs\Monitoring;

class MonitoringEventsData
{
    public function __construct(
        public readonly array $connection,
        public readonly array $items,
        public readonly array $pagination,
        public readonly array $meta = [],
    ) {
    }

    public function toArray(): array
    {
        return [
            'connection' => $this->connection,
            'items' => $this->items,
            'pagination' => $this->pagination,
            'meta' => $this->meta,
        ];
    }
}
