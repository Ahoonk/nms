<?php

namespace App\Services\Monitoring;

use App\DTOs\Monitoring\MonitoringAvailabilityData;
use App\DTOs\Monitoring\MonitoringEventsData;
use App\DTOs\Monitoring\MonitoringGraphsData;
use App\DTOs\Monitoring\MonitoringHostsData;
use App\DTOs\Monitoring\MonitoringOverviewData;
use App\DTOs\Monitoring\MonitoringProblemsData;
use App\Models\ZabbixConnection;
use App\Repositories\Contracts\DeviceRepositoryInterface;
use App\Services\Zabbix\ZabbixConnectionResolver;
use App\Services\Zabbix\ZabbixEventService;
use App\Services\Zabbix\ZabbixGraphService;
use App\Services\Zabbix\ZabbixHostService;
use App\Services\Zabbix\ZabbixProblemService;
use App\Services\WireGuard\WireGuardService;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Throwable;

class MonitoringService
{
    private const FETCH_LIMIT = 250;

    public function __construct(
        private readonly ZabbixConnectionResolver $resolver,
        private readonly ZabbixHostService $hosts,
        private readonly ZabbixProblemService $problems,
        private readonly ZabbixEventService $events,
        private readonly ZabbixGraphService $graphs,
        private readonly DeviceRepositoryInterface $devices,
        private readonly WireGuardService $wireguard,
    ) {
    }

    public function overview(?int $companyId = null): MonitoringOverviewData
    {
        return $this->remember('overview', $companyId, function () use ($companyId) {
            $connection = $this->resolver->resolve($companyId);

            if (! $connection) {
                return new MonitoringOverviewData(
                    connection: $this->connectionPayload(null, true),
                    summaryCards: [],
                    severityCards: [],
                    hostRows: [],
                    problemRows: [],
                    eventRows: [],
                    graphRows: [],
                    availability: $this->availabilitySummaryFromCounts(0, 0, 0),
                    meta: ['message' => 'No active Zabbix connection configured.'],
                );
            }

            $hostRows = $this->buildHosts($connection, 8, $companyId);
            $problemRows = $this->buildProblems($connection, 8);
            $eventRows = $this->buildEvents($connection, 8);
            $graphRows = $this->buildGraphs($connection, null, 6);

            $hostCounts = $this->countAvailability($hostRows);
            $severityCounts = $this->countSeverities($problemRows);

            return new MonitoringOverviewData(
                connection: $this->connectionPayload($connection),
                summaryCards: [
                    ['label' => 'Host Online', 'value' => $hostCounts['online']],
                    ['label' => 'Host Offline', 'value' => $hostCounts['offline']],
                    ['label' => 'Problem', 'value' => count($problemRows)],
                    ['label' => 'Latest Event', 'value' => count($eventRows)],
                ],
                severityCards: [
                    ['label' => 'High Severity', 'value' => $severityCounts['high']],
                    ['label' => 'Average', 'value' => $severityCounts['average']],
                    ['label' => 'Warning', 'value' => $severityCounts['warning']],
                    ['label' => 'Information', 'value' => $severityCounts['information']],
                ],
                hostRows: $hostRows,
                problemRows: $problemRows,
                eventRows: $eventRows,
                graphRows: $graphRows,
                availability: $this->availabilitySummaryFromCounts($hostCounts['online'], $hostCounts['offline'], $hostCounts['unknown']),
                meta: ['synced_at' => now()->toDateTimeString()],
            );
        });
    }

    public function problems(?int $companyId = null, array $filters = [], int $page = 1, int $perPage = 25): MonitoringProblemsData
    {
        $cacheKey = 'problems.' . $page . '.' . $perPage . '.' . md5(json_encode($filters));

        return $this->remember($cacheKey, $companyId, function () use ($companyId, $filters, $page, $perPage) {
            $connection = $this->resolver->resolve($companyId);

            if (! $connection) {
                return new MonitoringProblemsData(
                    connection: $this->connectionPayload(null, true),
                    items: [],
                    summary: [],
                    pagination: $this->paginationMeta(0, $page, $perPage),
                    meta: ['message' => 'No active Zabbix connection configured.'],
                );
            }

            $items = $this->filterProblems($this->buildProblems($connection, self::FETCH_LIMIT), $filters, $companyId);
            $severityCounts = $this->countSeverities($items);
            $pageData = $this->paginateRows($items, $page, $perPage);

            return new MonitoringProblemsData(
                connection: $this->connectionPayload($connection),
                items: $pageData['items'],
                summary: [
                    ['label' => 'Total Problems', 'value' => count($items)],
                    ['label' => 'High', 'value' => $severityCounts['high']],
                    ['label' => 'Average', 'value' => $severityCounts['average']],
                    ['label' => 'Warning', 'value' => $severityCounts['warning']],
                ],
                pagination: $pageData['pagination'],
                meta: ['synced_at' => now()->toDateTimeString()],
            );
        });
    }

