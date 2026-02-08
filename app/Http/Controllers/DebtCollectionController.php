<?php

namespace App\Http\Controllers;

use Schema;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Models\Invoice;
use App\Models\Customer;
use Illuminate\View\View;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\DebtCollectionTracking;
use App\Services\DebtCollectionService;
use App\Http\Resources\CustomerDebtResource;
use App\Support\TenantContext;
use App\Http\Requests\DebtCollection\CallLogRequest;
use App\Http\Requests\DebtCollection\UpdateTrackingRequest;

class DebtCollectionController extends Controller
{
    private DebtCollectionService $debtService;

    public function __construct(DebtCollectionService $debtService)
    {
        $this->debtService = $debtService;
        $this->middleware('auth');
    }

    /**
     * Main dashboard with optimized data loading
     */
    public function index()
    {
        try {
            $stats = $this->debtService->getDashboardStats();
        } catch (\Exception $e) {
            $stats = [
                'due_today' => 0,
                'due_this_week' => 0,
                'overdue' => 0,
                'high_priority' => 0
            ];
        }
        
        return view('debt_collection.index', compact('stats'));
    }

    /**
     * Get customers for DataTables (Table view)
     */
    public function getCustomersWithOutstanding(Request $request): JsonResponse
{
    try {
        if ($request->boolean('simple')) {
            $filters = $request->all();
            $filters['draw'] = $request->input('draw', 1);
            $filters['start'] = $request->input('start', 0);
            $filters['length'] = $request->input('length', 25);

            if ($request->has('search.value') && !empty($request->input('search.value'))) {
                $filters['search'] = $request->input('search.value');
            } elseif ($request->filled('search')) {
                $filters['search'] = $request->input('search');
            }

            $columns = [
                0 => 'customer_name',
                1 => 'outstanding_balance',
                2 => 'last_transaction_at',
                3 => 'last_call_at',
                4 => 'last_activity_at',
                5 => 'days_since_activity_sort',
                6 => 'last_promise_date',
                7 => 'promise_count',
                8 => 'promise_change_count',
                9 => 'last_note',
            ];

            if ($order = $request->input('order.0')) {
                $columnIdx = (int) $order['column'];
                $filters['order_column'] = $columns[$columnIdx] ?? 'days_since_activity_sort';
                $filters['order_dir'] = $order['dir'] ?? 'desc';
            } else {
                $filters['order_column'] = 'days_since_activity_sort';
                $filters['order_dir'] = 'desc';
            }

            $result = $this->debtService->getSimpleCustomersWithFilters($filters);

            return response()->json([
                'draw' => intval($filters['draw']),
                'recordsTotal' => intval($result['recordsTotal'] ?? $result['total']),
                'recordsFiltered' => intval($result['recordsFiltered'] ?? $result['filtered'] ?? $result['total']),
                'data' => $result['data']
            ]);
        }

        // Handle dropdown request specifically
        if ($request->has('for_dropdown')) {
            $searchTerm = '';
            
            // Handle search from Select2 AJAX
            if ($request->has('search.value')) {
                $searchTerm = $request->input('search.value');
            } elseif ($request->has('search')) {
                $searchTerm = $request->input('search');
            }
            
            $query = Customer::where('outstanding_balance', '>', 0)
                ->select('id', 'name', 'phone', 'outstanding_balance');
            
            // Add search functionality
            if (!empty($searchTerm)) {
                $query->where(function($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('phone', 'LIKE', "%{$searchTerm}%");
                });
            }
            
            $customers = $query->orderBy('name')
                ->limit($request->input('length', 50))
                ->get();
            
            // Transform for consistent response
            $transformedCustomers = $customers->map(function($customer) {
                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'customer_name' => $customer->name, // For backward compatibility
                    'phone' => $customer->phone,
                    'outstanding_balance' => (float) $customer->outstanding_balance
                ];
            });
                
            return response()->json([
                'success' => true,
                'data' => $transformedCustomers
            ]);
        }
        
        // Regular DataTables processing
        $filters = $request->all();
        $filters['draw'] = $request->input('draw', 1);
        $filters['start'] = $request->input('start', 0);
        $filters['length'] = $request->input('length', 25);

        $searchValue = $request->input('search.value', $request->input('search'));
        if (is_array($searchValue)) {
            $searchValue = $searchValue['value'] ?? '';
        }
        $filters['search'] = $searchValue;

        $result = $this->debtService->getCustomersWithFilters($filters);

        $promiseDates = collect();
        $customerIds = collect($result['data'])->pluck('id')->filter()->values();
        if ($customerIds->isNotEmpty() && Schema::hasTable('call_logs')) {
            $tenantId = TenantContext::currentId();
            $promiseDates = DB::table('call_logs')
                ->whereIn('customer_id', $customerIds)
                ->when($tenantId, function ($query, $tenantId) {
                    $query->where('tenant_id', $tenantId);
                })
                ->whereNotNull('payment_promise_date')
                ->groupBy('customer_id')
                ->selectRaw('customer_id, MAX(payment_promise_date) as last_promise_date')
                ->pluck('last_promise_date', 'customer_id');
        }
        
        // Transform data to ensure customer names are included
        $transformedData = collect($result['data'])->map(function ($customer) use ($promiseDates) {
            $promiseDate = $promiseDates[$customer->id] ?? $customer->debtCollectionTracking?->payment_promise_date;
            return [
                'id' => $customer->id,
                'name' => $customer->name,
                'customer_name' => $customer->name, // Add this for consistency
                'phone' => $customer->phone ?? null,
                'outstanding_balance' => $customer->outstanding_balance ?? 0,
                'due_date' => $customer->debtCollectionTracking?->due_date,
                'payment_promise_date' => $promiseDate
                    ? Carbon::parse($promiseDate)->format('d M, Y')
                    : null,
                'priority' => $customer->debtCollectionTracking?->priority ?? 'medium',
                'calls_made' => $customer->debtCollectionTracking?->calls_made ?? 0,
                'missed_calls' => $customer->debtCollectionTracking?->missed_calls ?? 0,
                'last_call_date' => $customer->debtCollectionTracking?->last_call_date,
                'last_interaction' => $customer->debtCollectionTracking?->last_call_date
                    ? Carbon::parse($customer->debtCollectionTracking->last_call_date)->format('d M, Y')
                    : 'Never',
                'call_tracking' => 'Calls: ' . ($customer->debtCollectionTracking?->calls_made ?? 0)
                    . ' | Missed: ' . ($customer->debtCollectionTracking?->missed_calls ?? 0),
                'action' => sprintf(
                    '<a href="%s" class="btn btn-sm btn-info mr-1"><i class="fas fa-history"></i></a>
                     <a href="%s" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>',
                    route('debt-collection.call-history', $customer->id),
                    route('debt-collection.edit-tracking', $customer->id)
                ),
            ];
        });
        
        return response()->json([
            'draw' => intval($filters['draw']),
            'recordsTotal' => intval($result['recordsTotal'] ?? $result['total']),
            'recordsFiltered' => intval($result['recordsFiltered'] ?? $result['filtered'] ?? $result['total']),
            'data' => $transformedData
        ]);
    } catch (\Exception $e) {
        \Log::error('Error in getCustomersWithOutstanding: ' . $e->getMessage());
        \Log::error('Request data: ' . json_encode($request->all()));
        
        return response()->json([
            'draw' => intval($request->input('draw', 1)),
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'data' => [],
            'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
        ], 500);
    }
}

    /**
     * Get customers for Cards view (Simple pagination)
     */
    public function getCustomersCards(Request $request): JsonResponse
    {
        try {
            $customers = $this->debtService->getCustomersWithFilters($request->all());
            
            // Transform the data for cards view - only include fields that exist
            $transformedData = collect($customers['data'])->map(function ($customer) {
                $data = [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'phone' => $customer->phone ?? null,
                    'outstanding_balance' => $customer->outstanding_balance ?? 0,
                    'due_date' => $customer->debtCollectionTracking?->due_date,
                    'priority' => $customer->debtCollectionTracking?->priority ?? 'medium',
                    'calls_made' => $customer->debtCollectionTracking?->calls_made ?? 0,
                    'missed_calls' => $customer->debtCollectionTracking?->missed_calls ?? 0,
                    'last_call_date' => $customer->debtCollectionTracking?->last_call_date 
                        ? Carbon::parse($customer->debtCollectionTracking->last_call_date)->format('M d, Y') 
                        : null,
                ];
                
                // Only add these fields if they exist in the tracking table
                if ($customer->debtCollectionTracking) {
                    if (isset($customer->debtCollectionTracking->payment_promise_date)) {
                        $data['payment_promise_date'] = $customer->debtCollectionTracking->payment_promise_date;
                    }
                    if (isset($customer->debtCollectionTracking->follow_up_date)) {
                        $data['follow_up_date'] = $customer->debtCollectionTracking->follow_up_date;
                    }
                }
                
                return $data;
            });
            
            // Return simple pagination format for cards
            return response()->json([
                'success' => true,
                'data' => $transformedData,
                'pagination' => [
                    'total' => $customers['total'],
                    'per_page' => $customers['per_page'],
                    'current_page' => $customers['current_page'],
                    'last_page' => ceil($customers['total'] / $customers['per_page']),
                    'from' => (($customers['current_page'] - 1) * $customers['per_page']) + 1,
                    'to' => min($customers['current_page'] * $customers['per_page'], $customers['total'])
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Cards Ajax Error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => config('app.debug') ? $e->getMessage() : 'Internal server error',
                'data' => []
            ], 500);
        }
    }

    /**
     * Get customer call history
     */
    public function getCallHistory(int $customerId): JsonResponse
    {
        try {
            $history = $this->debtService->getCustomerCallHistory($customerId);
            
            return response()->json([
                'success' => true,
                'data' => $history
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load call history'
            ], 500);
        }
    }

    public function export(Request $request)
    {
        try {
            return $this->debtService->exportData($request->all());
        } catch (\Exception $e) {
            return back()->with('error', 'Export failed: ' . $e->getMessage());
        }
    }

    /**
     * Log customer call
     */
    public function logCall(Request $request, int $customerId): JsonResponse
    {
        // Custom validation with more flexible boolean handling
        $validator = Validator::make($request->all(), [
            'call_status' => 'required|in:successful,missed,busy,disconnected',
            'duration' => 'nullable|integer|min:0',
            'notes' => 'nullable|string|max:1000',
            'payment_promise_date' => 'nullable|date|after_or_equal:today',
            'follow_up_required' => 'nullable', // Remove boolean validation temporarily
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();
            
            // Prepare data with proper boolean conversion
            $callData = $request->all();
            
            // Handle follow_up_required more robustly
            $followUpValue = $request->input('follow_up_required');
            
            // Convert various possible values to boolean
            if ($followUpValue === null || $followUpValue === '' || $followUpValue === 'false' || $followUpValue === false || $followUpValue === 0 || $followUpValue === '0') {
                $callData['follow_up_required'] = false;
            } else {
                $callData['follow_up_required'] = true;
            }
            
            \Log::info('Call data prepared', [
                'customer_id' => $customerId,
                'follow_up_required' => $callData['follow_up_required'],
                'original_value' => $followUpValue
            ]);
            
            $result = $this->debtService->logCustomerCall($customerId, $callData);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Call logged successfully',
                'data' => $result
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error logging call: ' . $e->getMessage());
            Log::error('Request data: ' . json_encode($request->all()));
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to log call: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update tracking information
     */
    public function updateTracking(Request $request, int $customerId): JsonResponse
    {
        $request->validate([
            'due_date' => 'nullable|date',
            'priority' => 'required|in:low,medium,high',
            'notes' => 'nullable|string|max:2000',
            'payment_promise_date' => 'nullable|date|after:today',
            'follow_up_date' => 'nullable|date|after:today',
            'calls_made' => 'nullable|integer|min:0',
            'missed_calls' => 'nullable|integer|min:0',
        ]);

        try {
            $tracking = $this->debtService->updateCustomerTracking($customerId, $request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'Tracking updated successfully',
                'data' => $tracking
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error updating tracking: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update tracking'
            ], 500);
        }
    }

    /**
     * Show call history for a customer
     */
    public function callHistory(int $customerId): View
    {
        try {
            $customer = Customer::findOrFail($customerId);
            $history = $this->debtService->getCustomerCallHistory($customerId);
            
            return view('debt_collection.call_history', compact('customer', 'history'));
        } catch (\Exception $e) {
            Log::error('Error loading call history: ' . $e->getMessage());
            return view('debt_collection.call_history', [
                'customer' => null,
                'history' => [],
                'error' => 'Customer not found'
            ]);
        }
    }

    public function dueToday(): View
    {
        $today = Carbon::today();
        $totalDueToday = Customer::where('outstanding_balance', '>', 0)
            ->whereHas('debtCollectionTracking', function ($query) use ($today) {
                $query->where(function ($q) use ($today) {
                    $q->whereDate('due_date', $today)
                        ->orWhere(function ($fallback) use ($today) {
                            $fallback->whereNull('due_date')
                                ->where(function ($dates) use ($today) {
                                    $dates->whereDate('payment_promise_date', $today)
                                        ->orWhereDate('follow_up_date', $today);
                                });
                        });
                });
            })
            ->count();

        return view('debt_collection.due_today', compact('totalDueToday'));
    }

    public function dueThisWeek(): View
    {
        $start = Carbon::now()->startOfWeek();
        $end = Carbon::now()->endOfWeek();

        $totalDueThisWeek = Customer::where('outstanding_balance', '>', 0)
            ->whereHas('debtCollectionTracking', function ($query) use ($start, $end) {
                $query->where(function ($q) use ($start, $end) {
                    $q->whereBetween('due_date', [$start, $end])
                        ->orWhere(function ($fallback) use ($start, $end) {
                            $fallback->whereNull('due_date')
                                ->where(function ($dates) use ($start, $end) {
                                    $dates->whereBetween('payment_promise_date', [$start, $end])
                                        ->orWhereBetween('follow_up_date', [$start, $end]);
                                });
                        });
                });
            })
            ->count();

        return view('debt_collection.due_this_week', compact('totalDueThisWeek'));
    }

    /**
     * Show edit tracking form
     */
    public function editTracking(int $customerId): View
    {
        try {
            $customer = Customer::with('debtCollectionTracking')->find($customerId);
            
            if (!$customer) {
                return view('debt_collection.edit_tracking', [
                    'customer' => null,
                    'tracking' => null,
                    'error' => 'Customer not found'
                ]);
            }
            
            // Get existing tracking or create a new instance for the form
            $tracking = $customer->debtCollectionTracking;
            if (!$tracking) {
                $tracking = new DebtCollectionTracking([
                    'customer_id' => $customerId,
                    'priority' => 'medium'
                ]);
            }
            
            return view('debt_collection.edit_tracking', compact('customer', 'tracking'));
            
        } catch (\Exception $e) {
            Log::error('Error loading tracking form: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return view('debt_collection.edit_tracking', [
                'customer' => null,
                'tracking' => null,
                'error' => 'An error occurred while loading the tracking information. Please try again.'
            ]);
        }
    }

    /**
     * Analytics dashboard
     */
    public function analytics(): View
    {
        try {
            $analytics = $this->debtService->getAnalyticsData();
            return view('debt_collection.analytics', compact('analytics'));
        } catch (\Exception $e) {
            Log::error('Error loading analytics: ' . $e->getMessage());
            return view('debt_collection.analytics', ['analytics' => []]);
        }
    }

    /**
     * Overdue accounts report
     */
    public function overdueAccounts(): View
    {
        try {
            $overdue = $this->debtService->getOverdueAccounts();
            return view('debt_collection.overdue_accounts', compact('overdue'));
        } catch (\Exception $e) {
            Log::error('Error loading overdue accounts: ' . $e->getMessage());
            return view('debt_collection.overdue_accounts', ['overdue' => []]);
        }
    }

    /**
     * Performance report
     */
    public function performance(): View
    {
        try {
            $performance = $this->debtService->getPerformanceData();
            return view('debt_collection.performance', compact('performance'));
        } catch (\Exception $e) {
            Log::error('Error loading performance: ' . $e->getMessage());
            return view('debt_collection.performance', ['performance' => []]);
        }
    }

    /**
     * Bulk actions
     */
    public function bulkAction(Request $request): JsonResponse
    {
        $request->validate([
            'action' => 'required|in:mark_priority,schedule_calls,send_reminders',
            'customer_ids' => 'required|array|min:1',
            'customer_ids.*' => 'exists:customers,id'
        ]);

        try {
            DB::beginTransaction();
            
            $result = $this->debtService->performBulkAction(
                $request->action,
                $request->customer_ids,
                $request->only(['priority', 'date', 'template'])
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Bulk action completed successfully",
                'affected_count' => $result['affected_count']
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error in bulk action: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Bulk action failed'
            ], 500);
        }
    }

    /**
     * Get dashboard stats (AJAX)
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = $this->debtService->getDashboardStats();
            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading stats: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load statistics'
            ], 500);
        }
    }
    /**
 * Show call schedule view
 */
public function callSchedule(): View
{
    try {
        $scheduleData = $this->debtService->getCallScheduleData();
        return view('debt_collection.call_schedule', compact('scheduleData'));
    } catch (\Exception $e) {
        Log::error('Error loading call schedule: ' . $e->getMessage());
        return view('debt_collection.call_schedule', ['scheduleData' => []]);
    }
}

/**
 * Get scheduled calls for AJAX (DataTables/Calendar)
 */
public function getScheduledCalls(Request $request): JsonResponse
{
    try {
        $filters = $request->all();
        $result = $this->debtService->getScheduledCallsWithFilters($filters);
        
        if ($request->has('calendar_view')) {
            return response()->json([
                'success' => true,
                'events' => $result['events']
            ]);
        }
        
        return response()->json([
            'draw' => intval($request->input('draw', 1)),
            'recordsTotal' => $result['total'],
            'recordsFiltered' => $result['filtered'],
            'data' => $result['data']
        ]);
    } catch (\Exception $e) {
        Log::error('Error loading scheduled calls: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to load scheduled calls'
        ], 500);
    }
}


