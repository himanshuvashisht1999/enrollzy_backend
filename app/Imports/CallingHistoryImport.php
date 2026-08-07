<?php

namespace App\Imports;

use App\Models\Customer;
use App\Models\CallingHistory;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Carbon\Carbon;

class CallingHistoryImport implements ToCollection, WithHeadingRow, SkipsEmptyRows, WithChunkReading
{
    protected $organization_id;

    public function __construct($organization_id)
    {
        $this->organization_id = $organization_id;
    }

    public function collection(Collection $rows)
    {
        $phones = $rows->pluck('phone_number')->filter()->unique();
        
        if ($phones->isEmpty()) {
            return;
        }

        // Fetch all existing customers in this chunk by phone
        $existingCustomers = Customer::whereIn('phone', $phones)->get()->keyBy('phone');
        
        $newCustomersToInsert = [];
        $callingHistoriesToInsert = [];
        $phonesProcessed = []; // To handle duplicates within the same chunk for customers

        foreach ($rows as $row) {
            $phone = $row['phone_number'] ?? null;
            if (!$phone) {
                continue;
            }

            $customerId = null;
            $customerName = null;

            if ($existingCustomers->has($phone)) {
                $existing = $existingCustomers->get($phone);
                $customerId = $existing->id;
                $customerName = $existing->name;
                $changes = false;
                
                if (isset($row['student_name']) && $existing->name !== $row['student_name']) {
                    $existing->name = $row['student_name'];
                    $customerName = $row['student_name'];
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
                if (!in_array($phone, $phonesProcessed)) {
                    // Create customer on the fly to get the ID for CallingHistory
                    $newCustomer = Customer::create([
                        'name'            => $row['student_name'] ?? 'Unknown',
                        'phone'           => $phone,
                        'category_id'     => $row['category_id'] ?? null,
                        'organization_id' => $this->organization_id,
                        'role'            => 'user',
                        'status'          => 'active',
                    ]);
                    
                    $existingCustomers->put($phone, $newCustomer);
                    $phonesProcessed[] = $phone;
                    
                    $customerId = $newCustomer->id;
                    $customerName = $newCustomer->name;
                } else {
                    $existing = $existingCustomers->get($phone);
                    $customerId = $existing->id;
                    $customerName = $existing->name;
                }
            }

            $reminderDate = null;
            if (!empty($row['reminder_date'])) {
                try {
                    $reminderDate = Carbon::parse($row['reminder_date'])->format('Y-m-d');
                } catch (\Exception $e) {
                    $reminderDate = null;
                }
            }

            $callingHistoriesToInsert[] = [
                'user_type'         => 'customer',
                'user_id'           => $customerId,
                'user_name'         => $customerName,
                'user_phone'        => $phone,
                'reason'            => $row['call_status_id'] ?? null,
                'calling_action_id' => $row['action_taken_id'] ?? null,
                'comment'           => $row['comment'] ?? null,
                'date_required'     => $reminderDate,
                'updated_by'        => auth()->id(),
                'status'            => 1,
                'organization_id'   => $this->organization_id,
                'created_at'        => now(),
                'updated_at'        => now(),
            ];
        }

        if (!empty($callingHistoriesToInsert)) {
            // Batch insert all new calling histories for this chunk
            foreach (array_chunk($callingHistoriesToInsert, 500) as $chunk) {
                CallingHistory::insert($chunk);
            }
        }
    }

    public function chunkSize(): int
    {
        return 2000;
    }
}
