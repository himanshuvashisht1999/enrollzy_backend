<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The graduate survey (website /survey/graduates): one row per mobile number; a repeat submission updates the row and
 * counts it. The main answers have their own columns (to filter and export); every question and answer as asked is
 * in `answers` (a list of {key, q, a}). The team follows up with status and notes (CRM: Customers → Graduate Survey).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('graduate_surveys')) return;
        Schema::create('graduate_surveys', function (Blueprint $t) {
            $t->id();
            $t->string('phone', 10)->unique();
            $t->string('name', 120);
            $t->string('email', 150)->nullable();
            $t->string('current_state', 60)->nullable()->index();
            $t->string('stream', 20)->index();                 // ba, bcom, mbbs ... (website App\Support\Survey\GraduateSurvey)
            $t->string('stream_label', 80);
            $t->string('grad_status', 20)->nullable();
            $t->string('grad_status_label', 80)->nullable();
            $t->string('aim', 20)->index();                    // higher, abroad, job, govt ...
            $t->string('aim_label', 80);
            $t->string('preferred_course', 150)->nullable();
            $t->string('preferred_place', 255)->nullable();    // states or countries
            $t->string('focus', 255)->nullable();              // exams, sectors, business type ...
            $t->json('answers');                               // [{key, q, a}] in the order asked
            $t->unsignedBigInteger('customer_id')->nullable()->index(); // the student with this mobile, when there is one
            $t->enum('status', ['new', 'contacted', 'converted', 'not_interested'])->default('new')->index();
            $t->text('notes')->nullable();
            $t->unsignedInteger('submissions')->default(1);
            $t->boolean('consent')->default(false);
            $t->json('utm')->nullable();                       // utm_source / medium / campaign of the link they came from
            $t->string('ip', 45)->nullable();
            $t->string('user_agent', 255)->nullable();
            $t->timestamps();                                  // created = first submission, updated = latest
            $t->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('graduate_surveys');
    }
};
