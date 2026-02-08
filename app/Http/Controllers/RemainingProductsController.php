<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Challan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Support\TenantContext;

class RemainingProductsController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:remaining-products-view', ['only' => ['index', 'getUndeliveredItemsData']]);
    }

    public function index()
    {
        $customers = Customer::orderBy('name')->get();
        $companies = Company::orderBy('name')->get();

        // Get summary statistics
        $stats = $this->getStatistics();

        return view('sales.remaining_products', compact('customers', 'companies', 'stats'));
    }

    private function getStatistics($customerId = null, $companyId = null)
    {
        $tenantId = TenantContext::currentId();
        $deliveredSubquery = '(SELECT invoice_item_id, SUM(quantity) as delivered_qty
                                FROM challan_items'
            . ($tenantId ? ' WHERE tenant_id = ' . (int) $tenantId : '')
            . ' GROUP BY invoice_item_id) as delivered';

        $query = DB::table('invoice_items')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->join('customers', 'invoices.customer_id', '=', 'customers.id')
            ->join('products', 'invoice_items.product_id', '=', 'products.id')
            ->leftJoin('companies', 'products.company_id', '=', 'companies.id')
            ->leftJoin(DB::raw($deliveredSubquery),
                    'invoice_items.id', '=', 'delivered.invoice_item_id')
            ->where('invoices.delivery_status', '!=', 'delivered')
            ->whereRaw('invoice_items.quantity > COALESCE(delivered.delivered_qty, 0)')
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

        if ($customerId) {
            $query->where('customers.id', $customerId);
        }
        if ($companyId) {
            $query->where('products.company_id', $companyId);
        }

        $totalItems = $query->count();
        $totalCustomers = (clone $query)->distinct()->count('customers.id');
        $totalQuantity = $query->selectRaw('SUM(invoice_items.quantity - COALESCE(delivered.delivered_qty, 0)) as total')
                              ->value('total') ?? 0;

        return [
            'total_items' => $totalItems,
            'total_customers' => $totalCustomers,
            'total_quantity' => number_format($totalQuantity, 2)
        ];
    }

    public function getUndeliveredItemsData(Request $request)
    {
        $draw = $request->get('draw');
        $start = $request->get('start', 0);
        $length = $request->get('length', 25);
        $searchValue = $request->get('search')['value'] ?? '';
        $tenantId = TenantContext::currentId();
        $deliveredSubquery = '(SELECT invoice_item_id, SUM(quantity) as delivered_qty
                                FROM challan_items'
            . ($tenantId ? ' WHERE tenant_id = ' . (int) $tenantId : '')
            . ' GROUP BY invoice_item_id) as delivered';

        // Base query for undelivered invoice items
        $query = DB::table('invoice_items')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->join('customers', 'invoices.customer_id', '=', 'customers.id')
            ->join('products', 'invoice_items.product_id', '=', 'products.id')
            ->leftJoin('companies', 'products.company_id', '=', 'companies.id')
            ->leftJoin(DB::raw($deliveredSubquery),
                    'invoice_items.id', '=', 'delivered.invoice_item_id')
            ->select(
                'invoice_items.id',
                'invoice_items.description',
                'invoice_items.quantity as ordered_quantity',
                'invoice_items.unit_price',
                DB::raw('COALESCE(delivered.delivered_qty, 0) as delivered_quantity'),
                DB::raw('invoice_items.quantity - COALESCE(delivered.delivered_qty, 0) as remaining_quantity'),
                DB::raw('(invoice_items.quantity - COALESCE(delivered.delivered_qty, 0)) * invoice_items.unit_price as remaining_value'),
                'invoices.id as invoice_id',
                'invoices.invoice_number',
                'invoices.invoice_date',
                'customers.id as customer_id',
                'customers.name as customer_name',
                'customers.phone as customer_phone',
                'products.id as product_id',
                'products.name as product_name',
                'companies.id as company_id',
                'companies.name as company_name'
            )
            ->where('invoices.delivery_status', '!=', 'delivered')
            ->whereRaw('invoice_items.quantity > COALESCE(delivered.delivered_qty, 0)')
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

        // Apply customer filter
        if ($request->filled('customer_id')) {
            $query->where('customers.id', $request->customer_id);
        }

        // Apply company filter
        if ($request->filled('company_id')) {
            $query->where('products.company_id', $request->company_id);
        }

        // Apply search
        if (!empty($searchValue)) {
            $query->where(function($q) use ($searchValue) {
                $q->where('customers.name', 'like', "%{$searchValue}%")
                  ->orWhere('products.name', 'like', "%{$searchValue}%")
                  ->orWhere('companies.name', 'like', "%{$searchValue}%")
                  ->orWhere('invoices.invoice_number', 'like', "%{$searchValue}%")
                  ->orWhere('invoice_items.description', 'like', "%{$searchValue}%");
            });
        }

        // Count before pagination
        $recordsTotal = (clone $query)->count();
        $recordsFiltered = $recordsTotal;

        // Apply ordering
        $orderColumn = (int) ($request->get('order')[0]['column'] ?? 0);
        $orderDir = strtolower($request->get('order')[0]['dir'] ?? 'asc');
        $orderDir = $orderDir === 'desc' ? 'desc' : 'asc';
        $columns = ['customer_name', 'company_name', 'product_name', 'invoice_number', 'invoice_date', 'ordered_quantity', 'delivered_quantity', 'remaining_quantity', 'actions'];
        $sortColumn = $columns[$orderColumn] ?? 'customer_name';

        if ($sortColumn !== 'actions') {
            $query->orderBy($sortColumn, $orderDir);
        }

        // Apply pagination
        $data = $query->skip($start)->take($length)->get();

        // Format data
        $data = $data->map(function($item) {
            return [
                'customer_name' => '<div class="customer-info">
                    <strong>' . e($item->customer_name) . '</strong>
                    <small class="text-muted d-block">' . e($item->customer_phone ?? '') . '</small>
                </div>',
                'company_name' => '<span class="badge badge-primary">' . e($item->company_name ?? 'N/A') . '</span>',
                'product_name' => '<div class="product-info">
                    <strong>' . e($item->product_name) . '</strong>
                    <small class="text-muted d-block">' . e($item->description ?? '') . '</small>
                </div>',
                'invoice_number' => '<a href="' . route('invoices.show', $item->invoice_id) . '" class="text-primary">' . e($item->invoice_number) . '</a>
                    <small class="text-muted d-block">' . Carbon::parse($item->invoice_date)->format('d M Y') . '</small>',
                'invoice_date' => Carbon::parse($item->invoice_date)->format('d-m-Y'),
                'ordered_quantity' => '<span class="badge badge-secondary">' . number_format($item->ordered_quantity, 2) . '</span>',
                'delivered_quantity' => '<span class="badge badge-info">' . number_format($item->delivered_quantity, 2) . '</span>',
                'remaining_quantity' => '<span class="badge badge-danger">' . number_format($item->remaining_quantity, 2) . '</span>',
                'actions' => '<div class="btn-group btn-group-sm">
                    <a href="' . route('invoices.show', $item->invoice_id) . '" class="btn btn-outline-info" title="View Invoice">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="' . route('challans.create', ['invoice_id' => $item->invoice_id]) . '" class="btn btn-outline-success" title="Create Delivery">
                        <i class="fas fa-truck"></i>
                    </a>
                </div>'
            ];
        });

        return response()->json([
            'draw' => intval($draw),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data
        ]);
    }
}
