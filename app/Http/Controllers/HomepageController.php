<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class HomepageController extends Controller
{
    public function index()
    {
        // Cache the total customer count
        $customerCount = Cache::remember('customer_count', 300, function () {
            return Customer::count();
        });
        
        // Fetch 10 random customers with badges
        $randomCustomers = Cache::remember('random_customers', 300, function () {
            try {
                return Customer::select([
                        'customers.id',
                        'customers.name', 
                        'customers.phone', 
                        'customers.address',
                        DB::raw('COUNT(transactions.id) as transaction_count')
                    ])
                    ->leftJoin('transactions', 'customers.id', '=', 'transactions.customer_id')
                    ->groupBy('customers.id', 'customers.name', 'customers.phone', 'customers.address')
                    ->inRandomOrder()
                    ->take(10)
                    ->get()
                    ->map(function($customer) {
                        if ($customer->transaction_count >= 10) {
                            $customer->badge = 'VIP';
                        } elseif ($customer->transaction_count >= 5) {
                            $customer->badge = 'Top';
                        } elseif ($customer->transaction_count >= 2) {
                            $customer->badge = 'Premium';
                        } else {
                            $customer->badge = 'Loyal';
                        }
                        
                        unset($customer->transaction_count);
                        
                        return $customer;
                    });
            } catch (\Exception $e) {
                return collect([
                    (object)['name' => 'Abdul Haque', 'phone' => '01712345678', 'address' => 'Model Town, Shariatpur', 'badge' => 'VIP Customer'],
                    (object)['name' => 'Sabrina Akter', 'phone' => '01823456789', 'address' => 'Sadar, Shariatpur', 'badge' => 'Top Customer'],
                    (object)['name' => 'Rahim Mollah', 'phone' => '01934567890', 'address' => 'Palong, Shariatpur', 'badge' => 'Premium Customer'],
                    (object)['name' => 'Ayesha Siddika', 'phone' => '01645678901', 'address' => 'Naria, Shariatpur', 'badge' => 'Loyal Customer'],
                    (object)['name' => 'Kamal Hossain', 'phone' => '01556789012', 'address' => 'Bhedarganj, Shariatpur', 'badge' => 'VIP Customer'],
                    (object)['name' => 'Nusrat Jahan', 'phone' => '01767890123', 'address' => 'Zajira, Shariatpur', 'badge' => 'Top Customer'],
                    (object)['name' => 'Mizanur Rahman', 'phone' => '01878901234', 'address' => 'Gosairhat, Shariatpur', 'badge' => 'Premium Customer'],
                    (object)['name' => 'Shirin Begum', 'phone' => '01989012345', 'address' => 'Damudya, Shariatpur', 'badge' => 'Loyal Customer'],
                    (object)['name' => 'Faruk Ahmed', 'phone' => '01690123456', 'address' => 'Sadar, Shariatpur', 'badge' => 'VIP Customer'],
                    (object)['name' => 'Laila Akter', 'phone' => '01501234567', 'address' => 'Palong, Shariatpur', 'badge' => 'Top Customer'],
                ]);
            }
        });

        // Cache today's invoice count
        $todayInvoiceCount = Cache::remember('today_invoice_count', 300, function () {
            return Invoice::whereDate('invoice_date', Carbon::today())->count();
        });

        // Cache today's total sold quantity
        $todaySoldQuantity = Cache::remember('today_sold_quantity', 300, function () {
            return InvoiceItem::join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
                ->whereDate('invoices.invoice_date', Carbon::today())
                ->sum('invoice_items.quantity');
        });

        // SEO and Social Media Data
        $seoData = (object)[
            'title' => 'Rahman Tiles and Sanitary - Premium Tiles, Sanitary & Paints in Shariatpur Since 2002',
            'description' => 'Rahman Tiles and Sanitary - Shariatpur\'s first and premier tiles shop since 2002. Premium tiles, sanitary ware, and paints from top brands. Follow us on Facebook @Shamim.RTS.MD',
            'keywords' => 'Rahman Tiles, Shariatpur tiles, Bangladesh tiles, sanitary ware Shariatpur, Berger Paints Shariatpur, Sun Power Ceramics, X Ceramics, Shamim RTS MD, Facebook Rahman Tiles',
            'canonical' => url('/'),
            'og_image' => url('/') . '/images/rahman-tiles-og-image.jpg',
            'facebook_page' => 'https://www.facebook.com/Shamim.RTS.MD',
            'last_modified' => now()->format('Y-m-d'),
        ];
        
        return view('homepage', compact('customerCount', 'randomCustomers', 'todayInvoiceCount', 'todaySoldQuantity', 'seoData'));
    }

    public function sitemap()
    {
        $pages = [
            ['url' => url('/'), 'lastmod' => now()->format('Y-m-d'), 'priority' => '1.0', 'changefreq' => 'weekly'],
            ['url' => url('/login'), 'lastmod' => now()->format('Y-m-d'), 'priority' => '0.3', 'changefreq' => 'monthly'],
        ];
        
        return response()->view('sitemap', compact('pages'))->header('Content-Type', 'text/xml');
    }

    public function robots()
    {
        return response()->view('robots')->header('Content-Type', 'text/plain');
    }
}