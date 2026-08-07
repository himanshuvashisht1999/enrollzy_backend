<?php

namespace App\Imports;

use App\Models\Customer;
use App\Models\CallingHistory;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Carbon\Carbon;

class CallingHistoryImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $organization_id;

    public function __construct($organization_id)
    {
        $this->organization_id = $organization_id;
    }

    public function model(array $row)
    {
        $phone = $row['phone_number'] ?? null;
        if (!$phone) {
            return null;
        }

        // Check if customer exists globally
        $customer = Customer::where('phone', $phone)->first();

        if ($customer) {
            // Update if name or category differ
            $changes = false;
            if (isset($row['student_name']) && $customer->name !== $row['student_name']) {
                $customer->name = $row['student_name'];
                $changes = true;
            }
            if (isset($row['category_id']) && $customer->category_id != $row['category_id']) {
                $customer->category_id = $row['category_id'];
                $changes = true;
            }
            if ($changes) {
                $customer->save();
            }
        } else {
            // Create new customer
            $customer = Customer::create([
                'name'            => $row['student_name'] ?? 'Unknown',
                'phone'           => $phone,
                'category_id'     => $row['category_id'] ?? null,
                'organization_id' => $this->organization_id,
                'role'            => 'user',
                'status'          => 'active',
            ]);
        }

        $reminderDate = null;
        if (!empty($row['reminder_date'])) {
            try {
                // If excel sends serial dates, Carbon can handle it if parseable, 
                // but usually we expect YYYY-MM-DD
                $reminderDate = Carbon::parse($row['reminder_date'])->format('Y-m-d');
            } catch (\Exception $e) {
                $reminderDate = null;
            }
        }

        // Create the calling history record
        return new CallingHistory([
            'user_type'         => 'customer',
            'user_id'           => $customer->id,
            'user_name'         => $customer->name,
            'user_phone'        => $customer->phone,
            'reason'            => $row['call_status_id'] ?? null, // legacy field name for status
            'calling_action_id' => $row['action_taken_id'] ?? null,
            'comment'           => $row['comment'] ?? null,
            'date_required'     => $reminderDate, // legacy field name for next call date
            'updated_by'        => auth()->id(),
            'status'            => 1,
            'organization_id'   => $this->organization_id,
        ]);
    }

    public function rules(): array
    {
        return [
            'phone_number' => 'required',
        ];
    }
}
