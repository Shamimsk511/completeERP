<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
use App\Models\Company;
use App\Support\TenantContext;
use App\Exports\ArrayExport;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class CustomerInsightController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:report-sales|report-financial', ['only' => [
            'index',
            'customerSummaryData',
            'categoryCustomerTotalsData',
            'categoryTopCustomerData',
            'companyTopCustomerData',
            'exportCustomerSummary',
            'exportCategoryTopCustomer',
            'exportCompanyTopCustomer',
            'exportCategoryCustomerTotals',
        ]]);
    }

    public function index(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);
        $months = $this->getMonthSpan($startDate, $endDate);
        $tenantId = TenantContext::currentId();

        $summary = DB::table('invoices')
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->when($tenantId, function ($query, $tenantId) {
                $query->where('invoices.tenant_id', $tenantId);
            })
            ->selectRaw('COUNT(*) as invoice_count')
            ->selectRaw('COUNT(DISTINCT customer_id) as customer_count')
            ->selectRaw('COALESCE(SUM(total), 0) as total_amount')
            ->first();

        $summary->avg_basket = $summary->invoice_count > 0
            ? $summary->total_amount / $summary->invoice_count
            : 0;

        $filters = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'category_id' => $request->category_id,
        ];

        $categories = Category::orderBy('name')->get(['id', 'name']);
        $companies = Company::orderBy('name')->get(['id', 'name']);

        return view('reports.customers.index', compact(
            'filters',
            'summary',
            'months',
            'categories',
            'companies'
        ));
    }

    public function customerSummaryData(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);
        $months = $this->getMonthSpan($startDate, $endDate);
        $tenantId = TenantContext::currentId();

        $query = DB::table('invoices')
            ->join('customers', 'invoices.customer_id', '=', 'customers.id')
            ->whereBetween('invoices.invoice_date', [$startDate, $endDate])
            ->when($tenantId, function ($query, $tenantId) {
                $query->where('invoices.tenant_id', $tenantId)
                    ->where('customers.tenant_id', $tenantId);
            })
            ->groupBy('customers.id', 'customers.name', 'customers.phone', 'customers.address')
            ->selectRaw('customers.id as customer_id')
            ->selectRaw('customers.name as customer_name')
            ->selectRaw('customers.phone as phone')
            ->selectRaw('customers.address as address')
            ->selectRaw('COUNT(invoices.id) as invoice_count')
            ->selectRaw('COALESCE(SUM(invoices.total), 0) as total_amount')
            ->selectRaw('COALESCE(AVG(invoices.total), 0) as avg_basket')
            ->selectRaw('MAX(invoices.invoice_date) as last_purchase');

        return DataTables::of($query)
            ->addColumn('frequency_per_month', function ($row) use ($months) {
                if ($months <= 0) {
                    return (float) $row->invoice_count;
                }
                return round(((float) $row->invoice_count) / $months, 2);
            })
            ->editColumn('last_purchase', function ($row) {
                return $row->last_purchase ? Carbon::parse($row->last_purchase)->format('d M, Y') : '';
            })
            ->filterColumn('customer_name', function ($q, $keyword) {
                $q->where('customers.name', 'like', '%' . $keyword . '%');
            })
            ->filterColumn('phone', function ($q, $keyword) {
                $q->where('customers.phone', 'like', '%' . $keyword . '%');
            })
            ->filterColumn('address', function ($q, $keyword) {
                $q->where('customers.address', 'like', '%' . $keyword . '%');
            })
            ->orderColumn('customer_name', 'customers.name $1')
            ->toJson();
    }

    public function categoryCustomerTotalsData(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);
        $categoryId = $request->category_id;

        $query = $this->categoryCustomerTotalsQuery($startDate, $endDate, $categoryId, true);

        return DataTables::of($query)
            ->filterColumn('category_name', function ($q, $keyword) {
                $q->where('categories.name', 'like', '%' . $keyword . '%');
            })
            ->filterColumn('customer_name', function ($q, $keyword) {
                $q->where('customers.name', 'like', '%' . $keyword . '%');
            })
            ->orderColumn('category_name', 'categories.name $1')
            ->orderColumn('customer_name', 'customers.name $1')
            ->toJson();
    }

    public function categoryTopCustomerData(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);
        $categoryId = $request->category_id;

        $base = $this->categoryCustomerTotalsQuery($startDate, $endDate, $categoryId, true);

        $max = DB::query()
            ->fromSub($base, 'cc')
            ->selectRaw('category_id, MAX(total_amount) as max_amount')
            ->groupBy('category_id');

        $query = DB::query()
            ->fromSub($base, 'cc')
            ->joinSub($max, 'cm', function ($join) {
                $join->on('cc.category_id', '=', 'cm.category_id')
                    ->on('cc.total_amount', '=', 'cm.max_amount');
            })
            ->select('cc.*');

        return DataTables::of($query)->toJson();
    }

    public function companyTopCustomerData(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);

        $base = $this->companyCustomerTotalsQuery($startDate, $endDate, true);

        $max = DB::query()
            ->fromSub($base, 'cc')
            ->selectRaw('company_id, MAX(total_amount) as max_amount')
            ->groupBy('company_id');

        $query = DB::query()
            ->fromSub($base, 'cc')
            ->joinSub($max, 'cm', function ($join) {
                $join->on('cc.company_id', '=', 'cm.company_id')
                    ->on('cc.total_amount', '=', 'cm.max_amount');
            })
            ->select('cc.*');

        return DataTables::of($query)->toJson();
    }

    public function exportCustomerSummary(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);
        $months = $this->getMonthSpan($startDate, $endDate);
        $tenantId = TenantContext::currentId();

        $rows = DB::table('invoices')
            ->join('customers', 'invoices.customer_id', '=', 'customers.id')
            ->whereBetween('invoices.invoice_date', [$startDate, $endDate])
            ->when($tenantId, function ($query, $tenantId) {
                $query->where('invoices.tenant_id', $tenantId)
                    ->where('customers.tenant_id', $tenantId);
            })
            ->groupBy('customers.id', 'customers.name', 'customers.phone', 'customers.address')
            ->selectRaw('customers.name as customer_name')
            ->selectRaw('customers.phone as phone')
            ->selectRaw('customers.address as address')
            ->selectRaw('COUNT(invoices.id) as invoice_count')
            ->selectRaw('COALESCE(SUM(invoices.total), 0) as total_amount')
            ->selectRaw('COALESCE(AVG(invoices.total), 0) as avg_basket')
            ->selectRaw('MAX(invoices.invoice_date) as last_purchase')
            ->get()
            ->map(function ($row) use ($months) {
                $frequency = $months > 0 ? round(((float) $row->invoice_count) / $months, 2) : (float) $row->invoice_count;
                return [
                    $row->customer_name,
                    $row->phone,
                    $row->address,
                    (int) $row->invoice_count,
                    (float) $row->total_amount,
                    (float) $row->avg_basket,
                    $frequency,
                    $row->last_purchase ? Carbon::parse($row->last_purchase)->format('Y-m-d') : '',
                ];
            })->values()->toArray();

        return Excel::download(
            new ArrayExport($rows, [
                'Customer',
                'Phone',
                'Address',
                'Invoices',
                'Total Amount',
                'Avg Basket',
                'Frequency / Month',
                'Last Purchase',
            ]),
            'customer_summary_' . $startDate . '_to_' . $endDate . '.xlsx'
        );
    }

    public function exportCategoryTopCustomer(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);
        $categoryId = $request->category_id;

        $base = $this->categoryCustomerTotalsQuery($startDate, $endDate, $categoryId, true);

        $max = DB::query()
            ->fromSub($base, 'cc')
            ->selectRaw('category_id, MAX(total_amount) as max_amount')
            ->groupBy('category_id');

        $rows = DB::query()
            ->fromSub($base, 'cc')
            ->joinSub($max, 'cm', function ($join) {
                $join->on('cc.category_id', '=', 'cm.category_id')
                    ->on('cc.total_amount', '=', 'cm.max_amount');
            })
            ->select('cc.*')
            ->get()
            ->map(function ($row) {
                return [
                    $row->category_name,
                    $row->customer_name,
                    (float) $row->total_quantity,
                    (float) $row->total_amount,
                ];
            })->values()->toArray();

        return Excel::download(
            new ArrayExport($rows, [
                'Category',
                'Top Customer',
                'Total Qty',
                'Total Amount',
            ]),
            'category_top_customer_' . $startDate . '_to_' . $endDate . '.xlsx'
        );
    }

    public function exportCompanyTopCustomer(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);

        $base = $this->companyCustomerTotalsQuery($startDate, $endDate, true);

        $max = DB::query()
            ->fromSub($base, 'cc')
            ->selectRaw('company_id, MAX(total_amount) as max_amount')
            ->groupBy('company_id');

        $rows = DB::query()
            ->fromSub($base, 'cc')
            ->joinSub($max, 'cm', function ($join) {
                $join->on('cc.company_id', '=', 'cm.company_id')
                    ->on('cc.total_amount', '=', 'cm.max_amount');
            })
            ->select('cc.*')
            ->get()
            ->map(function ($row) {
                return [
                    $row->company_name,
                    $row->customer_name,
                    (float) $row->total_quantity,
                    (float) $row->total_amount,
                ];
            })->values()->toArray();

        return Excel::download(
            new ArrayExport($rows, [
                'Company',
                'Top Customer',
                'Total Qty',
                'Total Amount',
            ]),
            'company_top_customer_' . $startDate . '_to_' . $endDate . '.xlsx'
        );
    }

    public function exportCategoryCustomerTotals(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);
        $categoryId = $request->category_id;

        $rows = $this->categoryCustomerTotalsQuery($startDate, $endDate, $categoryId, true)
            ->get()
            ->map(function ($row) {
                return [
                    $row->category_name,
                    $row->customer_name,
                    (float) $row->total_quantity,
                    (float) $row->total_amount,
                ];
            })->values()->toArray();

        return Excel::download(
            new ArrayExport($rows, [
                'Category',
                'Customer',
                'Total Qty',
                'Total Amount',
            ]),
            'category_customer_mix_' . $startDate . '_to_' . $endDate . '.xlsx'
        );
    }

    private function getDateRange(Request $request): array
    {
        $startDate = $request->start_date ?? Carbon::now()->subDays(30)->format('Y-m-d');
        $endDate = $request->end_date ?? Carbon::now()->format('Y-m-d');

        if (Carbon::parse($startDate)->gt(Carbon::parse($endDate))) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        return [$startDate, $endDate];
    }

    private function getMonthSpan(string $startDate, string $endDate): int
    {
        $start = Carbon::parse($startDate)->startOfMonth();
        $end = Carbon::parse($endDate)->startOfMonth();

        return max(1, $start->diffInMonths($end) + 1);
    }

    private function categoryCustomerTotalsQuery(string $startDate, string $endDate, $categoryId = null, bool $useAliasSelect = false)
    {
        $tenantId = TenantContext::currentId();

        $query = DB::table('invoice_items')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->join('customers', 'invoices.customer_id', '=', 'customers.id')
            ->join('products', 'invoice_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->whereBetween('invoices.invoice_date', [$startDate, $endDate])
            ->when($tenantId, function ($query, $tenantId) {
                $query->where('invoice_items.tenant_id', $tenantId)
                    ->where('invoices.tenant_id', $tenantId)
                    ->where('customers.tenant_id', $tenantId)
                    ->where('products.tenant_id', $tenantId)
                    ->where(function ($q) use ($tenantId) {
                        $q->whereNull('categories.tenant_id')
                          ->orWhere('categories.tenant_id', $tenantId);
                    });
            });

        if ($categoryId !== null && $categoryId !== '') {
            if ((string) $categoryId === '0') {
                $query->whereNull('products.category_id');
            } else {
                $query->where('products.category_id', $categoryId);
            }
        }

        $select = $useAliasSelect
            ? [
                DB::raw('COALESCE(categories.id, 0) as category_id'),
                DB::raw('COALESCE(categories.name, "Uncategorized") as category_name'),
                'customers.id as customer_id',
                'customers.name as customer_name',
                DB::raw('SUM(invoice_items.quantity) as total_quantity'),
                DB::raw('SUM(invoice_items.total) as total_amount'),
            ]
            : [
                'categories.id as category_id',
                'categories.name as category_name',
                'customers.id as customer_id',
                'customers.name as customer_name',
                DB::raw('SUM(invoice_items.quantity) as total_quantity'),
                DB::raw('SUM(invoice_items.total) as total_amount'),
            ];

        return $query
            ->groupBy('categories.id', 'categories.name', 'customers.id', 'customers.name')
            ->select($select);
    }

    private function companyCustomerTotalsQuery(string $startDate, string $endDate, bool $useAliasSelect = false)
    {
        $tenantId = TenantContext::currentId();

        $query = DB::table('invoice_items')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->join('customers', 'invoices.customer_id', '=', 'customers.id')
            ->join('products', 'invoice_items.product_id', '=', 'products.id')
            ->leftJoin('companies', 'products.company_id', '=', 'companies.id')
            ->whereBetween('invoices.invoice_date', [$startDate, $endDate])
            ->when($tenantId, function ($query, $tenantId) {
                $query->where('invoice_items.tenant_id', $tenantId)
                    ->where('invoices.tenant_id', $tenantId)
                    ->where('customers.tenant_id', $tenantId)
                    ->where('products.tenant_id', $tenantId)
                    ->where(function ($q) use ($tenantId) {
                        $q->whereNull('companies.tenant_id')
                          ->orWhere('companies.tenant_id', $tenantId);
                    });
            });

        $select = $useAliasSelect
            ? [
                DB::raw('COALESCE(companies.id, 0) as company_id'),
                DB::raw('COALESCE(companies.name, "Unassigned") as company_name'),
                'customers.id as customer_id',
                'customers.name as customer_name',
                DB::raw('SUM(invoice_items.quantity) as total_quantity'),
                DB::raw('SUM(invoice_items.total) as total_amount'),
            ]
            : [
                'companies.id as company_id',
                'companies.name as company_name',
                'customers.id as customer_id',
                'customers.name as customer_name',
                DB::raw('SUM(invoice_items.quantity) as total_quantity'),
                DB::raw('SUM(invoice_items.total) as total_amount'),
            ];

        return $query
            ->groupBy('companies.id', 'companies.name', 'customers.id', 'customers.name')
            ->select($select);
    }
}
