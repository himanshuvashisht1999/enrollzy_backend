<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if E-Learning Platform already exists
        $exists = DB::table('organisation_types')->where('title', 'E-Learning Platform')->exists();
        if (!$exists) {
            DB::table('organisation_types')->insert([
                'id' => 9,
                'title' => 'E-Learning Platform',
                'status' => 1,
                'sort_order' => 9,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('organisation_types')->where('title', 'E-Learning Platform')->delete();
    }
};