    public function events(?int $companyId = null, array $filters = [], int $page = 1, int $perPage = 25): MonitoringEventsData
    {
        $cacheKey = 'events.' . $page . '.' . $perPage . '.' . md5(json_encode($filters));

        return $this->remember($cacheKey, $companyId, function () use ($companyId, $filters, $page, $perPage) {
            $connection = $this->resolver->resolve($companyId);

            if (! $connection) {
                return new MonitoringEventsData(
                    connection: $this->connectionPayload(null, true),
                    items: [],
                    pagination: $this->paginationMeta(0, $page, $perPage),
                    meta: ['message' => 'No active Zabbix connection configured.'],
                );
            }

            $items = $this->filterEvents($this->buildEvents($connection, self::FETCH_LIMIT), $filters, $companyId);
            $pageData = $this->paginateRows($items, $page, $perPage);

            return new MonitoringEventsData(
                connection: $this->connectionPayload($connection),
                items: $pageData['items'],
                pagination: $pageData['pagination'],
                meta: ['synced_at' => now()->toDateTimeString()],
            );
        });
    }

    public function hosts(?int $companyId = null, array $filters = [], int $page = 1, int $perPage = 25): MonitoringHostsData
    {
        $cacheKey = 'hosts.' . $page . '.' . $perPage . '.' . md5(json_encode($filters));

        return $this->remember($cacheKey, $companyId, function () use ($companyId, $filters, $page, $perPage) {
            $connection = $this->resolver->resolve($companyId);

            if (! $connection) {
                return new MonitoringHostsData(
                    connection: $this->connectionPayload(null, true),
                    items: [],
                    summary: [],
                    pagination: $this->paginationMeta(0, $page, $perPage),
                    meta: ['message' => 'No active Zabbix connection configured.'],
                );
            }

            $items = $this->filterHosts($this->buildHosts($connection, self::FETCH_LIMIT, $companyId), $filters, $companyId);
            $counts = $this->countAvailability($items);
            $pageData = $this->paginateRows($items, $page, $perPage);

            return new MonitoringHostsData(
                connection: $this->connectionPayload($connection),
                items: $pageData['items'],
                summary: [
                    ['label' => 'Online', 'value' => $counts['online']],
                    ['label' => 'Offline', 'value' => $counts['offline']],
                    ['label' => 'Unknown', 'value' => $counts['unknown']],
                ],
                pagination: $pageData['pagination'],
                meta: ['synced_at' => now()->toDateTimeString()],
            );
        });
    }

    public function graphs(?int $companyId = null, ?int $hostId = null, array $filters = [], int $page = 1, int $perPage = 20): MonitoringGraphsData
    {
        $cacheSuffix = implode('.', array_filter(['graphs', (string) $page, (string) $perPage, $hostId ? 'host-' . $hostId : null, md5(json_encode($filters))]));

        return $this->remember($cacheSuffix, $companyId, function () use ($companyId, $hostId, $filters, $page, $perPage) {
            $connection = $this->resolver->resolve($companyId);

            if (! $connection) {
                return new MonitoringGraphsData(
                    connection: $this->connectionPayload(null, true),
                    hosts: [],
                    items: [],
                    selectedHostId: $hostId,
                    pagination: $this->paginationMeta(0, $page, $perPage),
                    meta: ['message' => 'No active Zabbix connection configured.'],
                );
            }

            $hosts = $this->filterHosts($this->buildHosts($connection, self::FETCH_LIMIT, $companyId), $filters, $companyId);
            $selectedHostId = $hostId ?? $this->firstHostId($hosts);
            $items = $this->filterGraphs($this->buildGraphs($connection, $selectedHostId, self::FETCH_LIMIT), $filters, $companyId, $hosts);
            $pageData = $this->paginateRows($items, $page, $perPage);

            return new MonitoringGraphsData(
                connection: $this->connectionPayload($connection),
                hosts: $hosts,
                items: $pageData['items'],
                selectedHostId: $selectedHostId,
                pagination: $pageData['pagination'],
                meta: ['synced_at' => now()->toDateTimeString()],
            );
        });
    }

