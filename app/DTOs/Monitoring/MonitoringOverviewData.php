<?php

namespace App\DTOs\Monitoring;

class MonitoringOverviewData
{
    public function __construct(
        public readonly array $connection,
        public readonly array $summaryCards,
        public readonly array $severityCards,
        public readonly array $hostRows,
        public readonly array $problemRows,
        public readonly array $eventRows,
        public readonly array $graphRows,
        public readonly array $availability,
        public readonly array $meta = [],
    ) {
    }

    public function toArray(): array
    {
        return [
            'connection' => $this->connection,
            'summaryCards' => $this->summaryCards,
            'severityCards' => $this->severityCards,
            'hostRows' => $this->hostRows,
            'problemRows' => $this->problemRows,
            'eventRows' => $this->eventRows,
            'graphRows' => $this->graphRows,
            'availability' => $this->availability,
            'meta' => $this->meta,
        ];
    }
}
