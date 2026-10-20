<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Log of campuses moved between organisations (splitting a multi-university brand). Holds the exact ids moved so a move can be undone. */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('organisation_moves')) {
            Schema::create('organisation_moves', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('from_organisation_id')->index();
                $table->unsignedBigInteger('to_organisation_id')->index();
                $table->boolean('created_target')->default(false);          // the target organisation was created by this move
                $table->longText('moved')->nullable();                      // {"campuses":[uuid...],"departments":[uuid...],"organisation_courses":[id...],"organisation_school_courses":[id...]}
                $table->enum('status', ['applied', 'undone'])->default('applied');
                $table->text('note')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->dateTime('undone_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('organisation_moves');
    }
};
