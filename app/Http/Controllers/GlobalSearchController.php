<?php

namespace App\Http\Controllers;

use App\Models\Challan;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payee;
use App\Models\Product;
use App\Models\Purchase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));
        $user = $request->user();

        if ($query === '' || mb_strlen($query) < 2 || !$user) {
            return response()->json([
                'query' => $query,
                'groups' => [],
                'count' => 0,
            ]);
        }

        $like = '%' . $this->escapeLike($query) . '%';
        $groups = [];
        $totalCount = 0;

        if ($user->can('invoice-list')) {
            $items = Invoice::query()
                ->select(['id', 'invoice_number', 'invoice_date', 'customer_id', 'total', 'due_amount'])
                ->with('customer:id,name')
                ->where(function ($q) use ($like) {
                    $q->where('invoice_number', 'like', $like)
                        ->orWhereHas('customer', function ($customer) use ($like) {
                            $customer->where('name', 'like', $like);
                        });
                })
                ->latest('id')
                ->limit(6)
                ->get()
                ->map(function (Invoice $invoice) {
                    return [
                        'title' => $invoice->invoice_number ?: ('Invoice #' . $invoice->id),
                        'subtitle' => trim(($invoice->customer->name ?? 'Unknown customer') . ' | ' . optional($invoice->invoice_date)->format('d M Y')),
                        'meta' => 'Due: ' . number_format((float) ($invoice->due_amount ?? 0), 2),
                        'url' => route('invoices.show', $invoice),
                        'icon' => 'fas fa-file-invoice',
                    ];
                })
                ->values()
                ->all();

            if (!empty($items)) {
                $groups[] = ['label' => 'Invoices', 'items' => $items];
                $totalCount += count($items);
            }
        }

        if ($user->can('customer-list')) {
            $items = Customer::query()
                ->select(['id', 'name', 'phone', 'address', 'outstanding_balance'])
                ->where(function ($q) use ($like) {
                    $q->where('name', 'like', $like)
                        ->orWhere('phone', 'like', $like)
                        ->orWhere('address', 'like', $like);
                })
                ->orderBy('name')
                ->limit(6)
                ->get()
                ->map(function (Customer $customer) {
                    return [
                        'title' => $customer->name,
                        'subtitle' => trim(($customer->phone ?: 'No phone') . ' | ' . ($customer->address ?: 'No address')),
                        'meta' => 'Outstanding: ' . number_format((float) ($customer->outstanding_balance ?? 0), 2),
                        'url' => route('customers.show', $customer),
                        'icon' => 'fas fa-user',
                    ];
                })
                ->values()
                ->all();

            if (!empty($items)) {
                $groups[] = ['label' => 'Customers', 'items' => $items];
                $totalCount += count($items);
            }
        }

        if ($user->can('product-list')) {
            $items = Product::query()
                ->select(['id', 'name', 'company_id', 'current_stock', 'sale_price'])
                ->with('company:id,name')
                ->where(function ($q) use ($like) {
                    $q->where('name', 'like', $like)
                        ->orWhereHas('company', function ($company) use ($like) {
                            $company->where('name', 'like', $like);
                        });
                })
                ->orderBy('name')
                ->limit(6)
                ->get()
                ->map(function (Product $product) {
                    return [
                        'title' => $product->name,
                        'subtitle' => $product->company->name ?? 'No company',
                        'meta' => 'Stock: ' . number_format((float) ($product->current_stock ?? 0), 2),
                        'url' => route('products.show', $product),
                        'icon' => 'fas fa-box',
                    ];
                })
                ->values()
                ->all();

            if (!empty($items)) {
                $groups[] = ['label' => 'Products', 'items' => $items];
                $totalCount += count($items);
            }
        }

        if ($user->can('challan-list')) {
            $items = Challan::query()
                ->select(['id', 'challan_number', 'challan_date', 'invoice_id'])
                ->with(['invoice:id,invoice_number,customer_id', 'invoice.customer:id,name'])
                ->where(function ($q) use ($like) {
                    $q->where('challan_number', 'like', $like)
                        ->orWhereHas('invoice', function ($invoice) use ($like) {
                            $invoice->where('invoice_number', 'like', $like)
                                ->orWhereHas('customer', function ($customer) use ($like) {
                                    $customer->where('name', 'like', $like);
                                });
                        });
                })
                ->latest('id')
                ->limit(6)
                ->get()
                ->map(function (Challan $challan) {
                    $invoiceNumber = $challan->invoice->invoice_number ?? 'N/A';
                    $customerName = $challan->invoice->customer->name ?? 'Unknown customer';

                    return [
                        'title' => $challan->challan_number ?: ('Challan #' . $challan->id),
                        'subtitle' => trim('Invoice: ' . $invoiceNumber . ' | ' . $customerName),
                        'meta' => optional($challan->challan_date)->format('d M Y') ?: '',
                        'url' => route('challans.show', $challan),
                        'icon' => 'fas fa-truck',
                    ];
                })
                ->values()
                ->all();

            if (!empty($items)) {
                $groups[] = ['label' => 'Challans', 'items' => $items];
                $totalCount += count($items);
            }
        }

        if ($user->can('payee-list')) {
            $items = Payee::query()
                ->select(['id', 'name', 'phone', 'type', 'current_balance'])
                ->where(function ($q) use ($like) {
                    $q->where('name', 'like', $like)
                        ->orWhere('phone', 'like', $like)
                        ->orWhere('type', 'like', $like);
                })
                ->orderBy('name')
                ->limit(6)
                ->get()
                ->map(function (Payee $payee) {
                    return [
                        'title' => $payee->name,
                        'subtitle' => trim(($payee->phone ?: 'No phone') . ' | ' . ucfirst((string) ($payee->type ?: 'payee'))),
                        'meta' => 'Balance: ' . number_format((float) ($payee->current_balance ?? 0), 2),
                        'url' => route('payees.show', $payee),
                        'icon' => 'fas fa-hand-holding-usd',
                    ];
                })
                ->values()
                ->all();

            if (!empty($items)) {
                $groups[] = ['label' => 'Payees', 'items' => $items];
                $totalCount += count($items);
            }
        }

        if ($user->can('purchase-list')) {
            $items = Purchase::query()
                ->select(['id', 'invoice_no', 'purchase_date', 'company_id', 'grand_total'])
                ->with('company:id,name')
                ->where(function ($q) use ($like) {
                    $q->where('invoice_no', 'like', $like)
                        ->orWhereHas('company', function ($company) use ($like) {
                            $company->where('name', 'like', $like);
                        });
                })
                ->latest('id')
                ->limit(6)
                ->get()
                ->map(function (Purchase $purchase) {
                    return [
                        'title' => $purchase->invoice_no ?: ('Purchase #' . $purchase->id),
                        'subtitle' => $purchase->company->name ?? 'No supplier',
                        'meta' => optional($purchase->purchase_date)->format('d M Y') ?: '',
                        'url' => route('purchases.show', $purchase),
                        'icon' => 'fas fa-shopping-bag',
                    ];
                })
                ->values()
                ->all();

            if (!empty($items)) {
                $groups[] = ['label' => 'Purchases', 'items' => $items];
                $totalCount += count($items);
            }
        }

        return response()->json([
            'query' => $query,
            'groups' => $groups,
            'count' => $totalCount,
        ]);
    }

    private function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}

