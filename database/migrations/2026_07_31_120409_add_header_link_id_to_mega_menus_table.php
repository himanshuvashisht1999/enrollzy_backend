<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mega_menus', function (Blueprint $table) {
            // Add header_link_id to link mega_menu sub-items to header_links as parent
            $table->unsignedBigInteger('header_link_id')->nullable()->after('parent_id');
        });
    }

    public function down(): void
    {
        Schema::table('mega_menus', function (Blueprint $table) {
            $table->dropColumn('header_link_id');
        });
    }
};
