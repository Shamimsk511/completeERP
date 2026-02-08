<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\Category;
use App\Models\Company;
use App\Models\ProductReturn;
use App\Models\ProductReturnItem;
use App\Models\Purchase;
use App\Models\OtherDelivery;
use App\Models\OtherDeliveryItem;
use App\Models\Customer;
use App\Models\Godown;
use App\Models\ErpFeatureSetting;
use App\Support\TenantContext;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductReportExport;
use App\Exports\ArrayExport;
use Yajra\DataTables\Facades\DataTables;

class ProductReportController extends Controller
{
    /**
     * Display the main report dashboard
     */
    public function index(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);
        $tenantId = TenantContext::currentId();

        $categories = Category::orderBy('name')->get();
        $godowns = ErpFeatureSetting::isEnabled('godown_management')
            ? Godown::orderBy('name')->get()
            : collect();
        $godownId = $request->get('godown_id');

        $salesSummaryQuery = DB::table('invoice_items')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->whereBetween('invoices.invoice_date', [$startDate, $endDate])
            ->when($tenantId, function ($query, $tenantId) {
                $query->where('invoice_items.tenant_id', $tenantId)
                    ->where('invoices.tenant_id', $tenantId);
            })
            ->selectRaw('COALESCE(SUM(invoice_items.quantity), 0) as sales_quantity')
            ->selectRaw('COALESCE(SUM(invoice_items.total), 0) as sales_amount')
            ->selectRaw('COUNT(DISTINCT invoices.id) as invoice_count');

        $this->applyGodownFilterToItems($salesSummaryQuery, $godownId, 'invoice_items.product_id');

        $salesSummary = $salesSummaryQuery->first();

        $returnsSummaryQuery = DB::table('product_return_items')
            ->join('product_returns', 'product_return_items.return_id', '=', 'product_returns.id')
            ->whereBetween('product_returns.return_date', [$startDate, $endDate])
            ->when($tenantId, function ($query, $tenantId) {
                $query->where('product_return_items.tenant_id', $tenantId)
                    ->where('product_returns.tenant_id', $tenantId);
            })
            ->selectRaw('COALESCE(SUM(product_return_items.quantity), 0) as return_quantity')
            ->selectRaw('COALESCE(SUM(product_return_items.total), 0) as return_amount');

        if (!empty($godownId) && ErpFeatureSetting::isEnabled('godown_management')) {
            $returnsSummaryQuery->where('product_return_items.godown_id', $godownId);
        }

        $this->applyGodownFilterToItems($returnsSummaryQuery, $godownId, 'product_return_items.product_id');

        $returnsSummary = $returnsSummaryQuery->first();

        $deliveriesSummaryQuery = DB::table('other_delivery_items')
            ->join('other_deliveries', 'other_delivery_items.other_delivery_id', '=', 'other_deliveries.id')
            ->whereBetween('other_deliveries.delivery_date', [$startDate, $endDate])
            ->when($tenantId, function ($query, $tenantId) {
                $query->where('other_delivery_items.tenant_id', $tenantId)
                    ->where('other_deliveries.tenant_id', $tenantId);
            })
            ->selectRaw('COALESCE(SUM(other_delivery_items.quantity), 0) as delivery_quantity');

        if (!empty($godownId) && ErpFeatureSetting::isEnabled('godown_management')) {
            $deliveriesSummaryQuery->where('other_delivery_items.godown_id', $godownId);
        }

        $this->applyGodownFilterToItems($deliveriesSummaryQuery, $godownId, 'other_delivery_items.product_id');

        $deliveriesSummary = $deliveriesSummaryQuery->first();

        $movementSummary = (object) [
            'total_quantity' => (float) ($salesSummary->sales_quantity ?? 0)
                + (float) ($returnsSummary->return_quantity ?? 0)
                + (float) ($deliveriesSummary->delivery_quantity ?? 0),
            'total_amount' => (float) ($salesSummary->sales_amount ?? 0)
                + (float) ($returnsSummary->return_amount ?? 0),
            'invoice_count' => (int) ($salesSummary->invoice_count ?? 0),
            'product_count' => 0,
        ];

        $movementIdSales = DB::table('invoice_items')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->whereBetween('invoices.invoice_date', [$startDate, $endDate])
            ->when($tenantId, function ($query, $tenantId) {
                $query->where('invoice_items.tenant_id', $tenantId)
                    ->where('invoices.tenant_id', $tenantId);
            })
            ->select('invoice_items.product_id');

        $this->applyGodownFilterToItems($movementIdSales, $godownId, 'invoice_items.product_id');

        $movementIdReturns = DB::table('product_return_items')
            ->join('product_returns', 'product_return_items.return_id', '=', 'product_returns.id')
            ->whereBetween('product_returns.return_date', [$startDate, $endDate])
            ->when($tenantId, function ($query, $tenantId) {
                $query->where('product_return_items.tenant_id', $tenantId)
                    ->where('product_returns.tenant_id', $tenantId);
            })
            ->select('product_return_items.product_id');

        if (!empty($godownId) && ErpFeatureSetting::isEnabled('godown_management')) {
            $movementIdReturns->where('product_return_items.godown_id', $godownId);
        }

        $this->applyGodownFilterToItems($movementIdReturns, $godownId, 'product_return_items.product_id');

        $movementIdDeliveries = DB::table('other_delivery_items')
            ->join('other_deliveries', 'other_delivery_items.other_delivery_id', '=', 'other_deliveries.id')
            ->whereBetween('other_deliveries.delivery_date', [$startDate, $endDate])
            ->when($tenantId, function ($query, $tenantId) {
                $query->where('other_delivery_items.tenant_id', $tenantId)
                    ->where('other_deliveries.tenant_id', $tenantId);
            })
            ->select('other_delivery_items.product_id');

        if (!empty($godownId) && ErpFeatureSetting::isEnabled('godown_management')) {
            $movementIdDeliveries->where('other_delivery_items.godown_id', $godownId);
        }

        $this->applyGodownFilterToItems($movementIdDeliveries, $godownId, 'other_delivery_items.product_id');

        $movementIdQuery = $movementIdSales
            ->union($movementIdReturns)
            ->union($movementIdDeliveries);

        $movementSummary->product_count = DB::query()
            ->fromSub($movementIdQuery, 'movement')
            ->count();

        $categoryTotals = $this->getCategoryMovementTotals($startDate, $endDate, $categories, $godownId);

        $selectedCategoryId = $request->category_id;
        if ($selectedCategoryId === null || $selectedCategoryId === '') {
            $selectedCategoryId = optional($categoryTotals->first())->category_id;
        }

        $selectedCategoryName = null;
        if ($selectedCategoryId !== null) {
            if ((string) $selectedCategoryId === '0') {
                $selectedCategoryName = 'Uncategorized';
            } else {
                $selectedCategoryName = optional($categories->firstWhere('id', (int) $selectedCategoryId))->name;
            }
        }

