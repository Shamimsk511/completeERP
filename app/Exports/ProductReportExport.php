<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductReportExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $data;
    protected $reportType;

    public function __construct(array $data, string $reportType)
    {
        $this->data = $data;
        $this->reportType = $reportType;
    }

    public function array(): array
    {
        $formattedData = [];
        
        foreach ($this->data as $item) {
            $row = [];
            
            switch ($this->reportType) {
                case 'sales':
                    $row = [
                        $item['product']['id'] ?? '',
                        $item['product']['name'] ?? '',
                        $item['product']['category']['name'] ?? 'N/A',
                        $item['product']['company']['name'] ?? 'N/A',
                        $item['quantity'] ?? 0,
                        $item['amount'] ?? 0,
                        $item['invoices'] ?? 0
                    ];
                    break;
                    
                case 'returns':
                    $row = [
                        $item['product']['id'] ?? '',
                        $item['product']['name'] ?? '',
                        $item['product']['category']['name'] ?? 'N/A',
                        $item['product']['company']['name'] ?? 'N/A',
                        $item['quantity'] ?? 0,
                        $item['amount'] ?? 0,
                        $item['returns'] ?? 0
                    ];
                    break;
                    
                case 'purchases':
                    $row = [
                        $item['product']['id'] ?? '',
                        $item['product']['name'] ?? '',
                        $item['product']['category']['name'] ?? 'N/A',
                        $item['product']['company']['name'] ?? 'N/A',
                        $item['quantity'] ?? 0,
                        $item['amount'] ?? 0,
                        $item['average_price'] ?? 0,
                        $item['purchases'] ?? 0
                    ];
                    break;
                    
                case 'deliveries':
                    $row = [
                        $item['product']['id'] ?? '',
                        $item['product']['name'] ?? '',
                        $item['product']['category']['name'] ?? 'N/A',
                        $item['product']['company']['name'] ?? 'N/A',
                        $item['quantity'] ?? 0,
                        $item['deliveries'] ?? 0
                    ];
                    break;
                    
                case 'consolidated':
                    $row = [
                        $item['product']['id'] ?? '',
                        $item['product']['name'] ?? '',
                        $item['product']['category']['name'] ?? 'N/A',
                        $item['product']['company']['name'] ?? 'N/A',
                        $item['sales'] ?? 0,
                        $item['returns'] ?? 0,
                        $item['purchases'] ?? 0,
                        $item['other_deliveries'] ?? 0,
                        $item['net_change'] ?? 0,
                        $item['product']['current_stock'] ?? 0
                    ];
                    break;
            }
            
            $formattedData[] = $row;
        }
        
        return $formattedData;
    }

    public function headings(): array
    {
        switch ($this->reportType) {
            case 'sales':
                return [
                    'ID',
                    'Product',
                    'Category',
                    'Company',
                    'Quantity Sold',
                    'Total Sales',
                    'Invoice Count'
                ];
                
            case 'returns':
                return [
                    'ID',
                    'Product',
                    'Category',
                    'Company',
                    'Quantity Returned',
                    'Total Amount',
                    'Return Count'
                ];
                
            case 'purchases':
                return [
                    'ID',
                    'Product',
                    'Category',
                    'Company',
                    'Quantity Purchased',
                    'Total Cost',
                    'Avg. Purchase Price',
                    'Purchase Count'
                ];
                
            case 'deliveries':
                return [
                    'ID',
                    'Product',
                    'Category',
                    'Company',
                    'Quantity Delivered',
                    'Delivery Count'
                ];
                
            case 'consolidated':
                return [
                    'ID',
                    'Product',
                    'Category',
                    'Company',
                    'Sales',
                    'Returns',
                    'Purchases',
                    'Other Deliveries',
                    'Net Change',
                    'Current Stock'
                ];
                
            default:
                return ['No data available'];
        }
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
