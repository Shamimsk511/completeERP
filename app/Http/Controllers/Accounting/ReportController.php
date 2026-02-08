<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\Account;
use App\Services\Accounting\FinancialReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    protected FinancialReportService $reportService;

    public function __construct(FinancialReportService $reportService)
    {
        $this->reportService = $reportService;
        $this->middleware('permission:accounting-reports');
    }

    /**
     * Trial Balance Report
     */
    public function trialBalance(Request $request)
    {
        $date = $request->get('date', now()->toDateString());
        $trialBalance = $this->reportService->getTrialBalance($date);

        if ($request->has('print')) {
            return view('accounting.reports.trial-balance-print', compact('trialBalance', 'date'));
        }

        return view('accounting.reports.trial-balance', compact('trialBalance', 'date'));
    }

    /**
     * Balance Sheet Report
     */
    public function balanceSheet(Request $request)
    {
        $date = $request->get('date', now()->toDateString());
        $balanceSheet = $this->reportService->getBalanceSheet($date);

        if ($request->has('print')) {
            return view('accounting.reports.balance-sheet-print', compact('balanceSheet', 'date'));
        }

        return view('accounting.reports.balance-sheet', compact('balanceSheet', 'date'));
    }

    /**
     * Profit & Loss Report
     */
    public function profitAndLoss(Request $request)
    {
        $fromDate = $request->get('from_date', now()->startOfYear()->toDateString());
        $toDate = $request->get('to_date', now()->toDateString());
        $profitLoss = $this->reportService->getProfitAndLoss($fromDate, $toDate);

        if ($request->has('print')) {
            return view('accounting.reports.profit-loss-print', compact('profitLoss', 'fromDate', 'toDate'));
        }

        return view('accounting.reports.profit-loss', compact('profitLoss', 'fromDate', 'toDate'));
    }

    /**
     * Day Book Report
     */
    public function dayBook(Request $request)
    {
        $date = $request->get('date', now()->toDateString());
        $dayBook = $this->reportService->getDayBook($date);

        if ($request->has('print')) {
            return view('accounting.reports.day-book-print', compact('dayBook', 'date'));
        }

        return view('accounting.reports.day-book', compact('dayBook', 'date'));
    }

    /**
     * Cash Book Report
     */
    public function cashBook(Request $request)
    {
        $fromDate = $request->get('from_date', now()->startOfMonth()->toDateString());
        $toDate = $request->get('to_date', now()->toDateString());
        $cashBook = $this->reportService->getCashBook($fromDate, $toDate);

        if ($request->has('print')) {
            return view('accounting.reports.cash-book-print', compact('cashBook', 'fromDate', 'toDate'));
        }

        return view('accounting.reports.cash-book', compact('cashBook', 'fromDate', 'toDate'));
    }

    /**
     * Bank Book Report
     */
    public function bankBook(Request $request)
    {
        $fromDate = $request->get('from_date', now()->startOfMonth()->toDateString());
        $toDate = $request->get('to_date', now()->toDateString());
        $bankAccountId = $request->get('bank_account_id');

        $data = $this->reportService->getBankBook($fromDate, $toDate, $bankAccountId);
        $bankAccounts = Account::bankAccounts()->orderBy('name')->get();

        if ($request->has('print')) {
            return view('accounting.reports.bank-book-print', compact('data', 'fromDate', 'toDate', 'bankAccounts', 'bankAccountId'));
        }

        return view('accounting.reports.bank-book', compact('data', 'fromDate', 'toDate', 'bankAccounts', 'bankAccountId'));
    }
}
