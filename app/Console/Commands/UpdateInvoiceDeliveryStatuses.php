<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use Illuminate\Console\Command;

class UpdateInvoiceDeliveryStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update delivery statuses for all invoices based on challan data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $invoices = Invoice::with(['items', 'challans'])->get();
        
        foreach ($invoices as $invoice) {
            $newStatus = $invoice->calculateDeliveryStatus();
            
            if ($newStatus != $invoice->delivery_status) {
                $invoice->delivery_status = $newStatus;
                $invoice->save();
            }
        }
        
        $this->info('Successfully updated delivery statuses for all invoices.');
    }
}
