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
        Schema::table('organisations', function (Blueprint $table) {});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('organisations', function (Blueprint $table) {
             // In a perfect world we would re-add all columns here, 
             // but for a destructive request, we might not be able to easily restore them 
             // without the original definitions.
             // We will leave this empty as per the "destructive" nature of the request.
         });
    }
};
