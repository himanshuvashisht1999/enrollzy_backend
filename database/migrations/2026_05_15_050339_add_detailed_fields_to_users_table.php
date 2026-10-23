<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $cols = [
                'dob' => 'date', 'gender' => 'string', 'aadhaar_number' => 'string', 'alternate_mobile' => 'string',
                'interested_in_ids' => 'json', 'interested_in_course' => 'string', 'program_level' => 'string', 'mode' => 'string', 'session_ids' => 'json',
                'father_name' => 'string', 'father_mobile' => 'string', 'father_email' => 'string', 'father_occupation' => 'string',
                'mother_name' => 'string', 'mother_mobile' => 'string', 'mother_email' => 'string', 'mother_occupation' => 'string',
                'sibling_enrolled' => 'string', 'referred_by' => 'string', 'source' => 'string',
                'registration_no' => 'string', 'class_batch' => 'string', 'counselor_name' => 'string', 'registration_date' => 'date', 'payment_status' => 'string', 'remarks' => 'text'
            ];
            foreach ($cols as $col => $type) {
                if (!Schema::hasColumn('users', $col)) {
                    if ($type === 'date') $table->date($col)->nullable();
                    elseif ($type === 'json') $table->json($col)->nullable();
                    elseif ($type === 'text') $table->text($col)->nullable();
                    else $table->string($col)->nullable();
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'dob', 'gender', 'aadhaar_number', 'alternate_mobile',
                'interested_in_ids', 'interested_in_course', 'program_level', 'mode', 'session_ids',
                'father_name', 'father_mobile', 'father_email', 'father_occupation',
                'mother_name', 'mother_mobile', 'mother_email', 'mother_occupation',
                'sibling_enrolled', 'referred_by', 'source',
                'registration_no', 'class_batch', 'counselor_name', 'registration_date', 'payment_status', 'remarks'
            ]);
        });
    }
};
