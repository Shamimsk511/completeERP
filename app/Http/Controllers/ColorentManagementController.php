<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Colorent;
use App\Models\Payee;
use App\Models\PayableTransaction;
use App\Models\ColorentPurchase;
use App\Models\ColorentPurchaseItem;
use App\Models\ColorentUsage;
use App\Services\PayeeAccountService;
use App\Services\Accounting\AutoPostingService;
use App\Support\TenantContext;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ColorentManagementController extends Controller
{
    public function index()
    {
        if (!auth()->user()->can('view colorents')) {
            abort(403, 'Unauthorized access to colorent management.');
        }

        $colorents = Colorent::orderBy('name')->get();
        $payees = Payee::query()
            ->where(function ($query) {
                $query->where('category', 'supplier')
                    ->orWhere('type', 'supplier');
            })
            ->orderBy('name')
            ->get(['id', 'name', 'current_balance', 'category', 'type']);

        $defaultPayeeId = optional($payees->first(function ($payee) {
            return stripos($payee->name, 'berger') !== false;
        }))->id;

        $canUpdatePrice = auth()->user()->can('update prices');

        $filters = [
            'colorent_id' => request('colorent_id'),
            'movement_type' => request('movement_type'),
            'date_from' => request('date_from'),
            'date_to' => request('date_to'),
        ];

        $movements = $this->buildMovements($filters, true);
        
        // Calculate totals
        $totalStock = $colorents->sum('stock');
        $totalValue = $colorents->sum(function($colorent) {
            return $colorent->stock * $colorent->price;
        });
        
        return view('colorents.management', compact(
            'colorents',
            'totalStock',
            'totalValue',
            'payees',
            'defaultPayeeId',
            'canUpdatePrice',
            'movements',
            'filters'
        ));
    }

    public function create()
    {
        if (!auth()->user()->can('edit colorents')) {
            abort(403, 'Unauthorized access to create colorents.');
        }

        return view('colorents.create');
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('edit colorents')) {
            abort(403, 'Unauthorized access to create colorents.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'nullable|numeric|min:0',
        ]);

        $colorent = Colorent::create([
            'name' => $validated['name'],
            'stock' => 0,
            'price' => $validated['price'] ?? 0,
        ]);

        return redirect()->route('colorents.management')
            ->with('success', "Colorent '{$colorent->name}' created. Use Purchase to add stock.");
    }

    public function edit(Colorent $colorent)
    {
        if (!auth()->user()->can('edit colorents')) {
            abort(403, 'Unauthorized access to edit colorents.');
        }

        return view('colorents.edit', compact('colorent'));
    }

    public function update(Request $request, Colorent $colorent)
    {
        if (!auth()->user()->can('edit colorents')) {
            abort(403, 'Unauthorized access to edit colorents.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'nullable|numeric|min:0',
        ]);

        $colorent->update([
            'name' => $validated['name'],
            'price' => $validated['price'] ?? $colorent->price,
        ]);

        return redirect()->route('colorents.management')
            ->with('success', "Colorent '{$colorent->name}' updated.");
    }

    public function exportMovements(Request $request)
    {
        if (!auth()->user()->can('view colorents')) {
            abort(403, 'Unauthorized access to colorent management.');
        }

        $filters = [
            'colorent_id' => $request->input('colorent_id'),
            'movement_type' => $request->input('movement_type'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
        ];

        $movements = $this->buildMovements($filters, false);

        $filename = 'colorent_movements_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($movements) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Date',
                'Colorent',
                'Type',
                'Quantity',
                'Unit Cost',
                'Total',
                'Source',
                'User',
                'Reference',
            ]);

            foreach ($movements as $movement) {
                fputcsv($handle, [
                    $movement['date']->format('Y-m-d'),
                    $movement['colorent'],
                    $movement['type'] === 'in' ? 'Added' : 'Poured',
                    $movement['quantity'],
                    $movement['unit_cost'] !== null ? number_format($movement['unit_cost'], 2, '.', '') : '',
                    $movement['total'] !== null ? number_format($movement['total'], 2, '.', '') : '',
                    $movement['source'],
                    $movement['user'],
                    $movement['reference'] ?? '',
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    protected function buildMovements(array $filters, bool $limit = true)
    {
        $colorentId = $filters['colorent_id'] ?? null;
        $movementType = $filters['movement_type'] ?? null;
        $dateFrom = $filters['date_from'] ?? null;
        $dateTo = $filters['date_to'] ?? null;

        $movements = collect();

        $rowLimit = $limit ? 120 : null;

        if ($movementType !== 'out') {
            $purchaseItems = ColorentPurchaseItem::with(['colorent', 'purchase.payee', 'purchase.createdBy'])
                ->when($colorentId, fn($query) => $query->where('colorent_id', $colorentId))
                ->when($dateFrom || $dateTo, function ($query) use ($dateFrom, $dateTo) {
                    $query->whereHas('purchase', function ($purchaseQuery) use ($dateFrom, $dateTo) {
                        if ($dateFrom) {
                            $purchaseQuery->whereDate('transaction_date', '>=', $dateFrom);
                        }
                        if ($dateTo) {
                            $purchaseQuery->whereDate('transaction_date', '<=', $dateTo);
                        }
                    });
                })
                ->orderByDesc('id')
                ->when($rowLimit, fn($query) => $query->limit($rowLimit))
                ->get();

            foreach ($purchaseItems as $item) {
                $purchase = $item->purchase;
                $movementDate = $purchase?->transaction_date ?? $item->created_at;
                $sortAt = $movementDate
                    ? Carbon::parse($movementDate)->setTimeFrom($item->created_at ?? now())
                    : ($item->created_at ?? now());

                $movements->push([
                    'sort_at' => $sortAt,
                    'date' => $movementDate ? Carbon::parse($movementDate) : ($item->created_at ?? now()),
                    'type' => 'in',
                    'colorent' => $item->colorent?->name ?? 'N/A',
                    'quantity' => $item->quantity,
                    'unit_cost' => $item->unit_cost,
                    'total' => $item->line_total,
                    'source' => $purchase?->payee?->name ?? 'Supplier',
                    'user' => $purchase?->createdBy?->name ?? 'System',
                    'reference' => $purchase?->reference_no,
                ]);
            }
        }

        if ($movementType !== 'in') {
            $usageItems = ColorentUsage::with(['colorent', 'createdBy'])
                ->when($colorentId, fn($query) => $query->where('colorent_id', $colorentId))
                ->when($dateFrom, fn($query) => $query->whereDate('used_at', '>=', $dateFrom))
                ->when($dateTo, fn($query) => $query->whereDate('used_at', '<=', $dateTo))
                ->orderByDesc('id')
                ->when($rowLimit, fn($query) => $query->limit($rowLimit))
                ->get();

            foreach ($usageItems as $usage) {
                $movementDate = $usage->used_at ?? $usage->created_at;
                $sortAt = $movementDate
                    ? Carbon::parse($movementDate)->setTimeFrom($usage->created_at ?? now())
                    : ($usage->created_at ?? now());

                $movements->push([
                    'sort_at' => $sortAt,
                    'date' => $movementDate ? Carbon::parse($movementDate) : ($usage->created_at ?? now()),
                    'type' => 'out',
                    'colorent' => $usage->colorent?->name ?? 'N/A',
                    'quantity' => $usage->quantity,
                    'unit_cost' => null,
                    'total' => null,
                    'source' => 'Tinting Machine',
                    'user' => $usage->createdBy?->name ?? 'System',
                    'reference' => null,
                ]);
            }
        }

        $movements = $movements->sortByDesc('sort_at');

        if ($limit) {
            $movements = $movements->take(50);
        }

        return $movements->values();
    }

    public function updateStock(Request $request, $id)
    {
        if (!auth()->user()->can('manage stock')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $colorent = Colorent::findOrFail($id);
        
        if ($request->has('new_stock')) {
            $newStock = max(0, $request->input('new_stock'));
        } else {
            $newStock = max(0, $colorent->stock + $request->input('change', 0));
        }
        
        $colorent->update(['stock' => $newStock]);

        // Calculate new totals for response
        $allColorents = Colorent::all();
        $totalStock = $allColorents->sum('stock');
        $totalValue = $allColorents->sum(function($c) {
            return $c->stock * $c->price;
        });

        return response()->json([
            'success' => true,
            'new_stock' => $colorent->stock,
            'total_stock' => $totalStock,
            'total_value' => number_format($totalValue, 2)
        ]);
    }

    public function updatePrice(Request $request, $id)
    {
        if (!auth()->user()->can('update prices')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'price' => 'required|numeric|min:0'
        ]);

        $colorent = Colorent::findOrFail($id);
        $colorent->update(['price' => $request->price]);

        // Calculate new totals for response
        $allColorents = Colorent::all();
        $totalStock = $allColorents->sum('stock');
        $totalValue = $allColorents->sum(function($c) {
            return $c->stock * $c->price;
        });

        return response()->json([
            'success' => true,
            'new_price' => number_format($colorent->price, 2),
            'total_stock' => $totalStock,
            'total_value' => number_format($totalValue, 2)
        ]);
    }

    public function storePurchase(Request $request)
    {
        if (!auth()->user()->can('manage stock')) {
            abort(403, 'Unauthorized access to colorent purchases.');
        }

        $tenantId = TenantContext::currentId();

        $validated = $request->validate([
            'payee_id' => [
                'required',
                Rule::exists('payees', 'id')->where(function ($query) use ($tenantId) {
                    if ($tenantId) {
                        $query->where('tenant_id', $tenantId);
                    }
                    $query->where(function ($inner) {
                        $inner->where('category', 'supplier')
                            ->orWhere('type', 'supplier');
                    });
                })
            ],
            'transaction_date' => 'required|date',
            'reference_no' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.colorent_id' => [
                'required',
                Rule::exists('colorents', 'id')->where(function ($query) use ($tenantId) {
                    if ($tenantId) {
                        $query->where('tenant_id', $tenantId);
                    }
                })
            ],
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0.01',
            'items.*.update_price' => 'nullable|boolean',
        ]);

        $payee = Payee::findOrFail($validated['payee_id']);
        $canUpdatePrice = auth()->user()->can('update prices');

        DB::beginTransaction();
        try {
            $purchase = ColorentPurchase::create([
                'payee_id' => $payee->id,
                'transaction_date' => $validated['transaction_date'],
                'reference_no' => $validated['reference_no'] ?? null,
                'total_amount' => 0,
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $totalAmount = 0;

            foreach ($validated['items'] as $item) {
                $quantity = (int) $item['quantity'];
                $unitCost = (float) $item['unit_cost'];
                $lineTotal = $quantity * $unitCost;
                $totalAmount += $lineTotal;

                $colorent = Colorent::query()
                    ->whereKey($item['colorent_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                $updatePrice = !empty($item['update_price']) && $canUpdatePrice;

                ColorentPurchaseItem::create([
                    'colorent_purchase_id' => $purchase->id,
                    'colorent_id' => $colorent->id,
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'line_total' => $lineTotal,
                    'update_price' => $updatePrice,
                ]);

                $colorent->increment('stock', $quantity);

                if ($updatePrice) {
                    $colorent->update(['price' => $unitCost]);
                }
            }

            $purchase->update(['total_amount' => $totalAmount]);

            app(PayeeAccountService::class)->ensureAccountForPayee($payee);

            $referenceNo = $validated['reference_no'] ?? null;
            if (!$referenceNo) {
                $referenceNo = 'CLR-PUR-' . now()->format('YmdHis');
            }

            $transaction = PayableTransaction::create([
                'payee_id' => $payee->id,
                'transaction_type' => 'cash_out',
                'payment_method' => null,
                'account_id' => null,
                'reference_no' => $referenceNo,
                'amount' => $totalAmount,
                'category' => 'colorent_purchase',
                'description' => $validated['notes'] ?? 'Colorent purchase',
                'transaction_date' => Carbon::parse($validated['transaction_date'])->toDateString(),
            ]);

            $payee->current_balance = (float) ($payee->current_balance ?? 0) + $totalAmount;
            $payee->save();

            try {
                app(AutoPostingService::class)->postPayableTransaction($transaction);
            } catch (\Exception $e) {
                \Log::warning("Failed to auto-post colorent purchase {$transaction->id}: " . $e->getMessage());
            }

            DB::commit();

            return redirect()->route('colorents.management')
                ->with('success', 'Colorent purchase recorded and supplier balance updated.');
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error recording colorent purchase: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function storeUsage(Request $request)
    {
        if (!auth()->user()->can('manage stock')) {
            abort(403, 'Unauthorized access to colorent usage.');
        }

        $tenantId = TenantContext::currentId();

        $validated = $request->validate([
            'colorent_id' => [
                'required',
                Rule::exists('colorents', 'id')->where(function ($query) use ($tenantId) {
                    if ($tenantId) {
                        $query->where('tenant_id', $tenantId);
                    }
                })
            ],
            'quantity' => 'required|integer|min:1',
            'used_at' => 'required|date',
            'notes' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $colorent = Colorent::query()
                ->whereKey($validated['colorent_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $quantity = (int) $validated['quantity'];
            if ($quantity > $colorent->stock) {
                throw ValidationException::withMessages([
                    'quantity' => "Only {$colorent->stock} unit(s) available for {$colorent->name}.",
                ]);
            }

            $colorent->decrement('stock', $quantity);

            ColorentUsage::create([
                'colorent_id' => $colorent->id,
                'used_at' => Carbon::parse($validated['used_at'])->toDateString(),
                'quantity' => $quantity,
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            DB::commit();

            return redirect()->route('colorents.management')
                ->with('success', 'Colorent usage recorded. Stock updated without accounting impact.');
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error recording colorent usage: ' . $e->getMessage())
                ->withInput();
        }
    }
}
