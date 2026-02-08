<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Transaction;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    public function run()
    {
        // Ensure we have customers
        if (Customer::count() == 0) {
            Customer::create([
                'name' => 'John Doe',
                'phone' => '1234567890',
                'address' => '123 Main St',
                'opening_balance' => 1000,
                'outstanding_balance' => 1000,
            ]);
            
            Customer::create([
                'name' => 'Jane Smith',
                'phone' => '0987654321',
                'address' => '456 Oak Ave',
                'opening_balance' => 500,
                'outstanding_balance' => 500,
            ]);
        }
        
        $customers = Customer::all();
        
        foreach ($customers as $customer) {
            // Create some debit transactions (customer paying)
            for ($i = 0; $i < 3; $i++) {
                $amount = rand(50, 200);
                Transaction::create([
                    'customer_id' => $customer->id,
                    'type' => 'debit',
                    'purpose' => 'Payment for invoice #' . rand(1000, 9999),
                    'method' => ['cash', 'bank', 'mobile_bank', 'cheque'][rand(0, 3)],
                    'amount' => $amount,
                    'note' => 'Sample debit transaction',
                    'created_at' => now()->subDays(rand(1, 30)),
                ]);
                
                $customer->outstanding_balance -= $amount;
            }
            
            // Create some credit transactions (customer receiving)
            for ($i = 0; $i < 2; $i++) {
                $amount = rand(100, 300);
                Transaction::create([
                    'customer_id' => $customer->id,
                    'type' => 'credit',
                    'purpose' => 'Product purchase #' . rand(1000, 9999),
                    'method' => ['cash', 'bank', 'mobile_bank', 'cheque'][rand(0, 3)],
                    'amount' => $amount,
                    'note' => 'Sample credit transaction',
                    'created_at' => now()->subDays(rand(1, 30)),
                ]);
                
                $customer->outstanding_balance += $amount;
            }
            
            $customer->save();
        }
    }
}