    public function availability(?int $companyId = null, array $filters = [], int $page = 1, int $perPage = 25): MonitoringAvailabilityData
    {
        $cacheKey = 'availability.' . $page . '.' . $perPage . '.' . md5(json_encode($filters));

        return $this->remember($cacheKey, $companyId, function () use ($companyId, $filters, $page, $perPage) {
            $connection = $this->resolver->resolve($companyId);

            if (! $connection) {
                return new MonitoringAvailabilityData(
                    connection: $this->connectionPayload(null, true),
                    summary: $this->availabilitySummaryFromCounts(0, 0, 0),
                    items: [],
                    pagination: $this->paginationMeta(0, $page, $perPage),
                    meta: ['message' => 'No active Zabbix connection configured.'],
                );
            }

            $items = $this->filterHosts($this->buildHosts($connection, self::FETCH_LIMIT, $companyId), $filters, $companyId);
            $counts = $this->countAvailability($items);
            $pageData = $this->paginateRows($items, $page, $perPage);

            return new MonitoringAvailabilityData(
                connection: $this->connectionPayload($connection),
                summary: $this->availabilitySummaryFromCounts($counts['online'], $counts['offline'], $counts['unknown']),
                items: $pageData['items'],
                pagination: $pageData['pagination'],
                meta: ['synced_at' => now()->toDateTimeString()],
            );
        });
    }

    public function dashboardTelemetry(?int $companyId = null): array
    {
        return $this->remember('dashboard-telemetry', $companyId, function () use ($companyId) {
            $connection = $this->resolver->resolve($companyId);

            if (! $connection) {
                return [
                    'connection' => $this->connectionPayload(null, true),
                    'cards' => $this->defaultTelemetryCards('No active Zabbix connection configured.'),
                    'meta' => ['message' => 'No active Zabbix connection configured.'],
                ];
            }

            $hosts = $this->buildHosts($connection, self::FETCH_LIMIT, $companyId);

            return [
                'connection' => $this->connectionPayload($connection),
                'cards' => $this->aggregateTelemetryCards($hosts),
                'meta' => ['synced_at' => now()->toDateTimeString()],
            ];
        });
    }

    public function featuredGraphs(?int $companyId = null): array
    {
        return $this->remember('featured-graphs', $companyId, function () use ($companyId) {
            $connection = $this->resolver->resolve($companyId);

            if (! $connection) {
                return [
                    'connection' => $this->connectionPayload(null, true),
                    'graphs' => [],
                    'meta' => ['message' => 'No active Zabbix connection configured.'],
                ];
            }

            $graphs = $this->buildGraphs($connection, null, self::FETCH_LIMIT);
            $featured = collect($graphs)->filter(function (array $graph): bool {
                $name = mb_strtolower((string) ($graph['name'] ?? ''));

                return str_contains($name, 'ether 1') && str_contains($name, 'network traffic');
            })->values()->all();

            return [
                'connection' => $this->connectionPayload($connection),
                'graphs' => $featured,
                'meta' => $featured
                    ? ['synced_at' => now()->toDateTimeString()]
                    : ['message' => 'No matching network traffic graph found.'],
            ];
        });
    }

    private function remember(string $key, ?int $companyId, callable $callback): mixed
    {
        return Cache::remember(
            $this->cacheKey($key, $companyId),
            now()->addSeconds((int) config('zabbix.cache_ttl', 30)),
            $callback
        );
    }

    private function cacheKey(string $key, ?int $companyId): string
    {
        return sprintf('monitoring.%s.%s', $key, $companyId ?? 'global');
    }

    private function connectionPayload(?ZabbixConnection $connection, bool $missing = false): array
    {
        if (! $connection) {
            return [
                'id' => null,
                'name' => 'No connection',
                'base_url' => null,
                'status' => $missing ? 'missing' : 'inactive',
                'scope' => 'global',
            ];
        }

        return [
            'id' => $connection->id,
            'name' => $connection->name,
            'base_url' => $connection->base_url,
            'status' => $connection->status,
            'scope' => $connection->company_id ? 'company' : 'global',
            'company_id' => $connection->company_id,
        ];
    }

