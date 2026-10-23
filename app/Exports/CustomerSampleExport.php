<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CustomerSampleExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            ['1', 'JYOTI', '8869935841', '1', '', 'BA', 'ABDUL SATTAR KHAN MAHAVIDHYALAYA, KHERON, RAEBARELI', '2026', 'Regular'],
            ['2', 'KAJAL', '7705070011', '2', 'kajal@example.com', 'BA', 'ABDUL SATTAR KHAN MAHAVIDHYALAYA, KHERON, RAEBARELI', '2026', 'Regular'],
        ];
    }

    public function headings(): array
    {
        return [
            'S.No',
            'NAME',
            'Phone Number',
            'Category ID',
            'Student Email',
            'Current Course',
            'Current University',
            'Passing Year',
            'Current Program Mode',
        ];
    }
}
