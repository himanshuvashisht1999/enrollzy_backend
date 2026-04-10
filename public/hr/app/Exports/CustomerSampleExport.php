<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

class CustomerSampleExport implements FromArray
{
    protected $customerFields;

    public function __construct($customerFields)
    {
        $this->customerFields = $customerFields;
    }

    public function array(): array
    {
        // Fixed columns
        $header = [
            'name',
            'email',
            'phone',
            'country',
            'state',
            'city',
            'status',
            'category_id',
        ];

        // Dynamic CustomerFields columns
        // You can name them however you like – here I use: cf_{id}_{label}
        foreach ($this->customerFields as $field) {
            $header[] = 'cf_' . $field->id . '_' . $field->label;
        }

        // Optional: one row of dummy/sample data
        $sampleRow = [
            'John Doe',
            'john@example.com',
            '9876543210',
            'India',
            'Rajasthan',
            'Jaipur',
            'active',
            1,      // category_id sample
        ];

        // For dynamic fields, just fill blank or example values
        foreach ($this->customerFields as $field) {
            $sampleRow[] = '';  // or 'sample value'
        }

        return [
            $header,
            $sampleRow,
        ];
    }
}
