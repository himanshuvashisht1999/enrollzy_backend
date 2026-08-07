<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CustomerCategorySampleExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            ['Standard Category', '', 'Standard', 'active'],
            ['Sub Category', 'Standard Category', 'Standard', 'active'],
            ['Credit Customer', '', 'Credit', 'active'],
        ];
    }

    public function headings(): array
    {
        return [
            'Category Name',
            'Parent Category Name',
            'Type',
            'Status'
        ];
    }
}
