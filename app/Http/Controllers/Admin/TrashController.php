<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Challan;
use App\Models\ChallanItem;
use App\Models\Customer;
use App\Models\ErpFeatureSetting;
use App\Models\Invoice;
use App\Models\OtherDelivery;
use App\Models\OtherDeliveryItem;
use App\Models\Product;
use App\Models\Transaction;
use App\Services\GodownStockService;
use App\Services\PaymentAllocationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TrashController extends Controller
{
    protected PaymentAllocationService $paymentAllocationService;

    /**
     * @var array<string, string>
     */
    protected array $typeLabels = [
        'invoices' => 'Invoices',
        'transactions' => 'Transactions',
        'customers' => 'Customers',
        'challans' => 'Challans',
        'products' => 'Products',
        'other-deliveries' => 'Other Deliveries',
    ];

    public function __construct(PaymentAllocationService $paymentAllocationService)
    {
        $this->paymentAllocationService = $paymentAllocationService;

        $this->middleware('role:Admin|Super Admin');
        $this->middleware('permission:trash-view', ['only' => ['index']]);
        $this->middleware('permission:trash-restore', ['only' => ['restore']]);
        $this->middleware('permission:trash-force-delete', ['only' => ['forceDelete']]);
    }

    public function index(Request $request): View
    {
        $type = $this->normalizeType($request->query('type'));
        if (!isset($this->typeLabels[$type])) {
            $type = 'invoices';
        }

        $records = $this->getQueryForType($type)
            ->orderByDesc('deleted_at')
            ->paginate(25)
            ->appends(['type' => $type]);

        return view('admin.trash.index', [
            'type' => $type,
            'types' => $this->typeLabels,
            'records' => $records,
        ]);
    }

    public function restore(Request $request, string $type, int $id): RedirectResponse
    {
        $type = $this->normalizeType($type);
        if (!isset($this->typeLabels[$type])) {
            return back()->with('error', 'Invalid trash type.');
        }

        $restoredInvoiceId = null;
        DB::beginTransaction();
        try {
            switch ($type) {
                case 'transactions':
                    /** @var Transaction $record */
                    $record = Transaction::withTrashed()->findOrFail($id);
                    $this->restoreTransaction($record);
                    break;
                case 'customers':
                    /** @var Customer $record */
                    $record = Customer::withTrashed()->findOrFail($id);
                    $this->ensureIsTrashed($record);
                    $record->restore();
                    break;
                case 'challans':
                    /** @var Challan $record */
                    $record = Challan::withTrashed()->findOrFail($id);
                    $this->restoreChallan($record);
                    break;
                case 'invoices':
                    /** @var Invoice $record */
                    $record = Invoice::withTrashed()->findOrFail($id);
                    $this->restoreInvoice($record);
                    $restoredInvoiceId = $record->id;
                    break;
                case 'products':
                    /** @var Product $record */
                    $record = Product::withTrashed()->findOrFail($id);
                    $this->ensureIsTrashed($record);
                    $record->restore();
                    break;
                case 'other-deliveries':
                    /** @var OtherDelivery $record */
                    $record = OtherDelivery::withTrashed()->findOrFail($id);
                    $this->restoreOtherDelivery($record);
                    break;
                default:
                    throw new \InvalidArgumentException('Unsupported trash type.');
            }

            DB::commit();
            if ($type === 'invoices' && $restoredInvoiceId) {
                return redirect()
                    ->route('invoices.show', $restoredInvoiceId)
                    ->with('success', 'Invoice restored successfully.');
            }

            return back()->with('success', $this->typeLabels[$type] . ' record restored successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Restore failed: ' . $e->getMessage());
        }
    }

    public function forceDelete(Request $request, string $type, int $id): RedirectResponse
    {
        $type = $this->normalizeType($type);
        if (!isset($this->typeLabels[$type])) {
            return back()->with('error', 'Invalid trash type.');
        }

        DB::beginTransaction();
        try {
            switch ($type) {
                case 'transactions':
                    /** @var Transaction $record */
                    $record = Transaction::withTrashed()->findOrFail($id);
                    $this->forceDeleteTransaction($record);
                    break;
                case 'customers':
                    /** @var Customer $record */
                    $record = Customer::withTrashed()->findOrFail($id);
                    $this->forceDeleteCustomer($record);
                    break;
                case 'challans':
                    /** @var Challan $record */
                    $record = Challan::withTrashed()->findOrFail($id);
                    $this->ensureIsTrashed($record);
                    $record->forceDelete();
                    break;
                case 'invoices':
                    /** @var Invoice $record */
                    $record = Invoice::withTrashed()->findOrFail($id);
                    $this->forceDeleteInvoice($record);
                    break;
                case 'products':
                    /** @var Product $record */
                    $record = Product::withTrashed()->findOrFail($id);
                    $this->ensureIsTrashed($record);
                    $record->forceDelete();
                    break;
                case 'other-deliveries':
                    /** @var OtherDelivery $record */
                    $record = OtherDelivery::withTrashed()->findOrFail($id);
                    $this->ensureIsTrashed($record);
                    $record->forceDelete();
                    break;
                default:
                    throw new \InvalidArgumentException('Unsupported trash type.');
            }

            DB::commit();
            return back()->with('success', 'Record permanently deleted.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Permanent deletion failed: ' . $e->getMessage());
        }
    }

    protected function restoreTransaction(Transaction $transaction): void
    {
        $this->ensureIsTrashed($transaction);
        $transaction->restore();

        if ($transaction->customer_id) {
            $this->paymentAllocationService->allocatePayments($transaction->customer_id);
        }
    }

    protected function restoreInvoice(Invoice $invoice): void
    {
        $this->ensureIsTrashed($invoice);
        $invoice->restore();

        Transaction::onlyTrashed()
            ->where('invoice_id', $invoice->id)
            ->restore();

        $this->paymentAllocationService->allocatePayments($invoice->customer_id);
    }

    protected function restoreChallan(Challan $challan): void
    {
        $this->ensureIsTrashed($challan);
        $challan->restore();

        $items = ChallanItem::withoutGlobalScopes()
            ->where('challan_id', $challan->id)
            ->get();

        foreach ($items as $item) {
            $product = Product::withTrashed()->find($item->product_id);
            if (!$product) {
                continue;
            }

            $quantity = (float) $item->quantity;
            $product->current_stock -= $quantity;
            $product->save();

            if (ErpFeatureSetting::isEnabled('godown_management')) {
                $resolvedGodownId = GodownStockService::resolveGodownId($item->godown_id, $product);
                if ($resolvedGodownId) {
                    GodownStockService::adjustStock($product->id, $resolvedGodownId, -$quantity);
                }
            }
        }

        $invoice = Invoice::withTrashed()->find($challan->invoice_id);
        if ($invoice) {
            $this->updateInvoiceDeliveryStatus($invoice);
        }
    }

    protected function restoreOtherDelivery(OtherDelivery $otherDelivery): void
    {
        $this->ensureIsTrashed($otherDelivery);
        $otherDelivery->restore();

        $items = OtherDeliveryItem::withoutGlobalScopes()
            ->where('other_delivery_id', $otherDelivery->id)
            ->get();

        foreach ($items as $item) {
            $product = Product::withTrashed()->find($item->product_id);
            if (!$product) {
                continue;
            }

            $quantity = (float) $item->quantity;
            $product->current_stock -= $quantity;
            $product->save();

            if (ErpFeatureSetting::isEnabled('godown_management')) {
                $resolvedGodownId = GodownStockService::resolveGodownId($item->godown_id, $product);
                if ($resolvedGodownId) {
                    GodownStockService::adjustStock($product->id, $resolvedGodownId, -$quantity);
                }
            }
        }
    }

    protected function forceDeleteTransaction(Transaction $transaction): void
    {
        $this->ensureIsTrashed($transaction);
        $customerId = $transaction->customer_id;
        $transaction->forceDelete();

        if ($customerId) {
            $this->paymentAllocationService->allocatePayments($customerId);
        }
    }

    protected function forceDeleteInvoice(Invoice $invoice): void
    {
        $this->ensureIsTrashed($invoice);
        $customerId = $invoice->customer_id;

        Transaction::onlyTrashed()
            ->where('invoice_id', $invoice->id)
            ->forceDelete();

        $invoice->forceDelete();
        $this->paymentAllocationService->allocatePayments($customerId);
    }

    protected function forceDeleteCustomer(Customer $customer): void
    {
        $this->ensureIsTrashed($customer);

        $hasActiveInvoices = Invoice::where('customer_id', $customer->id)->exists();
        $hasActiveTransactions = Transaction::where('customer_id', $customer->id)->exists();

        if ($hasActiveInvoices || $hasActiveTransactions) {
            throw new \RuntimeException('Customer still has active invoices or transactions.');
        }

        $customer->forceDelete();
    }

    protected function updateInvoiceDeliveryStatus(Invoice $invoice): void
    {
        $invoice->refresh();

        $allDelivered = true;
        $anyDelivered = false;

        foreach ($invoice->items as $item) {
            $deliveredQuantity = (float) $item->getDeliveredQuantityAttribute();

            if ($deliveredQuantity > 0) {
                $anyDelivered = true;
            }

            if ($deliveredQuantity < (float) $item->quantity) {
                $allDelivered = false;
            }
        }

        if ($allDelivered) {
            $invoice->delivery_status = 'delivered';
        } elseif ($anyDelivered) {
            $invoice->delivery_status = 'partial';
        } else {
            $invoice->delivery_status = 'pending';
        }

        $invoice->save();
    }

    protected function getQueryForType(string $type)
    {
        return match ($type) {
            'transactions' => Transaction::onlyTrashed()
                ->with([
                    'customer' => fn ($q) => method_exists($q->getModel(), 'withTrashed') ? $q->withTrashed() : $q,
                    'invoice' => fn ($q) => method_exists($q->getModel(), 'withTrashed') ? $q->withTrashed() : $q,
                    'deletedBy',
                ]),
            'customers' => Customer::onlyTrashed()->with(['deletedBy']),
            'challans' => Challan::onlyTrashed()
                ->with([
                    'invoice' => fn ($q) => method_exists($q->getModel(), 'withTrashed') ? $q->withTrashed() : $q,
                    'deletedBy',
                ]),
            'invoices' => Invoice::onlyTrashed()
                ->with([
                    'customer' => fn ($q) => method_exists($q->getModel(), 'withTrashed') ? $q->withTrashed() : $q,
                    'deletedBy',
                ]),
            'products' => Product::onlyTrashed()->with(['company', 'category', 'deletedBy']),
            'other-deliveries' => OtherDelivery::onlyTrashed()->with(['deliveredBy', 'deletedBy']),
            default => Transaction::onlyTrashed()->with(['deletedBy']),
        };
    }

    protected function ensureIsTrashed($model): void
    {
        if (!method_exists($model, 'trashed') || !$model->trashed()) {
            throw new \RuntimeException('Record is not in trash.');
        }
    }

    protected function normalizeType(?string $type): string
    {
        return (string) str($type ?? '')->trim()->lower();
    }
}
