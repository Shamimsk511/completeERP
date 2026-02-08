<?php

namespace App\Exports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CustomersExport implements FromCollection, WithHeadings, WithMapping
{
    protected $customers;

    public function __construct($customers = null)
    {
        $this->customers = $customers;
    }

    public function collection()
    {
        if ($this->customers) {
            return $this->customers;
        }
        
        return Customer::all();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Phone',
            'Address',
            'Opening Balance',
            'Outstanding Balance',
            'Created At'
        ];
    }

    public function map($customer): array
    {
        return [
            $customer->id,
            $customer->name,
            $customer->phone,
            $customer->address,
            $customer->opening_balance,
            $customer->outstanding_balance,
            $customer->created_at->format('Y-m-d H:i:s')
        ];
    }
}
