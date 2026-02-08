<?php
// File: app/Http/Requests/OpenCashRegisterRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class OpenCashRegisterRequest extends FormRequest
{
    public function authorize()
    {
        return Auth::check();
    }

    public function rules()
    {
        return [
            'opening_balance' => 'required|numeric|min:0|max:999999.99',
            'security_pin' => 'required|digits:4',
            'security_pin_confirmation' => 'required|same:security_pin',
            'notes' => 'nullable|string|max:1000',
            'terminal' => 'nullable|string|max:50',
        ];
    }

    public function messages()
    {
        return [
            'security_pin.digits' => 'Security PIN must be exactly 4 digits.',
            'security_pin_confirmation.same' => 'PIN confirmation does not match.',
            'opening_balance.max' => 'Opening balance cannot exceed ৳999,999.99',
            'opening_balance.required' => 'Opening balance is required.',
            'opening_balance.numeric' => 'Opening balance must be a valid number.',
            'opening_balance.min' => 'Opening balance cannot be negative.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check if user already has an open register
            $existingRegister = \App\Models\CashRegister::where('user_id', Auth::id())
                ->whereIn('status', ['open', 'suspended'])
                ->exists();

            if ($existingRegister) {
                $validator->errors()->add('general', 'You already have an active cash register.');
            }
        });
    }
}