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
        Schema::create('consultant_category_pivots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('consultant_id');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('sub_category_id')->nullable();
            $table->unsignedBigInteger('sub_sub_category_id')->nullable();
            $table->timestamps();

            $table->foreign('consultant_id')->references('id')->on('consultants')->onDelete('cascade');
        });

        // Migrate existing data if any
        $consultants = DB::table('consultants')->get();
        foreach ($consultants as $consultant) {
            if ($consultant->category_id) {
                DB::table('consultant_category_pivots')->insert([
                    'consultant_id' => $consultant->id,
                    'category_id' => $consultant->category_id,
                    'sub_category_id' => $consultant->sub_category_id,
                    'sub_sub_category_id' => $consultant->sub_sub_category_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultant_category_pivots');
    }
};
