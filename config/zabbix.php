<?php

return [
    'api_path' => env('ZABBIX_API_PATH', '/api_jsonrpc.php'),
    'timeout' => env('ZABBIX_TIMEOUT', 30),
    'cache_ttl' => env('ZABBIX_CACHE_TTL', 300),
    'default_connection_id' => env('ZABBIX_DEFAULT_CONNECTION_ID'),
];
