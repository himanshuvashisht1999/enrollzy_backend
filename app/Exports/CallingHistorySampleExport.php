<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CallingHistorySampleExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            ['9876543210', 'John Doe', '1', '1', '1', '2026-12-31', 'Follow up in december'],
            ['1234567890', 'Jane Smith', '2', '2', '2', '2026-11-15', 'Requested brochure'],
        ];
    }

    public function headings(): array
    {
        return [
            'phone_number',
            'student_name',
            'category_id',
            'call_status_id',
            'action_taken_id',
            'reminder_date',
            'comment',
        ];
    }
}
