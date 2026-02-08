<?php

namespace App\Menu\Filters;

use App\Models\ErpFeatureSetting;
use JeroenNoten\LaravelAdminLte\Menu\Filters\FilterInterface;

class FeatureFilter implements FilterInterface
{
    /**
     * Feature to menu mapping - maps feature keys to menu text/urls
     */
    protected $featureMenuMap = [
        // Invoice features
        'tiles_invoice' => [
            'texts' => ['Create Tiles Invoice'],
            'urls' => ['invoices/create'],
        ],
        'other_invoice' => [
            'texts' => ['Create Paints Invoice', 'Other-Invoice'],
            'urls' => ['invoices/create-other'],
        ],

        // Delivery features
        'other_delivery' => [
            'texts' => ['Other Deliveries', 'All Other Deliveries', 'Create Other Delivery'],
            'urls' => ['other-deliveries'],
        ],
        'other_delivery_returns' => [
            'texts' => ['All Other Returns', 'Create Other Return'],
            'urls' => ['other-delivery-returns'],
        ],

        // Inventory features
        'stock_adjustment' => [
            'texts' => ['Stock Adjustment'],
            'urls' => ['products/stock-adjustment'],
        ],
        'purchase_management' => [
            'texts' => ['Purchases', 'All Purchases', 'Create Purchase'],
            'urls' => ['purchases'],
        ],
        'godown_management' => [
            'texts' => ['Godowns', 'All Godowns', 'Add Godown'],
            'urls' => ['godowns'],
        ],

        // Financial features
        'payables' => [
            'texts' => ['Payables (Suppliers)', 'All Payees', 'Add New Payee', 'Aging Report'],
            'urls' => ['/payables'],
        ],
        'accounting' => [
            'texts' => [
                'Chart of Accounts',
                'Account Groups',
                'All Ledgers',
                'Create Account',
                'Bank Management',
                'All Banks',
                'Add Bank',
                'Bank Transactions',
                'Add Bank Transaction',
                'Financial Reports',
                'Trial Balance',
                'Balance Sheet',
                'Profit & Loss',
                'Day Book',
                'Cash Book',
                'Bank Book',
            ],
            'urls' => ['accounting'],
        ],
        'debt_collection' => [
            'texts' => ['Debt Collection', 'Outstanding Accounts', 'Due Today', 'Due This Week', 'Overdue Accounts', 'Call Schedule', 'Performance Metrics'],
            'urls' => ['debt-collection'],
        ],

        // Report features
        'sales_reports' => [
            'texts' => ['Sales Report'],
            'urls' => ['/reports/cash-flow'],
        ],
        'stock_reports' => [
            'texts' => ['Stock Reports'],
            'urls' => ['/products/reports'],
        ],

        // General features
        'sms_notifications' => [
            'texts' => ['SMS Management'],
            'urls' => ['sms'],
        ],
        'hr_payroll' => [
            'texts' => ['Employees', 'All Employees', 'Add Employee', 'Attendance', 'Advances', 'Adjustments', 'Payroll'],
            'urls' => ['hr'],
        ],
        'colorent_management' => [
            'texts' => ['Colorent Management'],
            'urls' => ['colorents'],
        ],
        'decor_calculator' => [
            'texts' => ['Decor Calculator', 'Calculator', 'Manage Categories', 'Tiles Settings'],
            'urls' => ['admin/decor-calculator', 'admin/tiles-categories', 'admin/tiles-settings'],
        ],
    ];

    /**
     * Headers that depend on specific features - hide header if ALL listed features are disabled
     */
    protected $headerFeatureMap = [
        'DELIVERY & CHALLANS' => ['other_delivery', 'other_delivery_returns', 'challan_delivery'],
        'DEBT COLLECTION' => ['debt_collection'],
        'ACCOUNTING' => ['accounting'],
        'HR & PAYROLL' => ['hr_payroll'],
        'REPORTS & ANALYTICS' => ['sales_reports', 'stock_reports'],
    ];

    /**
     * Transforms a menu item. Returns false to exclude the item.
     */
    public function transform($item)
    {
        // Check if this is a header
        if (isset($item['header'])) {
            return $this->transformHeader($item);
        }

        // Skip non-menu items
        if (!isset($item['text']) && !isset($item['url'])) {
            return $item;
        }

        // Check if this menu item should be hidden based on disabled features
        if ($this->shouldHideMenuItem($item)) {
            return false;
        }

        // Process submenu items if present
        if (isset($item['submenu']) && \is_array($item['submenu'])) {
            $filteredSubmenu = [];
            foreach ($item['submenu'] as $subitem) {
                $result = $this->transform($subitem);
                if ($result !== false) {
                    $filteredSubmenu[] = $result;
                }
            }

            // If all submenu items were filtered out, hide the parent too
            if (empty($filteredSubmenu)) {
                return false;
            }

            $item['submenu'] = $filteredSubmenu;
        }

        return $item;
    }

    /**
     * Transform header - hide if all features under it are disabled
     */
    protected function transformHeader($item): array|false
    {
        $headerText = $item['header'];

        // Check if this header has feature dependencies
        if (isset($this->headerFeatureMap[$headerText])) {
            $features = $this->headerFeatureMap[$headerText];
            $allDisabled = true;

            // Check if ALL features for this header are disabled
            foreach ($features as $featureKey) {
                if (ErpFeatureSetting::isEnabled($featureKey)) {
                    $allDisabled = false;
                    break;
                }
            }

            // Hide header if all its features are disabled
            if ($allDisabled) {
                return false;
            }
        }

        return $item;
    }

    /**
     * Check if a menu item should be hidden based on disabled features
     */
    protected function shouldHideMenuItem($item): bool
    {
        $itemText = $item['text'] ?? '';
        $itemUrl = $item['url'] ?? ($item['route'] ?? '');

        foreach ($this->featureMenuMap as $featureKey => $mapping) {
            // Check if feature is disabled
            if (ErpFeatureSetting::isDisabled($featureKey)) {
                // Check if this menu item matches the feature
                if ($this->matchesFeature($itemText, $itemUrl, $mapping)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if menu item matches a feature mapping
     */
    protected function matchesFeature(string $itemText, string $itemUrl, array $mapping): bool
    {
        // Check text matches
        if (!empty($mapping['texts']) && \in_array($itemText, $mapping['texts'])) {
            return true;
        }

        // Check URL matches (partial matching)
        if (!empty($mapping['urls']) && !empty($itemUrl)) {
            foreach ($mapping['urls'] as $url) {
                if (strpos($itemUrl, $url) !== false) {
                    return true;
                }
            }
        }

        return false;
    }
}
