<?php

namespace App\Imports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class CustomerImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $organization_id;

    public function __construct($organization_id)
    {
        $this->organization_id = $organization_id;
    }

    public function model(array $row)
    {
        $phone = $row['phone'] ?? null;
        if (!$phone) {
            return null; // Skip if no phone
        }

        $existing = Customer::where('phone', $phone)->first();

        if ($existing) {
            $changes = false;
            if (isset($row['name']) && $existing->name !== $row['name']) {
                $existing->name = $row['name'];
                $changes = true;
            }
            if (isset($row['email']) && $existing->email !== $row['email']) {
                $existing->email = $row['email'];
                $changes = true;
            }
            if (isset($row['category_id']) && $existing->category_id != $row['category_id']) {
                $existing->category_id = $row['category_id'];
                $changes = true;
            }

            if ($changes) {
                $existing->save();
            }

            return null; // Already handled update, no need to return a new model
        }

        return new Customer([
            'name'            => $row['name'] ?? 'Unknown',
            'phone'           => $phone,
            'email'           => $row['email'] ?? null,
            'category_id'     => $row['category_id'] ?? null,
            'organization_id' => $this->organization_id,
            'role'            => 'user',
            'status'          => 'active',
        ]);
    }

    public function rules(): array
    {
        return [
            'phone' => 'required',
        ];
    }
}
