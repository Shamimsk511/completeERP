<?php

namespace App\Imports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class CustomersImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new Customer([
            'name'               => $row['name'],
            'phone'              => $row['phone'],
            'address'            => $row['address'] ?? null,
            'opening_balance'    => $row['opening_balance'] ?? 0,
            'outstanding_balance' => $row['outstanding_balance'] ?? $row['opening_balance'] ?? 0,
        ]);
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'opening_balance' => 'nullable|numeric',
            'outstanding_balance' => 'nullable|numeric',
        ];
    }
}
