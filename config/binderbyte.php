<?php

return [
    'api_key' => env('BINDERBYTE_API_KEY'),
    'base_url' => env('BINDERBYTE_BASE_URL', 'https://api.binderbyte.com'),
    'timeout' => env('BINDERBYTE_TIMEOUT', 30),
    'cache_ttl' => env('BINDERBYTE_CACHE_TTL', 3600), // 1 hour
];