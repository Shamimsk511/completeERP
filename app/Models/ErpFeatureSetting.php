<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ErpFeatureSetting extends Model
{
    protected $fillable = [
        'feature_key',
        'feature_name',
        'feature_group',
        'description',
        'is_enabled',
        'config',
        'sort_order',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'config' => 'array',
    ];

    /**
     * Cache key for feature settings
     */
    const CACHE_KEY = 'erp_feature_settings';
    const CACHE_TTL = 3600; // 1 hour

    /**
     * Available feature groups
     */
    const GROUPS = [
        'invoices' => 'Invoices & Sales',
        'delivery' => 'Delivery & Challans',
        'inventory' => 'Inventory & Products',
        'financial' => 'Financial Management',
        'reports' => 'Reports & Analytics',
        'general' => 'General Features',
    ];

    /**
     * Default features that should be seeded
     */
    const DEFAULT_FEATURES = [
        // Invoice Features
        [
            'feature_key' => 'tiles_invoice',
            'feature_name' => 'Tiles Invoice',
            'feature_group' => 'invoices',
            'description' => 'Enable tiles invoice creation with box/pieces calculation',
            'is_enabled' => true,
            'sort_order' => 1,
        ],
        [
            'feature_key' => 'other_invoice',
            'feature_name' => 'Other/Paints Invoice',
            'feature_group' => 'invoices',
            'description' => 'Enable other products (paints, etc.) invoice creation',
            'is_enabled' => true,
            'sort_order' => 2,
        ],
        [
            'feature_key' => 'invoice_discount',
            'feature_name' => 'Invoice Discount',
            'feature_group' => 'invoices',
            'description' => 'Allow discounts on invoices',
            'is_enabled' => true,
            'sort_order' => 3,
        ],
        [
            'feature_key' => 'invoice_referrer',
            'feature_name' => 'Invoice Referrer',
            'feature_group' => 'invoices',
            'description' => 'Track referrers on invoices',
            'is_enabled' => true,
            'sort_order' => 4,
        ],

        // Delivery Features
        [
            'feature_key' => 'challan_delivery',
            'feature_name' => 'Challan Delivery',
            'feature_group' => 'delivery',
            'description' => 'Enable challan/delivery note creation from invoices',
            'is_enabled' => true,
            'sort_order' => 1,
        ],
        [
            'feature_key' => 'other_delivery',
            'feature_name' => 'Other Deliveries',
            'feature_group' => 'delivery',
            'description' => 'Enable standalone deliveries (not linked to invoices)',
            'is_enabled' => true,
            'sort_order' => 2,
        ],
        [
            'feature_key' => 'other_delivery_returns',
            'feature_name' => 'Other Delivery Returns',
            'feature_group' => 'delivery',
            'description' => 'Enable returns for other deliveries',
            'is_enabled' => true,
            'sort_order' => 3,
        ],

        // Inventory Features
        [
            'feature_key' => 'product_weight',
            'feature_name' => 'Product Weight',
            'feature_group' => 'inventory',
            'description' => 'Enable weight tracking on products and categories',
            'is_enabled' => true,
            'sort_order' => 1,
        ],
        [
            'feature_key' => 'stock_management',
            'feature_name' => 'Stock Management',
            'feature_group' => 'inventory',
            'description' => 'Enable stock tracking and alerts',
            'is_enabled' => true,
            'sort_order' => 2,
        ],
        [
            'feature_key' => 'stock_adjustment',
            'feature_name' => 'Stock Adjustment',
            'feature_group' => 'inventory',
            'description' => 'Allow manual stock adjustments',
            'is_enabled' => true,
            'sort_order' => 3,
        ],
        [
            'feature_key' => 'purchase_management',
            'feature_name' => 'Purchase Management',
            'feature_group' => 'inventory',
            'description' => 'Enable purchase order and stock-in management',
            'is_enabled' => true,
            'sort_order' => 4,
        ],
        [
            'feature_key' => 'godown_management',
            'feature_name' => 'Godown Management',
            'feature_group' => 'inventory',
            'description' => 'Enable godown/warehouse tracking for products and stock movements',
            'is_enabled' => true,
            'sort_order' => 5,
        ],
        [
            'feature_key' => 'prevent_negative_stock',
            'feature_name' => 'Prevent Negative Stock',
            'feature_group' => 'inventory',
            'description' => 'Block deliveries that would make stock negative',
            'is_enabled' => false,
            'sort_order' => 6,
        ],

        // Financial Features
        [
            'feature_key' => 'customer_payments',
            'feature_name' => 'Customer Payments',
            'feature_group' => 'financial',
            'description' => 'Track customer payments and transactions',
            'is_enabled' => true,
            'sort_order' => 1,
        ],
        [
            'feature_key' => 'payables',
            'feature_name' => 'Payables (Suppliers)',
            'feature_group' => 'financial',
            'description' => 'Track supplier payables and payments',
            'is_enabled' => true,
            'sort_order' => 2,
        ],
        [
            'feature_key' => 'accounting',
            'feature_name' => 'Accounting Module',
            'feature_group' => 'financial',
            'description' => 'Enable full accounting with chart of accounts and reports',
            'is_enabled' => true,
            'sort_order' => 3,
        ],
        [
            'feature_key' => 'debt_collection',
            'feature_name' => 'Debt Collection',
            'feature_group' => 'financial',
            'description' => 'Enable debt collection tracking and reminders',
            'is_enabled' => true,
            'sort_order' => 4,
        ],

        // Report Features
        [
            'feature_key' => 'sales_reports',
            'feature_name' => 'Sales Reports',
            'feature_group' => 'reports',
            'description' => 'Enable sales and revenue reports',
            'is_enabled' => true,
            'sort_order' => 1,
        ],
        [
            'feature_key' => 'stock_reports',
            'feature_name' => 'Stock Reports',
            'feature_group' => 'reports',
            'description' => 'Enable inventory and stock reports',
            'is_enabled' => true,
            'sort_order' => 2,
        ],

        // General Features
        [
            'feature_key' => 'sms_notifications',
            'feature_name' => 'SMS Notifications',
            'feature_group' => 'general',
            'description' => 'Send SMS notifications for transactions',
            'is_enabled' => true,
            'sort_order' => 1,
        ],
        [
            'feature_key' => 'hr_payroll',
            'feature_name' => 'HR & Payroll',
            'feature_group' => 'general',
            'description' => 'Enable employee management, attendance, and payroll',
            'is_enabled' => true,
            'sort_order' => 2,
        ],
        [
            'feature_key' => 'colorent_management',
            'feature_name' => 'Colorent Management',
            'feature_group' => 'general',
            'description' => 'Enable colorent/tinting management tool',
            'is_enabled' => true,
            'sort_order' => 3,
        ],
        [
            'feature_key' => 'decor_calculator',
            'feature_name' => 'Decor Calculator',
            'feature_group' => 'general',
            'description' => 'Enable tiles/decor calculator tool',
            'is_enabled' => true,
            'sort_order' => 4,
        ],
    ];

    /**
     * Check if a feature is enabled
     */
    public static function isEnabled(string $featureKey): bool
    {
        $settings = self::getAllCached();
        return $settings[$featureKey] ?? true; // Default to enabled if not found
    }

    /**
     * Check if a feature is disabled
     */
    public static function isDisabled(string $featureKey): bool
    {
        return !self::isEnabled($featureKey);
    }

    /**
     * Get all feature settings cached
     */
    public static function getAllCached(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return self::pluck('is_enabled', 'feature_key')->toArray();
        });
    }

    /**
     * Get all features grouped
     */
    public static function getAllGrouped()
    {
        return self::orderBy('feature_group')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('feature_group');
    }

    /**
     * Clear feature settings cache
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Update feature and clear cache
     */
    public static function updateFeature(string $featureKey, bool $isEnabled): bool
    {
        $updated = self::where('feature_key', $featureKey)
            ->update(['is_enabled' => $isEnabled]);

        if ($updated) {
            self::clearCache();
        }

        return $updated > 0;
    }

    /**
     * Seed default features
     */
    public static function seedDefaults(): void
    {
        foreach (self::DEFAULT_FEATURES as $feature) {
            self::firstOrCreate(
                ['feature_key' => $feature['feature_key']],
                $feature
            );
        }
        self::clearCache();
    }
}
