<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Counselling Schema Builder, the same as the exam module's: extra tabs unique to one counselling
 * (e.g. "Bond and service rules", "Stray vacancy round"), each a field with one value for the whole
 * counselling (cycle_id NULL) or one per cycle.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('cns_custom_fields')) {
            Schema::create('cns_custom_fields', function (Blueprint $table) {
                $table->id();
                $table->foreignId('counselling_id')->constrained('cns_counsellings')->cascadeOnDelete();
                $table->string('key', 60);
                $table->string('label', 120);
                $table->string('type', 20);
                $table->json('options')->nullable();
                $table->enum('scope', ['counselling', 'cycle'])->default('counselling');
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
                $table->softDeletes();
                $table->unique(['counselling_id', 'key']);
            });
        }
        if (!Schema::hasTable('cns_custom_values')) {
            Schema::create('cns_custom_values', function (Blueprint $table) {
                $table->id();
                $table->foreignId('field_id')->constrained('cns_custom_fields')->cascadeOnDelete();
                $table->foreignId('cycle_id')->nullable()->constrained('cns_cycles')->cascadeOnDelete();
                $table->json('value')->nullable();
                $table->timestamps();
                $table->index(['field_id', 'cycle_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cns_custom_values');
        Schema::dropIfExists('cns_custom_fields');
    }
};
