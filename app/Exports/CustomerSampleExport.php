<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CustomerSampleExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            ['1', 'JYOTI', '', 'BA', 'ABDUL SATTAR KHAN MAHAVIDHYALAYA, KHERON, RAEBARELI', '8869935841', '2026', 'Regular'],
            ['2', 'KAJAL', 'kajal@example.com', 'BA', 'ABDUL SATTAR KHAN MAHAVIDHYALAYA, KHERON, RAEBARELI', '7705070011', '2026', 'Regular'],
        ];
    }

    public function headings(): array
    {
        return [
            'S.No',
            'NAME',
            'Student Email',
            'Current Course',
            'Current University',
            'Phone Number',
            'Passing Year',
            'Current Program Mode',
        ];
    }
}
