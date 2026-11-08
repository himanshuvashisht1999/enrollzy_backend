<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** A college record can be a university's constituent college (College form → College status). */
return new class extends Migration
{
    private const BASE = ['central', 'state_public', 'state_private', 'deemed', 'institution_of_national_importance', 'autonomous_college', 'affiliated_college'];

    public function up(): void
    {
        if (!Schema::hasColumn('organisations', 'legal_entity_type')) return;
        $this->enum([...self::BASE, 'constituent_college']);
    }

    public function down(): void
    {
        if (!Schema::hasColumn('organisations', 'legal_entity_type')) return;
        DB::table('organisations')->where('legal_entity_type', 'constituent_college')->update(['legal_entity_type' => null]);
        $this->enum(self::BASE);
    }

    private function enum(array $values): void
    {
        $list = implode(',', array_map(fn ($v) => "'" . $v . "'", $values));
        DB::statement("ALTER TABLE `organisations` MODIFY `legal_entity_type` ENUM($list) NULL DEFAULT NULL");
    }
};
