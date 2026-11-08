<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Career Roadmap module, structured: an order for stages, classes and items; a type for each roadmap (stream, career
 * path, exams…) and each item (exam, course, scholarship, career…) with its facts in their own fields and links to
 * the Exam, Course and Scholarship modules; stream details; a Careers master (doctor, CA, engineer…); and the
 * career roadmap page's texts.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('career_roadmap_categories', function (Blueprint $t) {
            if (!Schema::hasColumn('career_roadmap_categories', 'sort_order')) $t->integer('sort_order')->default(0)->after('slug');
            if (!Schema::hasColumn('career_roadmap_categories', 'description')) $t->text('description')->nullable()->after('sort_order');
        });

        Schema::table('career_roadmap_stages', function (Blueprint $t) {
            if (!Schema::hasColumn('career_roadmap_stages', 'sort_order')) $t->integer('sort_order')->default(0)->after('slug');
            // what the year is about, one point per line (the list page's class heading)
            if (!Schema::hasColumn('career_roadmap_stages', 'key_points')) $t->text('key_points')->nullable()->after('description');
        });

        Schema::table('career_roadmap_sub_modules', function (Blueprint $t) {
            $add = fn (string $c) => !Schema::hasColumn('career_roadmap_sub_modules', $c);
            if ($add('sort_order')) $t->integer('sort_order')->default(0)->after('slug');
            // a roadmap (top level): stream, career_path, exams… ; an item: step, course, exam, scholarship, career…
            if ($add('roadmap_type')) $t->string('roadmap_type', 30)->nullable()->after('sort_order');
            if ($add('item_type')) $t->string('item_type', 20)->nullable()->after('roadmap_type');
            // the roadmap's address on the website: /career-roadmap/{class}/{url_slug}
            if ($add('url_slug')) $t->string('url_slug', 120)->nullable()->after('item_type');
            if ($add('summary')) $t->string('summary', 300)->nullable()->after('title');
            if ($add('eligibility')) $t->text('eligibility')->nullable()->after('long_description');
            if ($add('conducted_by')) $t->string('conducted_by', 190)->nullable()->after('eligibility');
            if ($add('when_text')) $t->string('when_text', 190)->nullable()->after('conducted_by');
            if ($add('duration')) $t->string('duration', 100)->nullable()->after('when_text');
            if ($add('fee')) $t->string('fee', 255)->nullable()->after('duration');
            if ($add('salary')) $t->string('salary', 255)->nullable()->after('fee');
            if ($add('official_url')) $t->string('official_url', 500)->nullable()->after('salary');
            if ($add('exam_id')) $t->unsignedBigInteger('exam_id')->nullable()->after('official_url');
            if ($add('course_id')) $t->unsignedBigInteger('course_id')->nullable()->after('exam_id');
            if ($add('scholarship_id')) $t->unsignedBigInteger('scholarship_id')->nullable()->after('course_id');
            if ($add('career_id')) $t->unsignedBigInteger('career_id')->nullable()->after('scholarship_id');
            // a stream roadmap: its subjects, who it suits, what it rules out (one per line) and the careers it opens
            if ($add('subjects')) $t->text('subjects')->nullable()->after('career_id');
            if ($add('who_should')) $t->text('who_should')->nullable()->after('subjects');
            if ($add('closes')) $t->text('closes')->nullable()->after('who_should');
            if ($add('career_ids')) $t->json('career_ids')->nullable()->after('closes');
            if ($add('meta_title')) $t->string('meta_title', 255)->nullable()->after('career_ids');
            if ($add('meta_description')) $t->string('meta_description', 500)->nullable()->after('meta_title');
        });
        Schema::table('career_roadmap_sub_modules', function (Blueprint $t) {
            $t->index(['stage_id', 'parent_id', 'sort_order'], 'crsm_tree_idx');
        });

        if (!Schema::hasTable('careers')) {
            Schema::create('careers', function (Blueprint $t) {
                $t->id();
                $t->string('name');
                $t->string('slug')->unique();
                $t->string('icon', 20)->nullable();
                $t->string('summary', 300)->nullable();      // one line: what the job is
                $t->text('about')->nullable();               // what they do, in a few lines
                $t->string('stream', 255)->nullable();       // the stream it needs after Class 10
                $t->json('route')->nullable();               // the steps, in order
                $t->json('exams')->nullable();               // exam names (the Exam module's where it has them)
                $t->json('course_ids')->nullable();          // master courses
                $t->string('salary', 255)->nullable();
                $t->text('recruiters')->nullable();
                $t->json('show_for')->nullable();            // classes (career_roadmap_stages ids) where it is a popular career
                $t->integer('sort_order')->default(0);
                $t->boolean('status')->default(true);
                $t->string('meta_title', 255)->nullable();
                $t->string('meta_description', 500)->nullable();
                $t->timestamps();
            });
        }

        // the career roadmap page's texts (hero, lead form, read and discuss, FAQs), edited in the CRM
        Schema::table('settings', function (Blueprint $t) {
            if (!Schema::hasColumn('settings', 'career_page_content')) $t->json('career_page_content')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('settings', fn (Blueprint $t) => $t->dropColumn('career_page_content'));
        Schema::dropIfExists('careers');
        Schema::table('career_roadmap_sub_modules', function (Blueprint $t) {
            $t->dropIndex('crsm_tree_idx');
            $t->dropColumn(['sort_order', 'roadmap_type', 'item_type', 'url_slug', 'summary', 'eligibility', 'conducted_by', 'when_text', 'duration', 'fee', 'salary',
                'official_url', 'exam_id', 'course_id', 'scholarship_id', 'career_id', 'subjects', 'who_should', 'closes', 'career_ids', 'meta_title', 'meta_description']);
        });
        Schema::table('career_roadmap_stages', fn (Blueprint $t) => $t->dropColumn(['sort_order', 'key_points']));
        Schema::table('career_roadmap_categories', fn (Blueprint $t) => $t->dropColumn(['sort_order', 'description']));
    }
};
