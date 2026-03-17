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
        Schema::create('exam_stages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->boolean('status')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Seed default stages
        DB::table('exam_stages')->insert([
            ['title' => 'Preliminary',          'status' => true, 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['title' => 'Mains',                'status' => true, 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['title' => 'Interview',            'status' => true, 'sort_order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['title' => 'Medical examination',  'status' => true, 'sort_order' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['title' => 'Skill test',           'status' => true, 'sort_order' => 5, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_stages');
    }
};

