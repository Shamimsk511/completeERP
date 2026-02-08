<?php

namespace App\Http\Controllers;

use App\Models\BusinessSetting;
use App\Models\Invoice;
use App\Models\Tenant;
use App\Support\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BusinessSettingController extends Controller
{
    public function __construct()
{
    $this->middleware('permission:business-settings-view', ['only' => ['index']]);
    $this->middleware('permission:business-settings-edit', ['only' => ['update']]);
}

    public function index(Request $request)
    {
        $selectedTenantId = TenantContext::currentId();
        $tenantList = null;

        if (auth()->user()?->hasRole('Super Admin')) {
            $tenantList = Tenant::orderBy('name')->get();
            if ($request->filled('tenant_id')) {
                $selectedTenantId = (int) $request->input('tenant_id');
            } elseif (!$selectedTenantId && $tenantList->isNotEmpty()) {
                $selectedTenantId = (int) $tenantList->first()->id;
            }
        }

        if (auth()->user()?->hasRole('Super Admin') && $selectedTenantId) {
            $settings = BusinessSetting::withoutGlobalScope('tenant')
                ->where('tenant_id', $selectedTenantId)
                ->first() ?? new BusinessSetting(['tenant_id' => $selectedTenantId]);
        } else {
            $settings = BusinessSetting::first() ?? new BusinessSetting();
        }
        $timezones = $this->getTimezoneList();
        $themes = $this->getThemeOptions();
        $invoiceTemplates = $this->getInvoiceTemplateOptions();
        $invoicePrintOptions = array_merge(
            $this->getDefaultInvoicePrintOptions(),
            (array) ($settings->invoice_print_options ?? [])
        );
        $previewInvoiceId = Invoice::query()->latest('id')->value('id');

        return view('admin.settings.business', compact(
            'settings',
            'timezones',
            'themes',
            'tenantList',
            'selectedTenantId',
            'invoiceTemplates',
            'invoicePrintOptions',
            'previewInvoiceId'
        ));
    }

    /**
     * Get list of common timezones grouped by region
     */
    private function getTimezoneList()
    {
        return [
            'Asia' => [
                'Asia/Dhaka' => '(UTC+06:00) Dhaka',
                'Asia/Kolkata' => '(UTC+05:30) Kolkata, Mumbai, New Delhi',
                'Asia/Karachi' => '(UTC+05:00) Karachi',
                'Asia/Dubai' => '(UTC+04:00) Dubai',
                'Asia/Riyadh' => '(UTC+03:00) Riyadh',
                'Asia/Singapore' => '(UTC+08:00) Singapore',
                'Asia/Hong_Kong' => '(UTC+08:00) Hong Kong',
                'Asia/Tokyo' => '(UTC+09:00) Tokyo',
                'Asia/Seoul' => '(UTC+09:00) Seoul',
                'Asia/Shanghai' => '(UTC+08:00) Beijing, Shanghai',
                'Asia/Bangkok' => '(UTC+07:00) Bangkok',
                'Asia/Jakarta' => '(UTC+07:00) Jakarta',
                'Asia/Kuala_Lumpur' => '(UTC+08:00) Kuala Lumpur',
                'Asia/Manila' => '(UTC+08:00) Manila',
                'Asia/Colombo' => '(UTC+05:30) Colombo',
                'Asia/Kathmandu' => '(UTC+05:45) Kathmandu',
            ],
            'Europe' => [
                'Europe/London' => '(UTC+00:00) London',
                'Europe/Paris' => '(UTC+01:00) Paris',
                'Europe/Berlin' => '(UTC+01:00) Berlin',
                'Europe/Moscow' => '(UTC+03:00) Moscow',
                'Europe/Amsterdam' => '(UTC+01:00) Amsterdam',
                'Europe/Rome' => '(UTC+01:00) Rome',
                'Europe/Madrid' => '(UTC+01:00) Madrid',
                'Europe/Istanbul' => '(UTC+03:00) Istanbul',
            ],
            'America' => [
                'America/New_York' => '(UTC-05:00) New York',
                'America/Chicago' => '(UTC-06:00) Chicago',
                'America/Denver' => '(UTC-07:00) Denver',
                'America/Los_Angeles' => '(UTC-08:00) Los Angeles',
                'America/Toronto' => '(UTC-05:00) Toronto',
                'America/Sao_Paulo' => '(UTC-03:00) Sao Paulo',
                'America/Mexico_City' => '(UTC-06:00) Mexico City',
            ],
            'Australia' => [
                'Australia/Sydney' => '(UTC+11:00) Sydney',
                'Australia/Melbourne' => '(UTC+11:00) Melbourne',
                'Australia/Brisbane' => '(UTC+10:00) Brisbane',
                'Australia/Perth' => '(UTC+08:00) Perth',
            ],
            'Africa' => [
                'Africa/Cairo' => '(UTC+02:00) Cairo',
                'Africa/Lagos' => '(UTC+01:00) Lagos',
                'Africa/Johannesburg' => '(UTC+02:00) Johannesburg',
                'Africa/Nairobi' => '(UTC+03:00) Nairobi',
            ],
            'Pacific' => [
                'Pacific/Auckland' => '(UTC+13:00) Auckland',
                'Pacific/Fiji' => '(UTC+12:00) Fiji',
                'Pacific/Honolulu' => '(UTC-10:00) Honolulu',
            ],
            'UTC' => [
                'UTC' => '(UTC+00:00) UTC',
            ],
        ];
    }

    private function getThemeOptions(): array
    {
        return config('themes', []);
    }

    private function getInvoiceTemplateOptions(): array
    {
        return [
            'standard' => 'Standard',
            'modern' => 'Modern',
            'simple' => 'Simple',
            'bold' => 'Bold',
            'elegant' => 'Elegant',
            'imaginative' => 'Imaginative',
        ];
    }

    private function getDefaultInvoicePrintOptions(): array
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

    public function update(Request $request)
{
    $invoiceTemplateKeys = implode(',', array_keys($this->getInvoiceTemplateOptions()));
    $rules = [
        'business_name' => 'required|string|max:255',
        'email' => 'nullable|email',
        'phone' => 'required|string',
        'address' => 'nullable|string',
        'bin_number' => 'nullable|string',
        'logo' => 'nullable|image|max:2048',
        'bank_details' => 'nullable|string',
        'return_policy_days' => 'nullable|integer|min:0',
        'return_policy_message' => 'nullable|string',
        'footer_message' => 'nullable|string',
        'customer_qr_expiry_days' => 'nullable|integer|min:1|max:3650',
        'timezone' => 'nullable|string|timezone',
        'theme' => 'nullable|string|in:' . implode(',', array_keys(config('themes', []))),
        'weekend_days' => 'nullable|array',
        'weekend_days.*' => 'string|in:Friday,Saturday,Sunday,Monday,Tuesday,Wednesday,Thursday',
        'invoice_template' => 'nullable|string|in:' . $invoiceTemplateKeys,
        'invoice_print_options' => 'nullable|array',
        'invoice_print_options.show_company_phone' => 'nullable|boolean',
        'invoice_print_options.show_company_email' => 'nullable|boolean',
        'invoice_print_options.show_company_address' => 'nullable|boolean',
        'invoice_print_options.show_company_bin' => 'nullable|boolean',
        'invoice_print_options.show_bank_details' => 'nullable|boolean',
        'invoice_print_options.show_terms' => 'nullable|boolean',
        'invoice_print_options.show_footer_message' => 'nullable|boolean',
        'invoice_print_options.show_customer_qr' => 'nullable|boolean',
        'invoice_print_options.show_signatures' => 'nullable|boolean',
        'invoice_print_options.invoice_phone_override' => 'nullable|string|max:255',
    ];

    if (auth()->user()?->hasRole('Super Admin')) {
        $rules['tenant_id'] = 'required|integer|exists:tenants,id';
    }

    $validated = $request->validate($rules);

    $selectedTenantId = TenantContext::currentId();
    if (auth()->user()?->hasRole('Super Admin') && $request->filled('tenant_id')) {
        $selectedTenantId = (int) $request->input('tenant_id');
    }

    if (auth()->user()?->hasRole('Super Admin') && $selectedTenantId) {
        $settings = BusinessSetting::withoutGlobalScope('tenant')
            ->where('tenant_id', $selectedTenantId)
            ->first() ?? new BusinessSetting(['tenant_id' => $selectedTenantId]);
    } else {
        $settings = BusinessSetting::first() ?? new BusinessSetting();
    }

    // Set default value for return_policy_days if it's null
    $validated['return_policy_days'] = $request->return_policy_days ?? 90;
    $validated['customer_qr_expiry_days'] = $request->customer_qr_expiry_days ?? 30;
    $validated['theme'] = $request->theme ?? ($settings->theme ?? 'indigo');
    $validated['weekend_days'] = $request->weekend_days ?? ($settings->weekend_days ?? ['Friday']);
    $validated['invoice_template'] = $request->invoice_template ?? ($settings->invoice_template ?? 'standard');

    $booleanOptionKeys = [
        'show_company_phone',
        'show_company_email',
        'show_company_address',
        'show_company_bin',
        'show_bank_details',
        'show_terms',
        'show_footer_message',
        'show_customer_qr',
        'show_signatures',
    ];

    $invoicePrintOptions = array_merge(
        $this->getDefaultInvoicePrintOptions(),
        (array) ($settings->invoice_print_options ?? []),
        (array) $request->input('invoice_print_options', [])
    );

    foreach ($booleanOptionKeys as $key) {
        $invoicePrintOptions[$key] = $request->boolean("invoice_print_options.$key");
    }

    $invoicePrintOptions['invoice_phone_override'] = trim((string) $request->input('invoice_print_options.invoice_phone_override', ''));
    $validated['invoice_print_options'] = $invoicePrintOptions;

    // Handle logo upload
    if ($request->hasFile('logo')) {
        if ($settings->logo && Storage::exists('public/'.$settings->logo)) {
            Storage::delete('public/'.$settings->logo);
        }
        
        $logoPath = $request->file('logo')->store('logos', 'public');
        $validated['logo'] = $logoPath;
    }

    $weekendDays = $validated['weekend_days'];
    unset($validated['weekend_days']);

    $settings->fill($validated);
    if ($settings->hasCast('weekend_days', ['array', 'json', 'object', 'collection'])) {
        $settings->weekend_days = $weekendDays;
    } else {
        $settings->weekend_days = is_array($weekendDays)
            ? json_encode(array_values($weekendDays))
            : $weekendDays;
    }
    if ($selectedTenantId) {
        $settings->tenant_id = $selectedTenantId;
    }
    $settings->save();

    if (auth()->user()?->hasRole('Super Admin') && $selectedTenantId) {
        return redirect()
            ->route('business-settings.index', ['tenant_id' => $selectedTenantId])
            ->with('success', 'Business settings updated successfully!');
    }

    return redirect()->back()->with('success', 'Business settings updated successfully!');
}
}
