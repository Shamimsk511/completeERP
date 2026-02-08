<?php

return [
    'enabled' => env('PERF_MODE', false),
    'ttl' => [
        'dashboard' => env('PERF_TTL_DASHBOARD', 60),
        'customer_dashboard' => env('PERF_TTL_CUSTOMER_DASHBOARD', 60),
        'cashflow_summary' => env('PERF_TTL_CASHFLOW', 120),
        'sales_insights' => env('PERF_TTL_SALES_INSIGHTS', 120),
        'lookup_lists' => env('PERF_TTL_LOOKUPS', 300),
    ],
];
