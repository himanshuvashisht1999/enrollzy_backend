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
        if (!Schema::hasTable('mega_menus')) {
            Schema::create('mega_menus', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->string('title');
                $table->string('url')->nullable();
                $table->string('column_title')->nullable(); // Column Group Heading e.g. "School Type", "Curriculum"
                $table->integer('sort_order')->default(0);
                $table->boolean('status')->default(true);
                $table->boolean('is_highlighted')->default(false);
                $table->timestamps();

                $table->foreign('parent_id')->references('id')->on('mega_menus')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mega_menus');
    }
};
