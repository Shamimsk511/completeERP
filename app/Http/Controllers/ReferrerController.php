<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Referrer;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReferrerController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:customer-list', ['only' => ['index', 'show']]);
        $this->middleware('permission:customer-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:customer-edit', ['only' => ['edit', 'update', 'updateInvoiceCompensation']]);
        $this->middleware('permission:customer-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        $referrers = Referrer::query()
            ->withCount('invoices')
            ->withSum('invoices', 'total')
            ->withSum('invoices', 'paid_amount')
            ->withSum('invoices', 'due_amount')
            ->orderBy('name')
            ->get();

        return view('referrers.index', compact('referrers'));
    }

    public function create()
    {
        return view('referrers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'profession' => 'nullable|string|max:255',
            'note' => 'nullable|string',
            'compensation_enabled' => 'nullable|boolean',
            'gift_enabled' => 'nullable|boolean',
        ]);

        $normalizedName = Str::lower(trim(preg_replace('/\s+/', ' ', $validated['name'])));
        $phone = $validated['phone'] ?? null;

        $duplicateQuery = Referrer::query()
            ->whereRaw('LOWER(TRIM(name)) = ?', [$normalizedName]);

        if ($phone) {
            $duplicateQuery->where('phone', $phone);
        }

        $existing = $duplicateQuery->first();
        if ($existing) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'referrer' => $existing,
                    'was_existing' => true,
                ]);
            }

            return redirect()->route('referrers.show', $existing->id)
                ->with('info', 'Referrer already exists.');
        }

        $referrer = Referrer::create([
            'name' => $validated['name'],
            'phone' => $phone,
            'profession' => $validated['profession'] ?? null,
            'note' => $validated['note'] ?? null,
            'compensation_enabled' => (bool) ($validated['compensation_enabled'] ?? false),
            'gift_enabled' => (bool) ($validated['gift_enabled'] ?? false),
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'referrer' => $referrer,
                'was_existing' => false,
            ]);
        }

        return redirect()->route('referrers.index')
            ->with('success', 'Referrer created successfully.');
    }

    public function show(Request $request, Referrer $referrer)
    {
        $categories = Category::orderBy('name')->get(['id', 'name']);

        $invoiceType = $request->get('invoice_type');
        $categoryId = $request->get('category_id');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $baseQuery = $referrer->invoices()
            ->when($invoiceType, function ($q) use ($invoiceType) {
                $q->where('invoice_type', $invoiceType);
            })
            ->when($categoryId, function ($q) use ($categoryId) {
                $q->whereHas('items.product', function ($itemQuery) use ($categoryId) {
                    $itemQuery->where('category_id', $categoryId);
                });
            })
            ->when($dateFrom, function ($q) use ($dateFrom) {
                $q->whereDate('invoice_date', '>=', $dateFrom);
            })
            ->when($dateTo, function ($q) use ($dateTo) {
                $q->whereDate('invoice_date', '<=', $dateTo);
            });

        $summary = [
            'invoice_count' => (clone $baseQuery)->count(),
            'total_sales' => (clone $baseQuery)->sum('total'),
            'total_collected' => (clone $baseQuery)->sum('paid_amount'),
            'total_due' => (clone $baseQuery)->sum('due_amount'),
            'compensated_count' => (clone $baseQuery)->where('referrer_compensated', true)->count(),
        ];

        $invoices = (clone $baseQuery)
            ->with(['customer', 'items.product.category'])
            ->orderBy('invoice_date', 'desc')
            ->get();

        return view('referrers.show', compact(
            'referrer',
            'invoices',
            'summary',
            'categories',
            'invoiceType',
            'categoryId',
            'dateFrom',
            'dateTo'
        ));
    }

    public function edit(Referrer $referrer)
    {
        return view('referrers.edit', compact('referrer'));
    }

    public function update(Request $request, Referrer $referrer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'profession' => 'nullable|string|max:255',
            'note' => 'nullable|string',
            'compensation_enabled' => 'nullable|boolean',
            'gift_enabled' => 'nullable|boolean',
        ]);

        $referrer->update([
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
            'profession' => $validated['profession'] ?? null,
            'note' => $validated['note'] ?? null,
            'compensation_enabled' => (bool) ($validated['compensation_enabled'] ?? false),
            'gift_enabled' => (bool) ($validated['gift_enabled'] ?? false),
        ]);

        return redirect()->route('referrers.show', $referrer->id)
            ->with('success', 'Referrer updated successfully.');
    }

    public function destroy(Referrer $referrer)
    {
        $referrer->delete();

        return redirect()->route('referrers.index')
            ->with('success', 'Referrer deleted successfully.');
    }

    public function updateInvoiceCompensation(Request $request, Referrer $referrer, Invoice $invoice)
    {
        if ($invoice->referrer_id !== $referrer->id) {
            return response()->json(['message' => 'Invoice does not belong to this referrer.'], 422);
        }

        $invoice->referrer_compensated = (bool) $request->boolean('referrer_compensated');
        $invoice->save();

        return response()->json([
            'success' => true,
            'referrer_compensated' => $invoice->referrer_compensated,
        ]);
    }
}
