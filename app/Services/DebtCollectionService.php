<?php

namespace App\Services;

use App\Models\CallLog;
use App\Models\Customer;
use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Symfony\Component\Clock\now;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use App\Models\DebtCollectionTracking;
use Illuminate\Support\Facades\Schema;
use App\Support\TenantContext;
use Illuminate\Pagination\LengthAwarePaginator;

class DebtCollectionService
{
    /**
     * Get optimized dashboard statistics
     */
    public function getDashboardStats(): array
    {
        $today = Carbon::today()->format('Y-m-d');
        $weekStart = Carbon::now()->startOfWeek()->format('Y-m-d');
        $weekEnd = Carbon::now()->endOfWeek()->format('Y-m-d');

        try {
            $stats = DB::select("
                SELECT 
                    COUNT(CASE WHEN dct.due_date = ? THEN 1 END) as due_today,
                    COUNT(CASE WHEN dct.due_date BETWEEN ? AND ? THEN 1 END) as due_this_week,
                    COUNT(CASE WHEN dct.due_date < ? THEN 1 END) as overdue,
                    COUNT(CASE WHEN dct.priority = 'high' THEN 1 END) as `high_priority`,
                    COALESCE(SUM(c.outstanding_balance), 0) as total_outstanding,
                    COALESCE(AVG(CASE WHEN dct.last_call_date IS NOT NULL 
                        THEN DATEDIFF(NOW(), dct.last_call_date) END), 0) as avg_days_since_contact
                FROM customers c
                LEFT JOIN debt_collection_trackings dct ON c.id = dct.customer_id
                WHERE c.outstanding_balance > 0
            ", [$today, $weekStart, $weekEnd, $today]);

            $result = $stats[0];

            return [
                'due_today' => (int) $result->due_today,
                'due_this_week' => (int) $result->due_this_week,
                'overdue' => (int) $result->overdue,
                'high_priority' => (int) $result->high_priority,
                'total_outstanding' => (float) $result->total_outstanding,
                'avg_days_since_contact' => (int) $result->avg_days_since_contact,
            ];

        } catch (\Exception $e) {
            return $this->getDashboardStatsEloquent();
        }
    }

    /**
     * Fallback method using Eloquent queries
     */
    private function getDashboardStatsEloquent(): array
    {
        $today = Carbon::today();
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();

        $customersQuery = Customer::where('outstanding_balance', '>', 0);
        
        $dueToday = (clone $customersQuery)->whereHas('debtCollectionTracking', function($query) use ($today) {
            $query->whereDate('due_date', $today);
        })->count();

        $dueThisWeek = (clone $customersQuery)->whereHas('debtCollectionTracking', function($query) use ($weekStart, $weekEnd) {
            $query->whereBetween('due_date', [$weekStart, $weekEnd]);
        })->count();

        $overdue = (clone $customersQuery)->whereHas('debtCollectionTracking', function($query) use ($today) {
            $query->where('due_date', '<', $today);
        })->count();

        $highPriority = (clone $customersQuery)->whereHas('debtCollectionTracking', function($query) {
            $query->where('priority', 'high');
        })->count();

        $totalOutstanding = Customer::where('outstanding_balance', '>', 0)->sum('outstanding_balance');

        $avgDaysSinceContact = DebtCollectionTracking::whereNotNull('last_call_date')
            ->selectRaw('AVG(DATEDIFF(NOW(), last_call_date)) as avg_days')
            ->value('avg_days') ?? 0;

        return [
            'due_today' => $dueToday,
            'due_this_week' => $dueThisWeek,
            'overdue' => $overdue,
            'high_priority' => $highPriority,
            'total_outstanding' => (float) $totalOutstanding,
            'avg_days_since_contact' => (int) $avgDaysSinceContact,
        ];
    }

    /**
     * Get customers with filters and pagination
     */
    public function getCustomersWithFilters(array $filters): array
    {
        $query = Customer::with([
            'debtCollectionTracking',
            'lastTransaction' => function($query) {
                $query->latest();
            },
            'lastPayment' => function($query) {
                $query->where('type', 'debit')->latest();
            }
        ])->where('outstanding_balance', '>', 0);

        $this->applyFilters($query, $filters);

        if (isset($filters['draw'])) {
            return $this->handleDataTablesPagination($query, $filters);
        } else {
            return $this->handleSimplePagination($query, $filters);
        }
    }

    public function getSimpleCustomersWithFilters(array $filters): array
    {
        $query = $this->buildSimpleCustomersQuery($filters);

        $orderColumn = $filters['order_column'] ?? 'days_since_activity_sort';
        $orderDirection = $filters['order_dir'] ?? 'desc';

        $allowedOrderColumns = [
            'customer_id',
            'customer_name',
            'phone',
            'outstanding_balance',
            'last_call_at',
            'last_transaction_at',
            'last_promise_date',
            'promise_count',
            'promise_change_count',
            'last_activity_at',
            'days_since_activity',
            'days_since_activity_sort',
        ];

        if (!in_array($orderColumn, $allowedOrderColumns, true)) {
            $orderColumn = 'days_since_activity_sort';
        }

        $orderDirection = strtolower($orderDirection) === 'asc' ? 'asc' : 'desc';
        $query->orderBy($orderColumn, $orderDirection);

        return $this->handleDataTablesPagination($query, $filters);
    }

    /**
     * Handle DataTables server-side pagination
     */
    /**
 * Handle DataTables server-side pagination - FIXED for call schedule
 */
private function handleDataTablesPagination($query, array $filters): array
{
    // Get total count before applying pagination
    $totalQuery = clone $query;
    $totalRecords = $totalQuery->count();
    
    $start = (int) ($filters['start'] ?? 0);
    $length = (int) ($filters['length'] ?? 25);
    
    if ($length <= 0) {
        $length = 25;
    }

    // Apply pagination
    $items = $query->offset($start)->limit($length)->get();

    return [
        'data' => $items,
        'total' => $totalRecords,
        'filtered' => $totalRecords,
        'draw' => $filters['draw'] ?? 1,
        'recordsTotal' => $totalRecords,
        'recordsFiltered' => $totalRecords,
    ];
}


    /**
     * Handle simple pagination for cards
     */
   /**
 * Handle simple pagination for cards - FIXED for call schedule
 */
private function handleSimplePagination($query, array $filters): array
{
    $perPage = (int) ($filters['per_page'] ?? 12);
    $page = (int) ($filters['page'] ?? 1);
    
    try {
        // Get total count before pagination
        $total = $query->count();
        
        // Calculate offset
        $offset = ($page - 1) * $perPage;
        
        // Get the actual data with pagination
        $items = $query->offset($offset)->limit($perPage)->get();
        
        return [
            'data' => $items,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage),
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $total),
            'filtered' => $total
        ];
    } catch (\Exception $e) {
        \Log::error('Pagination error in call schedule: ' . $e->getMessage());
        
        return [
            'data' => [],
            'total' => 0,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => 1,
            'from' => 0,
            'to' => 0,
            'filtered' => 0
        ];
    }
}


    /**
     * Apply filters to query
     */
    private function applyFilters($query, array $filters): void
    {
        if (!empty($filters['search'])) {
            $searchTerm = $filters['search'];
            if (is_array($searchTerm)) {
                $searchTerm = $searchTerm['value'] ?? '';
            }
            $searchTerm = trim((string) $searchTerm);
            if ($searchTerm !== '') {
                $search = '%' . $searchTerm . '%';
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', $search)
                      ->orWhere('phone', 'LIKE', $search);
                });
            }
        }

        if (!empty($filters['min_balance'])) {
            $query->where('outstanding_balance', '>=', $filters['min_balance']);
        }

        if (!empty($filters['max_balance'])) {
            $query->where('outstanding_balance', '<=', $filters['max_balance']);
        }

        if (!empty($filters['priority'])) {
            $query->whereHas('debtCollectionTracking', function ($q) use ($filters) {
                $q->where('priority', $filters['priority']);
            });
        }

        if (!empty($filters['balance_range'])) {
            $this->applyBalanceRangeFilter($query, $filters['balance_range']);
        }

        if (!empty($filters['due_date_start']) && !empty($filters['due_date_end'])) {
            $query->whereHas('debtCollectionTracking', function ($q) use ($filters) {
                $q->where(function ($inner) use ($filters) {
                    $inner->whereBetween('due_date', [$filters['due_date_start'], $filters['due_date_end']])
                        ->orWhere(function ($fallback) use ($filters) {
                            $fallback->whereNull('due_date')
                                ->where(function ($dates) use ($filters) {
                                    $dates->whereBetween('payment_promise_date', [$filters['due_date_start'], $filters['due_date_end']])
                                        ->orWhereBetween('follow_up_date', [$filters['due_date_start'], $filters['due_date_end']]);
                                });
                        });
                });
            });
        }

        if (!empty($filters['status'])) {
            switch ($filters['status']) {
                case 'overdue':
                    $query->whereHas('debtCollectionTracking', function ($q) {
                        $q->where('due_date', '<', Carbon::now());
                    });
                    break;
                case 'due_today':
                    $query->whereHas('debtCollectionTracking', function ($q) {
                        $q->whereDate('due_date', Carbon::today());
                    });
                    break;
                case 'due_week':
                    $query->whereHas('debtCollectionTracking', function ($q) {
                        $q->whereBetween('due_date', [
                            Carbon::now()->startOfWeek(),
                            Carbon::now()->endOfWeek()
                        ]);
                    });
                    break;
            }
        }

        $sortField = $filters['sort_field'] ?? 'outstanding_balance';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        
        $allowedSortFields = ['name', 'outstanding_balance', 'created_at', 'phone'];
        $sortDirection = strtolower($sortDirection) === 'asc' ? 'asc' : 'desc';
        
        if (in_array($sortField, $allowedSortFields, true)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderBy('outstanding_balance', 'desc');
        }
    }

    private function buildSimpleCustomersQuery(array $filters)
    {
        $hasCallLogs = Schema::hasTable('call_logs');
        $hasTransactions = Schema::hasTable('transactions');
        $tenantId = TenantContext::currentId();

        $lastCallInfo = null;
        $promiseStats = null;

        if ($hasCallLogs) {
            $lastCallId = DB::table('call_logs')
                ->when($tenantId, function ($query, $tenantId) {
                    $query->where('tenant_id', $tenantId);
                })
                ->selectRaw('customer_id, MAX(id) as last_call_id')
                ->groupBy('customer_id');

            $lastCallInfo = DB::table('call_logs as cl')
                ->joinSub($lastCallId, 'lci', function ($join) {
                    $join->on('cl.id', '=', 'lci.last_call_id');
                })
                ->when($tenantId, function ($query, $tenantId) {
                    $query->where('cl.tenant_id', $tenantId);
                })
                ->select(
                    'cl.customer_id',
                    'cl.called_at as last_call_at',
                    'cl.notes as last_note'
                );

            $promiseStats = DB::table('call_logs')
                ->when($tenantId, function ($query, $tenantId) {
                    $query->where('tenant_id', $tenantId);
                })
                ->whereNotNull('payment_promise_date')
                ->groupBy('customer_id')
                ->selectRaw('customer_id, COUNT(*) as promise_count, COUNT(DISTINCT payment_promise_date) as promise_unique, MAX(payment_promise_date) as last_promise_date');
        }

        $lastTransaction = null;
        if ($hasTransactions) {
            $lastTransaction = DB::table('transactions')
                ->when($tenantId, function ($query, $tenantId) {
                    $query->where('tenant_id', $tenantId);
                })
                ->groupBy('customer_id')
                ->selectRaw('customer_id, MAX(created_at) as last_transaction_at');
        }

        $query = DB::table('customers as c')
            ->leftJoin('debt_collection_trackings as dct', 'c.id', '=', 'dct.customer_id')
            ->when($lastCallInfo, function ($q) use ($lastCallInfo) {
                $q->leftJoinSub($lastCallInfo, 'lci', function ($join) {
                    $join->on('c.id', '=', 'lci.customer_id');
                });
            })
            ->when($promiseStats, function ($q) use ($promiseStats) {
                $q->leftJoinSub($promiseStats, 'ps', function ($join) {
                    $join->on('c.id', '=', 'ps.customer_id');
                });
            })
            ->when($lastTransaction, function ($q) use ($lastTransaction) {
                $q->leftJoinSub($lastTransaction, 'lt', function ($join) {
                    $join->on('c.id', '=', 'lt.customer_id');
                });
            })
            ->when($tenantId, function ($query, $tenantId) {
                $query->where('c.tenant_id', $tenantId)
                    ->where(function ($q) use ($tenantId) {
                        $q->whereNull('dct.tenant_id')
                          ->orWhere('dct.tenant_id', $tenantId);
                    });
            })
            ->where('c.outstanding_balance', '>', 0);

        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($search) {
                $q->where('c.name', 'like', $search)
                    ->orWhere('c.phone', 'like', $search);
            });
        }

        $lastCallExpr = $hasCallLogs ? 'DATE(lci.last_call_at)' : 'DATE(dct.last_call_date)';
        $lastTransactionExpr = $hasTransactions ? 'DATE(lt.last_transaction_at)' : 'NULL';
        $lastActivityExpr = "GREATEST(COALESCE({$lastCallExpr}, '1900-01-01'), COALESCE({$lastTransactionExpr}, '1900-01-01'))";

        if (!empty($filters['min_days'])) {
            $query->whereRaw("COALESCE(DATEDIFF(CURDATE(), {$lastActivityExpr}), 999999) >= ?", [(int) $filters['min_days']]);
        }

        return $query->selectRaw('
            c.id as customer_id,
            c.name as customer_name,
            c.phone as phone,
            c.outstanding_balance,
            ' . ($hasCallLogs ? 'lci.last_call_at as last_call_at' : 'dct.last_call_date as last_call_at') . ',
            ' . ($hasTransactions ? 'lt.last_transaction_at as last_transaction_at' : 'NULL as last_transaction_at') . ',
            ' . ($hasCallLogs ? 'lci.last_note as last_note' : 'dct.notes as last_note') . ',
            ' . ($hasCallLogs ? 'ps.last_promise_date as last_promise_date' : 'dct.payment_promise_date as last_promise_date') . ',
            COALESCE(' . ($hasCallLogs ? 'ps.promise_count' : '0') . ', 0) as promise_count,
            GREATEST(COALESCE(' . ($hasCallLogs ? 'ps.promise_unique' : '0') . ', 0) - 1, 0) as promise_change_count,
            NULLIF(' . $lastActivityExpr . ', "1900-01-01") as last_activity_at,
            CASE WHEN ' . $lastActivityExpr . ' = "1900-01-01" THEN NULL ELSE DATEDIFF(CURDATE(), ' . $lastActivityExpr . ') END as days_since_activity,
            COALESCE(DATEDIFF(CURDATE(), ' . $lastActivityExpr . '), 999999) as days_since_activity_sort
        ');
    }

    /**
     * Apply balance range filter
     */
    private function applyBalanceRangeFilter($query, string $range): void
    {
        switch ($range) {
            case '0-1000':
                $query->whereBetween('outstanding_balance', [0, 1000]);
                break;
            case '1000-5000':
                $query->whereBetween('outstanding_balance', [1000, 5000]);
                break;
            case '5000-10000':
                $query->whereBetween('outstanding_balance', [5000, 10000]);
                break;
            case '10000+':
                $query->where('outstanding_balance', '>', 10000);
                break;
        }
    }

    /**
     * Log customer call
     */
    public function logCustomerCall(int $customerId, array $data): array
    {
        try {
            $customer = Customer::findOrFail($customerId);
            
            $tracking = DebtCollectionTracking::firstOrCreate(
                ['customer_id' => $customerId],
                ['priority' => 'medium']
            );

            $followUpRequired = false;
            if (isset($data['follow_up_required'])) {
                $followUpRequired = (bool) $data['follow_up_required'];
            }

            $callLog = null;
            if (class_exists('App\Models\CallLog')) {
                $callLogData = [
                    'customer_id' => $customerId,
                    'call_status' => $data['call_status'],
                    'duration' => !empty($data['duration']) ? (int) $data['duration'] : null,
                    'notes' => !empty($data['notes']) ? $data['notes'] : null,
                    'payment_promise_date' => !empty($data['payment_promise_date']) ? $data['payment_promise_date'] : null,
                    'follow_up_required' => $followUpRequired,
                    'called_at' => now(),
                    'user_id' => Auth::id(),
                ];
       
                $callLog = CallLog::create($callLogData);
            }

            $updateData = [
                'last_call_date' => now(),
                'calls_made' => ($tracking->calls_made ?? 0) + 1,
            ];

            if ($data['call_status'] === 'missed') {
                $updateData['missed_calls'] = ($tracking->missed_calls ?? 0) + 1;
            }

            if ($this->columnExists('debt_collection_trackings', 'payment_promise_date') && !empty($data['payment_promise_date'])) {
                $updateData['payment_promise_date'] = $data['payment_promise_date'];
            }

            if ($this->columnExists('debt_collection_trackings', 'follow_up_date') && $followUpRequired === true) {
                $updateData['follow_up_date'] = Carbon::now()->addDays(3);
            }

            $tracking->update($updateData);

            if ($this->columnExists('debt_collection_trackings', 'notes') && !empty($data['notes'])) {
                $currentNotes = $tracking->notes ?? '';
                $dateStamp = now()->format('Y-m-d H:i');
                $newNote = "\n[$dateStamp] {$data['notes']}";
                $tracking->update(['notes' => $currentNotes . $newNote]);
            }

            return [
                'call_log' => $callLog,
                'tracking' => $tracking->fresh(),
            ];

        } catch (\Exception $e) {
            \Log::error('Error in logCustomerCall: ' . $e->getMessage());
            \Log::error('Data received: ' . json_encode($data));
            throw $e;
        }
    }

    /**
     * Check if a column exists in a table
     */
    private function columnExists(string $table, string $column): bool
    {
        try {
            return Schema::hasColumn($table, $column);
        } catch (\Exception $e) {
            \Log::warning("Could not check if column {$column} exists in table {$table}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update customer tracking
     */
    public function updateCustomerTracking(int $customerId, array $data): DebtCollectionTracking
    {
        try {
            $tracking = DebtCollectionTracking::firstOrCreate(
                ['customer_id' => $customerId],
                ['priority' => 'medium']
            );

            $allowedFields = ['priority', 'due_date', 'last_call_date', 'calls_made', 'missed_calls'];
            
            if ($this->columnExists('debt_collection_trackings', 'payment_promise_date')) {
                $allowedFields[] = 'payment_promise_date';
            }
            
            if ($this->columnExists('debt_collection_trackings', 'follow_up_date')) {
                $allowedFields[] = 'follow_up_date';
            }
            
            if ($this->columnExists('debt_collection_trackings', 'notes')) {
                $allowedFields[] = 'notes';
            }
            
            $updateData = array_intersect_key($data, array_flip($allowedFields));
            
            $updateData = array_filter($updateData, function($value) {
                return $value !== '' && $value !== null;
            });
            
            if (!empty($updateData)) {
                $tracking->update($updateData);
            }

            return $tracking->fresh();
            
        } catch (\Exception $e) {
            \Log::error('Error updating customer tracking: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get customer call history
     */
    public function getCustomerCallHistory(int $customerId): array
    {
        $callLogs = [];
        $stats = [];

        if (class_exists('App\Models\CallLog')) {
            $callLogs = CallLog::where('customer_id', $customerId)
                ->with('user:id,name')
                ->orderBy('called_at', 'desc')
                ->paginate(20);

            $stats = CallLog::where('customer_id', $customerId)
                ->selectRaw('
                    COUNT(*) as total_calls,
                    COUNT(CASE WHEN call_status = "successful" THEN 1 END) as successful_calls,
                    COUNT(CASE WHEN call_status = "missed" THEN 1 END) as missed_calls,
                    AVG(duration) as avg_duration
                ')
                ->first();
        } else {
            $tracking = DebtCollectionTracking::where('customer_id', $customerId)->first();
            $stats = (object) [
                'total_calls' => $tracking->calls_made ?? 0,
                'successful_calls' => ($tracking->calls_made ?? 0) - ($tracking->missed_calls ?? 0),
                'missed_calls' => $tracking->missed_calls ?? 0,
                'avg_duration' => 0
            ];
        }

        return [
            'call_logs' => $callLogs,
            'stats' => $stats,
        ];
    }

    /**
     * Perform bulk actions
     */
    public function performBulkAction(string $action, array $customerIds, array $options = []): array
    {
        $affectedCount = 0;

        switch ($action) {
            case 'mark_priority':
                $priority = $options['priority'] ?? 'high';
                foreach ($customerIds as $customerId) {
                    DebtCollectionTracking::updateOrCreate(
                        ['customer_id' => $customerId],
                        ['priority' => $priority]
                    );
                    $affectedCount++;
                }
                break;

            case 'schedule_calls':
                $date = $options['date'] ?? now()->addDays(1)->format('Y-m-d');
                if ($this->columnExists('debt_collection_trackings', 'follow_up_date')) {
                    foreach ($customerIds as $customerId) {
                        DebtCollectionTracking::updateOrCreate(
                            ['customer_id' => $customerId],
                            ['follow_up_date' => $date]
                        );
                        $affectedCount++;
                    }
                }
                break;

            case 'send_reminders':
                $affectedCount = count($customerIds);
                break;
        }

        return ['affected_count' => $affectedCount];
    }

    /**
     * Get analytics data
     */
    public function getAnalyticsData(): array
    {
        return [
            'collection_trend' => $this->getCollectionTrend(),
            'priority_distribution' => $this->getPriorityDistribution(),
            'call_effectiveness' => $this->getCallEffectiveness(),
            'aging_analysis' => $this->getAgingAnalysis(),
        ];
    }

    private function getCollectionTrend(): array
    {
        try {
            return DB::select("
                SELECT 
                    DATE(created_at) as date,
                    SUM(amount) as collected
                FROM transactions 
                WHERE type = 'debit' 
                AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY DATE(created_at)
                ORDER BY date
            ");
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getPriorityDistribution(): array
    {
        return DebtCollectionTracking::selectRaw('priority, COUNT(*) as count')
            ->groupBy('priority')
            ->get()
            ->toArray();
    }

    private function getCallEffectiveness(): array
    {
        if (!class_exists('App\Models\CallLog')) {
            return [];
        }

        try {
            return DB::select("
                SELECT 
                    call_status,
                    COUNT(*) as count,
                    AVG(CASE WHEN payment_promise_date IS NOT NULL THEN 1 ELSE 0 END) as promise_rate
                FROM call_logs 
                WHERE called_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY call_status
            ");
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getAgingAnalysis(): array
    {
        try {
            return DB::select("
                SELECT 
                    CASE 
                        WHEN DATEDIFF(NOW(), dct.due_date) <= 30 THEN '0-30'
                        WHEN DATEDIFF(NOW(), dct.due_date) <= 60 THEN '31-60'
                        WHEN DATEDIFF(NOW(), dct.due_date) <= 90 THEN '61-90'
                        ELSE '90+'
                    END as age_group,
                    COUNT(*) as count,
                    SUM(c.outstanding_balance) as total_amount
                FROM customers c
                INNER JOIN debt_collection_trackings dct ON c.id = dct.customer_id
                WHERE c.outstanding_balance > 0 AND dct.due_date < NOW()
                GROUP BY age_group
            ");
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Export debt collection data
     */
    public function exportData(array $filters)
    {
        $filters['per_page'] = 10000;
        $customers = $this->getCustomersWithFilters($filters);
        
        $filename = 'debt_collection_' . now()->format('Y_m_d_H_i_s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($customers) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, [
                'Customer Name', 
                'Phone', 
                'Outstanding Balance', 
                'Due Date', 
                'Priority', 
                'Last Call Date',
                'Calls Made',
                'Missed Calls'
            ]);
            
            foreach ($customers['data'] as $customer) {
                fputcsv($file, [
                    $customer->name,
                    $customer->phone,
                    $customer->outstanding_balance,
                    $customer->debtCollectionTracking?->due_date,
                    $customer->debtCollectionTracking?->priority ?? 'medium',
                    $customer->debtCollectionTracking?->last_call_date,
                    $customer->debtCollectionTracking?->calls_made ?? 0,
                    $customer->debtCollectionTracking?->missed_calls ?? 0,
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get overdue accounts with filters
     */
    public function getOverdueAccounts(array $filters = []): array
    {
        try {
            $query = Customer::where('outstanding_balance', '>', 0)
                ->with('debtCollectionTracking');

            if (!empty($filters['min_amount'])) {
                $query->where('outstanding_balance', '>=', $filters['min_amount']);
            }

            if (!empty($filters['priority'])) {
                $query->whereHas('debtCollectionTracking', function($q) use ($filters) {
                    $q->where('priority', $filters['priority']);
                });
            }

            $customers = $query->get()->map(function($customer) {
                $tracking = $customer->debtCollectionTracking;
                $dueDate = $tracking?->due_date ? Carbon::parse($tracking->due_date) : null;
                
                $customer->days_overdue = $dueDate ? max(0, $dueDate->diffInDays(now(), false)) : 0;
                
                return $customer;
            })->filter(function($customer) {
                return $customer->days_overdue > 0;
            });

            if (!empty($filters['days_overdue'])) {
                $customers = $customers->filter(function($customer) use ($filters) {
                    $days = $customer->days_overdue;
                    switch ($filters['days_overdue']) {
                        case '0_30':
                            return $days >= 1 && $days <= 30;
                        case '31_60':
                            return $days >= 31 && $days <= 60;
                        case '61_90':
                            return $days >= 61 && $days <= 90;
                        case '90_plus':
                            return $days > 90;
                        default:
                            return true;
                    }
                });
            }

            $aging_0_30 = $customers->filter(function($customer) {
                return $customer->days_overdue >= 1 && $customer->days_overdue <= 30;
            });

            $aging_31_60 = $customers->filter(function($customer) {
                return $customer->days_overdue >= 31 && $customer->days_overdue <= 60;
            });

            $aging_61_90 = $customers->filter(function($customer) {
                return $customer->days_overdue >= 61 && $customer->days_overdue <= 90;
            });

            $aging_90_plus = $customers->filter(function($customer) {
                return $customer->days_overdue > 90;
            });

            return [
                'aging_0_30' => [
                    'count' => $aging_0_30->count(),
                    'amount' => $aging_0_30->sum('outstanding_balance')
                ],
                'aging_31_60' => [
                    'count' => $aging_31_60->count(),
                    'amount' => $aging_31_60->sum('outstanding_balance')
                ],
                'aging_61_90' => [
                    'count' => $aging_61_90->count(),
                    'amount' => $aging_61_90->sum('outstanding_balance')
                ],
                'aging_90_plus' => [
                    'count' => $aging_90_plus->count(),
                    'amount' => $aging_90_plus->sum('outstanding_balance')
                ],
                'customers' => $customers->sortByDesc('days_overdue')->take(100)
            ];

        } catch (\Exception $e) {
            \Log::error('Error in getOverdueAccounts: ' . $e->getMessage());
            \Log::error('Filters: ' . json_encode($filters));
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return [
                'aging_0_30' => ['count' => 0, 'amount' => 0],
                'aging_31_60' => ['count' => 0, 'amount' => 0],
                'aging_61_90' => ['count' => 0, 'amount' => 0],
                'aging_90_plus' => ['count' => 0, 'amount' => 0],
                'customers' => collect([])
            ];
        }
    }

    /**
     * Get performance data
     */
    public function getPerformanceData(): array
    {
        try {
            $now = Carbon::now();
            $periodStart = $now->copy()->subDays(30)->startOfDay();
            $periodEnd = $now->copy()->endOfDay();

            $totalOutstanding = Customer::where('outstanding_balance', '>', 0)->sum('outstanding_balance');

            $totalCollections = 0;
            $totalInvoiced = 0;
            if (class_exists('App\Models\Transaction') && Schema::hasTable('transactions')) {
                $totalCollections = Transaction::where('type', 'debit')
                    ->whereBetween('created_at', [$periodStart, $periodEnd])
                    ->sum('amount');
                $totalInvoiced = Transaction::where('type', 'credit')
                    ->whereBetween('created_at', [$periodStart, $periodEnd])
                    ->sum('amount');
            }

            $totalCalls = 0;
            $successfulCalls = 0;
            if (class_exists('App\Models\CallLog') && Schema::hasTable('call_logs')) {
                $totalCalls = CallLog::whereBetween('called_at', [$periodStart, $periodEnd])->count();
                $successfulCalls = CallLog::whereBetween('called_at', [$periodStart, $periodEnd])
                    ->where('call_status', 'successful')
                    ->count();
            } else {
                $totalCalls = DebtCollectionTracking::sum('calls_made') ?? 0;
                $totalMissedCalls = DebtCollectionTracking::sum('missed_calls') ?? 0;
                $successfulCalls = max(0, $totalCalls - $totalMissedCalls);
            }

            $avgResolutionDays = 0;
            if (Schema::hasTable('transactions') && Schema::hasTable('debt_collection_trackings')) {
                $tenantId = TenantContext::currentId();
                $transactionSub = '(SELECT customer_id, MAX(created_at) AS last_payment_at
                                     FROM transactions
                                     WHERE type = "debit"'
                    . ($tenantId ? ' AND tenant_id = ' . (int) $tenantId : '')
                    . ' GROUP BY customer_id) tp';

                $avgResolutionDays = (float) DB::table('customers as c')
                    ->join('debt_collection_trackings as dct', 'c.id', '=', 'dct.customer_id')
                    ->join(DB::raw($transactionSub), 'tp.customer_id', '=', 'c.id')
                    ->when($tenantId, function ($query, $tenantId) {
                        $query->where('c.tenant_id', $tenantId)
                            ->where(function ($q) use ($tenantId) {
                                $q->whereNull('dct.tenant_id')
                                  ->orWhere('dct.tenant_id', $tenantId);
                            });
                    })
                    ->where('c.outstanding_balance', 0)
                    ->whereNotNull('dct.due_date')
                    ->whereBetween('tp.last_payment_at', [$periodStart, $periodEnd])
                    ->selectRaw('AVG(GREATEST(DATEDIFF(tp.last_payment_at, dct.due_date), 0)) as avg_days')
                    ->value('avg_days') ?? 0;
            }

            $collectionRate = 0;
            if ($totalInvoiced > 0) {
                $collectionRate = round(($totalCollections / $totalInvoiced) * 100, 2);
            }

            $monthlyTrends = [];
            $months = [];
            for ($i = 5; $i >= 0; $i--) {
                $label = $now->copy()->subMonths($i)->format('M Y');
                $months[$label] = 0.0;
            }

            if (Schema::hasTable('transactions')) {
                $trendStart = $now->copy()->subMonths(5)->startOfMonth();
                $trendEnd = $now->copy()->endOfMonth();

                $trendRows = Transaction::where('type', 'debit')
                    ->whereBetween('created_at', [$trendStart, $trendEnd])
                    ->selectRaw('DATE_FORMAT(created_at, "%b %Y") as month_label, SUM(amount) as total, MIN(created_at) as first_date')
                    ->groupBy('month_label')
                    ->orderBy('first_date')
                    ->get();

                foreach ($trendRows as $row) {
                    $months[$row->month_label] = (float) $row->total;
                }
            }

            $monthlyTrends = $months;

            return [
                'collection_rate' => $collectionRate,
                'total_collections' => (float) $totalCollections,
                'avg_resolution_days' => (int) round($avgResolutionDays),
                'total_calls' => (int) $totalCalls,
                'successful_calls' => (int) $successfulCalls,
                'call_success_rate' => $totalCalls > 0 ? round(($successfulCalls / $totalCalls) * 100, 2) : 0,
                'monthly_trends' => $monthlyTrends
            ];
        } catch (\Exception $e) {
            \Log::error('Error getting performance data: ' . $e->getMessage());
            return [
                'collection_rate' => 0,
                'total_collections' => 0,
                'avg_resolution_days' => 0,
                'total_calls' => 0,
                'successful_calls' => 0,
                'call_success_rate' => 0,
                'monthly_trends' => []
            ];
        }
    }
    /**
 * Get call schedule data
 */
public function getCallScheduleData(): array
{
    try {
        $today = Carbon::today();
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();

        // Get scheduled calls stats
        $stats = [
            'today_calls' => $this->getScheduledCallsCount(['date' => $today->format('Y-m-d')]),
            'week_calls' => $this->getScheduledCallsCount(['week_start' => $weekStart, 'week_end' => $weekEnd]),
            'overdue_calls' => $this->getScheduledCallsCount(['overdue' => true]),
            'high_priority' => $this->getScheduledCallsCount(['priority' => 'high'])
        ];

        // Get upcoming calls for today
        $todayCalls = $this->getScheduledCallsWithFilters([
            'date' => $today->format('Y-m-d'),
            'per_page' => 10
        ]);

        return [
            'stats' => $stats,
            'today_calls' => $todayCalls['data'] ?? [],
            'call_types' => ['follow_up', 'payment_reminder', 'final_notice', 'promise'],
            'priorities' => ['low', 'medium', 'high']
        ];
    } catch (\Exception $e) {
        \Log::error('Error getting call schedule data: ' . $e->getMessage());
        return [
            'stats' => ['today_calls' => 0, 'week_calls' => 0, 'overdue_calls' => 0, 'high_priority' => 0],
            'today_calls' => [],
            'call_types' => ['follow_up', 'payment_reminder', 'final_notice', 'promise'],
            'priorities' => ['low', 'medium', 'high']
        ];
    }
}

/**
 * Get scheduled calls with filters
 */
public function getScheduledCallsWithFilters(array $filters): array
{
    $query = $this->buildScheduledCallsQuery();
    
    $this->applyScheduleFilters($query, $filters);
    
    if (isset($filters['calendar_view'])) {
        return $this->handleCalendarFormat($query, $filters);
    }
    
    if (isset($filters['draw'])) {
        return $this->handleDataTablesPagination($query, $filters);
    }
    
    return $this->handleSimplePagination($query, $filters);
}

/**
 * Build base query for scheduled calls
 */
private function buildScheduledCallsQuery()
{
    $queries = [];
    $tenantId = TenantContext::currentId();

    if (Schema::hasTable('call_schedules')) {
        $queries[] = DB::table('call_schedules as cs')
            ->join('customers as c', 'cs.customer_id', '=', 'c.id')
            ->leftJoin('debt_collection_trackings as dct', 'c.id', '=', 'dct.customer_id')
            ->when($tenantId, function ($query, $tenantId) {
                $query->where('cs.tenant_id', $tenantId)
                    ->where('c.tenant_id', $tenantId)
                    ->where(function ($q) use ($tenantId) {
                        $q->whereNull('dct.tenant_id')
                          ->orWhere('dct.tenant_id', $tenantId);
                    });
            })
            ->select([
                'cs.id as schedule_id',
                'cs.customer_id',
                'c.name as customer_name',
                'c.phone',
                'c.outstanding_balance',
                'cs.scheduled_date',
                'cs.priority',
                'cs.call_type',
                'cs.notes',
                'cs.status',
                'cs.created_at',
                'dct.calls_made',
                'dct.missed_calls',
                DB::raw("'schedule' as entry_type")
            ]);
    }

    $promiseQuery = DB::table('debt_collection_trackings as dct')
        ->join('customers as c', 'dct.customer_id', '=', 'c.id')
        ->whereNotNull('dct.payment_promise_date')
        ->when($tenantId, function ($query, $tenantId) {
            $query->where('dct.tenant_id', $tenantId)
                ->where('c.tenant_id', $tenantId);
        })
        ->select([
            DB::raw('CONCAT("promise-", dct.id) as schedule_id'),
            'dct.customer_id',
            'c.name as customer_name',
            'c.phone',
            'c.outstanding_balance',
            'dct.payment_promise_date as scheduled_date',
            'dct.priority',
            DB::raw("'promise' as call_type"),
            'dct.notes',
            DB::raw("'promised' as status"),
            'dct.created_at',
            'dct.calls_made',
            'dct.missed_calls',
            DB::raw("'promise' as entry_type")
        ]);

    $followUpQuery = DB::table('debt_collection_trackings as dct')
        ->join('customers as c', 'dct.customer_id', '=', 'c.id')
        ->whereNotNull('dct.follow_up_date')
        ->when($tenantId, function ($query, $tenantId) {
            $query->where('dct.tenant_id', $tenantId)
                ->where('c.tenant_id', $tenantId);
        })
        ->select([
            DB::raw('CONCAT("followup-", dct.id) as schedule_id'),
            'dct.customer_id',
            'c.name as customer_name',
            'c.phone',
            'c.outstanding_balance',
            'dct.follow_up_date as scheduled_date',
            'dct.priority',
            DB::raw("'follow_up' as call_type"),
            'dct.notes',
            DB::raw("'pending' as status"),
            'dct.created_at',
            'dct.calls_made',
            'dct.missed_calls',
            DB::raw("'follow_up' as entry_type")
        ]);

    $queries[] = $promiseQuery;
    $queries[] = $followUpQuery;

    $union = array_shift($queries);
    foreach ($queries as $query) {
        $union = $union->unionAll($query);
    }

    return DB::query()->fromSub($union, 'scheduled_calls');
}


/**
 * Apply filters to scheduled calls query
 */
private function applyScheduleFilters($query, array $filters): void
{
    if (!empty($filters['date'])) {
        $query->whereDate('scheduled_date', $filters['date']);
    }

    if (!empty($filters['week_start']) && !empty($filters['week_end'])) {
        $query->whereBetween('scheduled_date', [$filters['week_start'], $filters['week_end']]);
    }

    if (!empty($filters['priority'])) {
        $query->where('priority', $filters['priority']);
    }

    if (!empty($filters['call_type'])) {
        $query->where('call_type', $filters['call_type']);
    }

    if (!empty($filters['status'])) {
        $query->where('status', $filters['status']);
    }

    if (isset($filters['overdue']) && $filters['overdue']) {
        $query->where('scheduled_date', '<', Carbon::now());
    }

    if (!empty($filters['search'])) {
        $searchTerm = $filters['search'];
        if (is_array($searchTerm)) {
            $searchTerm = $searchTerm['value'] ?? '';
        }
        $searchTerm = trim((string) $searchTerm);
        if ($searchTerm !== '') {
            $search = '%' . $searchTerm . '%';
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'LIKE', $search)
                  ->orWhere('phone', 'LIKE', $search);
            });
        }
    }

    $query->orderBy('scheduled_date', 'asc');
}


/**
 * Handle calendar format for scheduled calls
 */
private function handleCalendarFormat($query, array $filters): array
{
    $calls = $query->get();
    
    $events = $calls->map(function ($call) {
        return [
            'id' => $call->schedule_id,
            'title' => ($call->customer_name ?? 'Unknown Customer') . ' - ' . ucfirst($call->call_type ?? 'follow_up'),
            'start' => $call->scheduled_date,
            'backgroundColor' => $this->getPriorityColor($call->priority ?? 'medium'),
            'borderColor' => $this->getPriorityColor($call->priority ?? 'medium'),
            'extendedProps' => [
                'customer_id' => $call->customer_id,
                'customer_name' => $call->customer_name ?? 'Unknown Customer',
                'phone' => $call->phone,
                'balance' => $call->outstanding_balance,
                'priority' => $call->priority ?? 'medium',
                'call_type' => $call->call_type ?? 'follow_up',
                'notes' => $call->notes,
                'status' => $call->status ?? 'pending'
            ]
        ];
    });

    return ['events' => $events];
}

/**
 * Get priority color for calendar
 */
private function getPriorityColor(string $priority): string
{
    return match($priority) {
        'high' => '#dc3545',
        'medium' => '#ffc107',
        'low' => '#28a745',
        default => '#6c757d'
    };
}


/**
 * Schedule a customer call
 */
public function scheduleCustomerCall(int $customerId, array $data): array
{
    try {
        // Get customer name for response
        $customer = Customer::find($customerId);
        $customerName = $customer ? $customer->name : 'Unknown Customer';
        $tenantId = TenantContext::currentId();
        
        if (Schema::hasTable('call_schedules')) {
            $schedule = DB::table('call_schedules')->insertGetId([
                'tenant_id' => $tenantId,
                'customer_id' => $customerId,
                'scheduled_date' => $data['scheduled_date'],
                'priority' => $data['priority'],
                'call_type' => $data['call_type'],
                'notes' => $data['notes'] ?? null,
                'status' => 'pending',
                'created_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            return [
                'id' => $schedule, 
                'status' => 'scheduled',
                'customer_name' => $customerName
            ];
        } else {
            // Fallback to updating tracking table
            $tracking = DebtCollectionTracking::firstOrCreate(
                ['customer_id' => $customerId],
                ['priority' => 'medium']
            );
            
            $tracking->update([
                'follow_up_date' => $data['scheduled_date'],
                'priority' => $data['priority'],
                'notes' => ($tracking->notes ?? '') . "\n[" . now()->format('Y-m-d H:i') . "] Scheduled: " . ($data['notes'] ?? '')
            ]);
            
            return [
                'id' => $tracking->id, 
                'status' => 'scheduled',
                'customer_name' => $customerName
            ];
        }
    } catch (\Exception $e) {
        \Log::error('Error scheduling call: ' . $e->getMessage());
        throw $e;
    }
}
/**
 * Update call schedule
 */
public function updateCallSchedule(int $scheduleId, array $data): array
{
    try {
        if (Schema::hasTable('call_schedules')) {
            $tenantId = TenantContext::currentId();
            $updateData = array_filter($data, function($value) {
                return $value !== null && $value !== '';
            });
            $updateData['updated_at'] = now();
            
            DB::table('call_schedules')
                ->where('id', $scheduleId)
                ->when($tenantId, function ($query, $tenantId) {
                    $query->where('tenant_id', $tenantId);
                })
                ->update($updateData);
                
            return ['id' => $scheduleId, 'status' => 'updated'];
        } else {
            // Fallback to tracking table
            $tracking = DebtCollectionTracking::findOrFail($scheduleId);
            
            if (!empty($data['scheduled_date'])) {
                $tracking->follow_up_date = $data['scheduled_date'];
            }
            if (!empty($data['priority'])) {
                $tracking->priority = $data['priority'];
            }
            
            $tracking->save();
            
            return ['id' => $tracking->id, 'status' => 'updated'];
        }
    } catch (\Exception $e) {
        \Log::error('Error updating call schedule: ' . $e->getMessage());
        throw $e;
    }
}

/**
 * Get scheduled calls count
 */
private function getScheduledCallsCount(array $filters): int
{
    try {
        $query = $this->buildScheduledCallsQuery();
        $this->applyScheduleFilters($query, $filters);
        return $query->count();
    } catch (\Exception $e) {
        \Log::error('Error getting scheduled calls count: ' . $e->getMessage());
        return 0;
    }
}

    
}
