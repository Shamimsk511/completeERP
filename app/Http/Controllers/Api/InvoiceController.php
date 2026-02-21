<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InvoiceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$this->canReadInvoices($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view invoices.',
            ], 403);
        }

        $query = Invoice::query()
            ->with([
                'customer:id,name,phone,address,outstanding_balance',
            ]);

        if (!$this->applyTenantFilter($query, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'No tenant is assigned to this user.',
            ], 422);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', '%' . $search . '%')
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', (int) $request->customer_id);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', (string) $request->payment_status);
        }

        if ($request->filled('delivery_status')) {
            $query->where('delivery_status', (string) $request->delivery_status);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('invoice_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('invoice_date', '<=', $request->to_date);
        }

        $perPage = (int) $request->input('per_page', 20);
        $perPage = max(1, min($perPage, 100));

        $invoices = $query
            ->orderByDesc('invoice_date')
            ->orderByDesc('id')
            ->paginate($perPage);

        $items = $invoices->getCollection()->map(function (Invoice $invoice) {
            return [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'invoice_date' => optional($invoice->invoice_date)->toDateString(),
                'invoice_type' => $invoice->invoice_type,
                'customer' => [
                    'id' => $invoice->customer?->id,
                    'name' => $invoice->customer?->name,
                    'phone' => $invoice->customer?->phone,
                    'address' => $invoice->customer?->address,
                    'outstanding_balance' => (float) ($invoice->customer?->outstanding_balance ?? 0),
                ],
                'total' => (float) $invoice->total,
                'paid_amount' => (float) $invoice->paid_amount,
                'due_amount' => (float) $invoice->due_amount,
                'payment_status' => $invoice->payment_status,
                'delivery_status' => $invoice->delivery_status,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $items,
                'pagination' => [
                    'current_page' => $invoices->currentPage(),
                    'last_page' => $invoices->lastPage(),
                    'per_page' => $invoices->perPage(),
                    'total' => $invoices->total(),
                ],
            ],
        ]);
    }

    public function show(Request $request, Invoice $invoice): JsonResponse
    {
        $user = $request->user();

        if (!$this->canReadInvoices($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view invoices.',
            ], 403);
        }

        if (!$this->canAccessInvoiceTenant($invoice, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found.',
            ], 404);
        }

        $invoice->load([
            'customer:id,name,phone,address,outstanding_balance',
            'items:id,invoice_id,product_id,description,quantity,unit_price,total',
            'items.product' => function ($query) {
                if (method_exists($query->getModel(), 'withTrashed')) {
                    $query->withTrashed();
                }
                $query->select('id', 'name');
            },
        ]);

        $paidTotal = (float) Transaction::query()
            ->where('invoice_id', $invoice->id)
            ->where('type', 'debit')
            ->sum('amount');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'invoice_date' => optional($invoice->invoice_date)->toDateString(),
                'invoice_type' => $invoice->invoice_type,
                'customer' => [
                    'id' => $invoice->customer?->id,
                    'name' => $invoice->customer?->name,
                    'phone' => $invoice->customer?->phone,
                    'address' => $invoice->customer?->address,
                    'outstanding_balance' => (float) ($invoice->customer?->outstanding_balance ?? 0),
                ],
                'subtotal' => (float) $invoice->subtotal,
                'discount' => (float) $invoice->discount,
                'tax' => (float) $invoice->tax,
                'total' => (float) $invoice->total,
                'paid_amount' => (float) $invoice->paid_amount,
                'due_amount' => (float) $invoice->due_amount,
                'paid_total_from_transactions' => $paidTotal,
                'payment_status' => $invoice->payment_status,
                'delivery_status' => $invoice->delivery_status,
                'notes' => $invoice->notes,
                'items' => $invoice->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'product_name' => $item->product?->name,
                        'description' => $item->description,
                        'quantity' => (float) $item->quantity,
                        'unit_price' => (float) $item->unit_price,
                        'total' => (float) $item->total,
                    ];
                })->values(),
            ],
        ]);
    }

    public function printPdf(Request $request, Invoice $invoice): Response|JsonResponse
    {
        $user = $request->user();

        if (!$this->canReadInvoices($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view invoices.',
            ], 403);
        }

        if (!$this->canAccessInvoiceTenant($invoice, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found.',
            ], 404);
        }

        $invoice->load(['customer', 'items.product.category', 'items.product.company']);

        $settings = app('business.settings');
        $selectedTemplate = (string) ($settings->invoice_template ?? 'standard');
        $allowedTemplates = ['standard', 'modern', 'simple', 'bold', 'elegant', 'imaginative'];
        if (!in_array($selectedTemplate, $allowedTemplates, true)) {
            $selectedTemplate = 'standard';
        }

        $printOptions = array_merge(
            $this->defaultInvoicePrintOptions(),
            (array) ($settings->invoice_print_options ?? [])
        );

        $businessSettings = $settings;
        $html = view('invoices.print', compact(
            'invoice',
            'selectedTemplate',
            'printOptions',
            'businessSettings'
        ))->render();

        $pdf = Pdf::setOptions([
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
        ])->loadHTML($html)->setPaper('a4');

        $fileName = 'invoice-' . ($invoice->invoice_number ?: $invoice->id) . '.pdf';

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    protected function canReadInvoices($user): bool
    {
        if (!$user) {
            return false;
        }

        if (method_exists($user, 'hasRole') && ($user->hasRole('Super Admin') || $user->hasRole('Admin'))) {
            return true;
        }

        if (method_exists($user, 'can') && $user->can('invoice-list')) {
            return true;
        }

        return false;
    }

    protected function applyTenantFilter($query, $user): bool
    {
        if (!property_exists($user, 'tenant_id')) {
            return false;
        }

        $tenantId = $this->tenantIdForUser($user);
        if ($tenantId) {
            $query->where('invoices.tenant_id', $tenantId);
            return true;
        }

        if (method_exists($user, 'hasRole') && $user->hasRole('Super Admin')) {
            return true;
        }

        return false;
    }

    protected function canAccessInvoiceTenant(Invoice $invoice, $user): bool
    {
        $tenantId = $this->tenantIdForUser($user);

        if (method_exists($user, 'hasRole') && $user->hasRole('Super Admin')) {
            if (!$tenantId) {
                return true;
            }
        }

        if (!$tenantId) {
            return false;
        }

        return (int) $invoice->tenant_id === $tenantId;
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

        return !empty($user->tenant_id) ? (int) $user->tenant_id : null;
    }

    protected function defaultInvoicePrintOptions(): array
    {
        return [
            'show_company_phone' => true,
            'show_company_email' => true,
            'show_company_address' => true,
            'show_company_bin' => true,
            'show_bank_details' => true,
            'show_terms' => true,
            'show_footer_message' => true,
            'show_customer_qr' => true,
            'show_signatures' => true,
            'invoice_phone_override' => '',
        ];
    }
}
