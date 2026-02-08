<?php
return [
    // Cache settings
    'cache' => [
        'stats_ttl' => env('DEBT_COLLECTION_STATS_CACHE_TTL', 300), // 5 minutes
        'customers_ttl' => env('DEBT_COLLECTION_CUSTOMERS_CACHE_TTL', 60), // 1 minute
        'analytics_ttl' => env('DEBT_COLLECTION_ANALYTICS_CACHE_TTL', 600), // 10 minutes
    ],
    
    // Pagination settings
    'pagination' => [
        'per_page' => env('DEBT_COLLECTION_PER_PAGE', 25),
        'max_per_page' => env('DEBT_COLLECTION_MAX_PER_PAGE', 100),
    ],
    
    // Alert thresholds
    'thresholds' => [
        'high_balance' => env('DEBT_COLLECTION_HIGH_BALANCE', 10000),
        'overdue_days' => env('DEBT_COLLECTION_OVERDUE_DAYS', 30),
        'no_contact_days' => env('DEBT_COLLECTION_NO_CONTACT_DAYS', 14),
    ],
    
    // Export settings
    'export' => [
        'max_records' => env('DEBT_COLLECTION_EXPORT_MAX', 10000),
        'formats' => ['xlsx', 'csv', 'pdf'],
    ],
    
    // Call settings
    'calls' => [
        'max_duration' => env('DEBT_COLLECTION_MAX_CALL_DURATION', 999), // minutes
        'statuses' => ['successful', 'missed', 'busy', 'disconnected'],
        'outcomes' => ['payment_promised', 'payment_made', 'dispute', 'no_response', 'other'],
    ],
    
    // Priority levels
    'priorities' => [
        'low' => [
            'color' => '#10b981',
            'label' => 'Low Priority',
        ],
        'medium' => [
            'color' => '#f59e0b',
            'label' => 'Medium Priority',
        ],
        'high' => [
            'color' => '#ef4444',
            'label' => 'High Priority',
        ],
    ],
];