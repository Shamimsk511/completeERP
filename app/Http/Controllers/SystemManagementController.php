<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Schema;
use App\Services\BackupService;
use Carbon\Carbon;
use ZipArchive;

class SystemManagementController extends Controller
{
    protected BackupService $backupService;

    public function __construct(BackupService $backupService)
    {
        // Only users who can edit profiles can access this
        $this->middleware(['auth']);
        $this->backupService = $backupService;
    }

    /**
     * Display the system management dashboard
     */
    public function index()
    {
        $diskSpace = $this->getDiskSpace();
        $cacheInfo = $this->getCacheInfo();
        $backups = $this->getBackupsList();
        $migrationStatus = $this->getMigrationStatus();
        $systemInfo = $this->getSystemInfo();
        
        // Format disk space for view
        $diskSpace['used_formatted'] = $this->formatBytes($diskSpace['used']);
        $diskSpace['total_formatted'] = $this->formatBytes($diskSpace['total']);
        $diskSpace['free_formatted'] = $this->formatBytes($diskSpace['free']);
        
        // Format cache info for view
        $cacheInfo['view_cache_size_formatted'] = $this->formatBytes($cacheInfo['view_cache_size']);
        $cacheInfo['cache_size_formatted'] = $this->formatBytes($cacheInfo['cache_size']);
        
        return view('system.index', compact('diskSpace', 'cacheInfo', 'backups', 'migrationStatus', 'systemInfo'));
    }

    /**
     * Display backups management page
     */
    public function backups()
    {
        $stats = $this->backupService->getStats();
        $backups = $this->backupService->list();

        return view('system.backups', compact('stats', 'backups'));
    }

