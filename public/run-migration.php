<?php
/**
 * Emergency Migration Runner
 *
 * This script runs pending migrations without terminal access.
 * DELETE THIS FILE AFTER USE for security!
 *
 * Access: http://yourdomain.com/run-migration.php
 */

// Security check - require a secret key
$secretKey = 'run_migrations_2026';

if (!isset($_GET['key']) || $_GET['key'] !== $secretKey) {
    die('Access denied. Use: ?key=' . $secretKey);
}

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "<pre style='font-family: monospace; background: #1e1e1e; color: #00ff00; padding: 20px; border-radius: 10px;'>";
echo "=== ERP26 Migration Runner ===\n\n";

try {
    // Check database connection
    echo "Checking database connection...\n";
    DB::connection()->getPdo();
    echo "✓ Database connected: " . DB::connection()->getDatabaseName() . "\n\n";

    // Show pending migrations
    echo "Checking pending migrations...\n";

    $migrationPath = database_path('migrations');
    $migrationFiles = collect(File::files($migrationPath))
        ->map(fn($file) => pathinfo($file->getFilename(), PATHINFO_FILENAME))
        ->sort()
        ->values();

    $ranMigrations = collect();
    if (Schema::hasTable('migrations')) {
        $ranMigrations = DB::table('migrations')->pluck('migration');
    }

    $pending = $migrationFiles->diff($ranMigrations);

    echo "Total migrations: " . $migrationFiles->count() . "\n";
    echo "Completed: " . $ranMigrations->count() . "\n";
    echo "Pending: " . $pending->count() . "\n\n";

    if ($pending->count() > 0) {
        echo "Pending migrations:\n";
        foreach ($pending as $migration) {
            echo "  - $migration\n";
        }
        echo "\n";

        // Run migrations
        if (isset($_GET['run']) && $_GET['run'] === 'yes') {
            echo "Running migrations...\n\n";

            Artisan::call('migrate', ['--force' => true]);
            $output = Artisan::output();

            echo $output;
            echo "\n✓ Migrations completed!\n";

            // Clear caches
            echo "\nClearing caches...\n";
            Artisan::call('config:clear');
            Artisan::call('cache:clear');
            Artisan::call('view:clear');
            echo "✓ Caches cleared!\n";

            echo "\n=== DONE! ===\n";
            echo "\n⚠️  DELETE THIS FILE NOW for security!\n";
            echo "File location: " . __FILE__ . "\n";
        } else {
            echo "To run migrations, add: &run=yes\n";
            echo "URL: " . $_SERVER['REQUEST_URI'] . "&run=yes\n";
        }
    } else {
        echo "✓ All migrations are already applied!\n";
    }

} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString();
}

echo "</pre>";
