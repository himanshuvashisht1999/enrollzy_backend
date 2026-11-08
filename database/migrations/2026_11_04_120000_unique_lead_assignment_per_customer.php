<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * One assignment per lead: lead_assignments.customer_id becomes unique, so a lead can only ever sit with one staff
 * member (every assign / reassign updates that row). Older duplicate rows, if any, are removed first (the newest stays).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('DELETE la FROM lead_assignments la JOIN lead_assignments newer ON newer.customer_id = la.customer_id AND newer.id > la.id');
        $exists = DB::table('information_schema.statistics')->where('table_schema', DB::getDatabaseName())
            ->where('table_name', 'lead_assignments')->where('index_name', 'lead_assignments_customer_id_unique')->exists();
        if (!$exists) {
            DB::statement('ALTER TABLE lead_assignments ADD UNIQUE INDEX lead_assignments_customer_id_unique (customer_id)');
        }
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE lead_assignments DROP INDEX lead_assignments_customer_id_unique');
    }
};
