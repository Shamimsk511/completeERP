<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Customer;

class GenerateCustomerUsernames extends Command
{
    protected $signature = 'customers:generate-usernames {--show-only : Only show usernames without updating}';
    protected $description = 'Generate and display usernames for existing customers';

    public function handle()
    {
        $customers = Customer::orderBy('id')->get();
        
        $this->info("Customer Login Credentials:");
        $this->info("=" . str_repeat("=", 50));
        
        $headers = ['ID', 'Name', 'Phone', 'Username', 'Status'];
        $rows = [];
        
        foreach ($customers as $customer) {
            $status = $customer->phone ? 'Can Login' : 'No Phone';
            
            $rows[] = [
                $customer->id,
                $customer->name,
                $customer->phone ?: 'N/A',
                $customer->username,
                $status
            ];
        }
        
        $this->table($headers, $rows);
        
        $this->info("\nLogin Instructions:");
        $this->line("• Username: First name + Customer ID (e.g., 'john123')");
        $this->line("• Password: Customer's phone number");
        $this->line("• Login URL: " . url('/customer/login'));
        
        $canLoginCount = $customers->whereNotNull('phone')->count();
        $totalCount = $customers->count();
        
        $this->info("\nSummary:");
        $this->line("• Total customers: {$totalCount}");
        $this->line("• Can login: {$canLoginCount}");
        $this->line("• Cannot login: " . ($totalCount - $canLoginCount) . " (missing phone)");
        
        return 0;
    }
}