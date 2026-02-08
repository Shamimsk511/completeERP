<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ErpFeatureSetting;
use Symfony\Component\HttpFoundation\Response;

class CheckFeatureEnabled
{
    /**
     * URL patterns to feature key mapping
     */
    protected $urlFeatureMap = [
        // Invoice features
        'invoices/create-other' => 'other_invoice',

        // Delivery features
        'other-deliveries' => 'other_delivery',
        'other-delivery-returns' => 'other_delivery_returns',

        // Inventory features
        'products/stock-adjustment' => 'stock_adjustment',
        'purchases' => 'purchase_management',
        'godowns' => 'godown_management',

        // Financial features
        'payables' => 'payables',
        'accounting' => 'accounting',
        'debt-collection' => 'debt_collection',

        // Report features
        'reports/cash-flow' => 'sales_reports',
        'products/reports' => 'stock_reports',

        // General features
        'sms' => 'sms_notifications',
        'hr' => 'hr_payroll',
        'colorents' => 'colorent_management',
        'admin/decor-calculator' => 'decor_calculator',
        'admin/tiles-categories' => 'decor_calculator',
        'admin/tiles-settings' => 'decor_calculator',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->path();

        // Check if current path matches any disabled feature
        foreach ($this->urlFeatureMap as $urlPattern => $featureKey) {
            if (strpos($path, $urlPattern) !== false) {
                if (ErpFeatureSetting::isDisabled($featureKey)) {
                    return $this->featureDisabledResponse($request, $featureKey);
                }
            }
        }

        return $next($request);
    }

    /**
     * Return response for disabled feature
     */
    protected function featureDisabledResponse(Request $request, string $featureKey): Response
    {
        $feature = ErpFeatureSetting::where('feature_key', $featureKey)->first();
        $featureName = $feature ? $feature->feature_name : $featureKey;

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => "The '{$featureName}' feature is currently disabled.",
                'feature_key' => $featureKey,
            ], 403);
        }

        return redirect()->route('dashboard')->with('error', "The '{$featureName}' feature is currently disabled. Contact your administrator to enable it.");
    }
}
