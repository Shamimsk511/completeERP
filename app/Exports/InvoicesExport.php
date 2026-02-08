<?php

namespace App\Exports;

use App\Models\Invoice;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class InvoicesExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Invoice::with(['customer'])->get();
    }

    public function headings(): array
    {
        return [
            'Invoice Number',
            'Date',
            'Customer',
            'Type',
            'Total Amount',
            'Paid Amount',
            'Due Amount',
            'Payment Status',
            'Delivery Status',
            'Created At'
        ];
    }

    public function map($invoice): array
    {
        return [
            $invoice->invoice_number,
            $invoice->invoice_date->format('d-m-Y'),
            $invoice->customer->name,
            ucfirst($invoice->invoice_type),
            $invoice->total_amount,
            $invoice->paid_amount,
            $invoice->due_amount,
            ucfirst($invoice->payment_status),
            ucfirst($invoice->delivery_status),
            $invoice->created_at->format('d-m-Y H:i:s')
        ];
    }
}
