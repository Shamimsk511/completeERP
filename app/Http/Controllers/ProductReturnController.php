<?php

namespace App\Http\Controllers;

use App\Models\ProductReturn;
use App\Models\ProductReturnItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ErpFeatureSetting;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\TransactionSmsService;
use App\Services\GodownStockService;
use App\Services\PaymentAllocationService;
use App\Services\Accounting\AutoPostingService;
use App\Support\TenantContext;

class ProductReturnController extends Controller
{
    protected $transactionSmsService;
    protected PaymentAllocationService $paymentAllocationService;

    function __construct(TransactionSmsService $transactionSmsService, PaymentAllocationService $paymentAllocationService)
    {
        $this->transactionSmsService = $transactionSmsService;
        $this->paymentAllocationService = $paymentAllocationService;

        $this->middleware('permission:return-list|return-create|return-edit|return-delete', ['only' => ['index', 'show', 'getInvoicesByCustomer', 'getInvoiceItems']]);
        $this->middleware('permission:return-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:return-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:return-delete', ['only' => ['destroy']]);
        $this->middleware('permission:return-print', ['only' => ['print']]);
    }

    public function index()
    {
        return view('returns.index');
    }

    /**
     * Get returns data for DataTables (server-side processing)
     */
    public function data(Request $request)
    {
        $query = ProductReturn::with('customer');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('return_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('return_date', '<=', $request->to_date);
        }

        // Handle DataTables parameters
        $totalRecords = ProductReturn::count();
        $filteredRecords = $query->count();

        // Ordering
        $orderColumn = (int) $request->input('order.0.column', 1);
        $orderDir = strtolower($request->input('order.0.dir', 'desc'));
        $orderDir = $orderDir === 'asc' ? 'asc' : 'desc';

        $columns = ['id', 'return_number', 'customer_id', 'return_date', 'total', 'status', 'id'];
        $orderBy = $columns[$orderColumn] ?? 'return_date';

        $query->orderBy($orderBy, $orderDir);

        // Search
        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('return_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
            $filteredRecords = $query->count();
        }

        // Pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 15);

        $returns = $query->skip($start)->take($length)->get();

        // Format data for DataTables
        $data = $returns->map(function ($return) {
            return [
                'id' => $return->id,
                'return_number' => $return->return_number,
                'customer' => $return->customer ? [
                    'name' => $return->customer->name,
                    'phone' => $return->customer->phone
                ] : null,
                'customer_id' => $return->customer_id,
                'return_date' => $return->return_date->format('d M, Y'),
                'total' => $return->total,
                'status' => $return->status ?? 'pending',
            ];
        });

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data
        ]);
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $products = Product::with(['company', 'category'])->get();
        $return_number = ProductReturn::getNextReturnNumber();
        $return_date = Carbon::now()->format('Y-m-d');

        $cashBankAccounts = AutoPostingService::getCashBankAccounts();
        
        return view('returns.create', compact('customers', 'products', 'return_number', 'return_date', 'cashBankAccounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'return_number' => ['required', 'string', $this->tenantUniqueRule('product_returns', 'return_number')],
            'return_date' => 'required|date',
            'subtotal' => 'required|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'refund_amount' => 'nullable|numeric|min:0',
            'refund_account_id' => 'nullable|exists:accounts,id',
            'notes' => 'nullable|string',
            'product_id' => 'required|array',
            'product_id.*' => 'exists:products,id',
            'description' => 'required|array',
            'quantity' => 'required|array',
            'quantity.*' => 'numeric|min:0.01',
            'boxes' => 'required|array',
            'pieces' => 'required|array',
            'unit_price' => 'required|array',
            'unit_price.*' => 'numeric|min:0',
            'item_total' => 'required|array',
            'item_total.*' => 'numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Create product return record
            $productReturn = ProductReturn::create([
                'return_number' => $request->return_number,
                'customer_id' => $request->customer_id,
                'invoice_id' => $request->invoice_id,
                'return_date' => $request->return_date,
                'subtotal' => $request->subtotal,
                'tax' => $request->tax ?? 0,
                'total' => $request->total,
                'payment_method' => $request->payment_method,
                'status' => 'completed',
                'notes' => $request->notes,
            ]);

            // Create return items and update product stock
            for ($i = 0; $i < count($request->product_id); $i++) {
                $productId = $request->product_id[$i];
                $quantity = $request->quantity[$i];
                $product = Product::findOrFail($productId);
                $godownId = null;
                if (ErpFeatureSetting::isEnabled('godown_management')) {
                    $godownId = GodownStockService::resolveGodownId(null, $product);
                }
                
                // Create return item
                ProductReturnItem::create([
                    'return_id' => $productReturn->id,
                    'product_id' => $productId,
                    'godown_id' => $godownId,
                    'description' => $request->description[$i],
                    'quantity' => $quantity,
                    'boxes' => $request->boxes[$i],
                    'pieces' => $request->pieces[$i],
                    'unit_price' => $request->unit_price[$i],
                    'total' => $request->item_total[$i],
                    'invoice_item_id' => $request->invoice_item_id[$i] ?? null,
                ]);

                // Update product stock (increment stock for returned items)
                $product->current_stock += $quantity;
                $product->save();

                if (ErpFeatureSetting::isEnabled('godown_management') && $godownId) {
                    GodownStockService::adjustStock($product->id, $godownId, $quantity);
                }
            }

            // Create transaction for the return amount (debit type = decreasing outstanding balance)
            $customer = Customer::findOrFail($request->customer_id);
            
            Transaction::create([
                'customer_id' => $customer->id,
                'return_id' => $productReturn->id,
                'invoice_id' => $request->invoice_id,
                'type' => 'debit', // Debit because we're reducing what customer owes
                'purpose' => 'Return #' . $productReturn->return_number,
                'method' => $request->payment_method,
                'amount' => $request->total,
                'note' => 'Product return',
                'reference' => $productReturn->return_number,
            ]);

            $refundAmount = (float) ($request->refund_amount ?? 0);
            if ($refundAmount > 0) {
                $outstandingBefore = (float) ($customer->outstanding_balance ?? 0);
                $maxRefundable = max(0, $request->total - $outstandingBefore);
                if ($refundAmount > $maxRefundable + 0.01) {
                    throw new \Exception("Refund amount exceeds allowed limit (max {$maxRefundable}).");
                }

                if (!$request->refund_account_id) {
                    throw new \Exception('Refund account is required when refund amount is provided.');
                }

                Transaction::create([
                    'customer_id' => $customer->id,
                    'return_id' => $productReturn->id,
                    'type' => 'credit', // Credit to offset negative balance created by return
                    'purpose' => 'Return Payment #' . $productReturn->return_number,
                    'method' => $request->payment_method,
                    'account_id' => $request->refund_account_id,
                    'amount' => $refundAmount,
                    'note' => 'Return payment to customer',
                    'reference' => $productReturn->return_number,
                    'discount_amount' => 0,
                    'discount_reason' => null,
                ]);
            }
            $this->paymentAllocationService->allocatePayments($customer->id);

            // Send SMS notification for product return - ADD THIS BLOCK
            try {
                $productReturn->load('customer'); // Load customer relationship
                $this->transactionSmsService->sendProductReturnSms($productReturn);
            } catch (\Exception $e) {
                // Log SMS error but don't fail the return process
                \Log::warning("SMS failed for product return {$productReturn->id}: " . $e->getMessage());
            }

            DB::commit();

            return redirect()->route('returns.show', $productReturn)
                ->with('success', 'Product return created successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Product return creation failed: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(ProductReturn $return)
    {
        $return->load(['customer', 'invoice', 'items.product.category', 'items.product.company']);

        if (request()->ajax() || request()->wantsJson()) {
            $returnData = $return->toArray();
            $returnData['items'] = $return->items->map(function ($item) {
                $itemData = $item->toArray();
                $itemData['product'] = $item->product;
                return $itemData;
            })->toArray();

            return response()->json([
                'return' => $returnData,
            ]);
        }

        return view('returns.show', compact('return'));
    }

    public function edit(ProductReturn $return)
    {
        $customers = Customer::orderBy('name')->get();
        $products = Product::with(['company', 'category'])->get();
        $return->load(['customer', 'items.product.category', 'items.product.company']);

        $cashBankAccounts = AutoPostingService::getCashBankAccounts();

        $refundAmount = (float) Transaction::where('return_id', $return->id)
            ->where('type', 'credit')
            ->sum('amount');

        $refundAccountId = Transaction::where('return_id', $return->id)
            ->where('type', 'credit')
            ->value('account_id');
        
        return view('returns.edit', compact('return', 'customers', 'products', 'cashBankAccounts', 'refundAmount', 'refundAccountId'));
    }

    public function update(Request $request, ProductReturn $return)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'return_date' => 'required|date',
            'subtotal' => 'required|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'refund_amount' => 'nullable|numeric|min:0',
            'refund_account_id' => 'nullable|exists:accounts,id',
            'notes' => 'nullable|string',
            'product_id' => 'required|array',
            'product_id.*' => 'exists:products,id',
            'description' => 'required|array',
            'quantity' => 'required|array',
            'quantity.*' => 'numeric|min:0.01',
            'boxes' => 'required|array',
            'pieces' => 'required|array',
            'unit_price' => 'required|array',
            'unit_price.*' => 'numeric|min:0',
            'item_total' => 'required|array',
            'item_total.*' => 'numeric|min:0',
            
        ]);

        DB::beginTransaction();
        try {
            $oldCustomerId = $return->customer_id;
            $oldTotal = $return->total;
            $existingItems = ProductReturnItem::withoutGlobalScopes()
                ->where('return_id', $return->id)
                ->get();

            // Reverse stock changes for old return items
            foreach ($existingItems as $item) {
                $product = Product::findOrFail($item->product_id);
                $product->current_stock -= $item->quantity;
                $product->save();

                if (ErpFeatureSetting::isEnabled('godown_management')) {
                    $resolvedGodownId = GodownStockService::resolveGodownId($item->godown_id, $product);
                    if ($resolvedGodownId) {
                        GodownStockService::adjustStock($product->id, $resolvedGodownId, -$item->quantity);
                    }
                }
            }

            // Update return record
            $return->update([
                'customer_id' => $request->customer_id,
                'return_date' => $request->return_date,
                'subtotal' => $request->subtotal,
                'tax' => $request->tax ?? 0,
                'total' => $request->total,
                'payment_method' => $request->payment_method,
                'notes' => $request->notes,
            ]);

            // Delete old return items
            ProductReturnItem::withoutGlobalScopes()->where('return_id', $return->id)->delete();

            // Create new return items and update product stock
            for ($i = 0; $i < count($request->product_id); $i++) {
                $productId = $request->product_id[$i];
                $quantity = $request->quantity[$i];
                $product = Product::findOrFail($productId);
                $godownId = null;
                if (ErpFeatureSetting::isEnabled('godown_management')) {
                    $godownId = GodownStockService::resolveGodownId(null, $product);
                }
                
                // Create return item
                ProductReturnItem::create([
                    'return_id' => $return->id,
                    'product_id' => $productId,
                    'godown_id' => $godownId,
                    'description' => $request->description[$i],
                    'quantity' => $quantity,
                    'boxes' => $request->boxes[$i],
                    'pieces' => $request->pieces[$i],
                    'unit_price' => $request->unit_price[$i],
                    'total' => $request->item_total[$i],
                    'invoice_item_id' => $request->invoice_item_id[$i] ?? null,
                ]);

                // Update product stock
                $product->current_stock += $quantity;
                $product->save();

                if (ErpFeatureSetting::isEnabled('godown_management') && $godownId) {
                    GodownStockService::adjustStock($product->id, $godownId, $quantity);
                }
            }

            // Delete old transactions
            Transaction::where('return_id', $return->id)->delete();

            // Create new transaction
            Transaction::create([
                'customer_id' => $request->customer_id,
                'return_id' => $return->id,
                'invoice_id' => $return->invoice_id,
                'type' => 'debit',
                'purpose' => 'Return #' . $return->return_number,
                'method' => $request->payment_method,
                'amount' => $request->total,
                'note' => 'Product return (updated)',
                'reference' => $return->return_number,
            ]);

            $customer = Customer::findOrFail($request->customer_id);
            $refundAmount = (float) ($request->refund_amount ?? 0);
            if ($refundAmount > 0) {
                $outstandingBefore = (float) ($customer->outstanding_balance ?? 0);
                $maxRefundable = max(0, $request->total - $outstandingBefore);
                if ($refundAmount > $maxRefundable + 0.01) {
                    throw new \Exception("Refund amount exceeds allowed limit (max {$maxRefundable}).");
                }

                if (!$request->refund_account_id) {
                    throw new \Exception('Refund account is required when refund amount is provided.');
                }

                Transaction::create([
                    'customer_id' => $customer->id,
                    'return_id' => $return->id,
                    'type' => 'credit',
                    'purpose' => 'Return Payment #' . $return->return_number,
                    'method' => $request->payment_method,
                    'account_id' => $request->refund_account_id,
                    'amount' => $refundAmount,
                    'note' => 'Return payment to customer',
                    'reference' => $return->return_number,
                    'discount_amount' => 0,
                    'discount_reason' => null,
                ]);
            }
            $this->paymentAllocationService->allocatePayments($request->customer_id);
            if ($oldCustomerId !== $request->customer_id) {
                $this->paymentAllocationService->allocatePayments($oldCustomerId);
            }
// Send SMS notification for updated product return - ADD THIS BLOCK
            try {
                $return->load('customer'); // Load customer relationship
                $this->transactionSmsService->sendProductReturnSms($return);
            } catch (\Exception $e) {
                // Log SMS error but don't fail the return process
                \Log::warning("SMS failed for updated product return {$return->id}: " . $e->getMessage());
            }
            DB::commit();

            return redirect()->route('returns.show', $return)
                ->with('success', 'Product return updated successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Product return update failed: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(ProductReturn $return)
    {
        DB::beginTransaction();
        try {
            $existingItems = ProductReturnItem::withoutGlobalScopes()
                ->where('return_id', $return->id)
                ->get();

            // Reverse product stock changes
            foreach ($existingItems as $item) {
                $product = Product::findOrFail($item->product_id);
                $product->current_stock -= $item->quantity;
                $product->save();

                if (ErpFeatureSetting::isEnabled('godown_management')) {
                    $resolvedGodownId = GodownStockService::resolveGodownId($item->godown_id, $product);
                    if ($resolvedGodownId) {
                        GodownStockService::adjustStock($product->id, $resolvedGodownId, -$item->quantity);
                    }
                }
            }

            // Delete related transactions
            Transaction::where('return_id', $return->id)->delete();

            $this->paymentAllocationService->allocatePayments($return->customer_id);

            // Delete return and items (cascade)
            $return->delete();

            DB::commit();

            // Return JSON for AJAX requests
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Product return deleted successfully.'
                ]);
            }

            return redirect()->route('returns.index')
                ->with('success', 'Product return deleted successfully.');
        } catch (\Exception $e) {
            DB::rollback();

            // Return JSON for AJAX requests
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product return deletion failed: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Product return deletion failed: ' . $e->getMessage());
        }
    }

    public function print(ProductReturn $return)
    {
        $return->load(['customer', 'items.product.category', 'items.product.company']);
        $returnTransactions = Transaction::where('return_id', $return->id)
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->get(['id', 'type', 'amount', 'discount_amount', 'created_at']);

        $returnDebitTotal = (float) $returnTransactions
            ->where('type', 'debit')
            ->sum('amount');

        $refundTotal = (float) $returnTransactions
            ->where('type', 'credit')
            ->sum('amount');

        $anchorTransaction = $returnTransactions->first();
        $openingBalance = (float) ($return->customer->opening_balance ?? 0);

        if ($anchorTransaction) {
            $anchorCreatedAt = $anchorTransaction->created_at;
            $anchorId = $anchorTransaction->id;

            $creditsBefore = (float) Transaction::where('customer_id', $return->customer_id)
                ->where('type', 'credit')
                ->where(function ($query) use ($anchorCreatedAt, $anchorId) {
                    $query->where('created_at', '<', $anchorCreatedAt)
                        ->orWhere(function ($subQuery) use ($anchorCreatedAt, $anchorId) {
                            $subQuery->where('created_at', '=', $anchorCreatedAt)
                                ->where('id', '<', $anchorId);
                        });
                })
                ->sum('amount');

            $debitsBefore = (float) Transaction::where('customer_id', $return->customer_id)
                ->where('type', 'debit')
                ->where(function ($query) use ($anchorCreatedAt, $anchorId) {
                    $query->where('created_at', '<', $anchorCreatedAt)
                        ->orWhere(function ($subQuery) use ($anchorCreatedAt, $anchorId) {
                            $subQuery->where('created_at', '=', $anchorCreatedAt)
                                ->where('id', '<', $anchorId);
                        });
                })
                ->selectRaw('COALESCE(SUM(amount + COALESCE(discount_amount, 0)), 0) as total')
                ->value('total');

            $outstandingBeforeReturn = $openingBalance + $creditsBefore - $debitsBefore;
            $outstandingAfterReturn = $outstandingBeforeReturn - $returnDebitTotal + $refundTotal;
        } else {
            $outstandingAfterReturn = (float) ($return->customer->outstanding_balance ?? 0);
            $outstandingBeforeReturn = $outstandingAfterReturn + $returnDebitTotal - $refundTotal;
        }

        return view('returns.print', compact(
            'return',
            'outstandingBeforeReturn',
            'outstandingAfterReturn',
            'returnDebitTotal',
            'refundTotal'
        ));
    }

    /**
     * Approve a pending return
     */
    public function approve(ProductReturn $return)
    {
        try {
            if ($return->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending returns can be approved.'
                ], 400);
            }

            $return->update(['status' => 'approved']);

            return response()->json([
                'success' => true,
                'message' => 'Return approved successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve return: ' . $e->getMessage()
            ], 500);
        }
    }

    // AJAX methods for fetching data
    public function getInvoicesByCustomer($customerId)
    {
        $invoices = Invoice::where('customer_id', $customerId)
            ->orderBy('invoice_date', 'desc')
            ->get(['id', 'invoice_number', 'invoice_date', 'total']);
        
        return response()->json($invoices);
    }

    public function getInvoiceItems($invoiceId)
    {
        $items = InvoiceItem::where('invoice_id', $invoiceId)
            ->with('product')
            ->get();
        
        return response()->json($items);
    }

    public function validateReturn(Request $request)
    {
        $customerId = $request->customer_id;
        $products = $request->products; // Array of [product_id, quantity]
        $tenantId = TenantContext::currentId();
        
        $warnings = [];
        
        foreach ($products as $product) {
            $productId = $product['product_id'];
            $quantity = $product['quantity'];
            
            // Get purchase history
            $purchased = DB::table('invoice_items')
                ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
                ->where('invoices.customer_id', $customerId)
                ->where('invoice_items.product_id', $productId)
                ->when($tenantId, function ($query, $tenantId) {
                    $query->where('invoice_items.tenant_id', $tenantId)
                        ->where('invoices.tenant_id', $tenantId);
                })
                ->sum('invoice_items.quantity');
            
            // Get return history
            $returned = DB::table('product_return_items')
                ->join('product_returns', 'product_return_items.return_id', '=', 'product_returns.id')
                ->where('product_returns.customer_id', $customerId)
                ->where('product_return_items.product_id', $productId)
                ->when($tenantId, function ($query, $tenantId) {
                    $query->where('product_return_items.tenant_id', $tenantId)
                        ->where('product_returns.tenant_id', $tenantId);
                })
                ->sum('product_return_items.quantity');
            
            $available = $purchased - $returned;
            
            if ($purchased <= 0) {
                $warnings[] = [
                    'type' => 'never_purchased',
                    'product_id' => $productId,
                    'message' => 'Customer never purchased this product'
                ];
            } elseif ($quantity > $available) {
                $warnings[] = [
                    'type' => 'excess_quantity',
                    'product_id' => $productId,
                    'requested' => $quantity,
                    'available' => $available,
                    'message' => 'Return quantity exceeds available amount'
                ];
            }
        }
        
        return response()->json([
            'valid' => empty($warnings),
            'warnings' => $warnings
        ]);
    }

    // FIXED: Customer purchase history method
    public function getCustomerPurchaseHistory($customerId)
    {
        try {
            $customer = Customer::findOrFail($customerId, ['id', 'name', 'phone', 'address']);
            
            // Get purchased products from invoices - FIXED QUERY
            $purchasedProducts = InvoiceItem::query()
                ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
                ->join('products', 'invoice_items.product_id', '=', 'products.id')
                ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                ->leftJoin('companies', 'products.company_id', '=', 'companies.id')
                ->where('invoices.customer_id', $customerId)
                ->groupBy('invoice_items.product_id', 'products.name', 'categories.name', 'companies.name')
                ->selectRaw('
                    invoice_items.product_id,
                    products.name as product_name,
                    categories.name as category_name,
                    companies.name as company_name,
                    SUM(invoice_items.quantity) as total_purchased,
                    MAX(invoices.invoice_date) as last_purchase_date,
                    COUNT(DISTINCT invoices.id) as invoice_count
                ')
                ->get();

            // Get returned products - FIXED QUERY
            $returnedProducts = ProductReturnItem::query()
                ->join('product_returns', 'product_return_items.return_id', '=', 'product_returns.id')
                ->where('product_returns.customer_id', $customerId)
                ->groupBy('product_return_items.product_id')
                ->selectRaw('product_return_items.product_id, SUM(product_return_items.quantity) as total_returned')
                ->get()
                ->keyBy('product_id');

            // Build products array with available quantities - FIXED INDEXING
            $products = [];
            foreach ($purchasedProducts as $purchased) {
                $returned = $returnedProducts->get($purchased->product_id);
                $returnedQty = $returned ? $returned->total_returned : 0;
                $available = $purchased->total_purchased - $returnedQty;
                
                // Use product_id as key for JavaScript lookup
                $products[$purchased->product_id] = [
                    'name' => $purchased->product_name,
                    'category' => $purchased->category_name ?? 'N/A',
                    'company' => $purchased->company_name ?? 'N/A',
                    'purchased' => (float) $purchased->total_purchased,
                    'returned' => (float) $returnedQty,
                    'available' => (float) $available,
                    'last_purchase' => Carbon::parse($purchased->last_purchase_date)->format('d M, Y'),
                    'invoice_count' => $purchased->invoice_count
                ];
            }

            return response()->json([
                'success' => true,
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'address' => $customer->address
                ],
                'products' => $products,
                'summary' => [
                    'total_products' => count($products),
                    'total_purchased' => array_sum(array_column($products, 'purchased')),
                    'total_returned' => array_sum(array_column($products, 'returned')),
                    'total_available' => array_sum(array_column($products, 'available'))
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getCustomerPurchaseHistory: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Error loading purchase history: ' . $e->getMessage()
            ], 500);
        }
    }

    // FIXED: Customer details method
    public function getCustomerDetails($id)
    {
        try {
            $customer = Customer::findOrFail($id, ['id', 'name', 'phone', 'address', 'outstanding_balance']);
            return response()->json($customer);
        } catch (\Exception $e) {
            \Log::error('Error in getCustomerDetails: ' . $e->getMessage());
            return response()->json(['error' => 'Customer not found'], 404);
        }
    }

    // FIXED: Product details method
    public function getProductDetails($id)
    {
        try {
            $product = Product::with(['company', 'category'])->findOrFail($id);
            return response()->json($product);
        } catch (\Exception $e) {
            \Log::error('Error in getProductDetails: ' . $e->getMessage());
            return response()->json(['error' => 'Product not found'], 404);
        }
    }
}
