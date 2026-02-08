<?php

return [

    'title' => 'Rahman Tiles and Sanitary',
    'title_prefix' => '',
    'title_postfix' => '',

    'use_ico_only' => false,
    'use_full_favicon' => false,

    'google_fonts' => [
        'allowed' => true,
    ],

    'logo' => '<b>Rahman</b> Tiles',
    'logo_img' => 'vendor/adminlte/dist/img/AdminLTELogo.png',
    'logo_img_class' => 'brand-image img-circle elevation-3 opacity-75',
    'logo_img_xl' => null,
    'logo_img_xl_class' => 'brand-image-xs',
    'logo_img_alt' => 'Rahman Tiles',

    'auth_logo' => [
        'enabled' => false,
        'img' => [
            'path' => 'vendor/adminlte/dist/img/AdminLTELogo.png',
            'alt' => 'Auth Logo',
            'class' => '',
            'width' => 50,
            'height' => 50,
        ],
    ],

    'preloader' => [
        'enabled' => false,
        'mode' => 'fullscreen',
        'img' => [
            'path' => 'vendor/adminlte/dist/img/AdminLTELogo.png',
            'alt' => 'AdminLTE Preloader Image',
            'effect' => 'animation__shake',
            'width' => 60,
            'height' => 60,
        ],
    ],

    'usermenu_enabled' => true,
    'usermenu_header' => true,
    'usermenu_header_class' => 'bg-primary',
    'usermenu_image' => false,
    'usermenu_desc' => false,
    'usermenu_profile_url' => true,

    'layout_topnav' => null,
    'layout_boxed' => null,
    'layout_fixed_sidebar' => null,
    'layout_fixed_navbar' => null,
    'layout_fixed_footer' => null,
    'layout_dark_mode' => null,

    'classes_auth_card' => 'card-outline card-primary',
    'classes_auth_header' => '',
    'classes_auth_body' => '',
    'classes_auth_footer' => '',
    'classes_auth_icon' => '',
    'classes_auth_btn' => 'btn-flat btn-primary',

    'classes_body' => 'hold-transition sidebar-mini layout-fixed layout-navbar-fixed',
    'classes_brand' => 'custom-brand-gradient',
    'classes_brand_text' => 'font-weight-light text-white',
    'classes_content_wrapper' => '',
    'classes_content_header' => '',
    'classes_content' => '',

    'classes_sidebar' => 'sidebar-dark-primary elevation-4',
    'classes_sidebar_nav' => 'nav-child-indent nav-compact nav-pills nav-sidebar flex-column',
    'classes_topnav' => 'navbar-dark navbar-primary',
    'classes_topnav_nav' => 'navbar-expand',
    'classes_topnav_container' => 'container',

    'sidebar_mini' => 'lg',
    'sidebar_collapse' => false,
    'sidebar_collapse_auto_size' => false,
    'sidebar_collapse_remember' => false,
    'sidebar_collapse_remember_no_transition' => true,
    'sidebar_scrollbar_theme' => 'os-theme-light',
    'sidebar_scrollbar_auto_hide' => 'l',
    'sidebar_nav_accordion' => true,
    'sidebar_nav_animation_speed' => 300,

    'right_sidebar' => false,
    'right_sidebar_icon' => 'fas fa-cogs',
    'right_sidebar_theme' => 'dark',
    'right_sidebar_slide' => true,
    'right_sidebar_push' => true,
    'right_sidebar_scrollbar_theme' => 'os-theme-light',
    'right_sidebar_scrollbar_auto_hide' => 'l',

    'use_route_url' => false,
    'dashboard_url' => '/dashboard',
    'logout_url' => 'logout',
    'login_url' => 'login',
    'register_url' => 'register',
    'password_reset_url' => 'password/reset',
    'password_email_url' => 'password/email',
    'profile_url' => '/profile',
    'disable_darkmode_routes' => false,

    'laravel_asset_bundling' => false,
    'laravel_css_path' => 'css/app.css',
    'laravel_js_path' => 'js/app.js',

    /*
    |--------------------------------------------------------------------------
    | Menu Items
    |--------------------------------------------------------------------------
    */

    'menu' => [
        // =====================================================================
        // TOP NAVIGATION BAR - Quick Access Buttons
        // =====================================================================
        [
            'text' => '',
            'url'  => 'dashboard',
            'icon' => 'fas fa-tachometer-alt',
            'topnav' => true,
            'classes' => 'topnav-hide-mobile',
        ],
        [
            'text' => 'Invoice',
            'url'  => 'invoices/create',
            'icon' => 'fas fa-fw fa-plus',
            'can'  => 'invoice-create',
            'topnav' => true,
        ],
        [
            'text' => 'Other-Invoice',
            'url'  => 'invoices/create-other',
            'icon' => 'fas fa-fw fa-plus',
            'can'  => 'invoice-create',
            'topnav' => true,
            'classes' => 'topnav-hide-mobile',
        ],
        [
            'text' => 'Payments',
            'url' => 'transactions/create',
            'icon' => 'fas fa-plus',
            'can'  => 'transaction-create',
            'topnav' => true,
        ],
        [
            'text' => 'All Invoices',
            'url' => 'invoices',
            'icon' => 'fas fa-list',
            'can'  => 'invoice-list',
            'topnav' => true,
            'classes' => 'topnav-hide-mobile',
        ],
        [
            'text' => 'All Products',
            'url' => 'products',
            'icon' => 'fas fa-box',
            'can'  => 'product-list',
            'topnav' => true,
            'classes' => 'topnav-hide-mobile',
        ],

        // =====================================================================
        // SIDEBAR NAVIGATION
        // =====================================================================

        // =====================================================================
        // SALES & CUSTOMERS
        // =====================================================================
        [
            'header' => 'SALES & CUSTOMERS',
            'can' => ['invoice-list', 'invoice-create', 'return-list', 'return-create', 'customer-list', 'customer-create'],
        ],
        [
            'text'    => 'Customers',
            'icon'    => 'fas fa-fw fa-users',
            'can'     => ['customer-list', 'customer-create'],
            'submenu' => [
                [
                    'text' => 'All Customers',
                    'url'  => 'customers',
                    'icon' => 'fas fa-fw fa-list',
                    'can'  => 'customer-list',
                ],
                [
                    'text' => 'Add Customer',
                    'url'  => 'customers/create',
                    'icon' => 'fas fa-fw fa-user-plus',
                    'can'  => 'customer-create',
                ],
            ],
        ],
        [
            'text'    => 'Referrers',
            'icon'    => 'fas fa-fw fa-user-tag',
            'can'     => ['customer-list'],
            'submenu' => [
                [
                    'text' => 'All Referrers',
                    'url'  => 'referrers',
                    'icon' => 'fas fa-fw fa-list',
                    'can'  => 'customer-list',
                ],
                [
                    'text' => 'Add Referrer',
                    'url'  => 'referrers/create',
                    'icon' => 'fas fa-fw fa-user-plus',
                    'can'  => 'customer-list',
                ],
            ],
        ],
        [
            'text'    => 'Sales & Invoices',
            'icon'    => 'fas fa-fw fa-shopping-cart',
            'can'     => ['invoice-list', 'invoice-create'],
            'submenu' => [
                [
                    'text' => 'All Invoices',
                    'url'  => 'invoices',
                    'icon' => 'fas fa-fw fa-list',
                    'can'  => 'invoice-list',
                ],
                [
                    'text' => 'Create Tiles Invoice',
                    'url'  => 'invoices/create',
                    'icon' => 'fas fa-fw fa-plus',
                    'can'  => 'invoice-create',
                ],
                [
                    'text' => 'Create Paints Invoice',
                    'url'  => 'invoices/create-other',
                    'icon' => 'fas fa-fw fa-plus',
                    'can'  => 'invoice-create',
                ],
            ],
        ],
        [
            'text'    => 'Sales Returns',
            'icon'    => 'fas fa-fw fa-undo-alt',
            'can'     => ['return-list', 'return-create'],
            'submenu' => [
                [
                    'text' => 'All Sales Returns',
                    'url'  => 'returns',
                    'icon' => 'fas fa-fw fa-list',
                    'can'  => 'return-list',
                ],
                [
                    'text' => 'Create Sale Return',
                    'url'  => 'returns/create',
                    'icon' => 'fas fa-fw fa-plus',
                    'can'  => 'return-create',
                ],
            ],
        ],

        // =====================================================================
        // DELIVERY & CHALLANS
        // =====================================================================
        [
            'header' => 'DELIVERY & CHALLANS',
            'can' => ['challan-list', 'remaining-products-view', 'other-delivery-list', 'other-delivery-return-list'],
        ],
        [
            'text'    => 'Challans',
            'icon'    => 'fas fa-fw fa-truck',
            'can'     => ['challan-list', 'remaining-products-view'],
            'submenu' => [
                [
                    'text' => 'All Challans',
                    'url'  => 'challans',
                    'icon' => 'fas fa-fw fa-list',
                    'can'  => 'challan-list',
                ],
                [
                    'text' => 'Remaining Products',
                    'url'  => 'sales/remaining-products',
                    'icon' => 'fas fa-boxes',
                    'can'  => 'remaining-products-view',
                ],
            ],
        ],
        [
            'text'    => 'Other Deliveries',
            'icon'    => 'fas fa-fw fa-truck-loading',
            'can'     => ['other-delivery-list', 'other-delivery-create', 'other-delivery-return-list', 'other-delivery-return-create'],
            'submenu' => [
                [
                    'text' => 'All Other Deliveries',
                    'url'  => 'other-deliveries',
                    'icon' => 'fas fa-fw fa-list',
                    'can'  => 'other-delivery-list',
                ],
                [
                    'text' => 'Create Other Delivery',
                    'url'  => 'other-deliveries/create',
                    'icon' => 'fas fa-fw fa-plus',
                    'can'  => 'other-delivery-create',
                ],
                [
                    'text' => 'All Other Returns',
                    'url'  => 'other-delivery-returns',
                    'icon' => 'fas fa-fw fa-undo',
                    'can'  => 'other-delivery-return-list',
                ],
                [
                    'text' => 'Create Other Return',
                    'url'  => 'other-delivery-returns/create',
                    'icon' => 'fas fa-fw fa-plus-circle',
                    'can'  => 'other-delivery-return-create',
                ],
            ],
        ],

        // =====================================================================
        // INVENTORY & PRODUCTS
        // =====================================================================
        [
            'header' => 'INVENTORY & PRODUCTS',
            'can' => ['product-list', 'product-create', 'category-list', 'purchase-list', 'purchase-create'],
        ],
        [
            'text'    => 'Products',
            'icon'    => 'fas fa-fw fa-box',
            'can'     => ['product-list', 'product-create', 'category-list'],
            'submenu' => [
                [
                    'text' => 'All Products',
                    'url'  => 'products',
                    'icon' => 'fas fa-fw fa-list',
                    'can'  => 'product-list',
                ],
                [
                    'text' => 'Add Product',
                    'url'  => 'products/create',
                    'icon' => 'fas fa-fw fa-plus',
                    'can'  => 'product-create',
                ],
                [
                    'text' => 'Stock Adjustment',
                    'url'  => 'products/stock-adjustment',
                    'icon' => 'fas fa-fw fa-balance-scale',
                ],
                [
                    'text' => 'Categories',
                    'url'  => 'categories',
                    'icon' => 'fas fa-fw fa-tags',
                    'can'  => 'category-list',
                ],
            ],
        ],
        [
            'text'    => 'Godowns',
            'icon'    => 'fas fa-fw fa-warehouse',
            'can'     => ['godown-list', 'godown-create'],
            'submenu' => [
                [
                    'text' => 'All Godowns',
                    'url'  => 'godowns',
                    'icon' => 'fas fa-fw fa-list',
                    'can'  => 'godown-list',
                ],
                [
                    'text' => 'Add Godown',
                    'url'  => 'godowns/create',
                    'icon' => 'fas fa-fw fa-plus',
                    'can'  => 'godown-create',
                ],
            ],
        ],
        [
            'text'    => 'Companies/Brands',
            'icon'    => 'fas fa-fw fa-building',
            'can'     => ['product-list', 'product-create'],
            'submenu' => [
                [
                    'text' => 'All Companies',
                    'url'  => 'companies',
                    'icon' => 'fas fa-fw fa-list',
                ],
                [
                    'text' => 'Add Company',
                    'url'  => 'companies/create',
                    'icon' => 'fas fa-fw fa-plus',
                ],
            ],
        ],
        [
            'text'    => 'Purchases',
            'icon'    => 'fas fa-fw fa-warehouse',
            'can'     => ['purchase-list', 'purchase-create'],
            'submenu' => [
                [
                    'text' => 'All Purchases',
                    'url'  => 'purchases',
                    'icon' => 'fas fa-fw fa-list',
                    'can'  => 'purchase-list',
                ],
                [
                    'text' => 'Create Purchase',
                    'url'  => 'purchases/create',
                    'icon' => 'fas fa-fw fa-plus',
                    'can'  => 'purchase-create',
                ],
            ],
        ],

        // =====================================================================
        // FINANCIAL MANAGEMENT
        // =====================================================================
        [
            'header' => 'FINANCIAL MANAGEMENT',
            'can' => ['transaction-list', 'transaction-create', 'payee-list', 'payee-create', 'payable-transaction-list', 'payable-transaction-create'],
        ],
        [
            'text' => 'Customer Payments',
            'icon' => 'fas fa-money-bill-wave',
            'can'  => ['transaction-list', 'transaction-create'],
            'submenu' => [
                [
                    'text' => 'All Transactions',
                    'url' => 'transactions',
                    'icon' => 'fas fa-list',
                    'can'  => 'transaction-list',
                ],
                [
                    'text' => 'Add Transaction',
                    'url' => 'transactions/create',
                    'icon' => 'fas fa-plus',
                    'can'  => 'transaction-create',
                ],
            ],
        ],
        [
            'text' => 'Payables (Suppliers)',
            'icon' => 'fas fa-hand-holding-usd',
            'can'  => ['payee-list', 'payee-create', 'payable-transaction-list', 'payable-transaction-create', 'payee-reports'],
            'submenu' => [
                [
                    'text' => 'All Payees',
                    'url'  => '/payables/payees',
                    'icon' => 'fas fa-users',
                    'can'  => 'payee-list',
                ],
                [
                    'text' => 'Add New Payee',
                    'url'  => '/payables/payees/create',
                    'icon' => 'fas fa-user-plus',
                    'can'  => 'payee-create',
                ],
                [
                    'text' => 'All Transactions',
                    'url'  => '/payables/transactions',
                    'icon' => 'fas fa-exchange-alt',
                    'can'  => 'payable-transaction-list',
                ],
                [
                    'text' => 'Add Transaction',
                    'url'  => '/payables/transactions/create',
                    'icon' => 'fas fa-plus-circle',
                    'can'  => 'payable-transaction-create',
                ],
                [
                    'text' => 'Aging Report',
                    'url'  => '/payables/reports/aging',
                    'icon' => 'fas fa-calendar-alt',
                    'can'  => 'payee-reports',
                ],
            ],
        ],
        [
            'text' => 'Expenses',
            'icon' => 'fas fa-receipt',
            'can'  => ['expense-list', 'expense-create', 'expense-edit', 'expense-delete'],
            'submenu' => [
                [
                    'text' => 'All Expenses',
                    'url'  => '/expenses',
                    'icon' => 'fas fa-list',
                    'can'  => 'expense-list',
                ],
                [
                    'text' => 'Add Expense',
                    'url'  => '/expenses/create',
                    'icon' => 'fas fa-plus-circle',
                    'can'  => 'expense-create',
                ],
                [
                    'text' => 'Expense Categories',
                    'url'  => '/expenses/categories',
                    'icon' => 'fas fa-tags',
                    'can'  => 'expense-list',
                ],
            ],
        ],
        // Cash Registers disabled

        // =====================================================================
        // HR & PAYROLL
        // =====================================================================
        [
            'header' => 'HR & PAYROLL',
            'can' => ['employee-list', 'employee-attendance', 'employee-payroll', 'employee-advance', 'employee-adjustment'],
        ],
        [
            'text' => 'Employees',
            'icon' => 'fas fa-user-tie',
            'can'  => ['employee-list', 'employee-create', 'employee-edit'],
            'submenu' => [
                [
                    'text' => 'All Employees',
                    'url'  => 'hr/employees',
                    'icon' => 'fas fa-list',
                    'can'  => 'employee-list',
                ],
                [
                    'text' => 'Add Employee',
                    'url'  => 'hr/employees/create',
                    'icon' => 'fas fa-user-plus',
                    'can'  => 'employee-create',
                ],
                [
                    'text' => 'Attendance',
                    'url'  => 'hr/attendance',
                    'icon' => 'fas fa-calendar-check',
                    'can'  => 'employee-attendance',
                ],
                [
                    'text' => 'Advances',
                    'url'  => 'hr/advances',
                    'icon' => 'fas fa-hand-holding-usd',
                    'can'  => 'employee-advance',
                ],
                [
                    'text' => 'Adjustments',
                    'url'  => 'hr/adjustments',
                    'icon' => 'fas fa-sliders-h',
                    'can'  => 'employee-adjustment',
                ],
                [
                    'text' => 'Payroll',
                    'url'  => 'hr/payrolls',
                    'icon' => 'fas fa-money-check-alt',
                    'can'  => 'employee-payroll',
                ],
            ],
        ],

        // =====================================================================
        // DEBT COLLECTION
        // =====================================================================
        [
            'header' => 'DEBT COLLECTION',
            'can' => ['debt-collection-dashboard', 'debt-collection-track', 'debt-collection-view-reports'],
        ],
        [
            'text' => 'Debt Collection',
            'icon' => 'fas fa-file-invoice-dollar',
            'can'  => ['debt-collection-dashboard', 'debt-collection-track', 'debt-collection-view-reports'],
            'submenu' => [
                [
                    'text' => 'Outstanding Accounts',
                    'url'  => 'debt-collection',
                    'icon' => 'fas fa-users',
                    'can'  => 'debt-collection-track',
                ],
                [
                    'text' => 'Due Today',
                    'url'  => 'debt-collection/due-today',
                    'icon' => 'fas fa-calendar-day',
                    'label'       => 'HOT',
                    'label_color' => 'danger',
                    'can'  => 'debt-collection-track',
                ],
                [
                    'text' => 'Due This Week',
                    'url'  => 'debt-collection/due-this-week',
                    'icon' => 'fas fa-calendar-week',
                    'can'  => 'debt-collection-track',
                ],
                [
                    'text' => 'Overdue Accounts',
                    'url'  => 'debt-collection/reports/overdue',
                    'icon' => 'fas fa-exclamation-circle',
                    'can'  => 'debt-collection-track',
                ],
                [
                    'text' => 'Call Schedule',
                    'url'  => 'debt-collection/call-schedule',
                    'icon' => 'fas fa-phone-alt',
                    'can'  => 'debt-collection-track',
                ],
                [
                    'text' => 'Performance Metrics',
                    'url'  => 'debt-collection/reports/performance',
                    'icon' => 'fas fa-chart-line',
                    'can'  => 'debt-collection-view-reports',
                ],
            ],
        ],

        // =====================================================================
        // ACCOUNTING (Tally-like)
        // =====================================================================
        [
            'header' => 'ACCOUNTING',
            'can' => ['account-list', 'accounting-reports'],
        ],
        [
            'text' => 'Chart of Accounts',
            'icon' => 'fas fa-sitemap',
            'can'  => ['account-list'],
            'submenu' => [
                [
                    'text' => 'Account Groups',
                    'url'  => 'accounting/account-groups',
                    'icon' => 'fas fa-folder-tree',
                    'can'  => 'account-list',
                ],
                [
                    'text' => 'All Ledgers',
                    'url'  => 'accounting/accounts',
                    'icon' => 'fas fa-book',
                    'can'  => 'account-list',
                ],
                [
                    'text' => 'Create Account',
                    'url'  => 'accounting/accounts/create',
                    'icon' => 'fas fa-plus',
                    'can'  => 'account-create',
                ],
            ],
        ],
        [
            'text' => 'Bank Management',
            'icon' => 'fas fa-university',
            'can'  => ['account-list', 'account-create'],
            'submenu' => [
                [
                    'text' => 'All Banks',
                    'url'  => 'accounting/banks',
                    'icon' => 'fas fa-list',
                    'can'  => 'account-list',
                ],
                [
                    'text' => 'Add Bank',
                    'url'  => 'accounting/banks/create',
                    'icon' => 'fas fa-plus',
                    'can'  => 'account-create',
                ],
                [
                    'text' => 'Bank Transactions',
                    'url'  => 'accounting/bank-transactions',
                    'icon' => 'fas fa-exchange-alt',
                    'can'  => 'account-list',
                ],
                [
                    'text' => 'Add Bank Transaction',
                    'url'  => 'accounting/bank-transactions/create',
                    'icon' => 'fas fa-plus-circle',
                    'can'  => 'account-create',
                ],
            ],
        ],
        [
            'text' => 'Financial Reports',
            'icon' => 'fas fa-chart-pie',
            'can'  => 'accounting-reports',
            'submenu' => [
                [
                    'text' => 'Trial Balance',
                    'url'  => 'accounting/reports/trial-balance',
                    'icon' => 'fas fa-balance-scale',
                    'can'  => 'accounting-reports',
                ],
                [
                    'text' => 'Balance Sheet',
                    'url'  => 'accounting/reports/balance-sheet',
                    'icon' => 'fas fa-file-invoice-dollar',
                    'can'  => 'accounting-reports',
                ],
                [
                    'text' => 'Profit & Loss',
                    'url'  => 'accounting/reports/profit-loss',
                    'icon' => 'fas fa-chart-line',
                    'can'  => 'accounting-reports',
                ],
                [
                    'text' => 'Day Book',
                    'url'  => 'accounting/reports/day-book',
                    'icon' => 'fas fa-calendar-day',
                    'can'  => 'accounting-reports',
                ],
                [
                    'text' => 'Cash Book',
                    'url'  => 'accounting/reports/cash-book',
                    'icon' => 'fas fa-money-bill',
                    'can'  => 'accounting-reports',
                ],
                [
                    'text' => 'Bank Book',
                    'url'  => 'accounting/reports/bank-book',
                    'icon' => 'fas fa-university',
                    'can'  => 'accounting-reports',
                ],
            ],
        ],

        // =====================================================================
        // REPORTS & ANALYTICS
        // =====================================================================
        [
            'header' => 'REPORTS & ANALYTICS',
            'can' => ['report-sales', 'report-inventory', 'report-financial', 'report-export', 'invoice-list'],
        ],
        [
            'text' => 'Sales Report',
            'url'  => '/reports/cash-flow',
            'icon' => 'fas fa-coins',
            'can'  => ['invoice-list'],
        ],
        [
            'text' => 'Stock Reports',
            'url'  => '/products/reports',
            'icon' => 'fas fa-warehouse',
            'can'  => ['report-inventory', 'product-list'],
        ],
        [
            'text' => 'Product Insights',
            'url'  => '/reports/products',
            'icon' => 'fas fa-chart-bar',
            'can'  => ['report-sales', 'report-inventory', 'report-financial', 'report-export'],
        ],
        [
            'text' => 'Customer Insights',
            'url'  => '/reports/customers',
            'icon' => 'fas fa-users',
            'can'  => ['report-sales', 'report-financial'],
        ],

        // =====================================================================
        // USER MANAGEMENT
        // =====================================================================
        [
            'header' => 'USER MANAGEMENT',
            'can' => ['user-list', 'user-create', 'role-list', 'role-create'],
        ],
        [
            'text'    => 'Users',
            'icon'    => 'fas fa-fw fa-users-cog',
            'can'     => ['user-list', 'user-create'],
            'submenu' => [
                [
                    'text' => 'All Users',
                    'url'  => 'users',
                    'icon' => 'fas fa-fw fa-list',
                    'can'  => 'user-list',
                ],
                [
                    'text' => 'Add User',
                    'url'  => 'users/create',
                    'icon' => 'fas fa-fw fa-user-plus',
                    'can'  => 'user-create',
                ],
            ],
        ],
        [
            'text'    => 'Roles & Permissions',
            'icon'    => 'fas fa-fw fa-user-shield',
            'can'     => ['role-list', 'role-create'],
            'submenu' => [
                [
                    'text' => 'All Roles',
                    'url'  => 'roles',
                    'icon' => 'fas fa-fw fa-list',
                    'can'  => 'role-list',
                ],
                [
                    'text' => 'Add Role',
                    'url'  => 'roles/create',
                    'icon' => 'fas fa-fw fa-plus',
                    'can'  => 'role-create',
                ],
            ],
        ],
        [
            'text' => 'Team Chat',
            'icon' => 'fas fa-comments',
            'url'  => 'chat',
            'can'  => 'chat-access',
        ],

        // =====================================================================
        // TOOLS
        // =====================================================================
        [
            'header' => 'TOOLS',
        ],
        [
            'text' => 'Colorent Management',
            'url'  => 'colorents/management',
            'icon' => 'fas fa-flask',
        ],
        [
            'text' => 'Decor Calculator',
            'icon' => 'fas fa-th-large',
            'submenu' => [
                [
                    'text' => 'Calculator',
                    'url'  => 'admin/decor-calculator',
                    'icon' => 'fas fa-calculator',
                ],
                [
                    'text' => 'Manage Categories',
                    'url'  => 'admin/tiles-categories',
                    'icon' => 'fas fa-list',
                ],
                [
                    'text' => 'Tiles Settings',
                    'url'  => 'admin/tiles-settings',
                    'icon' => 'fas fa-wrench',
                ],
            ],
        ],

        // =====================================================================
        // SYSTEM SETTINGS
        // =====================================================================
        [
            'header' => 'SYSTEM',
            'can' => ['business-settings-view', 'erp-features-view'],
        ],
        [
            'text' => 'Business Settings',
            'url'  => 'business-settings',
            'icon' => 'fas fa-building',
            'can'  => 'business-settings-view',
        ],
        [
            'text' => 'Companies / Tenants',
            'url'  => 'tenants',
            'icon' => 'fas fa-building',
            'can'  => 'super-admin-only',
        ],
        [
            'text' => 'Feature Settings',
            'url'  => 'erp-settings/features',
            'icon' => 'fas fa-toggle-on',
            'can'  => 'erp-features-view',
        ],
        [
            'text' => 'System Tools',
            'route' => 'system.index',
            'icon' => 'fas fa-server',
            'can' => 'business-settings-view',
        ],
        [
            'text' => 'SMS Management',
            'icon' => 'fas fa-sms',
            'can'  => ['business-settings-view'],
            'submenu' => [
                [
                    'text' => 'Dashboard',
                    'url'  => 'sms/dashboard',
                    'icon' => 'fas fa-tachometer-alt',
                    'can'  => 'business-settings-view',
                ],
                [
                    'text' => 'Settings',
                    'url'  => 'sms/settings',
                    'icon' => 'fas fa-cogs',
                    'can'  => 'business-settings-view',
                ],
            ],
        ],

        // =====================================================================
        // ACCOUNT / PROFILE
        // =====================================================================
        [
            'header' => 'MY ACCOUNT',
        ],
        [
            'text' => 'My Profile',
            'url'  => 'profile',
            'icon' => 'fas fa-fw fa-user-circle',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Menu Filters
    |--------------------------------------------------------------------------
    */

    'filters' => [
        JeroenNoten\LaravelAdminLte\Menu\Filters\GateFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\HrefFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\SearchFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ActiveFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ClassesFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\LangFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\DataFilter::class,
        App\Menu\Filters\FeatureFilter::class, // Custom filter for ERP feature toggles
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugins Initialization
    |--------------------------------------------------------------------------
    */

    'plugins' => [
        'Datatables' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css',
                ],
            ],
        ],
        'Select2' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.css',
                ],
            ],
        ],
        'Chartjs' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.0/Chart.bundle.min.js',
                ],
            ],
        ],
        'Sweetalert2' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/adminlte/plugins/sweetalert2/sweetalert2.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => true,
                    'location' => 'vendor/adminlte/plugins/sweetalert2/sweetalert2.min.css',
                ],
            ],
        ],
        'Pace' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/themes/blue/pace-theme-center-radar.min.css',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/pace.min.js',
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | IFrame
    |--------------------------------------------------------------------------
    */

    'iframe' => [
        'default_tab' => [
            'url' => null,
            'title' => null,
        ],
        'buttons' => [
            'close' => true,
            'close_all' => true,
            'close_all_other' => true,
            'scroll_left' => true,
            'scroll_right' => true,
            'fullscreen' => true,
        ],
        'options' => [
            'loading_screen' => 1000,
            'auto_show_new_tab' => true,
            'use_navbar_items' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Livewire
    |--------------------------------------------------------------------------
    */

    'livewire' => false,
];
