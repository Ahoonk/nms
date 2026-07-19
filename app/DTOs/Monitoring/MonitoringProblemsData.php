<?php

namespace App\DTOs\Monitoring;

class MonitoringProblemsData
{
    public function __construct(
        public readonly array $connection,
        public readonly array $items,
        public readonly array $summary,
        public readonly array $pagination,
        public readonly array $meta = [],
    ) {
    }

    public function toArray(): array
    {
        return [
            'connection' => $this->connection,
            'items' => $this->items,
            'summary' => $this->summary,
            'pagination' => $this->pagination,
            'meta' => $this->meta,
        ];
    }
}
