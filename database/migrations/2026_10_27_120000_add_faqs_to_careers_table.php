<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** A career's own questions and answers (the career page's FAQ shows them first, then the ones made from its data). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('careers', function (Blueprint $t) {
            if (!Schema::hasColumn('careers', 'faqs')) $t->json('faqs')->nullable()->after('recruiters');
        });
    }

    public function down(): void
    {
        Schema::table('careers', fn (Blueprint $t) => $t->dropColumn('faqs'));
    }
};
