<?php

namespace App\DTOs\Dashboard;

readonly class DashboardSummaryData
{
    public function __construct(
        public int $totalCompany,
        public int $totalSite,
        public int $totalDevice,
        public int $hostOnline,
        public int $hostOffline,
        public int $problem,
    ) {
    }

    public function toArray(): array
    {
        return [
            'totalCompany' => $this->totalCompany,
            'totalSite' => $this->totalSite,
            'totalDevice' => $this->totalDevice,
            'hostOnline' => $this->hostOnline,
            'hostOffline' => $this->hostOffline,
            'problem' => $this->problem,
        ];
    }

    public function cards(): array
    {
        return collect([
            ['label' => 'Total Company', 'value' => $this->totalCompany, 'hint' => 'Multi-company ready'],
            ['label' => 'Total Site', 'value' => $this->totalSite, 'hint' => 'Branch and datacenter nodes'],
            ['label' => 'Total Device', 'value' => $this->totalDevice, 'hint' => 'Router, switch, firewall, and more'],
            ['label' => 'Host Online', 'value' => $this->hostOnline, 'hint' => 'Zabbix API source of truth'],
            ['label' => 'Host Offline', 'value' => $this->hostOffline, 'hint' => 'Awaiting live telemetry'],
            ['label' => 'Problem', 'value' => $this->problem, 'hint' => 'Problem feed will land in Stage 2'],
        ])->values()->all();
    }
}