    private function buildHosts(ZabbixConnection $connection, int $limit, ?int $companyId = null): array
    {
        $deviceIndex = $this->deviceIndex($companyId);

        try {
            $response = $this->hosts->list($connection, [
                'output' => ['hostid', 'host', 'name', 'status', 'available', 'error'],
                'selectGroups' => ['groupid', 'name'],
                'selectParentTemplates' => ['templateid', 'name'],
                'selectInterfaces' => ['interfaceid', 'ip', 'dns', 'useip', 'available', 'main'],
                'selectItems' => ['itemid', 'name', 'key_', 'lastvalue', 'lastclock', 'units'],
                'sortfield' => 'name',
                'sortorder' => 'ASC',
                'limit' => $limit,
            ]);
        } catch (Throwable $e) {
            throw $e;
        }

        return collect($response['result'] ?? [])
            ->map(function (array $host) use ($deviceIndex): array {

    $mainInterface = collect($host['interfaces'] ?? [])
        ->first(fn ($i) => (int) ($i['main'] ?? 0) === 1);

    if (!$mainInterface) {
        $mainInterface = $host['interfaces'][0] ?? [];
    }

    logger()->info([
        'host' => $host['name'],
        'host_available' => $host['available'] ?? null,
        'interface_available' => $mainInterface['available'] ?? null,
    ]);

    $icmpItem = collect($host['items'] ?? [])
        ->first(fn (array $item) => ($item['key_'] ?? '') === 'icmpping');

    if ($icmpItem && isset($icmpItem['lastvalue'])) {
        $availability = ((int) $icmpItem['lastvalue'] === 1)
            ? $this->availabilityMeta(1)
            : $this->availabilityMeta(2);
    } else {
        $availability = $this->availabilityMeta(
            (int) ($mainInterface['available'] ?? 0)
        );
    }

    $status = $this->hostStatusMeta((int) ($host['status'] ?? 0));
    $device = $this->matchDeviceToHost($host, $deviceIndex);

                return [
                    'hostid' => Arr::get($host, 'hostid'),
                    'name' => Arr::get($host, 'name', Arr::get($host, 'host')),
                    'host' => Arr::get($host, 'host'),
                    'ip' => $this->firstInterfaceIp($host),
                    'status' => $status['label'],
                    'status_class' => $status['class'],
                    'availability' => $availability['label'],
                    'availability_class' => $availability['class'],
                    'error' => Arr::get($host, 'error'),
                    'groups' => collect(Arr::get($host, 'groups', []))->pluck('name')->values()->all(),
                    'templates' => collect(Arr::get($host, 'parentTemplates', []))->pluck('name')->values()->all(),
                    'mapped_device' => (bool) $device,
                    'device' => $device,
                    'site' => $device['site'] ?? null,
                    'telemetry' => $this->hostTelemetry($host),
                    'latest_data' => collect(Arr::get($host, 'items', []))
                        ->take(3)
                        ->map(fn (array $item): array => [
                            'name' => Arr::get($item, 'name'),
                            'key' => Arr::get($item, 'key_'),
                            'lastvalue' => Arr::get($item, 'lastvalue', '-'),
                            'lastclock' => $this->formatTimestamp(Arr::get($item, 'lastclock')),
                        ])
                        ->values()
                        ->all(),
                ];
            })
            ->values()
            ->all();
    }

    private function buildProblems(ZabbixConnection $connection, int $limit): array
    {
        try {
            $response = $this->problems->list($connection, [
                'output' => ['eventid', 'objectid', 'clock', 'name', 'severity', 'acknowledged', 'r_eventid'],
                'selectTags' => 'extend',
                'sortfield' => 'eventid',
                'sortorder' => 'DESC',
                'recent' => true,
                'limit' => $limit,
            ]);
        } catch (Throwable $e) {
            throw $e;
        }

        return collect($response['result'] ?? [])
            ->map(function (array $problem): array {
                $severity = $this->severityMeta((int) ($problem['severity'] ?? 0));

                return [
                    'event_id' => Arr::get($problem, 'eventid'),
                    'host' => Arr::get($problem, 'name', '-'),
                    'severity' => $severity['label'],
                    'severity_class' => $severity['class'],
                    'duration' => $this->durationLabel((int) Arr::get($problem, 'clock')),
                    'status' => (int) Arr::get($problem, 'acknowledged') === 1 ? 'Acknowledged' : 'Open',
                    'status_class' => (int) Arr::get($problem, 'acknowledged') === 1 ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-300' : 'bg-rose-500/10 text-rose-600 dark:text-rose-300',
                    'tag' => $this->firstTag(Arr::get($problem, 'tags', [])),
                    'message' => Arr::get($problem, 'name', '-'),
                    'clock' => Arr::get($problem, 'clock'),
                    'clock_label' => $this->formatTimestamp(Arr::get($problem, 'clock')),
                    'acknowledged' => (int) Arr::get($problem, 'acknowledged') === 1,
                ];
            })
            ->values()
            ->all();
    }

    private function buildEvents(ZabbixConnection $connection, int $limit): array
    {
        try {
            $response = $this->events->list($connection, [
                'output' => ['eventid', 'source', 'object', 'objectid', 'clock', 'ns', 'value', 'severity', 'name', 'acknowledged'],
                'selectTags' => 'extend',
                'sortfield' => ['clock'],
                'sortorder' => 'DESC',
                'limit' => $limit,
            ]);
        } catch (Throwable $e) {
            throw $e;
        }

        return collect($response['result'] ?? [])
            ->map(function (array $event): array {
                $severity = $this->severityMeta((int) ($event['severity'] ?? 0));

                return [
                    'event_id' => Arr::get($event, 'eventid'),
                    'source' => Arr::get($event, 'source'),
                    'object' => Arr::get($event, 'object'),
                    'name' => Arr::get($event, 'name', '-'),
                    'type' => (int) Arr::get($event, 'value') === 1 ? 'Problem' : 'Recovery',
                    'type_class' => (int) Arr::get($event, 'value') === 1 ? 'bg-rose-500/10 text-rose-600 dark:text-rose-300' : 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-300',
                    'severity' => $severity['label'],
                    'severity_class' => $severity['class'],
                    'status' => (int) Arr::get($event, 'acknowledged') === 1 ? 'Acknowledged' : 'Open',
                    'status_class' => (int) Arr::get($event, 'acknowledged') === 1 ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-300' : 'bg-slate-500/10 text-slate-600 dark:text-slate-300',
                    'message' => Arr::get($event, 'name', '-'),
                    'clock' => Arr::get($event, 'clock'),
                    'clock_label' => $this->formatTimestamp(Arr::get($event, 'clock')),
                ];
            })
            ->values()
            ->all();
    }

