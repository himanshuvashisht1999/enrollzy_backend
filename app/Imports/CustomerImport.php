<?php

namespace App\Imports;

use App\Models\Customer;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class CustomerImport implements ToCollection, WithHeadingRow, SkipsEmptyRows, WithChunkReading
{
    protected $organization_id;

    public function __construct($organization_id)
    {
        $this->organization_id = $organization_id;
    }

    public function collection(Collection $rows)
    {
        $phones = $rows->pluck('phone')->filter()->unique();
        
        if ($phones->isEmpty()) {
            return;
        }

        // Fetch all existing customers in this chunk by phone
        $existingCustomers = Customer::whereIn('phone', $phones)->get()->keyBy('phone');
        
        $newRecords = [];
        $phonesProcessed = []; // To handle duplicates within the same chunk

        foreach ($rows as $row) {
            $phone = $row['phone'] ?? null;
            if (!$phone || in_array($phone, $phonesProcessed)) {
                continue;
            }

            if ($existingCustomers->has($phone)) {
                $existing = $existingCustomers->get($phone);
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
                    $existing->save(); // Save updates
                }
            } else {
                $newRecords[] = [
                    'name'            => $row['name'] ?? 'Unknown',
                    'phone'           => $phone,
                    'email'           => $row['email'] ?? null,
                    'category_id'     => $row['category_id'] ?? null,
                    'organization_id' => $this->organization_id,
                    'role'            => 'user',
                    'status'          => 'active',
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ];
            }
            
            $phonesProcessed[] = $phone;
        }

        if (!empty($newRecords)) {
            // Batch insert all new records for this chunk at once
            Customer::insert($newRecords);
        }
    }

    public function chunkSize(): int
    {
        return 2000;
    }
}
