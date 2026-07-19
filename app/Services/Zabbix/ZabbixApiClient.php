<?php

namespace App\Services\Zabbix;

use App\DTOs\Zabbix\ZabbixApiRequestData;
use App\Models\ZabbixConnection;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class ZabbixApiClient
{
    /**
     * @throws ConnectionException
     */
    public function request(ZabbixConnection $connection, ZabbixApiRequestData $payload): array
    {
        $response = Http::baseUrl($this->endpoint($connection))
            ->timeout($connection->timeout_seconds ?: (int) config('zabbix.timeout', 30))
            ->acceptJson()
            ->asJson()
            ->post('', $payload->toArray());

        $this->ensureSuccessful($response);

        return $response->json();
    }

    public function login(ZabbixConnection $connection): string
    {
        if ($connection->api_token) {
            return $connection->api_token;
        }

        if (blank($connection->username) || blank($connection->password)) {
            throw new RuntimeException('Zabbix connection requires username/password or API token.');
        }

        $cacheKey = sprintf('zabbix.auth.token.%d', $connection->id);

        return Cache::remember($cacheKey, now()->addMinutes(50), function () use ($connection) {
            $payload = new ZabbixApiRequestData(
                method: 'user.login',
                params: [
                    'username' => $connection->username,
                    'password' => $connection->password,
                ],
                id: Str::uuid()->toString(),
            );

            $response = $this->request($connection, $payload);

            if (! isset($response['result']) || ! is_string($response['result'])) {
                throw new RuntimeException('Unable to authenticate to Zabbix API.');
            }

            return $response['result'];
        });
    }

    public function call(ZabbixConnection $connection, string $method, array $params = [], ?string $auth = null): array
    {
        $payload = new ZabbixApiRequestData(
            method: $method,
            params: $params,
            auth: $auth,
            id: Str::uuid()->toString(),
        );

        $response = $this->request($connection, $payload);

        if (array_key_exists('error', $response)) {
            $message = $response['error']['data'] ?? $response['error']['message'] ?? 'Unknown Zabbix API error.';
            throw new RuntimeException($message);
        }

        return $response;
    }

    private function endpoint(ZabbixConnection $connection): string
    {
        return rtrim($connection->base_url, '/') . config('zabbix.api_path', '/api_jsonrpc.php');
    }

    private function ensureSuccessful(Response $response): void
    {
        if (! $response->successful()) {
            throw new RuntimeException(sprintf(
                'Zabbix API request failed with HTTP %s.',
                $response->status()
            ));
        }
    }
}
