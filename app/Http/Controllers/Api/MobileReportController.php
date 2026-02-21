<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\OtherDelivery;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileReportController extends Controller
{
    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$this->canAccessReports($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to access reports.',
            ], 403);
        }

        $tenantId = $this->tenantIdForUser($user);
        $today = now()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();

        $todaySales = Invoice::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->whereDate('invoice_date', $today)
            ->sum('total');

        $todayCollections = Transaction::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->where('type', 'debit')
            ->whereDate('created_at', $today)
            ->selectRaw('COALESCE(SUM(amount + COALESCE(discount_amount, 0)), 0) as total')
            ->value('total');

        $monthSales = Invoice::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->whereBetween('invoice_date', [$monthStart, $monthEnd])
            ->sum('total');

        $monthCollections = Transaction::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->where('type', 'debit')
            ->whereBetween('created_at', [$monthStart . ' 00:00:00', $monthEnd . ' 23:59:59'])
            ->selectRaw('COALESCE(SUM(amount + COALESCE(discount_amount, 0)), 0) as total')
            ->value('total');

        $outstandingReceivable = Customer::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->where('outstanding_balance', '>', 0)
            ->sum('outstanding_balance');

        $lowStockProducts = Product::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->where('is_stock_managed', true)
            ->where('current_stock', '<=', 5)
            ->count();

        $pendingDeliveries = OtherDelivery::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->whereIn('status', ['pending', 'in_transit'])
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'today_sales' => (float) $todaySales,
                'today_collections' => (float) ($todayCollections ?? 0),
                'month_sales' => (float) $monthSales,
                'month_collections' => (float) ($monthCollections ?? 0),
                'outstanding_receivable' => (float) $outstandingReceivable,
                'low_stock_products' => (int) $lowStockProducts,
                'pending_deliveries' => (int) $pendingDeliveries,
            ],
        ]);
    }

    protected function canAccessReports($user): bool
    {
        if (!$user) {
            return false;
        }

        if (method_exists($user, 'hasRole') && ($user->hasRole('Super Admin') || $user->hasRole('Admin'))) {
            return true;
        }

        return method_exists($user, 'can') && (
            $user->can('invoice-list') ||
            $user->can('transaction-list') ||
            $user->can('product-list')
        );
    }

    protected function tenantIdForUser($user): ?int
    {
        if (!$user) {
            return null;
        }

        $tokenName = (string) ($user->currentAccessToken()?->name ?? '');
        if (preg_match('/\|tenant:(\d+)$/', $tokenName, $matches)) {
            return (int) $matches[1];
        }

        if (!empty($user->tenant_id)) {
            return (int) $user->tenant_id;
        }

        return 1;
    }
}

