<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\Account;
use App\Models\Accounting\AccountGroup;
use App\Models\Accounting\BankTransaction;
use App\Services\Accounting\GeneralLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BankController extends Controller
{
    protected GeneralLedgerService $glService;

    public function __construct(GeneralLedgerService $glService)
    {
        $this->glService = $glService;
        $this->middleware('permission:account-list')->only(['index', 'show']);
        $this->middleware('permission:account-create')->only(['create', 'store']);
        $this->middleware('permission:account-edit')->only(['edit', 'update']);
        $this->middleware('permission:account-delete')->only(['destroy']);
    }

    public function index()
    {
        $banks = Account::where('account_type', 'bank')
            ->orderBy('name')
            ->get();

        return view('accounting.banks.index', compact('banks'));
    }

    public function create()
    {
        $codePreview = $this->generateBankCode();

        return view('accounting.banks.create', compact('codePreview'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:50',
            'ifsc_code' => 'nullable|string|max:20',
            'opening_balance' => 'nullable|numeric|min:0',
            'opening_balance_type' => 'nullable|in:debit,credit',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $bankGroupId = AccountGroup::where('code', 'BANK-ACCOUNTS')->value('id')
            ?? AccountGroup::where('nature', 'assets')->orderBy('display_order')->value('id');

        if (!$bankGroupId) {
            return back()->with('error', 'Bank account group not found. Please seed chart of accounts.')
                ->withInput();
        }

        $openingBalance = $validated['opening_balance'] ?? 0;
        $openingType = $validated['opening_balance_type'] ?? 'debit';
        $isActive = $request->has('is_active');

        DB::transaction(function () use ($validated, $bankGroupId, $openingBalance, $openingType, $isActive) {
            Account::create([
                'name' => $validated['name'],
                'code' => $this->generateBankCode(),
                'account_group_id' => $bankGroupId,
                'account_type' => 'bank',
                'opening_balance' => $openingBalance,
                'opening_balance_type' => $openingType,
                'current_balance' => $openingBalance,
                'current_balance_type' => $openingType,
                'bank_name' => $validated['bank_name'] ?? null,
                'bank_account_number' => $validated['bank_account_number'] ?? null,
                'ifsc_code' => $validated['ifsc_code'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'is_active' => $isActive,
                'is_system' => false,
            ]);
        });

        return redirect()->route('accounting.banks.index')
            ->with('success', 'Bank account created successfully.');
    }

    public function show(Account $bank)
    {
        $this->ensureBankAccount($bank);

        $transactions = BankTransaction::with('counterAccount')
            ->where('bank_account_id', $bank->id)
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15);

        return view('accounting.banks.show', compact('bank', 'transactions'));
    }

    public function edit(Account $bank)
    {
        $this->ensureBankAccount($bank);

        if ($bank->is_system) {
            return redirect()->route('accounting.banks.index')
                ->with('error', 'System bank accounts cannot be edited.');
        }

        return view('accounting.banks.edit', compact('bank'));
    }

    public function update(Request $request, Account $bank)
    {
        $this->ensureBankAccount($bank);

        if ($bank->is_system) {
            return redirect()->route('accounting.banks.index')
                ->with('error', 'System bank accounts cannot be modified.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:50',
            'ifsc_code' => 'nullable|string|max:20',
            'opening_balance' => 'nullable|numeric|min:0',
            'opening_balance_type' => 'nullable|in:debit,credit',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $bank->update([
            'name' => $validated['name'],
            'bank_name' => $validated['bank_name'] ?? null,
            'bank_account_number' => $validated['bank_account_number'] ?? null,
            'ifsc_code' => $validated['ifsc_code'] ?? null,
            'opening_balance' => $validated['opening_balance'] ?? 0,
            'opening_balance_type' => $validated['opening_balance_type'] ?? 'debit',
            'notes' => $validated['notes'] ?? null,
            'is_active' => $request->has('is_active'),
        ]);

        $this->glService->updateAccountBalance($bank);

        return redirect()->route('accounting.banks.show', $bank)
            ->with('success', 'Bank account updated successfully.');
    }

    public function destroy(Account $bank)
    {
        $this->ensureBankAccount($bank);

        if (!$bank->canDelete()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete this bank account. It may be a system account or have ledger entries.',
            ], 422);
        }

        $bank->delete();

        return response()->json([
            'success' => true,
            'message' => 'Bank account deleted successfully.',
        ]);
    }

    protected function ensureBankAccount(Account $bank): void
    {
        if ($bank->account_type !== 'bank') {
            abort(404);
        }
    }

    protected function generateBankCode(): string
    {
        $prefix = 'BANK-';
        $codes = Account::where('code', 'like', $prefix . '%')->pluck('code');
        $max = 0;

        foreach ($codes as $code) {
            $suffix = substr($code, strlen($prefix));
            if (ctype_digit($suffix)) {
                $max = max($max, (int) $suffix);
            }
        }

        $next = $max + 1;
        $candidate = $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);

        while (Account::where('code', $candidate)->exists()) {
            $next++;
            $candidate = $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
        }

        return $candidate;
    }
}