    private function buildGraphs(ZabbixConnection $connection, ?int $hostId, int $limit): array
    {
        $params = [
            'output' => ['graphid', 'name', 'width', 'height'],
            'selectHosts' => ['hostid', 'name', 'host'],
            'sortfield' => 'name',
            'sortorder' => 'ASC',
            'limit' => $limit,
        ];

        if ($hostId) {
            $params['hostids'] = [$hostId];
        }

        try {
            $response = $this->graphs->list($connection, $params);
        } catch (Throwable) {
            return [];
        }

        return collect($response['result'] ?? [])
            ->map(function (array $graph) use ($connection): array {
                $host = Arr::get($graph, 'hosts.0.name') ?? Arr::get($graph, 'hosts.0.host') ?? '-';
                $graphId = Arr::get($graph, 'graphid');

                return [
    'graph_id' => $graphId,
    'name' => Arr::get($graph, 'name', '-'),
    'host' => $host,

    // link ke halaman graph Zabbix
    'link' => $this->graphLink($connection, $graphId),

    // endpoint image Laravel
    'image' => route('monitoring.graph.image', [
        'graph' => $graphId,
    ]),

    'size' => sprintf(
        '%sx%s',
        Arr::get($graph, 'width', 0),
        Arr::get($graph, 'height', 0)
    ),
];
            })
            ->values()
            ->all();
    }

    private function filterProblems(array $rows, array $filters, ?int $companyId = null): array
    {
        return collect($rows)
            ->filter(function (array $row) use ($filters): bool {
                if (filled($filters['search'] ?? null) && ! $this->rowMatchesSearch($row, (string) $filters['search'])) {
                    return false;
                }

                if (filled($filters['severity'] ?? null) && strtolower((string) ($row['severity'] ?? '')) !== strtolower((string) $filters['severity'])) {
                    return false;
                }

                if (filled($filters['status'] ?? null) && strtolower((string) ($row['status'] ?? '')) !== strtolower((string) $filters['status'])) {
                    return false;
                }

                return true;
            })
            ->values()
            ->all();
    }

    private function filterEvents(array $rows, array $filters, ?int $companyId = null): array
    {
        return collect($rows)
            ->filter(function (array $row) use ($filters): bool {
                if (filled($filters['search'] ?? null) && ! $this->rowMatchesSearch($row, (string) $filters['search'])) {
                    return false;
                }

                if (filled($filters['severity'] ?? null) && strtolower((string) ($row['severity'] ?? '')) !== strtolower((string) $filters['severity'])) {
                    return false;
                }

                if (filled($filters['status'] ?? null) && strtolower((string) ($row['status'] ?? '')) !== strtolower((string) $filters['status'])) {
                    return false;
                }

                if (filled($filters['type'] ?? null) && strtolower((string) ($row['type'] ?? '')) !== strtolower((string) $filters['type'])) {
                    return false;
                }

                return true;
            })
            ->values()
            ->all();
    }

    private function filterHosts(array $rows, array $filters, ?int $companyId = null): array
    {
        return collect($rows)
            ->filter(function (array $row) use ($filters): bool {
                if (filled($filters['search'] ?? null) && ! $this->rowMatchesSearch($row, (string) $filters['search'])) {
                    return false;
                }

                if (filled($filters['status'] ?? null) && strtolower((string) ($row['status'] ?? '')) !== strtolower((string) $filters['status'])) {
                    return false;
                }

                if (filled($filters['availability'] ?? null) && strtolower((string) ($row['availability'] ?? '')) !== strtolower((string) $filters['availability'])) {
                    return false;
                }

                if (filled($filters['device_type'] ?? null) && strtolower((string) Arr::get($row, 'device.type', '')) !== strtolower((string) $filters['device_type'])) {
                    return false;
                }

                if (filled($filters['site_id'] ?? null) && (int) Arr::get($row, 'site.id', 0) !== (int) $filters['site_id']) {
                    return false;
                }

                return true;
            })
            ->values()
            ->all();
    }

