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
        if (!Schema::hasTable('academic_classes')) {
            Schema::create('academic_classes', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('title')->nullable();
                $table->boolean('status')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        $initialClasses = [
            'Playgroup', 'Nursery', 'LKG', 'UKG',
            'Class 1', 'Class 2', 'Class 3', 'Class 4', 'Class 5',
            'Class 6', 'Class 7', 'Class 8', 'Class 9', 'Class 10',
            'Class 11', 'Class 12', 'Dropper'
        ];

        $now = now();
        foreach ($initialClasses as $index => $className) {
            $exists = DB::table('academic_classes')->where('name', $className)->exists();
            if (!$exists) {
                DB::table('academic_classes')->insert([
                    'name' => $className,
                    'title' => $className,
                    'status' => true,
                    'sort_order' => $index + 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_classes');
    }
};
