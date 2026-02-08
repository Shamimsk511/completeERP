<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class CustomersTemplateExport implements FromArray, WithHeadings, ShouldAutoSize
{
    /**
     * @return array
     */
    public function array(): array
    {
        return [
            // Example row
            [
                'name' => 'John Doe',
                'phone' => '01700000000',
                'address' => 'Dhaka, Bangladesh',
                'opening_balance' => 1000,
                'outstanding_balance' => 1000,
            ],
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'name',
            'phone',
            'address',
            'opening_balance',
            'outstanding_balance',
        ];
    }
}
