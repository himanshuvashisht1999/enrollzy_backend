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
        Schema::table('community_questions', function (Blueprint $table) {
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->after('is_verified');
            $table->boolean('is_active')->default(true)->after('status');
        });

        // Set status based on existing is_verified values
        \DB::statement("UPDATE community_questions SET status = CASE WHEN is_verified = 1 THEN 'approved' ELSE 'pending' END");

        Schema::table('community_replies', function (Blueprint $table) {
            $table->string('image')->nullable()->after('content');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->after('image');
            $table->boolean('is_active')->default(true)->after('status');
        });

        // By default existing replies might be considered approved
        \DB::statement("UPDATE community_replies SET status = 'approved'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('community_questions', function (Blueprint $table) {
            $table->dropColumn(['status', 'is_active']);
        });

        Schema::table('community_replies', function (Blueprint $table) {
            $table->dropColumn(['image', 'status', 'is_active']);
        });
    }
};
