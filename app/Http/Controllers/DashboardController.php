<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Purchase;
use App\Models\OtherDelivery;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Support\TenantContext;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $this->touchCurrentUser($request);

        // Time filter handling
        $timeFilter = $request->input('time_filter', 'month'); // Default to current month
        $startDate = null;
        $endDate = null;
        
        if ($timeFilter === 'custom') {
            $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : Carbon::now()->startOfMonth();
            $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : Carbon::now();
        } elseif ($timeFilter === 'year') {
            $startDate = Carbon::now()->startOfYear();
            $endDate = Carbon::now();
        } else { // month
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now();
        }
        
        $cacheKey = sprintf(
            'dashboard.summary.%s.%s.%s',
            $timeFilter,
            $startDate->toDateString(),
            $endDate->toDateString()
        );

        if (config('perf.enabled')) {
            $summary = Cache::remember($cacheKey, config('perf.ttl.dashboard'), function () use ($startDate, $endDate) {
                $invoiceQuery = Invoice::whereBetween('invoice_date', [$startDate, $endDate]);

                return [
                    'totalInvoices' => $invoiceQuery->count(),
                    'totalInvoiceAmount' => $invoiceQuery->sum('total'),
                    'totalBalanceDue' => Customer::sum('outstanding_balance'),
                    'totalProductQuantity' => Product::sum('current_stock'),
                    'latestInvoices' => Invoice::with('customer')
                        ->orderBy('invoice_date', 'desc')
                        ->take(5)
                        ->get(),
                    'topStockItems' => Product::orderBy('current_stock', 'desc')
                        ->take(10)
                        ->get(),
                ];
            });
        } else {
            $invoiceQuery = Invoice::whereBetween('invoice_date', [$startDate, $endDate]);
            $summary = [
                'totalInvoices' => $invoiceQuery->count(),
                'totalInvoiceAmount' => $invoiceQuery->sum('total'),
                'totalBalanceDue' => Customer::sum('outstanding_balance'),
                'totalProductQuantity' => Product::sum('current_stock'),
                'latestInvoices' => Invoice::with('customer')
                    ->orderBy('invoice_date', 'desc')
                    ->take(5)
                    ->get(),
                'topStockItems' => Product::orderBy('current_stock', 'desc')
                    ->take(10)
                    ->get(),
            ];
        }
        
        return view('dashboard', array_merge($summary, [
            'timeFilter' => $timeFilter,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'todayStats' => $this->getTodayStats(),
            'onlineSnapshot' => $this->getOnlineSnapshot(),
            'chatUsers' => $this->getChatUsers(),
            'presentEmployees' => $this->getPresentEmployees(),
            'employeeSummary' => $this->getEmployeeSummary(),
            'hourlySales' => $this->getHourlySales(),
            'monthlySales' => $this->getMonthlySales(),
            'monthlyTrend' => $this->getMonthlyTrend(),
            'insights' => $this->getInsights(),
        ]));
    }

    public function live(Request $request)
    {
        $this->touchCurrentUser($request);

        return response()->json([
            'todayStats' => $this->getTodayStats(),
            'onlineSnapshot' => $this->getOnlineSnapshot(),
            'presentEmployees' => $this->getPresentEmployees(),
            'employeeSummary' => $this->getEmployeeSummary(),
            'hourlySales' => $this->getHourlySales(),
            'monthlyTrend' => $this->getMonthlyTrend(),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    protected function touchCurrentUser(Request $request): void
    {
        $user = $request->user();
        if ($user) {
            $user->forceFill(['last_seen_at' => now()])->save();
        }
    }

    protected function tenantUsersQuery()
    {
        $tenantId = TenantContext::currentId();

        if (!$tenantId) {
            return User::query();
        }

        return User::query()->where(function ($query) use ($tenantId) {
            $query->where('tenant_id', $tenantId)
                ->orWhereHas('tenants', function ($tenantQuery) use ($tenantId) {
                    $tenantQuery->where('tenants.id', $tenantId);
                });
        });
    }

    protected function getTodayStats(): array
    {
        $today = now()->toDateString();

        $invoiceBase = Invoice::whereDate('invoice_date', $today);
        $purchaseBase = Purchase::whereDate('purchase_date', $today);
        $otherDeliveryBase = OtherDelivery::whereDate('delivery_date', $today);

        return [
            'invoice_count' => (clone $invoiceBase)->count(),
            'invoice_amount' => (float) ((clone $invoiceBase)->sum('total') ?? 0),
            'purchase_count' => (clone $purchaseBase)->count(),
            'purchase_amount' => (float) ((clone $purchaseBase)->sum('grand_total') ?? 0),
            'other_delivery_count' => (clone $otherDeliveryBase)->count(),
        ];
    }

    protected function getOnlineSnapshot(): array
    {
        $onlineThreshold = now()->subMinutes(5);
        $onlineBase = $this->tenantUsersQuery()->where('last_seen_at', '>=', $onlineThreshold);

        $users = (clone $onlineBase)
            ->orderByDesc('last_seen_at')
            ->take(10)
            ->get(['id', 'name', 'last_seen_at'])
            ->map(function (User $user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'last_seen' => optional($user->last_seen_at)->diffForHumans(),
                ];
            })
            ->values();

        return [
            'count' => (clone $onlineBase)->count(),
            'users' => $users,
        ];
    }

    protected function getChatUsers(): array
    {
        return $this->tenantUsersQuery()
            ->whereKeyNot(auth()->id())
            ->orderBy('name')
            ->take(30)
            ->get(['id', 'name'])
            ->map(function (User $user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                ];
            })
            ->values()
            ->all();
    }

    protected function getPresentEmployees(): array
    {
        $today = now()->toDateString();

        return EmployeeAttendance::with('employee:id,name')
            ->whereDate('date', $today)
            ->where('status', 'present')
            ->orderBy('id', 'desc')
            ->take(10)
            ->get()
            ->map(function (EmployeeAttendance $attendance) {
                return [
                    'id' => $attendance->employee_id,
                    'name' => optional($attendance->employee)->name ?? 'Unknown',
                ];
            })
            ->values()
            ->all();
    }

    protected function getEmployeeSummary(): array
    {
        $today = now()->toDateString();
        $activeEmployeeCount = Employee::where('status', 'active')->count();
        $presentCount = EmployeeAttendance::whereDate('date', $today)->where('status', 'present')->count();

        return [
            'active' => $activeEmployeeCount,
            'present' => $presentCount,
            'absent_or_unmarked' => max($activeEmployeeCount - $presentCount, 0),
        ];
    }

    protected function getHourlySales(): array
    {
        $today = now()->toDateString();
        $hourRows = Invoice::selectRaw('HOUR(created_at) as hour_slot, SUM(total) as total_sales')
            ->whereDate('created_at', $today)
            ->groupBy(DB::raw('HOUR(created_at)'))
            ->pluck('total_sales', 'hour_slot');

        $labels = [];
        $values = [];

        for ($hour = 0; $hour < 24; $hour++) {
            $labels[] = str_pad((string) $hour, 2, '0', STR_PAD_LEFT) . ':00';
            $values[] = (float) ($hourRows[$hour] ?? 0);
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    protected function getMonthlySales(): array
    {
        $start = now()->startOfMonth()->subMonths(11)->toDateString();
        $end = now()->endOfMonth()->toDateString();

        $rows = Invoice::selectRaw("DATE_FORMAT(invoice_date, '%Y-%m') as ym, SUM(total) as total_sales")
            ->whereBetween('invoice_date', [$start, $end])
            ->groupBy('ym')
            ->orderBy('ym')
            ->get()
            ->keyBy('ym');

        $labels = [];
        $values = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = now()->startOfMonth()->subMonths($i);
            $key = $month->format('Y-m');
            $labels[] = $month->format('M Y');
            $values[] = (float) (optional($rows->get($key))->total_sales ?? 0);
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    protected function getMonthlyTrend(): array
    {
        $currentMonth = now()->startOfMonth();
        $lastMonth = now()->copy()->subMonth()->startOfMonth();

        $currentTotal = (float) Invoice::whereBetween('invoice_date', [
            $currentMonth->toDateString(),
            $currentMonth->copy()->endOfMonth()->toDateString(),
        ])->sum('total');

        $lastTotal = (float) Invoice::whereBetween('invoice_date', [
            $lastMonth->toDateString(),
            $lastMonth->copy()->endOfMonth()->toDateString(),
        ])->sum('total');

        $growthPercent = $lastTotal > 0
            ? (($currentTotal - $lastTotal) / $lastTotal) * 100
            : null;

        return [
            'current_total' => $currentTotal,
            'last_total' => $lastTotal,
            'growth_percent' => $growthPercent,
            'is_up' => $growthPercent !== null ? $growthPercent >= 0 : true,
        ];
    }

    protected function getInsights(): array
    {
        return [
            'low_stock_count' => Product::where('current_stock', '<=', 5)->count(),
            'due_customer_count' => Customer::where('outstanding_balance', '>', 0)->count(),
            'unpaid_invoice_count' => Invoice::whereIn('payment_status', ['unpaid', 'partial'])->count(),
        ];
    }
}
