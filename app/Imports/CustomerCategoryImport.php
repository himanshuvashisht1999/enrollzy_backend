<?php

namespace App\Imports;

use App\Models\CustomerCategory;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CustomerCategoryImport implements ToModel, WithHeadingRow
{
    protected $organizationId;

    public function __construct($organizationId)
    {
        $this->organizationId = $organizationId;
    }

    public function model(array $row)
    {
        if (empty($row['category_name'])) {
            return null;
        }

        // Find parent category if parent_name is provided
        $parentId = 0;
        if (!empty($row['parent_category_name'])) {
            $parent = CustomerCategory::where('organization_id', $this->organizationId)
                ->where('name', trim($row['parent_category_name']))
                ->where('parent_id', 0)
                ->first();
            if ($parent) {
                $parentId = $parent->id;
            }
        }

        return new CustomerCategory([
            'name' => trim($row['category_name']),
            'parent_id' => $parentId,
            'customer_type' => !empty($row['type']) ? trim($row['type']) : 'Standard',
            'status' => !empty($row['status']) ? trim($row['status']) : 'active',
            'organization_id' => $this->organizationId,
        ]);
    }
}
