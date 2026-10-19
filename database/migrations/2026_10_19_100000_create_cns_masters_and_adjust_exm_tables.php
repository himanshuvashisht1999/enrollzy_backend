<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Written so it can be re-run after a partial failure (MySQL DDL is not transactional):
 * every step checks whether it has already been applied.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->createMasters();

        $now = now();
        $this->seedCategories($now);
        $ids = DB::table('cns_categories')->pluck('id', 'slug');

        $this->moveFeeCategories($ids);
        $this->moveAgeRelaxationCategories($ids);

        // The counselling spec splits "government" into central / state.
        DB::statement("ALTER TABLE exm_conducting_bodies MODIFY body_type ENUM('government','central_govt','state_govt','university','private','other') NULL");

        $this->extendEventTypes($now);
    }

    private function createMasters(): void
    {
        if (!Schema::hasTable('cns_categories')) {
            Schema::create('cns_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('short_name', 50)->nullable();
                $table->string('slug', 80)->unique();
                $table->enum('scope', ['central', 'state'])->default('central');
                $table->json('states')->nullable();                 // when scope = state
                $table->boolean('is_pwd_subcategory')->default(false);
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('status')->default(true);
                $table->timestamps();
            });
        }
        if (!Schema::hasTable('cns_quotas')) {
            Schema::create('cns_quotas', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('short_name', 50)->nullable();
                $table->text('description')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('status')->default(true);
                $table->timestamps();
            });
        }
        if (!Schema::hasTable('cns_round_types')) {
            Schema::create('cns_round_types', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->boolean('is_fresh_registration_default')->default(false);
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('status')->default(true);
                $table->timestamps();
            });
        }
        if (!Schema::hasTable('cns_candidate_actions')) {
            Schema::create('cns_candidate_actions', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->text('description')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('status')->default(true);
                $table->timestamps();
            });
        }
        if (!Schema::hasTable('cns_document_types')) {
            Schema::create('cns_document_types', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->text('description')->nullable();
                $table->boolean('is_affidavit')->default(false);
                $table->boolean('status')->default(true);
                $table->timestamps();
            });
        }
        // Codes the counselling authorities use for a college. One college has a different code
        // at MCC, at JoSAA and at every state, so this is a child table, not a column.
        if (!Schema::hasTable('cns_institute_codes')) {
            Schema::create('cns_institute_codes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('organisation_id')->index();
                $table->foreignId('conducting_body_id')->constrained('exm_conducting_bodies')->cascadeOnDelete();
                $table->string('code', 60);
                $table->unsignedSmallInteger('valid_from_year')->nullable();
                $table->timestamps();
                $table->unique(['conducting_body_id', 'code']);
                $table->index(['organisation_id', 'conducting_body_id']);
            });
        }
    }

    private function seedCategories($now): void
    {
        // The central list the Exam Module previously hard-coded.
        $central = [['General', 'general', 'Gen'], ['EWS', 'ews', 'EWS'], ['OBC-NCL', 'obc_ncl', 'OBC'], ['SC', 'sc', 'SC'], ['ST', 'st', 'ST'], ['PwD', 'pwd', 'PwD']];
        foreach ($central as $i => [$name, $slug, $short]) {
            if (!DB::table('cns_categories')->where('slug', $slug)->exists()) {
                DB::table('cns_categories')->insert(['name' => $name, 'short_name' => $short, 'slug' => $slug, 'scope' => 'central',
                    'sort_order' => $i + 1, 'status' => true, 'created_at' => $now, 'updated_at' => $now]);
            }
        }
    }

    /** exm_fees.category (text key) -> category_id (master). */
    private function moveFeeCategories($ids): void
    {
        if (!Schema::hasColumn('exm_fees', 'category_id')) {
            Schema::table('exm_fees', fn (Blueprint $t) => $t->unsignedBigInteger('category_id')->nullable()->after('stage_id'));
        }
        if (!Schema::hasColumn('exm_fees', 'category')) {
            return;   // already converted
        }
        foreach (DB::table('exm_fees')->whereNull('category_id')->get(['id', 'category']) as $row) {
            DB::table('exm_fees')->where('id', $row->id)->update(['category_id' => $ids[$row->category] ?? $ids['general']]);
        }
        // Add the replacement index first: the cycle_id foreign key needs an index at all times.
        Schema::table('exm_fees', function (Blueprint $table) {
            $table->index(['cycle_id', 'category_id']);
        });
        Schema::table('exm_fees', function (Blueprint $table) {
            $table->dropIndex(['cycle_id', 'category']);
            $table->dropColumn('category');
        });
        Schema::table('exm_fees', function (Blueprint $table) {
            $table->foreign('category_id')->references('id')->on('cns_categories');
        });
    }

    /** Eligibility age relaxation: [{category: 'sc', years}] -> [{category_id, years}]. */
    private function moveAgeRelaxationCategories($ids): void
    {
        foreach (DB::table('exm_eligibilities')->whereNotNull('age_relaxation')->get(['id', 'age_relaxation']) as $row) {
            $old = json_decode($row->age_relaxation, true) ?: [];
            if (!collect($old)->contains(fn ($r) => array_key_exists('category', $r))) {
                continue;
            }
            $rows = collect($old)->map(fn ($r) => ['category_id' => $ids[$r['category'] ?? ''] ?? null, 'years' => $r['years'] ?? null])->all();
            DB::table('exm_eligibilities')->where('id', $row->id)->update(['age_relaxation' => json_encode($rows)]);
        }
    }

    /** Event types gain applies_to; counselling-only types are added. */
    private function extendEventTypes($now): void
    {
        if (!Schema::hasColumn('exm_event_types', 'applies_to')) {
            Schema::table('exm_event_types', function (Blueprint $table) {
                $table->enum('applies_to', ['exam', 'counselling', 'both'])->default('exam')->after('status_source');
            });
        }
        DB::table('exm_event_types')->whereIn('slug', ['registration', 'merit-list'])->update(['applies_to' => 'both']);

        $order = (int) DB::table('exm_event_types')->max('sort_order');
        $types = [
            ['Fee / deposit payment', 'dates'], ['Choice filling', 'dates'], ['Choice locking', 'dates'], ['Seat processing', 'dates'],
            ['Allotment result', 'manual'], ['Reporting / joining', 'dates'], ['Document verification', 'dates'],
            ['Resignation window', 'dates'], ['Upgradation window', 'dates'], ['Seat matrix release', 'manual'],
            ['Refund processing', 'dates'], ['Classes begin', 'dates'],
        ];
        foreach ($types as [$name, $source]) {
            $slug = Str::slug($name);
            if (!DB::table('exm_event_types')->where('slug', $slug)->exists()) {
                DB::table('exm_event_types')->insert(['name' => $name, 'slug' => $slug, 'status_source' => $source, 'applies_to' => 'counselling',
                    'sort_order' => ++$order, 'status' => true, 'created_at' => $now, 'updated_at' => $now]);
            }
        }
    }

    public function down(): void
    {
        DB::table('exm_event_types')->where('applies_to', 'counselling')->delete();
        Schema::table('exm_event_types', fn (Blueprint $t) => $t->dropColumn('applies_to'));
        DB::table('exm_conducting_bodies')->whereNotIn('body_type', ['government', 'private', 'university'])->update(['body_type' => null]);
        DB::statement("ALTER TABLE exm_conducting_bodies MODIFY body_type ENUM('government','private','university') NULL");

        $slugs = DB::table('cns_categories')->pluck('slug', 'id');
        Schema::table('exm_fees', fn (Blueprint $t) => $t->string('category', 20)->nullable()->after('stage_id'));
        foreach (DB::table('exm_fees')->get(['id', 'category_id']) as $row) {
            DB::table('exm_fees')->where('id', $row->id)->update(['category' => $slugs[$row->category_id] ?? 'general']);
        }
        Schema::table('exm_fees', function (Blueprint $table) {
            $table->index(['cycle_id', 'category']);
        });
        Schema::table('exm_fees', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropIndex(['cycle_id', 'category_id']);
            $table->dropColumn('category_id');
        });

        foreach (['cns_institute_codes', 'cns_document_types', 'cns_candidate_actions', 'cns_round_types', 'cns_quotas', 'cns_categories'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
