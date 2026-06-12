<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'http://localhost:5173',
        'http://127.0.0.1:5173',
        'http://localhost:3000',
        'https://ingeniotech-web.vercel.app',
    ],
    // Permite los preview deployments de Vercel (ingeniotech-web-*.vercel.app)
    'allowed_origins_patterns' => ['#^https://ingeniotech-web-.*\.vercel\.app$#'],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
