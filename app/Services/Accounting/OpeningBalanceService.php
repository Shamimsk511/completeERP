<?php

namespace App\Services\Accounting;

use App\Models\Company;
use App\Models\Payee;
use App\Models\Accounting\Account;
use App\Services\PayeeAccountService;

class OpeningBalanceService
{
    protected GeneralLedgerService $glService;
    protected AutoPostingService $autoPostingService;
    protected PayeeAccountService $payeeAccountService;

    public function __construct(
        GeneralLedgerService $glService,
        AutoPostingService $autoPostingService,
        PayeeAccountService $payeeAccountService
    )
    {
        $this->glService = $glService;
        $this->autoPostingService = $autoPostingService;
        $this->payeeAccountService = $payeeAccountService;
    }

    public function postCompanyOpeningBalance(Company $company, float $amount, string $balanceType, ?string $asOf = null, ?int $userId = null): bool
    {
        if (!$company->isSupplierType()) {
            return false;
        }

        $amount = round($amount, 2);
        $balanceType = $balanceType === 'debit' ? 'debit' : 'credit';

        $supplierAccount = $this->autoPostingService->getOrCreateSupplierAccount($company);
        $supplierAccount->update([
            'opening_balance' => $amount,
            'opening_balance_type' => $balanceType,
        ]);
        $this->glService->updateAccountBalance($supplierAccount);

        return true;
    }

    public function postPayeeOpeningBalance(Payee $payee, float $amount, string $balanceType, ?string $asOf = null, ?int $userId = null): bool
    {
        $amount = round($amount, 2);
        $balanceType = $balanceType === 'debit' ? 'debit' : 'credit';

        $account = $payee->account ?: $this->payeeAccountService->ensureAccountForPayee($payee);
        $account->update([
            'opening_balance' => $amount,
            'opening_balance_type' => $balanceType,
        ]);
        $this->glService->updateAccountBalance($account);

        return true;
    }
}
