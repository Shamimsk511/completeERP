<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tenantScopedUniques = [
        'customers' => ['name', 'phone'],
        'categories' => ['name'],
        'companies' => ['name'],
        'invoices' => ['invoice_number'],
        'challans' => ['challan_number'],
        'product_returns' => ['return_number'],
        'other_deliveries' => ['challan_number'],
        'other_delivery_returns' => ['return_number'],
        'account_groups' => ['code'],
        'accounts' => ['code'],
        'godowns' => ['name'],
    ];

    public function up(): void
    {
        foreach ($this->tenantScopedUniques as $tableName => $columns) {
            if (!$this->isTenantTableReady($tableName)) {
                continue;
            }

            foreach ($columns as $column) {
                if (!Schema::hasColumn($tableName, $column)) {
                    continue;
                }

                $legacyIndexName = $this->legacyIndexName($tableName, $column);
                $tenantIndexName = $this->tenantIndexName($tableName, $column);

                $this->dropUniqueIfExists($tableName, $legacyIndexName);
                $this->addTenantUniqueIfMissing($tableName, $column, $tenantIndexName);
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tenantScopedUniques as $tableName => $columns) {
            if (!$this->isTenantTableReady($tableName)) {
                continue;
            }

            foreach ($columns as $column) {
                if (!Schema::hasColumn($tableName, $column)) {
                    continue;
                }

                $legacyIndexName = $this->legacyIndexName($tableName, $column);
                $tenantIndexName = $this->tenantIndexName($tableName, $column);

                $this->dropUniqueIfExists($tableName, $tenantIndexName);
                $this->addGlobalUniqueIfMissing($tableName, $column, $legacyIndexName);
            }
        }
    }

    private function isTenantTableReady(string $tableName): bool
    {
        return Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'tenant_id');
    }

    private function legacyIndexName(string $tableName, string $column): string
    {
        return "{$tableName}_{$column}_unique";
    }

    private function tenantIndexName(string $tableName, string $column): string
    {
        return "uq_{$tableName}_tenant_{$column}";
    }

    private function dropUniqueIfExists(string $tableName, string $indexName): void
    {
        if (!$this->indexExists($tableName, $indexName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($indexName) {
            $table->dropUnique($indexName);
        });
    }

    private function addTenantUniqueIfMissing(string $tableName, string $column, string $indexName): void
    {
        if ($this->indexExists($tableName, $indexName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($column, $indexName) {
            $table->unique(['tenant_id', $column], $indexName);
        });
    }

    private function addGlobalUniqueIfMissing(string $tableName, string $column, string $indexName): void
    {
        if ($this->indexExists($tableName, $indexName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($column, $indexName) {
            $table->unique($column, $indexName);
        });
    }

    private function indexExists(string $tableName, string $indexName): bool
    {
        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            return DB::table('information_schema.statistics')
                ->where('table_schema', DB::getDatabaseName())
                ->where('table_name', $tableName)
                ->where('index_name', $indexName)
                ->where('non_unique', 0)
                ->exists();
        }

        if ($driver === 'pgsql') {
            $result = DB::selectOne(
                'select 1 from pg_indexes where schemaname = current_schema() and tablename = ? and indexname = ? limit 1',
                [$tableName, $indexName]
            );

            return $result !== null;
        }

        if ($driver === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list('{$tableName}')");

            foreach ($indexes as $index) {
                if (($index->name ?? null) === $indexName) {
                    return true;
                }
            }
        }

        return false;
    }
};
