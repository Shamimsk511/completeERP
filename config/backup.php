<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Backup Storage Path
    |--------------------------------------------------------------------------
    |
    | The directory where backup files will be stored.
    |
    */
    'path' => storage_path('app/backups'),

    /*
    |--------------------------------------------------------------------------
    | Backup Retention
    |--------------------------------------------------------------------------
    |
    | How long to keep backups before auto-deletion.
    | keep_days: Delete backups older than this many days
    | max_backups: Keep maximum this many backup files
    |
    */
    'keep_days' => env('BACKUP_KEEP_DAYS', 30),
    'max_backups' => env('BACKUP_MAX_COUNT', 20),

    /*
    |--------------------------------------------------------------------------
    | File Directories to Backup
    |--------------------------------------------------------------------------
    |
    | Directories in storage/app/public that should be included in full backups.
    |
    */
    'file_directories' => [
        'products',              // Product images
        'invoices',              // Generated invoices
        'documents',             // Uploaded documents
        'imports',               // Import files
        'exports',               // Export files
        'attachments',           // File attachments
    ],

    /*
    |--------------------------------------------------------------------------
    | Excluded from File Backup
    |--------------------------------------------------------------------------
    |
    | Files or patterns to exclude from file backups.
    |
    */
    'exclude_patterns' => [
        '.gitignore',
        '.gitkeep',
        '*.tmp',
        'Thumbs.db',
        '.DS_Store',
        '*.log',
    ],

    /*
    |--------------------------------------------------------------------------
    | Backup Notifications
    |--------------------------------------------------------------------------
    |
    | Enable notifications for backup completion/failures
    |
    */
    'notifications_enabled' => env('BACKUP_NOTIFICATIONS', false),
    'notification_email' => env('BACKUP_NOTIFICATION_EMAIL', 'admin@example.com'),

    /*
    |--------------------------------------------------------------------------
    | Database Tables to Backup
    |--------------------------------------------------------------------------
    |
    | Optional: List of specific database tables (if needed)
    | By default, mysqldump backs up all tables automatically.
    |
    */
    'tables' => [
        // Core System
        'users',
        'roles',
        'permissions',
        'model_has_roles',
        'model_has_permissions',
        'role_has_permissions',

        // Business Data
        'companies',
        'categories',
        'products',
        'customers',
        'payees',

        // Transactions
        'invoices',
        'purchases',
        'transactions',
        'cash_registers',

        // Reports & Documents
        'delivery_orders',
        'product_returns',

        // System
        'settings',
        'activity_logs',

        // Laravel System Tables
        'migrations',
        'cache',
        'cache_locks',
        'sessions',
        'jobs',
        'job_batches',
        'failed_jobs',
        'password_reset_tokens',
    ],
];