        $filters = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'category_id' => $selectedCategoryId,
            'godown_id' => $godownId,
        ];

        return view('reports.products.index', compact(
            'filters',
            'categories',
            'godowns',
            'movementSummary',
            'selectedCategoryName'
        ));
    }

    public function movementProductsData(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);

        $godownId = $request->get('godown_id');
        $categoryId = $request->input('category_id');
        $query = $this->movementProductQuery($startDate, $endDate, $godownId)
            ->whereRaw('(COALESCE(sales.sales_quantity, 0) + COALESCE(returns.return_quantity, 0) + COALESCE(deliveries.delivery_quantity, 0)) > 0');

        if ($categoryId !== null && $categoryId !== '') {
            if ((string) $categoryId === '0') {
                $query->whereNull('products.category_id');
            } else {
                $query->where('products.category_id', $categoryId);
            }
        }

        return DataTables::of($query)
            ->filterColumn('product_name', function ($q, $keyword) {
                $q->where('products.name', 'like', '%' . $keyword . '%');
            })
            ->filterColumn('category_name', function ($q, $keyword) {
                $q->where('categories.name', 'like', '%' . $keyword . '%');
            })
            ->filterColumn('company_name', function ($q, $keyword) {
                $q->where('companies.name', 'like', '%' . $keyword . '%');
            })
            ->filterColumn('movement_quantity', function ($q, $keyword) {
                $keyword = trim($keyword);
                if ($keyword !== '') {
                    $q->whereRaw(
                        '(COALESCE(sales.sales_quantity, 0) + COALESCE(returns.return_quantity, 0) + COALESCE(deliveries.delivery_quantity, 0)) like ?',
                        ['%' . $keyword . '%']
                    );
                }
            })
            ->filterColumn('movement_amount', function ($q, $keyword) {
                $keyword = trim($keyword);
                if ($keyword !== '') {
                    $q->whereRaw(
                        '(COALESCE(sales.sales_amount, 0) + COALESCE(returns.return_amount, 0)) like ?',
                        ['%' . $keyword . '%']
                    );
                }
            })
            ->orderColumn('product_name', 'products.name $1')
            ->orderColumn('category_name', 'categories.name $1')
            ->orderColumn('company_name', 'companies.name $1')
            ->orderColumn('movement_quantity', 'movement_quantity $1')
            ->orderColumn('movement_amount', 'movement_amount $1')
            ->toJson();
    }

    public function nonMovingProductsData(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);

        $godownId = $request->get('godown_id');
        $categoryId = $request->input('category_id');
        $query = $this->movementProductQuery($startDate, $endDate, $godownId)
            ->whereRaw('(COALESCE(sales.sales_quantity, 0) + COALESCE(returns.return_quantity, 0) + COALESCE(deliveries.delivery_quantity, 0)) = 0');

        if ($categoryId !== null && $categoryId !== '') {
            if ((string) $categoryId === '0') {
                $query->whereNull('products.category_id');
            } else {
                $query->where('products.category_id', $categoryId);
            }
        }

        return DataTables::of($query)
            ->filterColumn('product_name', function ($q, $keyword) {
                $q->where('products.name', 'like', '%' . $keyword . '%');
            })
            ->filterColumn('category_name', function ($q, $keyword) {
                $q->where('categories.name', 'like', '%' . $keyword . '%');
            })
            ->filterColumn('company_name', function ($q, $keyword) {
                $q->where('companies.name', 'like', '%' . $keyword . '%');
            })
            ->orderColumn('product_name', 'products.name $1')
            ->orderColumn('category_name', 'categories.name $1')
            ->orderColumn('company_name', 'companies.name $1')
            ->toJson();
    }

    public function companySummaryData(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);

        $godownId = $request->get('godown_id');
        $rows = $this->movementProductQuery($startDate, $endDate, $godownId)->get();

        $summary = $rows->filter(function ($row) {
            return (float) $row->movement_quantity > 0;
        })->groupBy(function ($row) {
            return $row->company_id ?? 0;
        })->map(function ($rows) {
            $sorted = $rows->sortByDesc('movement_quantity')->values();
            $topProduct = $sorted->first();
            $first = $sorted->first();

            return [
                'company_id' => $first->company_id ?? 0,
                'company_name' => $first->company_name ?? 'Unassigned',
                'total_quantity' => $sorted->sum('movement_quantity'),
                'total_amount' => $sorted->sum('movement_amount'),
                'top_product' => $topProduct ? $topProduct->product_name : null,
                'top_quantity' => $topProduct ? $topProduct->movement_quantity : 0,
                'top_amount' => $topProduct ? $topProduct->movement_amount : 0,
            ];
        })->values();

        return DataTables::of($summary)->toJson();
    }

    public function categorySummaryData(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);

        $godownId = $request->get('godown_id');
        $rows = $this->movementProductQuery($startDate, $endDate, $godownId)->get();

        $summary = $rows->filter(function ($row) {
            return (float) $row->movement_quantity > 0;
        })->groupBy(function ($row) {
            return $row->category_id ?? 0;
        })->map(function ($rows) {
            $first = $rows->first();

            return [
                'category_id' => $first->category_id ?? 0,
                'category_name' => $first->category_name ?? 'Uncategorized',
                'total_quantity' => $rows->sum('movement_quantity'),
                'total_amount' => $rows->sum('movement_amount'),
            ];
        })->values();

        return DataTables::of($summary)->toJson();
    }

    public function categoryCompanySummaryData(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);

        $godownId = $request->get('godown_id');
        $categoryId = $request->input('category_id');
        if ($categoryId === null || $categoryId === '') {
            $categoryId = $this->getDefaultCategoryId($startDate, $endDate, $godownId);
        }
        $rows = $this->movementProductQuery($startDate, $endDate, $godownId)->get();

        $summary = $rows->filter(function ($row) use ($categoryId) {
            $rowCategory = $row->category_id ?? 0;
            return (float) $row->movement_quantity > 0 && (string) $rowCategory === (string) $categoryId;
        })->groupBy(function ($row) {
            return $row->company_id ?? 0;
        })->map(function ($rows) {
            $first = $rows->first();

            return [
                'company_id' => $first->company_id ?? 0,
                'company_name' => $first->company_name ?? 'Unassigned',
                'total_quantity' => $rows->sum('movement_quantity'),
                'total_amount' => $rows->sum('movement_amount'),
            ];
        })->values();

        return DataTables::of($summary)->toJson();
    }

    public function exportMovementProducts(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);

        $godownId = $request->get('godown_id');
        $rows = $this->movementProductQuery($startDate, $endDate, $godownId)->get()
            ->filter(function ($row) {
                return (float) $row->movement_quantity > 0;
            })
            ->map(function ($row) {
                return [
                    $row->product_name,
                    $row->category_name,
                    $row->company_name,
                    (float) $row->movement_quantity,
                    (float) $row->movement_amount,
                ];
            })->values()->toArray();

        return Excel::download(
            new ArrayExport($rows, [
                'Product',
                'Category',
                'Company',
                'Movement Qty',
                'Movement Amount',
            ]),
            'product_movement_' . $startDate . '_to_' . $endDate . '.xlsx'
        );
    }

    public function exportNonMovingProducts(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);

        $godownId = $request->get('godown_id');
        $rows = $this->movementProductQuery($startDate, $endDate, $godownId)->get()
            ->filter(function ($row) {
                return (float) $row->movement_quantity <= 0;
            })
            ->map(function ($row) {
                return [
                    $row->product_name,
                    $row->category_name,
                    $row->company_name,
                ];
            })->values()->toArray();

        return Excel::download(
            new ArrayExport($rows, [
                'Product',
                'Category',
                'Company',
            ]),
            'non_moving_products_' . $startDate . '_to_' . $endDate . '.xlsx'
        );
    }

    public function exportCompanySummary(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);

        $godownId = $request->get('godown_id');
        $rows = $this->movementProductQuery($startDate, $endDate, $godownId)->get();

        $summary = $rows->filter(function ($row) {
            return (float) $row->movement_quantity > 0;
        })->groupBy(function ($row) {
            return $row->company_id ?? 0;
        })->map(function ($rows) {
            $sorted = $rows->sortByDesc('movement_quantity')->values();
            $topProduct = $sorted->first();
            $first = $sorted->first();

            return [
                $first->company_name ?? 'Unassigned',
                (float) $sorted->sum('movement_quantity'),
                (float) $sorted->sum('movement_amount'),
                $topProduct ? $topProduct->product_name : '',
                $topProduct ? (float) $topProduct->movement_quantity : 0,
                $topProduct ? (float) $topProduct->movement_amount : 0,
            ];
        })->values()->toArray();

        return Excel::download(
            new ArrayExport($summary, [
                'Company',
                'Total Movement Qty',
                'Total Movement Amount',
                'Top Product',
                'Top Product Qty',
                'Top Product Amount',
            ]),
            'company_movement_summary_' . $startDate . '_to_' . $endDate . '.xlsx'
        );
    }

    public function exportCategorySummary(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);

        $godownId = $request->get('godown_id');
        $rows = $this->movementProductQuery($startDate, $endDate, $godownId)->get();

        $summary = $rows->filter(function ($row) {
            return (float) $row->movement_quantity > 0;
        })->groupBy(function ($row) {
            return $row->category_id ?? 0;
        })->map(function ($rows) {
            $first = $rows->first();
            return [
                $first->category_name ?? 'Uncategorized',
                (float) $rows->sum('movement_quantity'),
                (float) $rows->sum('movement_amount'),
            ];
        })->values()->toArray();

        return Excel::download(
            new ArrayExport($summary, [
                'Category',
                'Total Movement Qty',
                'Total Movement Amount',
            ]),
            'category_movement_summary_' . $startDate . '_to_' . $endDate . '.xlsx'
        );
    }

    public function exportCategoryCompanySummary(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);

        $godownId = $request->get('godown_id');
        $categoryId = $request->input('category_id');
        if ($categoryId === null || $categoryId === '') {
            $categoryId = $this->getDefaultCategoryId($startDate, $endDate, $godownId);
        }
        $rows = $this->movementProductQuery($startDate, $endDate, $godownId)->get();

        $summary = $rows->filter(function ($row) use ($categoryId) {
            $rowCategory = $row->category_id ?? 0;
            return (float) $row->movement_quantity > 0 && (string) $rowCategory === (string) $categoryId;
        })->groupBy(function ($row) {
            return $row->company_id ?? 0;
        })->map(function ($rows) {
            $first = $rows->first();
            return [
                $first->company_name ?? 'Unassigned',
                (float) $rows->sum('movement_quantity'),
                (float) $rows->sum('movement_amount'),
            ];
        })->values()->toArray();

        return Excel::download(
            new ArrayExport($summary, [
                'Company',
                'Total Movement Qty',
                'Total Movement Amount',
            ]),
            'category_company_movement_' . $startDate . '_to_' . $endDate . '.xlsx'
        );
    }
    
    /**
     * Generate sales report
     */
    public function salesReport(Request $request)
    {
        $filters = $this->getFilters($request);
        $products = Product::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        $companies = Company::brands()->orderBy('name')->get();
        $godowns = ErpFeatureSetting::isEnabled('godown_management')
            ? Godown::orderBy('name')->get()
            : collect();
        
        $query = InvoiceItem::with(['invoice.customer', 'product.category', 'product.company'])
            ->whereHas('product', function($q) use ($filters) {
                if (!empty($filters['product_id'])) {
                    $q->where('id', $filters['product_id']);
                }
                if (!empty($filters['category_id'])) {
                    $q->where('category_id', $filters['category_id']);
                }
                if (!empty($filters['company_id'])) {
                    $q->where('company_id', $filters['company_id']);
                }
            })
            ->whereHas('invoice', function($q) use ($filters) {
                $q->whereBetween('invoice_date', [$filters['start_date'], $filters['end_date']]);
                
                if (!empty($filters['customer_id'])) {
                    $q->where('customer_id', $filters['customer_id']);
                }
            });

        $this->applyGodownFilterToItems($query, $filters['godown_id']);
            
        $salesData = $this->getSalesByProduct($query->get());
        
        // Get top selling products for chart
        $topSellingProducts = $salesData->sortByDesc('quantity')->take(10);
        
        if ($request->has('export') && $request->export == 'excel') {
            return Excel::download(
                new ProductReportExport($salesData->toArray(), 'sales'),
                'sales_report_' . $filters['start_date'] . '_to_' . $filters['end_date'] . '.xlsx'
            );
        }
        
        return view('reports.products.sales', compact(
            'salesData', 
            'filters', 
            'products', 
            'categories', 
            'companies',
            'godowns',
            'topSellingProducts'
        ));
    }
    
    /**
     * Generate returns report
     */
    public function returnsReport(Request $request)
    {
        $filters = $this->getFilters($request);
        $products = Product::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        $companies = Company::brands()->orderBy('name')->get();
        $customers = Customer::orderBy('name')->get();
        $godowns = ErpFeatureSetting::isEnabled('godown_management')
            ? Godown::orderBy('name')->get()
            : collect();
        
        $query = ProductReturnItem::with(['productReturn.customer', 'product.category', 'product.company'])
            ->whereHas('product', function($q) use ($filters) {
                if (!empty($filters['product_id'])) {
                    $q->where('id', $filters['product_id']);
                }
                if (!empty($filters['category_id'])) {
                    $q->where('category_id', $filters['category_id']);
                }
                if (!empty($filters['company_id'])) {
                    $q->where('company_id', $filters['company_id']);
                }
            })
            ->whereHas('productReturn', function($q) use ($filters) {
                $q->whereBetween('return_date', [$filters['start_date'], $filters['end_date']]);
                
                if (!empty($filters['customer_id'])) {
                    $q->where('customer_id', $filters['customer_id']);
                }
            });

        if (!empty($filters['godown_id']) && ErpFeatureSetting::isEnabled('godown_management')) {
            $query->where('godown_id', $filters['godown_id']);
        }

        $this->applyGodownFilterToItems($query, $filters['godown_id']);
            
        $returnsData = $this->getReturnsByProduct($query->get());
        
        if ($request->has('export') && $request->export == 'excel') {
            return Excel::download(
                new ProductReportExport($returnsData->toArray(), 'returns'),
                'returns_report_' . $filters['start_date'] . '_to_' . $filters['end_date'] . '.xlsx'
            );
        }
        
        return view('reports.products.returns', compact(
            'returnsData', 
            'filters', 
            'products', 
            'categories', 
            'companies',
            'customers',
            'godowns'
        ));
    }
    
    /**
     * Generate purchases report
     */
