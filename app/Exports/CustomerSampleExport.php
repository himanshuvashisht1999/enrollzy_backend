<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CustomerSampleExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            ['John Doe', '9876543210', 'john@example.com', '1'],
            ['Jane Smith', '1234567890', 'jane@example.com', '2'],
        ];
    }

    public function headings(): array
    {
        return [
            'name',
            'phone',
            'email',
            'category_id',
        ];
    }
}
