<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Career categories (Medical & healthcare, Engineering & technology…): the filter and the groups of the website's
 * careers page. Each career belongs to one. Fills the categories and files the careers the content pass created
 * (a career that already has a category keeps it).
 */
return new class extends Migration
{
    private const CATEGORIES = [
        // slug => [name, icon, careers]
        'medical' => ['Medical & healthcare', 'med', ['doctor', 'specialist-doctor', 'pharmacist', 'nurse', 'physiotherapist', 'veterinarian', 'psychologist']],
        'engineering' => ['Engineering & technology', 'eng', ['engineer', 'psu-engineer', 'software-developer', 'data-scientist']],
        'science' => ['Science & research', 'data', ['scientist', 'biotechnologist', 'agricultural-scientist']],
        'commerce' => ['Commerce & finance', 'ca', ['chartered-accountant', 'company-secretary', 'bank-officer', 'finance-analyst', 'economist']],
        'management' => ['Management & hospitality', 'biz', ['manager', 'hotel-manager']],
        'law' => ['Law', 'law', ['lawyer', 'judge']],
        'government' => ['Government & civil services', 'gov', ['civil-servant', 'ssc-officer']],
        'defence' => ['Defence & aviation', 'def', ['defence-officer', 'merchant-navy-officer', 'pilot']],
        'design' => ['Design, media & architecture', 'des', ['designer', 'architect', 'journalist']],
        'education' => ['Education & teaching', 'arts', ['teacher', 'professor']],
        'sports' => ['Sports', 'arts', ['sportsperson']],
    ];

    public function up(): void
    {
        if (!Schema::hasTable('career_categories')) {
            Schema::create('career_categories', function (Blueprint $t) {
                $t->id();
                $t->string('name');
                $t->string('slug')->unique();
                $t->string('icon', 20)->nullable();
                $t->string('description', 300)->nullable();
                $t->integer('sort_order')->default(0);
                $t->boolean('status')->default(true);
                $t->timestamps();
            });
        }
        Schema::table('careers', function (Blueprint $t) {
            if (!Schema::hasColumn('careers', 'category_id')) $t->unsignedBigInteger('category_id')->nullable()->after('icon')->index();
        });

        $i = 0;
        foreach (self::CATEGORIES as $slug => [$name, $icon, $careers]) {
            $i += 10;
            $id = DB::table('career_categories')->where('slug', $slug)->value('id')
                ?? DB::table('career_categories')->insertGetId(['name' => $name, 'slug' => $slug, 'icon' => $icon, 'sort_order' => $i, 'status' => 1, 'created_at' => now(), 'updated_at' => now()]);
            DB::table('careers')->whereIn('slug', $careers)->whereNull('category_id')->update(['category_id' => $id, 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        Schema::table('careers', fn (Blueprint $t) => $t->dropColumn('category_id'));
        Schema::dropIfExists('career_categories');
    }
};
