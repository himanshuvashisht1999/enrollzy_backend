<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('lead_assignments', 'is_reassigned')) {
            Schema::table('lead_assignments', function (Blueprint $table) {
                $table->boolean('is_reassigned')->default(false)->after('assigned_by')->index();
            });
        }

        // Backfill existing genuine reassigned leads from lead_activity_logs (where old_staff_id != admin_id)
        $reassignedLogs = DB::table('lead_activity_logs')
            ->where('action_type', 'reassigned')
            ->get();

        $genuineCustomerIds = [];
        foreach ($reassignedLogs as $log) {
            $props = json_decode($log->properties, true) ?? [];
            $oldStaff = $props['old_staff_id'] ?? null;
            if ($oldStaff && $oldStaff != $log->admin_id) {
                $genuineCustomerIds[] = $log->customer_id;
            }
        }

        $genuineCustomerIds = array_unique(array_filter($genuineCustomerIds));

        if (!empty($genuineCustomerIds)) {
            DB::table('lead_assignments')
                ->whereIn('customer_id', $genuineCustomerIds)
                ->update(['is_reassigned' => 1]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('lead_assignments', 'is_reassigned')) {
            Schema::table('lead_assignments', function (Blueprint $table) {
                $table->dropColumn('is_reassigned');
            });
        }
    }
};
