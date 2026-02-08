<?php
// File: app/Http/Requests/AddTransactionRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddTransactionRequest extends FormRequest
{
    public function authorize()
    {
        return \Illuminate\Support\Facades\Auth::check();
    }

    public function rules()
    {
        return [
            'transaction_id' => 'nullable|exists:transactions,id',
            'transaction_type' => [
                'required',
                Rule::in(['sale', 'return', 'expense', 'deposit', 'withdrawal'])
            ],
            'payment_method' => [
                'required',
                Rule::in(['cash', 'bank', 'mobile_bank', 'cheque', 'card'])
            ],
            'amount' => 'required|numeric|min:0.01|max:999999.99',
            'reference_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:500',
        ];
    }

    public function messages()
    {
        return [
            'amount.min' => 'Amount must be at least ৳0.01',
            'amount.max' => 'Amount cannot exceed ৳999,999.99',
            'transaction_type.in' => 'Invalid transaction type selected.',
            'payment_method.in' => 'Invalid payment method selected.',
            'amount.required' => 'Transaction amount is required.',
            'amount.numeric' => 'Amount must be a valid number.',
            'transaction_type.required' => 'Transaction type is required.',
            'payment_method.required' => 'Payment method is required.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $cashRegister = $this->route('cashRegister');
            
            if ($cashRegister && $cashRegister->status !== 'open') {
                $validator->errors()->add('general', 'Cannot add transactions to a closed register.');
            }

            // Check if linking to existing transaction that's already linked
            if ($this->transaction_id) {
                $existingLink = \App\Models\CashRegisterTransaction::where('transaction_id', $this->transaction_id)
                    ->where(function($query) {
                        $query->whereNull('notes')
                              ->orWhere('notes', 'not like', '%[VOIDED]%');
                    })
                    ->exists();
                    
                if ($existingLink) {
                    $validator->errors()->add('transaction_id', 'This transaction is already linked to another cash register entry.');
                }
            }
        });
    }
}