<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CustomerPasswordController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:customer');
    }

    public function show()
    {
        return view('customer.change-password');
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $customer = Auth::guard('customer')->user();
        if (!$customer) {
            return redirect()->route('customer.login');
        }

        $customer->password = Hash::make($validated['password']);
        $customer->password_changed_at = now();
        $customer->password_change_skipped_at = null;
        $customer->save();

        $request->session()->forget('customer_password_prompt');

        return redirect()->route('customer.dashboard')
            ->with('success', 'Your password has been updated.');
    }

    public function skip(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        if (!$customer) {
            return redirect()->route('customer.login');
        }

        $customer->password_change_skipped_at = now();
        $customer->save();

        $request->session()->forget('customer_password_prompt');

        return redirect()->back()
            ->with('info', 'You can set a password later from your profile.');
    }
}