    private function filterGraphs(array $rows, array $filters, ?int $companyId = null, array $hosts = []): array
    {
        return collect($rows)
            ->filter(function (array $row) use ($filters): bool {
                if (filled($filters['search'] ?? null) && ! $this->rowMatchesSearch($row, (string) $filters['search'])) {
                    return false;
                }

                return true;
            })
            ->values()
            ->all();
    }

    private function rowMatchesSearch(array $row, string $search): bool
    {
        $needle = mb_strtolower(trim($search));
        $haystack = mb_strtolower((string) json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return $needle === '' || str_contains($haystack, $needle);
    }

    private function paginateRows(array $rows, int $page, int $perPage): array
    {
        $page = max(1, $page);
        $perPage = max(1, $perPage);
        $total = count($rows);
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min($page, $lastPage);
        $offset = ($page - 1) * $perPage;
        $items = array_slice($rows, $offset, $perPage);
        $from = $total === 0 ? 0 : $offset + 1;
        $to = min($offset + $perPage, $total);

        return [
            'items' => $items,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => $lastPage,
                'from' => $from,
                'to' => $to,
                'has_more' => $page < $lastPage,
            ],
        ];
    }

    private function paginationMeta(int $total, int $page, int $perPage): array
    {
        $page = max(1, $page);
        $perPage = max(1, $perPage);
        $lastPage = max(1, (int) ceil($total / $perPage));

        return [
            'current_page' => min($page, $lastPage),
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => $lastPage,
            'from' => $total > 0 ? (($page - 1) * $perPage) + 1 : 0,
            'to' => $total > 0 ? min($page * $perPage, $total) : 0,
            'has_more' => $page < $lastPage,
        ];
    }

    private function deviceIndex(?int $companyId): array
    {
        $devices = $this->devices->allByCompany($companyId);
        $byHostId = [];
        $byHostname = [];

        foreach ($devices as $device) {
            $payload = [
                'id' => $device->id,
                'hostname' => $device->hostname,
                'ip' => $device->ip,
                'device_type' => $device->device_type instanceof \BackedEnum ? $device->device_type->value : (string) $device->device_type,
                'status' => $device->status instanceof \BackedEnum ? $device->status->value : (string) $device->status,
                'zabbix_host_id' => $device->zabbix_host_id,
                'site' => $device->site ? [
                    'id' => $device->site->id,
                    'name' => $device->site->name,
                    'company_id' => $device->site->company_id,
                    'company_name' => $device->site->company?->name,
                ] : null,
            ];

            if (filled($device->zabbix_host_id)) {
                $byHostId[(string) $device->zabbix_host_id] = $payload;
            }

            if (filled($device->hostname)) {
                $byHostname[mb_strtolower($device->hostname)] = $payload;
                $byHostname[$this->normalizeLookupKey($device->hostname)] = $payload;
            }

            if (filled($device->ip)) {
                $byHostname[$this->normalizeLookupKey($device->ip)] = $payload;
            }
        }

        return [
            'byHostId' => $byHostId,
            'byHostname' => $byHostname,
        ];
    }

    private function hostTelemetry(array $host): array
    {
        $items = collect(Arr::get($host, 'items', []))->values();

        return [
            'cpu' => $this->telemetryFromItem($this->findTelemetryItem($items->all(), [
                'system.cpu.util',
                'cpu utilization',
                'cpu',
            ])),
            'ram' => $this->telemetryFromItem($this->findTelemetryItem($items->all(), [
                'vm.memory.size',
                'memory utilization',
                'used memory',
                'memory',
                'ram',
            ])),
            'bandwidth' => $this->telemetryFromItem($this->findTelemetryItem($items->all(), [
                'interface traffic',
                'network traffic',
                'net.if',
                'bandwidth',
                'traffic',
            ])),
        ];
    }

    private function findTelemetryItem(array $items, array $patterns): ?array
    {
        foreach ($patterns as $pattern) {
            $needle = mb_strtolower(trim($pattern));

            foreach ($items as $item) {
                if (! is_array($item)) {
                    continue;
                }

                $haystack = mb_strtolower(implode(' ', [
                    (string) Arr::get($item, 'name', ''),
                    (string) Arr::get($item, 'key_', ''),
                ]));

                if ($needle !== '' && str_contains($haystack, $needle)) {
                    return $item;
                }
            }
        }

        return null;
    }

    private function telemetryFromItem(?array $item): ?array
    {
        if (! $item) {
            return null;
        }

        $value = Arr::get($item, 'lastvalue');

        if ($value === null || $value === '') {
            return null;
        }

        return [
            'label' => Arr::get($item, 'name', '-'),
            'key' => Arr::get($item, 'key_'),
            'value' => $value,
            'unit' => Arr::get($item, 'units'),
            'numeric' => is_numeric($value) ? (float) $value : null,
        ];
    }