/**
 * Schedule a call
 */
public function scheduleCall(Request $request): JsonResponse
{
    $request->validate([
        'customer_id' => 'required|exists:customers,id',
        'scheduled_date' => 'required|date|after:now',
        'priority' => 'required|in:low,medium,high',
        'notes' => 'nullable|string|max:500',
        'call_type' => 'required|in:follow_up,payment_reminder,final_notice'
    ]);

    try {
        DB::beginTransaction();
        
        $schedule = $this->debtService->scheduleCustomerCall(
            $request->customer_id,
            $request->all()
        );
        
        DB::commit();
        
        return response()->json([
            'success' => true,
            'message' => 'Call scheduled successfully',
            'data' => $schedule
        ]);
    } catch (\Exception $e) {
        DB::rollback();
        Log::error('Error scheduling call: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to schedule call'
        ], 500);
    }
}


/**
 * Update scheduled call
 */
public function updateScheduledCall(Request $request, int $scheduleId): JsonResponse
{
    $request->validate([
        'scheduled_date' => 'nullable|date|after:now',
        'priority' => 'nullable|in:low,medium,high',
        'notes' => 'nullable|string|max:500',
        'status' => 'nullable|in:pending,completed,cancelled,rescheduled'
    ]);

    try {
        $schedule = $this->debtService->updateCallSchedule($scheduleId, $request->all());
        
        return response()->json([
            'success' => true,
            'message' => 'Call schedule updated successfully',
            'data' => $schedule
        ]);
    } catch (\Exception $e) {
        Log::error('Error updating call schedule: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to update call schedule'
        ], 500);
    }
}
}
