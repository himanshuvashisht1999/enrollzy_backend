<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exm_exams', function (Blueprint $table) {
            $table->string('cover_image')->nullable()->after('logo');
            $table->boolean('featured')->default(false)->after('has_stages');
            $table->unsignedInteger('sort_order')->default(0)->after('featured');
            // Archived keeps the page live but marks the exam as discontinued.
            $table->boolean('is_archived')->default(false)->after('status');
        });

        Schema::table('exm_conducting_bodies', function (Blueprint $table) {
            $table->enum('body_type', ['government', 'private', 'university'])->nullable()->after('short_name');
        });
    }

    public function down(): void
    {
        Schema::table('exm_exams', fn (Blueprint $t) => $t->dropColumn(['cover_image', 'featured', 'sort_order', 'is_archived']));
        Schema::table('exm_conducting_bodies', fn (Blueprint $t) => $t->dropColumn('body_type'));
    }
};