    private function aggregateTelemetryCards(array $hosts): array
    {
        $metrics = [
            'cpu' => ['label' => 'CPU', 'unit' => '%', 'empty' => 'Awaiting live telemetry'],
            'ram' => ['label' => 'RAM', 'unit' => '%', 'empty' => 'Awaiting live telemetry'],
            'bandwidth' => ['label' => 'Bandwidth', 'unit' => '', 'empty' => 'Awaiting live telemetry'],
        ];

        $cards = [];

        foreach ($metrics as $key => $config) {
            $samples = collect($hosts)
                ->map(fn (array $host): ?array => Arr::get($host, 'telemetry.' . $key))
                ->filter(fn ($sample): bool => is_array($sample) && array_key_exists('numeric', $sample) && $sample['numeric'] !== null)
                ->values();

            if ($samples->isEmpty()) {
                $cards[] = [
                    'label' => $config['label'],
                    'value' => 'N/A',
                    'hint' => $config['empty'],
                ];
                continue;
            }

            $average = round($samples->avg('numeric'), 2);
            $cards[] = [
                'label' => $config['label'],
                'value' => $this->formatTelemetryValue($average, $config['unit']),
                'hint' => sprintf('Avg from %d hosts', $samples->count()),
            ];
        }

        $cards[] = [
            'label' => 'Availability',
            'value' => sprintf('%s%%', $this->availabilitySummaryFromCounts(
                collect($hosts)->filter(fn (array $host): bool => ($host['availability'] ?? '') === 'Online')->count(),
                collect($hosts)->filter(fn (array $host): bool => ($host['availability'] ?? '') === 'Offline')->count(),
                collect($hosts)->filter(fn (array $host): bool => ! in_array($host['availability'] ?? '', ['Online', 'Offline'], true))->count(),
            )['uptime']),
            'hint' => 'Derived from Zabbix host availability',
        ];

        return $cards;
    }

    private function defaultTelemetryCards(string $hint): array
    {
        return [
            ['label' => 'CPU', 'value' => 'N/A', 'hint' => $hint],
            ['label' => 'RAM', 'value' => 'N/A', 'hint' => $hint],
            ['label' => 'Bandwidth', 'value' => 'N/A', 'hint' => $hint],
            ['label' => 'Availability', 'value' => '0%', 'hint' => $hint],
        ];
    }

    private function formatTelemetryValue(float|int $value, string $unit = ''): string
    {
        $unit = trim($unit);

        if ($unit === '%' || $unit === 'percent') {
            return rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.') . '%';
        }

        if ($unit === '') {
            return rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');
        }

        return rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.') . ' ' . $unit;
    }

    private function matchDeviceToHost(array $host, array $deviceIndex): ?array
    {
        $hostId = (string) Arr::get($host, 'hostid', '');
        $hostName = mb_strtolower((string) (Arr::get($host, 'host') ?? Arr::get($host, 'name') ?? ''));
        $normalizedHostName = $this->normalizeLookupKey($hostName);
        $interfaces = Arr::get($host, 'interfaces', []);
        $firstInterface = Arr::first($interfaces);
        $hostIp = is_array($firstInterface) ? (string) Arr::get($firstInterface, 'ip', '') : '';

        return $deviceIndex['byHostId'][$hostId]
            ?? $deviceIndex['byHostname'][$hostName]
            ?? $deviceIndex['byHostname'][$normalizedHostName]
            ?? ($hostIp ? ($deviceIndex['byHostname'][$this->normalizeLookupKey($hostIp)] ?? null) : null)
            ?? null;
    }

    private function normalizeLookupKey(string $value): string
    {
        return mb_strtolower(preg_replace('/[^a-z0-9]+/i', '', trim($value)) ?? '');
    }

    private function graphLink(ZabbixConnection $connection, mixed $graphId): ?string
    {
        if (! $graphId) {
            return null;
        }

        return rtrim($connection->base_url, '/') . '/chart2.php?graphid=' . $graphId . '&from=now-24h&to=now';
    }

    private function firstHostId(array $hosts): ?int
    {
        $first = Arr::first($hosts);

        return $first ? (int) Arr::get($first, 'hostid') : null;
    }

    private function firstInterfaceIp(array $host): ?string
    {
        $interfaces = Arr::get($host, 'interfaces', []);
        $first = Arr::first($interfaces);

        return $first ? Arr::get($first, 'ip') : null;
    }

    private function firstTag(array $tags): ?string
    {
        $first = Arr::first($tags);

        if (! is_array($first)) {
            return null;
        }

        $tag = Arr::get($first, 'tag');
        $value = Arr::get($first, 'value');

        if (! $tag && ! $value) {
            return null;
        }

        return trim(sprintf('%s%s%s', $tag ?? '', $tag && $value ? ': ' : '', $value ?? ''));
    }

