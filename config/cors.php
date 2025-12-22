<?php


return [
  'paths' => ['api/*', 'sanctum/csrf-cookie', '/oauth/*'],
  'allowed_methods'   => ['*'],
  'allowed_origins'   => [
    'http://localhost:5173',
    '*'
  ],
  'allowed_headers'   => ['*'],
  'exposed_headers'   => [],
  'max_age'           => 3600,
  'supports_credentials' => false,
];