/**
 * Generate the purchases report with proper variable naming
 */
public function purchasesReport(Request $request)
{
    $filters = $this->getFilters($request);
    $products = Product::orderBy('name')->get();
    $categories = Category::orderBy('name')->get();
    $companies = Company::brands()->orderBy('name')->get();
    $godowns = ErpFeatureSetting::isEnabled('godown_management')
        ? Godown::orderBy('name')->get()
        : collect();
    $tenantId = TenantContext::currentId();
    
    // Using DB query builder for purchase items
    $purchasesQuery = DB::table('purchases')
        ->join('purchase_items', 'purchases.id', '=', 'purchase_items.purchase_id')
        ->join('products', 'purchase_items.product_id', '=', 'products.id')
        ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
        ->leftJoin('companies', 'products.company_id', '=', 'companies.id')
        ->select(
            'products.id as product_id',
            'products.name as product_name',
            'categories.name as category_name',
            'companies.name as company_name',
            DB::raw('SUM(purchase_items.quantity) as total_quantity'),
            DB::raw('SUM(purchase_items.total_price) as total_amount'),
            DB::raw('COUNT(DISTINCT purchases.id) as purchase_count'),
            DB::raw('AVG(purchase_items.purchase_price) as average_price')
        )
        ->whereBetween('purchases.purchase_date', [$filters['start_date'], $filters['end_date']])
        ->when($tenantId, function ($query, $tenantId) {
            $query->where('purchases.tenant_id', $tenantId)
                ->where('purchase_items.tenant_id', $tenantId)
                ->where('products.tenant_id', $tenantId)
                ->where(function ($q) use ($tenantId) {
                    $q->whereNull('categories.tenant_id')
                      ->orWhere('categories.tenant_id', $tenantId);
                })
                ->where(function ($q) use ($tenantId) {
                    $q->whereNull('companies.tenant_id')
                      ->orWhere('companies.tenant_id', $tenantId);
                });
        })
        ->when(!empty($filters['product_id']), function($q) use ($filters) {
            return $q->where('products.id', $filters['product_id']);
        })
        ->when(!empty($filters['category_id']), function($q) use ($filters) {
            return $q->where('products.category_id', $filters['category_id']);
        })
        ->when(!empty($filters['company_id']), function($q) use ($filters) {
            return $q->where('products.company_id', $filters['company_id']);
        })
        ->when(!empty($filters['godown_id']) && ErpFeatureSetting::isEnabled('godown_management'), function($q) use ($filters) {
            return $q->where('purchase_items.godown_id', $filters['godown_id']);
        })
        ->groupBy('products.id', 'products.name', 'categories.name', 'companies.name');
    
    // Convert to a format compatible with the view
    $purchasesData = $purchasesQuery->get()->map(function($item) {
        // Create a product object with necessary properties
        $product = new \stdClass();
        $product->id = $item->product_id;
        $product->name = $item->product_name;
        
        // Create category and company objects
        $category = new \stdClass();
        $category->name = $item->category_name;
        $product->category = $category;
        
        $company = new \stdClass();
        $company->name = $item->company_name;
        $product->company = $company;
        
        return [
            'product' => $product,
            'quantity' => $item->total_quantity,
            'amount' => $item->total_amount,
            'average_price' => $item->average_price,
            'purchases' => $item->purchase_count
        ];
    });
    
    if ($request->has('export') && $request->export == 'excel') {
        return Excel::download(
            new ProductReportExport($purchasesData->toArray(), 'purchases'),
            'purchases_report_' . $filters['start_date'] . '_to_' . $filters['end_date'] . '.xlsx'
        );
    }
    
    return view('reports.products.purchases', compact(
        'purchasesData', 
        'filters', 
        'products', 
        'categories', 
        'companies',
        'godowns'
    ));

}

    
    /**
     * Generate other deliveries report
     */
    public function otherDeliveriesReport(Request $request)
    {
        $filters = $this->getFilters($request);
        $products = Product::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        $companies = Company::brands()->orderBy('name')->get();
        $godowns = ErpFeatureSetting::isEnabled('godown_management')
            ? Godown::orderBy('name')->get()
            : collect();
        
        $query = OtherDeliveryItem::with(['otherDelivery', 'product.category', 'product.company'])
            ->whereHas('product', function($q) use ($filters) {
                if (!empty($filters['product_id'])) {
                    $q->where('id', $filters['product_id']);
                }
                if (!empty($filters['category_id'])) {
                    $q->where('category_id', $filters['category_id']);
                }
                if (!empty($filters['company_id'])) {
                    $q->where('company_id', $filters['company_id']);
                }
            })
            ->whereHas('otherDelivery', function($q) use ($filters) {
                $q->whereBetween('delivery_date', [$filters['start_date'], $filters['end_date']]);
            });

        if (!empty($filters['godown_id']) && ErpFeatureSetting::isEnabled('godown_management')) {
            $query->where('godown_id', $filters['godown_id']);
        }
            
        $deliveriesData = $this->getDeliveriesByProduct($query->get());
        
        if ($request->has('export') && $request->export == 'excel') {
            return Excel::download(
                new ProductReportExport($deliveriesData->toArray(), 'deliveries'),
                'deliveries_report_' . $filters['start_date'] . '_to_' . $filters['end_date'] . '.xlsx'
            );
        }
        
        return view('reports.products.other-deliveries', compact('deliveriesData', 'filters', 'products', 'categories', 'companies', 'godowns'));
    }
    
    /**
     * Generate consolidated report showing all movements for each product
     */
    public function consolidatedReport(Request $request)
    {
        $filters = $this->getFilters($request);
        $products = Product::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        $companies = Company::brands()->orderBy('name')->get();
        $godowns = ErpFeatureSetting::isEnabled('godown_management')
            ? Godown::orderBy('name')->get()
            : collect();

        $productsQuery = Product::with(['category', 'company']);

        // Apply filters
        if (!empty($filters['product_id'])) {
            $productsQuery->where('id', $filters['product_id']);
        }
        if (!empty($filters['category_id'])) {
            $productsQuery->where('category_id', $filters['category_id']);
        }
        if (!empty($filters['company_id'])) {
            $productsQuery->where('company_id', $filters['company_id']);
        }

        $this->applyGodownFilterToProducts($productsQuery, $filters['godown_id']);

        $filteredProducts = $productsQuery->get();
        $reportData = $this->getConsolidatedDataOptimized($filteredProducts, $filters);

        if ($request->has('export') && $request->export == 'excel') {
            return Excel::download(
                new ProductReportExport($reportData, 'consolidated'),
                'consolidated_report_' . $filters['start_date'] . '_to_' . $filters['end_date'] . '.xlsx'
            );
        }

        return view('reports.products.consolidated', compact('reportData', 'filters', 'products', 'categories', 'companies', 'godowns'));
    }
    
    /**
     * Get detailed sales information for a specific product
     */
    public function getProductSaleDetails(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $startDate = $request->start_date ?: Carbon::now()->subYear()->format('Y-m-d');
        $endDate = $request->end_date ?: Carbon::now()->format('Y-m-d');

        $items = InvoiceItem::where('product_id', $request->product_id)
            ->with(['invoice.customer', 'product'])
            ->whereHas('invoice', function($q) use ($startDate, $endDate) {
                $q->whereBetween('invoice_date', [$startDate, $endDate]);
            })
            ->get();

        return view('reports.products.partials.sale-details', compact('items'));
    }
    
    /**
     * Get detailed return information for a specific product
     */
    public function getProductReturnDetails(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $startDate = $request->start_date ?: Carbon::now()->subYear()->format('Y-m-d');
        $endDate = $request->end_date ?: Carbon::now()->format('Y-m-d');
        $godownId = $request->godown_id;

        $itemsQuery = ProductReturnItem::where('product_id', $request->product_id)
            ->with(['productReturn.customer', 'product'])
            ->whereHas('productReturn', function($q) use ($startDate, $endDate) {
                $q->whereBetween('return_date', [$startDate, $endDate]);
            })
            ->orderBy('created_at', 'desc');

        if (!empty($godownId) && ErpFeatureSetting::isEnabled('godown_management')) {
            $itemsQuery->where('godown_id', $godownId);
        }

        $items = $itemsQuery->get();

        return view('reports.products.partials.return-details', compact('items'));
    }
    
    /**
     * Get detailed purchase information for a specific product
     */
    public function getProductPurchaseDetails(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $startDate = $request->start_date ?: Carbon::now()->subYear()->format('Y-m-d');
        $endDate = $request->end_date ?: Carbon::now()->format('Y-m-d');
        $godownId = $request->godown_id;
        $tenantId = TenantContext::currentId();

        $items = DB::table('purchases')
            ->join('purchase_items', 'purchases.id', '=', 'purchase_items.purchase_id')
            ->join('products', 'purchase_items.product_id', '=', 'products.id')
            ->leftJoin('companies', 'purchases.company_id', '=', 'companies.id')
            ->select(
                'purchases.id as purchase_id',
                'purchases.purchase_date',
                'purchases.invoice_no',
                'companies.name as company_name',
                'purchase_items.quantity',
                'purchase_items.purchase_price',
                'purchase_items.total_price'
            )
            ->where('purchase_items.product_id', $request->product_id)
            ->whereBetween('purchases.purchase_date', [$startDate, $endDate])
            ->when($tenantId, function ($query, $tenantId) {
                $query->where('purchases.tenant_id', $tenantId)
                    ->where('purchase_items.tenant_id', $tenantId)
                    ->where('products.tenant_id', $tenantId)
                    ->where(function ($q) use ($tenantId) {
                        $q->whereNull('companies.tenant_id')
                          ->orWhere('companies.tenant_id', $tenantId);
                    });
            })
            ->when(!empty($godownId) && ErpFeatureSetting::isEnabled('godown_management'), function ($query) use ($godownId) {
                $query->where('purchase_items.godown_id', $godownId);
            })
            ->orderBy('purchases.purchase_date', 'desc')
            ->get();

        return view('reports.products.partials.purchase-details', compact('items'));
    }
    
    /**
     * Get detailed delivery information for a specific product
     */
    public function getProductDeliveryDetails(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $startDate = $request->start_date ?: Carbon::now()->subYear()->format('Y-m-d');
        $endDate = $request->end_date ?: Carbon::now()->format('Y-m-d');
        $godownId = $request->godown_id;

        $itemsQuery = OtherDeliveryItem::where('product_id', $request->product_id)
            ->with(['otherDelivery', 'product'])
            ->whereHas('otherDelivery', function($q) use ($startDate, $endDate) {
                $q->whereBetween('delivery_date', [$startDate, $endDate]);
            })
            ->orderBy('created_at', 'desc');

        if (!empty($godownId) && ErpFeatureSetting::isEnabled('godown_management')) {
            $itemsQuery->where('godown_id', $godownId);
        }

        $items = $itemsQuery->get();

        return view('reports.products.partials.delivery-details', compact('items'));
    }

    private function applyGodownFilterToProducts($query, ?int $godownId)
    {
        if (!empty($godownId) && ErpFeatureSetting::isEnabled('godown_management')) {
            $tenantId = TenantContext::currentId();
            $query->whereIn('products.id', function ($sub) use ($godownId, $tenantId) {
                $sub->select('product_id')
                    ->from('product_godown_stocks')
                    ->where('godown_id', $godownId)
                    ->when($tenantId, function ($inner, $tenantId) {
                        $inner->where('tenant_id', $tenantId);
                    });
            });
        }
    }

    private function applyGodownFilterToItems($query, ?int $godownId, string $column = 'product_id')
    {
        if (!empty($godownId) && ErpFeatureSetting::isEnabled('godown_management')) {
            $tenantId = TenantContext::currentId();
            $query->whereIn($column, function ($sub) use ($godownId, $tenantId) {
                $sub->select('product_id')
                    ->from('product_godown_stocks')
                    ->where('godown_id', $godownId)
                    ->when($tenantId, function ($inner, $tenantId) {
                        $inner->where('tenant_id', $tenantId);
                    });
            });
        }
    }
    
    /**
     * Extract filters from request
     */
    private function getFilters(Request $request)
    {
        return [
            'start_date' => $request->start_date ?? Carbon::now()->subDays(30)->format('Y-m-d'),
            'end_date' => $request->end_date ?? Carbon::now()->format('Y-m-d'),
            'product_id' => $request->product_id,
            'category_id' => $request->category_id,
            'company_id' => $request->company_id,
            'customer_id' => $request->customer_id,
            'godown_id' => $request->godown_id,
        ];
    }

    private function getDateRange(Request $request): array
    {
        $startDate = $request->start_date ?? Carbon::now()->subDays(30)->format('Y-m-d');
        $endDate = $request->end_date ?? Carbon::now()->format('Y-m-d');

        return [$startDate, $endDate];
    }

    private function movementProductQuery(string $startDate, string $endDate, ?int $godownId = null)
    {
        $tenantId = TenantContext::currentId();

        $salesSub = DB::table('invoice_items')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->whereBetween('invoices.invoice_date', [$startDate, $endDate])
            ->when($tenantId, function ($query, $tenantId) {
                $query->where('invoice_items.tenant_id', $tenantId)
                    ->where('invoices.tenant_id', $tenantId);
            })
            ->groupBy('invoice_items.product_id')
            ->selectRaw('invoice_items.product_id, SUM(invoice_items.quantity) as sales_quantity, SUM(invoice_items.total) as sales_amount');

        $returnsSub = DB::table('product_return_items')
            ->join('product_returns', 'product_return_items.return_id', '=', 'product_returns.id')
            ->whereBetween('product_returns.return_date', [$startDate, $endDate])
            ->when($tenantId, function ($query, $tenantId) {
                $query->where('product_return_items.tenant_id', $tenantId)
                    ->where('product_returns.tenant_id', $tenantId);
            })
            ->groupBy('product_return_items.product_id')
            ->selectRaw('product_return_items.product_id, SUM(product_return_items.quantity) as return_quantity, SUM(product_return_items.total) as return_amount');

        $deliveriesSub = DB::table('other_delivery_items')
            ->join('other_deliveries', 'other_delivery_items.other_delivery_id', '=', 'other_deliveries.id')
            ->whereBetween('other_deliveries.delivery_date', [$startDate, $endDate])
            ->when($tenantId, function ($query, $tenantId) {
                $query->where('other_delivery_items.tenant_id', $tenantId)
                    ->where('other_deliveries.tenant_id', $tenantId);
            })
            ->groupBy('other_delivery_items.product_id')
            ->selectRaw('other_delivery_items.product_id, SUM(other_delivery_items.quantity) as delivery_quantity');

        if (!empty($godownId) && ErpFeatureSetting::isEnabled('godown_management')) {
            $returnsSub->where('product_return_items.godown_id', $godownId);
            $deliveriesSub->where('other_delivery_items.godown_id', $godownId);
        }

        $query = DB::table('products')
            ->leftJoinSub($salesSub, 'sales', function ($join) {
                $join->on('products.id', '=', 'sales.product_id');
            })
            ->leftJoinSub($returnsSub, 'returns', function ($join) {
                $join->on('products.id', '=', 'returns.product_id');
            })
            ->leftJoinSub($deliveriesSub, 'deliveries', function ($join) {
                $join->on('products.id', '=', 'deliveries.product_id');
            })
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('companies', 'products.company_id', '=', 'companies.id')
            ->when($tenantId, function ($query, $tenantId) {
                $query->where('products.tenant_id', $tenantId)
                    ->where(function ($q) use ($tenantId) {
                        $q->whereNull('categories.tenant_id')
                          ->orWhere('categories.tenant_id', $tenantId);
                    })
                    ->where(function ($q) use ($tenantId) {
                        $q->whereNull('companies.tenant_id')
                          ->orWhere('companies.tenant_id', $tenantId);
                    });
            })
            ->select(
                'products.id as product_id',
                'products.name as product_name',
                'products.category_id',
                'products.company_id',
                DB::raw("COALESCE(categories.name, 'Uncategorized') as category_name"),
                DB::raw("COALESCE(companies.name, 'Unassigned') as company_name"),
                DB::raw('COALESCE(sales.sales_quantity, 0) as sales_quantity'),
                DB::raw('COALESCE(returns.return_quantity, 0) as return_quantity'),
                DB::raw('COALESCE(deliveries.delivery_quantity, 0) as delivery_quantity'),
                DB::raw('(COALESCE(sales.sales_quantity, 0) + COALESCE(returns.return_quantity, 0) + COALESCE(deliveries.delivery_quantity, 0)) as movement_quantity'),
                DB::raw('(COALESCE(sales.sales_amount, 0) + COALESCE(returns.return_amount, 0)) as movement_amount')
            );

        $this->applyGodownFilterToProducts($query, $godownId);

        return $query;
    }

    private function getCategoryMovementTotals(string $startDate, string $endDate, $categories, ?int $godownId = null)
    {
        $categoryMap = $categories->keyBy('id');
        $totals = [];
        $tenantId = TenantContext::currentId();

        $salesQuery = DB::table('invoice_items')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->join('products', 'invoice_items.product_id', '=', 'products.id')
            ->whereBetween('invoices.invoice_date', [$startDate, $endDate])
            ->when($tenantId, function ($query, $tenantId) {
                $query->where('invoice_items.tenant_id', $tenantId)
                    ->where('invoices.tenant_id', $tenantId)
                    ->where('products.tenant_id', $tenantId);
            })
            ->groupBy('products.category_id')
            ->selectRaw('products.category_id as category_id, SUM(invoice_items.quantity) as qty, SUM(invoice_items.total) as amount');

        if (!empty($godownId) && ErpFeatureSetting::isEnabled('godown_management')) {
            $salesQuery->whereIn('invoice_items.product_id', function ($sub) use ($godownId) {
                $sub->select('product_id')
                    ->from('product_godown_stocks')
                    ->where('godown_id', $godownId);
            });
        }

        $sales = $salesQuery->get();

        foreach ($sales as $row) {
            $id = $row->category_id ?? 0;
            $totals[$id]['quantity'] = ($totals[$id]['quantity'] ?? 0) + (float) $row->qty;
            $totals[$id]['amount'] = ($totals[$id]['amount'] ?? 0) + (float) $row->amount;
        }

        $returns = DB::table('product_return_items')
            ->join('product_returns', 'product_return_items.return_id', '=', 'product_returns.id')
            ->join('products', 'product_return_items.product_id', '=', 'products.id')
            ->whereBetween('product_returns.return_date', [$startDate, $endDate])
            ->when($tenantId, function ($query, $tenantId) {
                $query->where('product_return_items.tenant_id', $tenantId)
                    ->where('product_returns.tenant_id', $tenantId)
                    ->where('products.tenant_id', $tenantId);
            })
            ->when(!empty($godownId) && ErpFeatureSetting::isEnabled('godown_management'), function ($query) use ($godownId) {
                $query->where('product_return_items.godown_id', $godownId);
            })
            ->groupBy('products.category_id')
            ->selectRaw('products.category_id as category_id, SUM(product_return_items.quantity) as qty, SUM(product_return_items.total) as amount')
            ->get();

        foreach ($returns as $row) {
            $id = $row->category_id ?? 0;
            $totals[$id]['quantity'] = ($totals[$id]['quantity'] ?? 0) + (float) $row->qty;
            $totals[$id]['amount'] = ($totals[$id]['amount'] ?? 0) + (float) $row->amount;
        }

        $deliveries = DB::table('other_delivery_items')
            ->join('other_deliveries', 'other_delivery_items.other_delivery_id', '=', 'other_deliveries.id')
            ->join('products', 'other_delivery_items.product_id', '=', 'products.id')
            ->whereBetween('other_deliveries.delivery_date', [$startDate, $endDate])
            ->when($tenantId, function ($query, $tenantId) {
                $query->where('other_delivery_items.tenant_id', $tenantId)
                    ->where('other_deliveries.tenant_id', $tenantId)
                    ->where('products.tenant_id', $tenantId);
            })
            ->when(!empty($godownId) && ErpFeatureSetting::isEnabled('godown_management'), function ($query) use ($godownId) {
                $query->where('other_delivery_items.godown_id', $godownId);
            })
            ->groupBy('products.category_id')
            ->selectRaw('products.category_id as category_id, SUM(other_delivery_items.quantity) as qty')
            ->get();

        foreach ($deliveries as $row) {
            $id = $row->category_id ?? 0;
            $totals[$id]['quantity'] = ($totals[$id]['quantity'] ?? 0) + (float) $row->qty;
        }

        return collect($totals)->map(function ($values, $id) use ($categoryMap) {
            return (object) [
                'category_id' => (int) $id,
                'category_name' => (string) $id === '0'
                    ? 'Uncategorized'
                    : (optional($categoryMap->get((int) $id))->name ?? 'Uncategorized'),
                'total_quantity' => (float) ($values['quantity'] ?? 0),
                'total_amount' => (float) ($values['amount'] ?? 0),
            ];
        })->sortByDesc('total_quantity')->values();
    }

    private function getDefaultCategoryId(string $startDate, string $endDate, ?int $godownId = null): ?int
    {
        $categories = Category::orderBy('name')->get(['id', 'name']);
        $categoryTotals = $this->getCategoryMovementTotals($startDate, $endDate, $categories, $godownId);

        return optional($categoryTotals->first())->category_id;
    }
    
    /**
     * Process and summarize sales data by product
     */
    private function getSalesByProduct($items)
    {
        return $items->groupBy('product_id')->map(function($group) {
            $product = $group->first()->product;
            return [
                'product' => $product,
                'quantity' => $group->sum('quantity'),
                'amount' => $group->sum('total'),
                'invoices' => $group->pluck('invoice_id')->unique()->count(),
                'customers' => $group->pluck('invoice.customer_id')->unique()->count(),
                'average_price' => $group->sum('total') / $group->sum('quantity')
            ];
        });
    }
    
    /**
     * Process and summarize return data by product
     */
    private function getReturnsByProduct($items)
    {
        return $items->groupBy('product_id')->map(function($group) {
            $product = $group->first()->product;
            return [
                'product' => $product,
                'quantity' => $group->sum('quantity'),
                'amount' => $group->sum('total'),
                'returns' => $group->pluck('return_id')->unique()->count(),
                'customers' => $group->pluck('productReturn.customer_id')->unique()->count()
            ];
        });
    }
    
    /**
     * Process and summarize delivery data by product
     */
    private function getDeliveriesByProduct($items)
    {
        return $items->groupBy('product_id')->map(function($group) {
            $product = $group->first()->product;
            return [
                'product' => $product,
                'quantity' => $group->sum('quantity'),
                'deliveries' => $group->pluck('other_delivery_id')->unique()->count(),
                'status' => $this->summarizeDeliveryStatus($group)
            ];
        });
    }
    
    /**
     * Helper to summarize delivery statuses
     */
    private function summarizeDeliveryStatus($deliveryItems)
    {
        $pending = 0;
        $delivered = 0;
        $cancelled = 0;
        
        foreach ($deliveryItems as $item) {
            switch ($item->otherDelivery->status) {
                case 'pending':
                    $pending += $item->quantity;
                    break;
                case 'delivered':
                    $delivered += $item->quantity;
                    break;
                case 'cancelled':
                    $cancelled += $item->quantity;
                    break;
            }
        }
        
        return [
            'pending' => $pending,
            'delivered' => $delivered,
            'cancelled' => $cancelled
        ];
    }
    
    /**
     * Get consolidated data using optimized batch queries (4 queries total instead of N*13)
     */
    private function getConsolidatedDataOptimized($products, $filters)
    {
        $productIds = $products->pluck('id')->toArray();
        $godownId = $filters['godown_id'] ?? null;
        $tenantId = TenantContext::currentId();

        if (empty($productIds)) {
            return [];
        }

        // Batch query for sales - single query for all products
        $salesData = DB::table('invoice_items')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->whereIn('invoice_items.product_id', $productIds)
            ->whereBetween('invoices.invoice_date', [$filters['start_date'], $filters['end_date']])
            ->when($tenantId, function ($query, $tenantId) {
                $query->where('invoice_items.tenant_id', $tenantId)
                    ->where('invoices.tenant_id', $tenantId);
            })
            ->groupBy('invoice_items.product_id')
            ->select('invoice_items.product_id', DB::raw('SUM(invoice_items.quantity) as total'))
            ->pluck('total', 'product_id');

        // Batch query for returns - single query for all products
        $returnsData = DB::table('product_return_items')
            ->join('product_returns', 'product_return_items.return_id', '=', 'product_returns.id')
            ->whereIn('product_return_items.product_id', $productIds)
            ->whereBetween('product_returns.return_date', [$filters['start_date'], $filters['end_date']])
            ->when($tenantId, function ($query, $tenantId) {
                $query->where('product_return_items.tenant_id', $tenantId)
                    ->where('product_returns.tenant_id', $tenantId);
            })
            ->when(!empty($godownId) && ErpFeatureSetting::isEnabled('godown_management'), function ($query) use ($godownId) {
                $query->where('product_return_items.godown_id', $godownId);
            })
            ->groupBy('product_return_items.product_id')
            ->select('product_return_items.product_id', DB::raw('SUM(product_return_items.quantity) as total'))
            ->pluck('total', 'product_id');

        // Batch query for purchases - single query for all products
        $purchasesData = DB::table('purchase_items')
            ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
            ->whereIn('purchase_items.product_id', $productIds)
            ->whereBetween('purchases.purchase_date', [$filters['start_date'], $filters['end_date']])
            ->when($tenantId, function ($query, $tenantId) {
                $query->where('purchase_items.tenant_id', $tenantId)
                    ->where('purchases.tenant_id', $tenantId);
            })
            ->when(!empty($godownId) && ErpFeatureSetting::isEnabled('godown_management'), function ($query) use ($godownId) {
                $query->where('purchase_items.godown_id', $godownId);
            })
            ->groupBy('purchase_items.product_id')
            ->select('purchase_items.product_id', DB::raw('SUM(purchase_items.quantity) as total'))
            ->pluck('total', 'product_id');

        // Batch query for other deliveries - single query for all products
        $deliveriesData = DB::table('other_delivery_items')
            ->join('other_deliveries', 'other_delivery_items.other_delivery_id', '=', 'other_deliveries.id')
            ->whereIn('other_delivery_items.product_id', $productIds)
            ->whereBetween('other_deliveries.delivery_date', [$filters['start_date'], $filters['end_date']])
            ->when($tenantId, function ($query, $tenantId) {
                $query->where('other_delivery_items.tenant_id', $tenantId)
                    ->where('other_deliveries.tenant_id', $tenantId);
            })
            ->when(!empty($godownId) && ErpFeatureSetting::isEnabled('godown_management'), function ($query) use ($godownId) {
                $query->where('other_delivery_items.godown_id', $godownId);
            })
            ->groupBy('other_delivery_items.product_id')
            ->select('other_delivery_items.product_id', DB::raw('SUM(other_delivery_items.quantity) as total'))
            ->pluck('total', 'product_id');

        // Build result using the batch data
        $result = [];
        foreach ($products as $product) {
            $sales = $salesData[$product->id] ?? 0;
            $returns = $returnsData[$product->id] ?? 0;
            $purchases = $purchasesData[$product->id] ?? 0;
            $otherDeliveries = $deliveriesData[$product->id] ?? 0;

            $result[] = [
                'product' => $product,
                'sales' => $sales,
                'returns' => $returns,
                'purchases' => $purchases,
                'other_deliveries' => $otherDeliveries,
                'net_change' => $purchases - $sales - $otherDeliveries + $returns,
            ];
        }

        return $result;
    }

    /**
     * Get consolidated data for all product movements (legacy - kept for reference)
     */
    private function getConsolidatedData($products, $filters)
    {
        $result = [];
        $tenantId = TenantContext::currentId();

        foreach ($products as $product) {
            // Get sales data
            $sales = InvoiceItem::where('product_id', $product->id)
                ->whereHas('invoice', function($q) use ($filters) {
                    $q->whereBetween('invoice_date', [$filters['start_date'], $filters['end_date']]);
                })
                ->sum('quantity');

            // Get returns data
            $returns = ProductReturnItem::where('product_id', $product->id)
                ->whereHas('productReturn', function($q) use ($filters) {
                    $q->whereBetween('return_date', [$filters['start_date'], $filters['end_date']]);
                })
                ->sum('quantity');

            // Get purchase data using DB query builder
            $purchases = DB::table('purchases')
                ->join('purchase_items', 'purchases.id', '=', 'purchase_items.purchase_id')
                ->where('purchase_items.product_id', $product->id)
                ->whereBetween('purchases.purchase_date', [$filters['start_date'], $filters['end_date']])
                ->when($tenantId, function ($query, $tenantId) {
                    $query->where('purchases.tenant_id', $tenantId)
                        ->where('purchase_items.tenant_id', $tenantId);
                })
                ->sum('purchase_items.quantity');

            // Get other deliveries data
            $otherDeliveries = OtherDeliveryItem::where('product_id', $product->id)
                ->whereHas('otherDelivery', function($q) use ($filters) {
                    $q->whereBetween('delivery_date', [$filters['start_date'], $filters['end_date']]);
                })
                ->sum('quantity');

            // Calculate opening and closing stock
            $stockMovements = $this->calculateStockMovements($product->id, $filters);

            $result[] = [
                'product' => $product,
                'sales' => $sales,
                'returns' => $returns,
                'purchases' => $purchases,
                'other_deliveries' => $otherDeliveries,
                'net_change' => $purchases - $sales - $otherDeliveries + $returns,
                'opening_stock' => $stockMovements['opening_stock'],
                'closing_stock' => $stockMovements['closing_stock']
            ];
        }

        return $result;
    }
    
    /**
     * Calculate opening and closing stock for a product in the given period
     */
    private function calculateStockMovements($productId, $filters)
    {
        // Current stock represents closing stock
        $product = Product::find($productId);
        $closingStock = $product->current_stock;
        $tenantId = TenantContext::currentId();
        
        // Calculate all movements after the end date to get to the closing stock
        $salesAfterPeriod = InvoiceItem::where('product_id', $productId)
            ->whereHas('invoice', function($q) use ($filters) {
                $q->where('invoice_date', '>', $filters['end_date']);
            })
            ->sum('quantity');
            
        $returnsAfterPeriod = ProductReturnItem::where('product_id', $productId)
            ->whereHas('productReturn', function($q) use ($filters) {
                $q->where('return_date', '>', $filters['end_date']);
            })
            ->sum('quantity');
            
        $purchasesAfterPeriod = DB::table('purchases')
            ->join('purchase_items', 'purchases.id', '=', 'purchase_items.purchase_id')
            ->where('purchase_items.product_id', $productId)
            ->where('purchases.purchase_date', '>', $filters['end_date'])
            ->when($tenantId, function ($query, $tenantId) {
                $query->where('purchases.tenant_id', $tenantId)
                    ->where('purchase_items.tenant_id', $tenantId);
            })
            ->sum('purchase_items.quantity');
            
        $otherDeliveriesAfterPeriod = OtherDeliveryItem::where('product_id', $productId)
            ->whereHas('otherDelivery', function($q) use ($filters) {
                $q->where('delivery_date', '>', $filters['end_date']);
            })
            ->sum('quantity');
            
        // Calculate stock at end of period by removing movements after period end
        $stockAtPeriodEnd = $closingStock - $purchasesAfterPeriod + $salesAfterPeriod + $otherDeliveriesAfterPeriod - $returnsAfterPeriod;
            
        // Calculate movements within the period
        $salesInPeriod = InvoiceItem::where('product_id', $productId)
            ->whereHas('invoice', function($q) use ($filters) {
                $q->whereBetween('invoice_date', [$filters['start_date'], $filters['end_date']]);
            })
            ->sum('quantity');
            
        $returnsInPeriod = ProductReturnItem::where('product_id', $productId)
            ->whereHas('productReturn', function($q) use ($filters) {
                $q->whereBetween('return_date', [$filters['start_date'], $filters['end_date']]);
            })
            ->sum('quantity');
            
        $purchasesInPeriod = DB::table('purchases')
            ->join('purchase_items', 'purchases.id', '=', 'purchase_items.purchase_id')
            ->where('purchase_items.product_id', $productId)
            ->whereBetween('purchases.purchase_date', [$filters['start_date'], $filters['end_date']])
            ->when($tenantId, function ($query, $tenantId) {
                $query->where('purchases.tenant_id', $tenantId)
                    ->where('purchase_items.tenant_id', $tenantId);
            })
            ->sum('purchase_items.quantity');
            
        $otherDeliveriesInPeriod = OtherDeliveryItem::where('product_id', $productId)
            ->whereHas('otherDelivery', function($q) use ($filters) {
                $q->whereBetween('delivery_date', [$filters['start_date'], $filters['end_date']]);
            })
            ->sum('quantity');
            
        // Calculate opening stock by removing movements during period
        $openingStock = $stockAtPeriodEnd - $purchasesInPeriod + $salesInPeriod + $otherDeliveriesInPeriod - $returnsInPeriod;
        
        return [
            'opening_stock' => max(0, $openingStock),  // Prevent negative opening stock
            'closing_stock' => $stockAtPeriodEnd
        ];
    }
}
