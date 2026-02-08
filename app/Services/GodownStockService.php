<?php

namespace App\Services;

use App\Models\Godown;
use App\Models\Product;
use App\Models\ProductGodownStock;
use App\Models\ErpFeatureSetting;
use App\Support\TenantContext;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GodownStockService
{
    public static function isEnabled(): bool
    {
        return ErpFeatureSetting::isEnabled('godown_management');
    }

    public static function resolveGodownId(?int $godownId, ?Product $product = null): ?int
    {
        if ($godownId) {
            return $godownId;
        }

        if ($product && $product->default_godown_id) {
            return $product->default_godown_id;
        }

        return Godown::defaultId();
    }

    public static function adjustStock(int $productId, ?int $godownId, float $delta): void
    {
        if (!self::isEnabled() || !$godownId) {
            return;
        }

        $stock = ProductGodownStock::firstOrNew([
            'product_id' => $productId,
            'godown_id' => $godownId,
        ]);

        $stock->quantity = (float) $stock->quantity + $delta;
        $stock->save();
    }

    public static function setStock(int $productId, ?int $godownId, float $quantity): void
    {
        if (!self::isEnabled() || !$godownId) {
            return;
        }

        $stock = ProductGodownStock::firstOrNew([
            'product_id' => $productId,
            'godown_id' => $godownId,
        ]);

        $stock->quantity = (float) $quantity;
        $stock->save();
    }

    public static function getAvailableStock(int $productId, ?int $godownId): float
    {
        if (!self::isEnabled() || !$godownId) {
            return 0.0;
        }

        return (float) ProductGodownStock::where('product_id', $productId)
            ->where('godown_id', $godownId)
            ->value('quantity') ?? 0.0;
    }

    public static function getOldestGodownIdForProduct(int $productId): ?int
    {
        if (!self::isEnabled()) {
            return null;
        }

        $tenantId = TenantContext::currentId();

        $godownId = DB::table('product_godown_stocks as pgs')
            ->join('godowns', 'pgs.godown_id', '=', 'godowns.id')
            ->leftJoin('purchase_items', function ($join) {
                $join->on('purchase_items.godown_id', '=', 'pgs.godown_id')
                    ->on('purchase_items.product_id', '=', 'pgs.product_id');
            })
            ->leftJoin('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
            ->where('pgs.product_id', $productId)
            ->when($tenantId, function ($query, $tenantId) {
                $query->where('pgs.tenant_id', $tenantId)
                    ->where('godowns.tenant_id', $tenantId)
                    ->where(function ($q) use ($tenantId) {
                        $q->whereNull('purchase_items.tenant_id')
                          ->orWhere('purchase_items.tenant_id', $tenantId);
                    })
                    ->where(function ($q) use ($tenantId) {
                        $q->whereNull('purchases.tenant_id')
                          ->orWhere('purchases.tenant_id', $tenantId);
                    });
            })
            ->where('pgs.quantity', '>', 0)
            ->groupBy('pgs.godown_id')
            ->orderByRaw('MIN(purchases.purchase_date) IS NULL')
            ->orderByRaw('MIN(purchases.purchase_date) ASC')
            ->value('pgs.godown_id');

        if ($godownId) {
            return (int) $godownId;
        }

        return Godown::defaultId();
    }

    public static function getAvailableGodownsForProduct(int $productId): Collection
    {
        if (!self::isEnabled()) {
            return collect();
        }

        $tenantId = TenantContext::currentId();

        return DB::table('godowns')
            ->leftJoin('product_godown_stocks as pgs', function ($join) use ($productId) {
                $join->on('godowns.id', '=', 'pgs.godown_id')
                    ->where('pgs.product_id', $productId);
            })
            ->leftJoin('purchase_items', function ($join) use ($productId) {
                $join->on('purchase_items.godown_id', '=', 'godowns.id')
                    ->where('purchase_items.product_id', $productId);
            })
            ->leftJoin('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
            ->when($tenantId, function ($query, $tenantId) {
                $query->where('godowns.tenant_id', $tenantId)
                    ->where(function ($q) use ($tenantId) {
                        $q->whereNull('pgs.tenant_id')
                          ->orWhere('pgs.tenant_id', $tenantId);
                    })
                    ->where(function ($q) use ($tenantId) {
                        $q->whereNull('purchase_items.tenant_id')
                          ->orWhere('purchase_items.tenant_id', $tenantId);
                    })
                    ->where(function ($q) use ($tenantId) {
                        $q->whereNull('purchases.tenant_id')
                          ->orWhere('purchases.tenant_id', $tenantId);
                    });
            })
            ->groupBy('godowns.id', 'godowns.name', 'godowns.location', 'pgs.quantity')
            ->select(
                'godowns.id',
                'godowns.name',
                'godowns.location',
                DB::raw('COALESCE(pgs.quantity, 0) as stock'),
                DB::raw('MIN(purchases.purchase_date) as oldest_purchase_date')
            )
            ->orderByRaw('MIN(purchases.purchase_date) IS NULL')
            ->orderByRaw('MIN(purchases.purchase_date) ASC')
            ->orderBy('godowns.name')
            ->get();
    }
}
