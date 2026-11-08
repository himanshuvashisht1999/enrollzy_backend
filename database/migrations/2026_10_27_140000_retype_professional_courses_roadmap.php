<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * "Professional courses" (Class 12: diplomas in digital marketing, animation, hotel management, cabin crew…) are routes to
 * jobs, not skills: it moves to Career paths, so the website lists it under Career opportunities. Only the roadmap
 * with that title in Class 12 changes.
 */
return new class extends Migration
{
    public function up(): void
    {
        $cls = DB::table('career_roadmap_stages')->whereRaw('LOWER(TRIM(title)) = ?', ['class 12'])->value('id');
        if ($cls) DB::table('career_roadmap_sub_modules')->whereNull('parent_id')->where('stage_id', $cls)->where('roadmap_type', 'skills')
            ->where('title', 'like', 'Professional courses%')->update(['roadmap_type' => 'career_path', 'updated_at' => now()]);
    }

    public function down(): void
    {
        $cls = DB::table('career_roadmap_stages')->whereRaw('LOWER(TRIM(title)) = ?', ['class 12'])->value('id');
        if ($cls) DB::table('career_roadmap_sub_modules')->whereNull('parent_id')->where('stage_id', $cls)->where('roadmap_type', 'career_path')
            ->where('title', 'like', 'Professional courses%')->update(['roadmap_type' => 'skills']);
    }
};
