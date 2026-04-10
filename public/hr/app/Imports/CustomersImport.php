<?php

namespace App\Imports;

use App\Models\Users;
use App\Models\UserCustomerField;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CustomersImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        $orgId = Auth::guard('admin')->user()->organization_id ?? null;

        foreach ($rows as $row) {

            // basic skip if name/email/phone empty
            if (empty($row['name']) && empty($row['phone'])) {
                continue;
            }
            $email = $row['email'] ?? null;
            $phone = $row['phone'] ?? null;

            // ❗ Skip if email or phone already exists
            // $exists = Users::where('email', $email)
            //     ->orWhere('phone', $phone)
            //     ->exists();
            $exists = Users::where('phone', $phone)->where('organization_id',$orgId)
                ->exists();

            if ($exists) {
                continue; // skip this row
            }

            // Create user (same structure as your storeCustomers)
            $user = Users::create([
                'name'           => $row['name'] ?? '',
                'email'           => $email,
                'phone'           => $phone,
                'category_id'    => $row['category_id'] ?? null,
                'institute_id'   => $row['institute_id'] ?? 0,
                'status'         => $row['status'] ?? 'active',
                'country'        => $row['country'] ?? '',
                'state'          => $row['state'] ?? '',
                'city'           => $row['city'] ?? '',
                'organization_id'=> $orgId,
            ]);

            // Now handle dynamic customer_fields
            // Header format assumed: cf_{id}_{label}
            foreach ($row as $key => $value) {
                if (! $value) {
                    continue;
                }

                if (strpos($key, 'cf_') === 0) {
                    $parts = explode('_', $key);
                    $fieldId = $parts[1] ?? null;

                    if ($fieldId) {
                        UserCustomerField::create([
                            'user_id'           => $user->id,
                            'customer_field_id' => $fieldId,
                            'value'             => $value,
                        ]);
                    }
                }
            }

        }
    }
}
