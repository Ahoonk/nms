<?php

namespace App\DTOs\Monitoring;

class MonitoringAvailabilityData
{
    public function __construct(
        public readonly array $connection,
        public readonly array $summary,
        public readonly array $items,
        public readonly array $pagination,
        public readonly array $meta = [],
    ) {
    }

    public function toArray(): array
    {
        return [
            'connection' => $this->connection,
            'summary' => $this->summary,
            'items' => $this->items,
            'pagination' => $this->pagination,
            'meta' => $this->meta,
        ];
    }
}
