<?php

return [
    'testEndpoint' => env('SIMPLEPAY_TEST_ENDPOINT', 'https://test.oppwa.com/'),
    'liveEndpoint' => env('SIMPLEPAY_LIVE_ENDPOINT', 'https://oppwa.com/'),
    'version' => env('SIMPLEPAY_VERSION', 'v1'),
    'environment' => env('SIMPLEPAY_ENVIRONMENT', 'test'),
    'userId' => env('SIMPLEPAY_USER_ID', '8a8294184e542a5c014e691d340708cc'),
    'password' => env('SIMPLEPAY_PASSWORD', '2gmZHAeSWK'),
    'entityId' => env('SIMPLEPAY_ENTITY_ID', '8a8294184e542a5c014e691d33f808c8'),
];
