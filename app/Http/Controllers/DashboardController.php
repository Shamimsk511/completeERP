<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Time filter handling
        $timeFilter = $request->input('time_filter', 'month'); // Default to current month
        $startDate = null;
        $endDate = null;
        
        if ($timeFilter === 'custom') {
            $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : Carbon::now()->startOfMonth();
            $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : Carbon::now();
        } elseif ($timeFilter === 'year') {
            $startDate = Carbon::now()->startOfYear();
            $endDate = Carbon::now();
        } else { // month
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now();
        }
        
        $cacheKey = sprintf(
            'dashboard.summary.%s.%s.%s',
            $timeFilter,
            $startDate->toDateString(),
            $endDate->toDateString()
        );

        if (config('perf.enabled')) {
            $summary = Cache::remember($cacheKey, config('perf.ttl.dashboard'), function () use ($startDate, $endDate) {
                $invoiceQuery = Invoice::whereBetween('invoice_date', [$startDate, $endDate]);

                return [
                    'totalInvoices' => $invoiceQuery->count(),
                    'totalInvoiceAmount' => $invoiceQuery->sum('total'),
                    'totalBalanceDue' => Customer::sum('outstanding_balance'),
                    'totalProductQuantity' => Product::sum('current_stock'),
                    'latestInvoices' => Invoice::with('customer')
                        ->orderBy('invoice_date', 'desc')
                        ->take(5)
                        ->get(),
                    'topStockItems' => Product::orderBy('current_stock', 'desc')
                        ->take(10)
                        ->get(),
                ];
            });
        } else {
            $invoiceQuery = Invoice::whereBetween('invoice_date', [$startDate, $endDate]);
            $summary = [
                'totalInvoices' => $invoiceQuery->count(),
                'totalInvoiceAmount' => $invoiceQuery->sum('total'),
                'totalBalanceDue' => Customer::sum('outstanding_balance'),
                'totalProductQuantity' => Product::sum('current_stock'),
                'latestInvoices' => Invoice::with('customer')
                    ->orderBy('invoice_date', 'desc')
                    ->take(5)
                    ->get(),
                'topStockItems' => Product::orderBy('current_stock', 'desc')
                    ->take(10)
                    ->get(),
            ];
        }
        
        return view('dashboard', array_merge($summary, [
            'timeFilter' => $timeFilter,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]));
    }
}