    /**
     * Clear various Laravel caches
     */
    public function clearCache(Request $request)
    {
        $request->validate([
            'cache_type' => 'required|in:view,cache,config,route,all'
        ]);

        $results = [];
        $cacheType = $request->cache_type;

        try {
            switch ($cacheType) {
                case 'view':
                    Artisan::call('view:clear');
                    $results[] = 'View cache cleared successfully';
                    break;
                    
                case 'cache':
                    Artisan::call('cache:clear');
                    $results[] = 'Application cache cleared successfully';
                    break;
                    
                case 'config':
                    Artisan::call('config:clear');
                    $results[] = 'Configuration cache cleared successfully';
                    break;
                    
                case 'route':
                    Artisan::call('route:clear');
                    $results[] = 'Route cache cleared successfully';
                    break;
                    
                case 'all':
                    Artisan::call('view:clear');
                    Artisan::call('cache:clear');
                    Artisan::call('config:clear');
                    Artisan::call('route:clear');
                    $results[] = 'All caches cleared successfully';
                    break;
            }

            return response()->json([
                'success' => true,
                'message' => implode(', ', $results),
                'cache_info' => $this->getCacheInfo()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error clearing cache: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cache optimization commands
     */
    public function optimizeCache(Request $request)
    {
        $request->validate([
            'optimize_type' => 'required|in:view,route,config,all'
        ]);

        $results = [];
        $optimizeType = $request->optimize_type;

        try {
            switch ($optimizeType) {
                case 'view':
                    Artisan::call('view:cache');
                    $results[] = 'Views cached for optimization';
                    break;
                    
                case 'route':
                    Artisan::call('route:cache');
                    $results[] = 'Routes cached for optimization';
                    break;
                    
                case 'config':
                    Artisan::call('config:cache');
                    $results[] = 'Configuration cached for optimization';
                    break;
                    
                case 'all':
                    Artisan::call('view:cache');
                    Artisan::call('route:cache');
                    Artisan::call('config:cache');
                    $results[] = 'All optimizations completed';
                    break;
            }

            return response()->json([
                'success' => true,
                'message' => implode(', ', $results),
                'cache_info' => $this->getCacheInfo()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error optimizing: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create database backup
     */
    public function createBackup()
    {
        try {
            $result = $this->backupService->create();

            if ($result['success']) {
                return back()->with('success', "Database backup created: {$result['filename']} ({$result['size']})");
            }

            return back()->with('error', $result['message']);

        } catch (\Exception $e) {
            return back()->with('error', 'Error creating backup: ' . $e->getMessage());
        }
    }

    /**
     * Create full backup
     */
    public function createFullBackup()
    {
        try {
            $result = $this->backupService->createFull();

            if ($result['success']) {
                $filesInfo = isset($result['files_count']) ? " including {$result['files_count']} files" : '';
                return back()->with('success', "Full backup created: {$result['filename']} ({$result['size']}){$filesInfo}");
            }

            return back()->with('error', $result['message']);

        } catch (\Exception $e) {
            return back()->with('error', 'Error creating full backup: ' . $e->getMessage());
        }
    }

    /**
     * Download backup file
     */
    public function downloadBackup($filename)
    {
        $filepath = $this->backupService->getPath($filename);

        if (!$filepath) {
            return back()->with('error', 'Backup file not found');
        }

        return response()->download($filepath);
    }

    /**
     * Delete backup file
     */
    public function deleteBackup($filename)
    {
        try {
            if ($this->backupService->delete($filename)) {
                return back()->with('success', 'Backup deleted successfully');
            }

            return back()->with('error', 'Failed to delete backup');

        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting backup: ' . $e->getMessage());
        }
    }

    /**
     * Restore database from backup
     */
    public function restoreBackup(Request $request)
    {
        $request->validate([
            'backup_filename' => 'required|string'
        ]);

        try {
            $result = $this->backupService->restore($request->backup_filename);

            if ($result['success']) {
                return back()->with('success', $result['message']);
            }

            return back()->with('error', $result['message']);

        } catch (\Exception $e) {
            return back()->with('error', 'Error restoring backup: ' . $e->getMessage());
        }
    }

    /**
     * Restore from uploaded backup file
     */
    public function restoreUpload(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|max:512000|mimes:sql,zip'
        ]);

        try {
            $result = $this->backupService->restoreFromUpload($request->file('backup_file'));

            if ($result['success']) {
                return back()->with('success', $result['message']);
            }

            return back()->with('error', $result['message']);

        } catch (\Exception $e) {
            return back()->with('error', 'Error restoring from upload: ' . $e->getMessage());
        }
    }

    /**
     * Cleanup old backups
     */
    public function cleanupBackups()
    {
        try {
            $statsBefore = $this->backupService->getStats();
            
            $this->backupService->cleanup();
            
            $statsAfter = $this->backupService->getStats();
            
            $freedSpace = $statsBefore['total_size_bytes'] - $statsAfter['total_size_bytes'];

            return response()->json([
                'success' => true,
                'message' => "Cleanup completed! Freed: " . $this->formatBytes($freedSpace),
                'stats_before' => $statsBefore,
                'stats_after' => $statsAfter
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error during cleanup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get cache information
     */
    private function getCacheInfo()
    {
        $viewCachePath = storage_path('framework/views');
        $cachePath = storage_path('framework/cache');
        
        return [
            'view_cache_files' => File::exists($viewCachePath) ? count(File::files($viewCachePath)) : 0,
            'cache_files' => File::exists($cachePath) ? count(File::allFiles($cachePath)) : 0,
            'view_cache_size' => File::exists($viewCachePath) ? $this->getDirectorySize($viewCachePath) : 0,
            'cache_size' => File::exists($cachePath) ? $this->getDirectorySize($cachePath) : 0,
        ];
    }

    /**
     * Display AdminLTE configuration (read-only).
     */
    public function adminlteConfig()
    {
        $config = config('adminlte');
        $configPath = base_path('config/adminlte.php');

        return view('system.adminlte-config', compact('config', 'configPath'));
    }

    /**
     * Get disk space information
     */
    private function getDiskSpace()
    {
        $path = storage_path();
        return [
            'free' => disk_free_space($path),
            'total' => disk_total_space($path),
            'used' => disk_total_space($path) - disk_free_space($path)
        ];
    }

    /**
     * Get list of available backups
     */
    private function getBackupsList()
    {
        $backupPath = storage_path('app/backups');
        
        if (!File::exists($backupPath)) {
            return [];
        }

        $files = File::files($backupPath);
        $backups = [];

        foreach ($files as $file) {
            if (in_array($file->getExtension(), ['sql', 'zip'], true)) {
                $backups[] = [
                    'name' => $file->getFilename(),
                    'size' => $this->formatBytes($file->getSize()),
                    'created_at' => Carbon::createFromTimestamp($file->getMTime())->format('Y-m-d H:i:s'),
                    'age' => Carbon::createFromTimestamp($file->getMTime())->diffForHumans()
                ];
            }
        }

        // Sort by creation date (newest first)
        usort($backups, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        return $backups;
    }

    /**
     * Get migration status - which have run and which are pending
     */
    private function getMigrationStatus()
    {
        $migrationPath = database_path('migrations');
        $migrationFiles = collect(File::exists($migrationPath) ? File::files($migrationPath) : [])
            ->map(function ($file) {
                return pathinfo($file->getFilename(), PATHINFO_FILENAME);
            })
            ->sort()
            ->values();

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
        $parts = explode('_', $name);
        $dateParts = array_slice($parts, 0, 4);
        $descParts = array_slice($parts, 4);

        $date = '';
        if (count($dateParts) >= 3) {
            $date = $dateParts[0] . '-' . $dateParts[1] . '-' . $dateParts[2];
        }

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
     * Get directory size in bytes
     */
    private function getDirectorySize($path)
    {
        if (!File::exists($path)) {
            return 0;
        }

        $size = 0;
        $files = File::allFiles($path);
        
        foreach ($files as $file) {
            $size += $file->getSize();
        }
        
        return $size;
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
