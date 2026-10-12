<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('facility_organisation_type')) {
            Schema::create('facility_organisation_type', function (Blueprint $table) {
                $table->id();
                $table->uuid('facility_id');
                $table->unsignedBigInteger('organisation_type_id');
                $table->timestamps();

                $table->foreign('facility_id')
                    ->references('id')
                    ->on('facilities')
                    ->onDelete('cascade');

                $table->foreign('organisation_type_id')
                    ->references('id')
                    ->on('organisation_types')
                    ->onDelete('cascade');

                $table->unique(['facility_id', 'organisation_type_id'], 'facility_org_type_unique');
            });

            // Seed existing facilities to existing organisation types so current functionality is preserved
            $facilities = DB::table('facilities')->pluck('id');
            $orgTypes = DB::table('organisation_types')->pluck('id');
            $now = now();
            $inserts = [];
            foreach ($facilities as $facilityId) {
                foreach ($orgTypes as $orgTypeId) {
                    $inserts[] = [
                        'facility_id' => $facilityId,
                        'organisation_type_id' => $orgTypeId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            if (!empty($inserts)) {
                // Chunk insert if many
                foreach (array_chunk($inserts, 100) as $chunk) {
                    DB::table('facility_organisation_type')->insert($chunk);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facility_organisation_type');
    }
};