    private function severityMeta(int $severity): array
    {
        return match ($severity) {
            5 => ['label' => 'Disaster', 'class' => 'bg-rose-600/10 text-rose-700 dark:text-rose-300'],
            4 => ['label' => 'High', 'class' => 'bg-orange-500/10 text-orange-700 dark:text-orange-300'],
            3 => ['label' => 'Average', 'class' => 'bg-amber-500/10 text-amber-700 dark:text-amber-300'],
            2 => ['label' => 'Warning', 'class' => 'bg-yellow-500/10 text-yellow-700 dark:text-yellow-300'],
            1 => ['label' => 'Information', 'class' => 'bg-sky-500/10 text-sky-700 dark:text-sky-300'],
            default => ['label' => 'Not classified', 'class' => 'bg-slate-500/10 text-slate-700 dark:text-slate-300'],
        };
    }

    private function hostStatusMeta(int $status): array
    {
        return match ($status) {
            0 => ['label' => 'Enabled', 'class' => 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300'],
            1 => ['label' => 'Disabled', 'class' => 'bg-slate-500/10 text-slate-700 dark:text-slate-300'],
            default => ['label' => 'Unknown', 'class' => 'bg-amber-500/10 text-amber-700 dark:text-amber-300'],
        };
    }

    private function availabilityMeta(int $available): array
    {
        return match ($available) {
            1 => ['label' => 'Online', 'class' => 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300'],
            2 => ['label' => 'Offline', 'class' => 'bg-rose-500/10 text-rose-700 dark:text-rose-300'],
            default => ['label' => 'Unknown', 'class' => 'bg-slate-500/10 text-slate-700 dark:text-slate-300'],
        };
    }

    private function countAvailability(array $rows): array
    {
        return collect($rows)->reduce(function (array $carry, array $row): array {
            $availability = $row['availability'] ?? 'Unknown';

            if ($availability === 'Online') {
                $carry['online']++;
            } elseif ($availability === 'Offline') {
                $carry['offline']++;
            } else {
                $carry['unknown']++;
            }

            return $carry;
        }, ['online' => 0, 'offline' => 0, 'unknown' => 0]);
    }

    private function countSeverities(array $rows): array
    {
        return collect($rows)->reduce(function (array $carry, array $row): array {
            $severity = $row['severity'] ?? '';

            if ($severity === 'High' || $severity === 'Disaster') {
                $carry['high']++;
            } elseif ($severity === 'Average') {
                $carry['average']++;
            } elseif ($severity === 'Warning') {
                $carry['warning']++;
            } elseif ($severity === 'Information') {
                $carry['information']++;
            }

            return $carry;
        }, ['high' => 0, 'average' => 0, 'warning' => 0, 'information' => 0]);
    }

    private function formatTimestamp(mixed $timestamp): ?string
    {
        if (! is_numeric($timestamp)) {
            return null;
        }

        return Carbon::createFromTimestamp((int) $timestamp)->format('Y-m-d H:i:s');
    }

    private function durationLabel(int $timestamp): string
    {
        if ($timestamp <= 0) {
            return '-';
        }

        return Carbon::createFromTimestamp($timestamp)->diffForHumans(now(), true);
    }

    private function availabilitySummaryFromCounts(int $online, int $offline, int $unknown): array
    {
        $total = $online + $offline + $unknown;
        $uptime = $total > 0 ? round(($online / $total) * 100, 2) : 0;

        return [
            'total' => $total,
            'online' => $online,
            'offline' => $offline,
            'unknown' => $unknown,
            'uptime' => $uptime,
        ];
    }

private function buildWireGuardPeers(): array
{
    try {
        $peers = $this->wireguard->peers();
    } catch (\Throwable $e) {
        return [];
    }

    return collect($peers)
        ->map(function ($peer) {

            $lastHandshake = (int)$peer['last_handshake'];

            return [

                'interface' => $peer['interface'],

                'public_key' => substr($peer['public_key'],0,12).'...',

                'endpoint' => $peer['endpoint'],

                'allowed_ips' => $peer['allowed_ips'],

                'status' => $lastHandshake > (time()-180)
                    ? 'Online'
                    : 'Offline',

                'rx' => $this->formatBytes((int)$peer['rx_bytes']),

                'tx' => $this->formatBytes((int)$peer['tx_bytes']),

                'last_seen' => $lastHandshake
                    ? Carbon::createFromTimestamp($lastHandshake)->diffForHumans()
                    : 'Never',
            ];
        })
        ->values()
        ->all();
}

private function formatBytes(int $bytes): string
{
    if ($bytes >= 1073741824) {
        return round($bytes / 1073741824,2).' GB';
    }

    if ($bytes >= 1048576) {
        return round($bytes / 1048576,2).' MB';
    }

    if ($bytes >= 1024) {
        return round($bytes / 1024,2).' KB';
    }

    return $bytes.' B';
}
}
