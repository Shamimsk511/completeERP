<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class DatabaseUpdateController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
        // Only allow users with system management permissions
        // You can customize this check based on your permission system
    }

    /**
     * Display the database update page
     */
    public function index()
    {
        $migrationStatus = $this->getMigrationStatus();
        $systemInfo = $this->getSystemInfo();

        return view('system.database-update', compact('migrationStatus', 'systemInfo'));
    }

    /**
     * Get migration status - which have run and which are pending
     */
    private function getMigrationStatus()
    {
        $migrationPath = database_path('migrations');
        $migrationFiles = collect(File::files($migrationPath))
            ->map(function ($file) {
                return pathinfo($file->getFilename(), PATHINFO_FILENAME);
            })
            ->sort()
            ->values();

        // Get ran migrations from database
        $ranMigrations = collect();
        if (Schema::hasTable('migrations')) {
            $ranMigrations = DB::table('migrations')->pluck('migration');
        }

        $pending = [];
        $completed = [];

        foreach ($migrationFiles as $migration) {
            $info = $this->parseMigrationName($migration);

            if ($ranMigrations->contains($migration)) {
                $completed[] = [
                    'name' => $migration,
                    'description' => $info['description'],
                    'date' => $info['date'],
                    'batch' => $this->getMigrationBatch($migration),
                ];
            } else {
                $pending[] = [
                    'name' => $migration,
                    'description' => $info['description'],
                    'date' => $info['date'],
                ];
            }
        }

        return [
            'pending' => $pending,
            'completed' => $completed,
            'pending_count' => count($pending),
            'completed_count' => count($completed),
            'total' => count($migrationFiles),
        ];
    }

    /**
     * Parse migration name to get human-readable description
     */
    private function parseMigrationName($name)
    {
        // Extract date (first 4 parts: YYYY_MM_DD_HHMMSS)
        $parts = explode('_', $name);
        $dateParts = array_slice($parts, 0, 4);
        $descParts = array_slice($parts, 4);

        $date = '';
        if (count($dateParts) >= 3) {
            $date = $dateParts[0] . '-' . $dateParts[1] . '-' . $dateParts[2];
        }

        // Convert remaining parts to readable description
        $description = ucwords(str_replace('_', ' ', implode('_', $descParts)));

        return [
            'date' => $date,
            'description' => $description,
        ];
    }

    /**
     * Get migration batch number
     */
    private function getMigrationBatch($migration)
    {
        if (!Schema::hasTable('migrations')) {
            return null;
        }

        return DB::table('migrations')
            ->where('migration', $migration)
            ->value('batch');
    }

    /**
     * Get system information
     */
    private function getSystemInfo()
    {
        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'database_connection' => config('database.default'),
            'database_name' => DB::connection()->getDatabaseName(),
            'app_environment' => app()->environment(),
            'app_debug' => config('app.debug'),
            'timezone' => config('app.timezone'),
        ];
    }

    /**
     * Run all pending migrations
     */
    public function runMigrations(Request $request)
    {
        try {
            // Increase execution time for migrations
            set_time_limit(300);

            // Run migrations
            $options = ['--force' => true];
            if ($request->boolean('seed')) {
                $options['--seed'] = true;
                if ($request->filled('seeder')) {
                    $options['--seeder'] = $request->input('seeder');
                }
            }

            Artisan::call('migrate', $options);
            $output = Artisan::output();

            // Clear caches after migration
            $this->clearCaches();

            return response()->json([
                'success' => true,
                'message' => 'Migrations completed successfully!',
                'output' => $output,
                'status' => $this->getMigrationStatus(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Migration failed: ' . $e->getMessage(),
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null,
            ], 500);
        }
    }

    /**
     * Run a specific migration
     */
    public function runSingleMigration(Request $request)
    {
        $request->validate([
            'migration' => 'required|string'
        ]);

        $migrationName = $request->migration;
        $migrationPath = database_path('migrations');

        // Find the migration file
        $files = File::files($migrationPath);
        $migrationFile = null;

        foreach ($files as $file) {
            if (pathinfo($file->getFilename(), PATHINFO_FILENAME) === $migrationName) {
                $migrationFile = $file;
                break;
            }
        }

        if (!$migrationFile) {
            return response()->json([
                'success' => false,
                'message' => 'Migration file not found: ' . $migrationName,
            ], 404);
        }

        try {
            set_time_limit(120);

            // Include and run the migration
            $migration = require $migrationFile->getPathname();

            DB::beginTransaction();

            if (method_exists($migration, 'up')) {
                $migration->up();
            }

            // Record the migration
            $batch = DB::table('migrations')->max('batch') + 1;
            DB::table('migrations')->insert([
                'migration' => $migrationName,
                'batch' => $batch,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Migration '{$migrationName}' completed successfully!",
                'status' => $this->getMigrationStatus(),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Migration failed: ' . $e->getMessage(),
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null,
            ], 500);
        }
    }

    /**
     * Check database connection and tables
     */
    public function checkDatabase()
    {
        try {
            // Test connection
            DB::connection()->getPdo();

            // Get table list
            $tables = DB::select('SHOW TABLES');
            $tableKey = 'Tables_in_' . DB::connection()->getDatabaseName();

            $tableList = collect($tables)->map(function ($table) use ($tableKey) {
                return $table->$tableKey;
            })->toArray();

            // Check critical tables
            $criticalTables = [
                'users', 'customers', 'products', 'invoices', 'invoice_items',
                'transactions', 'categories', 'companies', 'migrations',
            ];

            $missingTables = array_diff($criticalTables, $tableList);

            return response()->json([
                'success' => true,
                'connected' => true,
                'database' => DB::connection()->getDatabaseName(),
                'table_count' => count($tableList),
                'tables' => $tableList,
                'critical_tables_ok' => empty($missingTables),
                'missing_tables' => $missingTables,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'connected' => false,
                'message' => 'Database connection failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear all caches
     */
    public function clearCaches()
    {
        try {
            Artisan::call('config:clear');
            Artisan::call('cache:clear');
            Artisan::call('view:clear');
            Artisan::call('route:clear');

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'All caches cleared successfully!',
                ]);
            }

            return true;

        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cache clear failed: ' . $e->getMessage(),
                ], 500);
            }

            return false;
        }
    }

    /**
     * Optimize application
     */
    public function optimize()
    {
        try {
            Artisan::call('config:cache');
            Artisan::call('route:cache');
            Artisan::call('view:cache');

            return response()->json([
                'success' => true,
                'message' => 'Application optimized successfully!',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Optimization failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle debug mode by updating APP_DEBUG in .env
     */
    public function toggleDebug(Request $request)
    {
        $envPath = base_path('.env');

        if (!File::exists($envPath)) {
            return response()->json([
                'success' => false,
                'message' => 'Environment file not found.',
            ], 404);
        }

        if (!is_writable($envPath)) {
            return response()->json([
                'success' => false,
                'message' => 'Environment file is not writable. Update file permissions and try again.',
            ], 403);
        }

        try {
            $current = (bool) config('app.debug');
            $target = $request->has('debug') ? (bool) $request->boolean('debug') : !$current;
            $envValue = $target ? 'true' : 'false';

            $envContent = File::get($envPath);
            if (preg_match('/^APP_DEBUG=.*/m', $envContent)) {
                $envContent = preg_replace('/^APP_DEBUG=.*/m', 'APP_DEBUG=' . $envValue, $envContent, 1);
            } else {
                $envContent = rtrim($envContent) . PHP_EOL . 'APP_DEBUG=' . $envValue . PHP_EOL;
            }

            File::put($envPath, $envContent);

            config(['app.debug' => $target]);
            Artisan::call('config:clear');

            return response()->json([
                'success' => true,
                'message' => $target ? 'Debug mode enabled.' : 'Debug mode disabled.',
                'debug' => $target,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle debug mode: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create storage link (for shared hosting)
     */
    public function createStorageLink()
    {
        try {
            $target = storage_path('app/public');
            $link = public_path('storage');

            if (file_exists($link)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Storage link already exists.',
                ]);
            }

            // Try symlink first
            if (@symlink($target, $link)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Storage link created successfully!',
                ]);
            }

            // Fallback: copy files instead (for shared hosting that doesn't support symlinks)
            if (!file_exists($link)) {
                mkdir($link, 0755, true);
            }

            return response()->json([
                'success' => true,
                'message' => 'Storage directory created. Note: You may need to manually copy files from storage/app/public to public/storage on shared hosting.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create storage link: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Run database seeder
     */
    public function runSeeder(Request $request)
    {
        $request->validate([
            'seeder' => 'nullable|string'
        ]);

        try {
            set_time_limit(120);

            $seeder = $request->seeder;

            if ($seeder) {
                $seederClass = $this->normalizeSeederClass($seeder);
                Artisan::call('db:seed', [
                    '--class' => $seederClass,
                    '--force' => true
                ]);
            } else {
                Artisan::call('db:seed', ['--force' => true]);
            }

            $output = Artisan::output();

            return response()->json([
                'success' => true,
                'message' => 'Seeder completed successfully!',
                'output' => $output,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Seeder failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get available seeders
     */
    public function getSeeders()
    {
        $seederPath = database_path('seeders');
        $seeders = [];

        if (File::exists($seederPath)) {
            $files = File::files($seederPath);
            foreach ($files as $file) {
                $name = pathinfo($file->getFilename(), PATHINFO_FILENAME);
                if ($name !== 'DatabaseSeeder') {
                    $seeders[] = $name;
                }
            }
        }

        return response()->json([
            'success' => true,
            'seeders' => $seeders,
        ]);
    }

    /**
     * Seed chart of accounts (for new expense accounts)
     */
    public function seedAccounts()
    {
        try {
            set_time_limit(120);

            $seederClass = $this->normalizeSeederClass('ChartOfAccountsSeeder');
            Artisan::call('db:seed', [
                '--class' => $seederClass,
                '--force' => true
            ]);

            $output = Artisan::output();

            return response()->json([
                'success' => true,
                'message' => 'Chart of Accounts seeded successfully! New expense accounts for Labour, Transportation, and Other Purchase Expenses have been added.',
                'output' => $output,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Seeding failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Sync existing customers and companies to ledger accounts
     */
    public function syncCustomersToLedger()
    {
        try {
            set_time_limit(120);

            $seederClass = $this->normalizeSeederClass('SyncCustomersAndCompaniesToAccountsSeeder');
            Artisan::call('db:seed', [
                '--class' => $seederClass,
                '--force' => true
            ]);

            $output = Artisan::output();

            return response()->json([
                'success' => true,
                'message' => 'Customers and Companies have been synced to ledger accounts successfully!',
                'output' => $output,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Backfill opening balances for payees/companies
     */
    public function backfillOpeningBalances(Request $request)
    {
        try {
            set_time_limit(300);

            $currentTenantId = \App\Support\TenantContext::currentId();
            $options = ['--force' => true];
            if ($request->boolean('dry_run')) {
                $options['--dry-run'] = true;
            }
            if ($request->filled('tenant_id')) {
                $options['--tenant'] = $request->input('tenant_id');
            }
            if ($request->boolean('payees_only')) {
                $options['--payees'] = true;
            }
            if ($request->boolean('companies_only')) {
                $options['--companies'] = true;
            }
            if ($request->boolean('force_rebuild')) {
                $options['--force'] = true;
            }

            Artisan::call('accounting:backfill-opening-balances', $options);
            $output = Artisan::output();

            if ($currentTenantId) {
                \App\Support\TenantContext::set($currentTenantId);
            } else {
                \App\Support\TenantContext::clear();
            }

            return response()->json([
                'success' => true,
                'message' => $request->boolean('dry_run')
                    ? 'Dry run completed. No changes were made.'
                    : 'Opening balance backfill completed successfully.',
                'output' => $output,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Backfill failed: ' . $e->getMessage(),
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null,
            ], 500);
        }
    }

    /**
     * Assign default godown to products and seed missing stock rows
     */
    public function assignDefaultGodownToProducts()
    {
        try {
            set_time_limit(120);

            Artisan::call('products:assign-default-godown');
            $output = Artisan::output();

            return response()->json([
                'success' => true,
                'message' => 'Default godown assignment completed successfully!',
                'output' => $output,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Assignment failed: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Fix common issues
     */
    public function fixCommonIssues()
    {
        $fixes = [];

        try {
            // 1. Create storage directories
            $storageDirs = [
                storage_path('app/public'),
                storage_path('app/backups'),
                storage_path('framework/cache'),
                storage_path('framework/sessions'),
                storage_path('framework/views'),
                storage_path('logs'),
            ];

            foreach ($storageDirs as $dir) {
                if (!File::exists($dir)) {
                    File::makeDirectory($dir, 0755, true);
                    $fixes[] = "Created directory: " . basename($dir);
                }
            }

            // 2. Ensure bootstrap/cache exists and is writable
            $bootstrapCache = base_path('bootstrap/cache');
            if (!File::exists($bootstrapCache)) {
                File::makeDirectory($bootstrapCache, 0755, true);
                $fixes[] = "Created bootstrap/cache directory";
            }

            // 3. Clear compiled files
            if (File::exists(base_path('bootstrap/cache/config.php'))) {
                File::delete(base_path('bootstrap/cache/config.php'));
                $fixes[] = "Cleared cached config";
            }

            if (File::exists(base_path('bootstrap/cache/routes-v7.php'))) {
                File::delete(base_path('bootstrap/cache/routes-v7.php'));
                $fixes[] = "Cleared cached routes";
            }

            // 4. Clear caches
            $this->clearCaches();
            $fixes[] = "Cleared all application caches";

            return response()->json([
                'success' => true,
                'message' => 'Common issues fixed!',
                'fixes' => $fixes,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fix failed: ' . $e->getMessage(),
                'fixes' => $fixes,
            ], 500);
        }
    }

    private function normalizeSeederClass(string $seeder): string
    {
        if (str_contains($seeder, '\\')) {
            return $seeder;
        }

        return 'Database\\Seeders\\' . $seeder;
    }

    
}
