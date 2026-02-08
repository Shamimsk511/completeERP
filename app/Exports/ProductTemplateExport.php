<?php

namespace App\Exports;

use App\Models\Company;
use App\Models\Category;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductTemplateExport implements 
    FromCollection, 
    WithHeadings, 
    WithMapping,
    WithStrictNullComparison,
    WithTitle,
    WithStyles
{
    public function collection()
    {
        // Sample data
        return collect([
            [
                'name' => 'Example Product',
                'description' => 'Product description here',
                'company_id' => '1',
                'category_id' => '1',
                'opening_stock' => 10,
                'purchase_price' => 100,
                'sale_price' => 150,
            ]
        ]);
    }

    public function headings(): array
    {
        return [
            'name',
            'description',
            'company_id',
            'category_id',
            'opening_stock',
            'purchase_price',
            'sale_price',
        ];
    }

    public function map($row): array
    {
        return [
            $row['name'],
            $row['description'],
            $row['company_id'],
            $row['category_id'],
            $row['opening_stock'],
            $row['purchase_price'],
            $row['sale_price'],
        ];
    }
    
    public function title(): string
    {
        return 'Product Template';
    }
    
    public function styles(Worksheet $sheet)
    {
        // Style the headers
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);
        $sheet->getStyle('A1:G1')->getFont()->setSize(12);
        
        // Add reference data in separate section
        $row = 4;
        $sheet->setCellValue("A{$row}", "REFERENCE DATA:");
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $row++;
        
        $sheet->setCellValue("A{$row}", "Companies:");
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $row++;
        
        $companies = Company::select('id', 'name')->orderBy('name')->get();
        foreach ($companies as $company) {
            $sheet->setCellValue("A{$row}", "ID: {$company->id}");
            $sheet->setCellValue("B{$row}", $company->name);
            $row++;
        }
        
        $row++;
        $sheet->setCellValue("A{$row}", "Categories:");
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $row++;
        
        $categories = Category::select('id', 'name')->orderBy('name')->get();
        foreach ($categories as $category) {
            $sheet->setCellValue("A{$row}", "ID: {$category->id}");
            $sheet->setCellValue("B{$row}", $category->name);
            $row++;
        }
        
        // Auto-size columns
        foreach (range('A', 'G') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        return $sheet;
    }
}
