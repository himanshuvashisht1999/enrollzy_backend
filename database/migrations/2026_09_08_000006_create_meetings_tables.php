<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('meetings')) {
            Schema::create('meetings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->unsignedBigInteger('project_id')->nullable()->index();
                $table->string('title');
                $table->text('description')->nullable();
                $table->date('meeting_date');
                $table->time('start_time');
                $table->time('end_time')->nullable();
                $table->unsignedInteger('created_by')->nullable()->index();
                $table->string('meeting_type', 50)->default('online'); // online, in_person, hybrid
                $table->string('meeting_link')->nullable();
                $table->string('location')->nullable();
                $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
                $table->text('minutes_of_meeting')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('meeting_participants')) {
            Schema::create('meeting_participants', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('meeting_id')->index();
                $table->unsignedInteger('user_id')->nullable()->index();
                $table->unsignedBigInteger('external_contact_id')->nullable()->index();
                $table->enum('response', ['pending', 'accepted', 'declined', 'tentative'])->default('pending');
                $table->boolean('attended')->default(false);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_participants');
        Schema::dropIfExists('meetings');
    }
};
