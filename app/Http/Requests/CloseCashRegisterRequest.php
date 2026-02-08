<?php
// File: app/Http/Requests/CloseCashRegisterRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CloseCashRegisterRequest extends FormRequest
{
    public function authorize()
    {
        return \Illuminate\Support\Facades\Auth::check();
    }

    public function rules()
    {
        $actualBalance = $this->input('actual_closing_balance', 0);
        $expectedBalance = $this->route('cashRegister')->expected_closing_balance ?? 0;
        $variance = abs($actualBalance - $expectedBalance);
        $hasVariance = $variance >= 0.01;

        return [
            'actual_closing_balance' => 'required|numeric|min:0|max:9999999.99',
            'closing_notes' => $hasVariance ? 'required|string|min:10|max:1000' : 'nullable|string|max:1000',
        ];
    }

    public function messages()
    {
        return [
            'closing_notes.required' => 'Explanation is required when there is a variance in cash count.',
            'closing_notes.min' => 'Please provide a detailed explanation (at least 10 characters).',
            'actual_closing_balance.max' => 'Closing balance cannot exceed ৳9,999,999.99',
            'actual_closing_balance.required' => 'Actual closing balance is required.',
            'actual_closing_balance.numeric' => 'Actual closing balance must be a valid number.',
            'actual_closing_balance.min' => 'Actual closing balance cannot be negative.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $cashRegister = $this->route('cashRegister');
            
            if ($cashRegister && $cashRegister->status !== 'open') {
                $validator->errors()->add('general', 'This cash register is not open and cannot be closed.');
            }
        });
    }
}