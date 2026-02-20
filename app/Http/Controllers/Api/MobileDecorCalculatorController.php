<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TilesCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileDecorCalculatorController extends Controller
{
    public function calculate(Request $request): JsonResponse
    {
        $user = $request->user();
        $tenantId = $this->tenantIdForUser($user);
        if (!$this->canAccessCalculator($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to access decor calculator.',
            ], 403);
        }

        $validated = $request->validate([
            'category_id' => 'required|exists:tiles_categories,id',
            'quantity' => 'required|numeric|min:0.01',
            'height' => 'required|numeric|min:0.01',
            'light_times' => 'required|numeric|min:0',
            'light_qty' => 'required|numeric|min:0',
            'deco_times' => 'required|numeric|min:0',
            'deco_qty' => 'required|numeric|min:0',
            'deep_times' => 'nullable|numeric|min:0',
            'deep_qty' => 'nullable|numeric|min:0',
            'exclude_deep' => 'boolean',
        ]);

        $category = TilesCategory::query()
            ->whereKey((int) $validated['category_id'])
            ->when(
                !empty($tenantId),
                fn ($q) => $q->where('tenant_id', $tenantId)
            )
            ->first();

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Tiles category not found for current tenant.',
            ], 422);
        }

        $tileHeight = (float) ($category->height ?? 0);
        $tileWidth = (float) ($category->width ?? 0);
        if ($tileHeight <= 0 || $tileWidth <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Selected category is missing tile height/width.',
            ], 422);
        }

        $wallHeightInches = ((float) $validated['height']) * 12;
        $totalVerticalTiles = $wallHeightInches / $tileHeight;
        $verticalDecimal = $totalVerticalTiles - floor($totalVerticalTiles);

        $decoRows = (float) $validated['deco_times'];
        if ($decoRows > $totalVerticalTiles) {
            return response()->json([
                'success' => false,
                'message' => 'Deco rows exceed total vertical tiles for the given height.',
            ], 422);
        }

        $remainingTiles = $totalVerticalTiles - $decoRows;
        $halfRemaining = $remainingTiles / 2;
        $halfDecimal = $halfRemaining - floor($halfRemaining);

        if ($halfDecimal > 0.5) {
            $deepTiles = ceil($halfRemaining) + $verticalDecimal;
        } else {
            $deepTiles = floor($halfRemaining) + $verticalDecimal;
        }

        $lightTiles = $remainingTiles - $deepTiles;
        $excludeDeep = (bool) ($validated['exclude_deep'] ?? false);
        if ($excludeDeep) {
            $lightTiles = $remainingTiles;
            $deepTiles = 0;
        }

        $wallWidthFeet = ((float) $validated['quantity']) / ((float) $validated['height']);
        $wallWidthInches = $wallWidthFeet * 12;
        $horizontalTiles = ceil($wallWidthInches / $tileWidth);

        $totalDecoTiles = $horizontalTiles * $decoRows * ((float) $validated['deco_qty']);
        $deepQty = (float) ($validated['deep_qty'] ?? 0);
        if ($excludeDeep) {
            $deepQty = 0;
        }
        $totalDeepTiles = $horizontalTiles * $deepTiles * $deepQty;
        $totalLightTiles = $horizontalTiles * $lightTiles * ((float) $validated['light_qty']);

        $tileAreaSqFt = ($tileHeight * $tileWidth) / 144;
        $decoSqFt = $totalDecoTiles * $tileAreaSqFt;
        $deepSqFt = $totalDeepTiles * $tileAreaSqFt;
        $lightSqFt = $totalLightTiles * $tileAreaSqFt;

        return response()->json([
            'success' => true,
            'data' => [
                'total_vertical_tiles' => $totalVerticalTiles,
                'deco_rows' => $decoRows,
                'light_rows' => $lightTiles,
                'deep_rows' => $deepTiles,
                'horizontal_tiles' => $horizontalTiles,
                'light_quantity' => $totalLightTiles,
                'light_sqft' => $lightSqFt,
                'deco_quantity' => $totalDecoTiles,
                'deco_sqft' => $decoSqFt,
                'deep_quantity' => $totalDeepTiles,
                'deep_sqft' => $deepSqFt,
                'total_tiles' => $totalLightTiles + $totalDecoTiles + $totalDeepTiles,
                'total_sqft' => $lightSqFt + $decoSqFt + $deepSqFt,
            ],
        ]);
    }

    protected function canAccessCalculator($user): bool
    {
        if (!$user) {
            return false;
        }

        if (method_exists($user, 'hasRole') && ($user->hasRole('Super Admin') || $user->hasRole('Admin'))) {
            return true;
        }

        if (method_exists($user, 'can') && (
            $user->can('invoice-create') ||
            $user->can('purchase-create') ||
            $user->can('other-delivery-create')
        )) {
            return true;
        }

        return false;
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
}
