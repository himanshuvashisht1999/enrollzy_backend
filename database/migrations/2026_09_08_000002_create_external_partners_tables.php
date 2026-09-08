<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('external_organizations')) {
            Schema::create('external_organizations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->string('name');
                $table->string('organization_type', 100)->default('Associate'); // Associate, Agency, Vendor, Consultant, Freelancer, Partner, Third-Party Team, Other
                $table->string('contact_person')->nullable();
                $table->string('email')->nullable();
                $table->string('phone', 50)->nullable();
                $table->text('address')->nullable();
                $table->string('website')->nullable();
                $table->text('notes')->nullable();
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('external_contacts')) {
            Schema::create('external_contacts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->unsignedBigInteger('external_organization_id')->index();
                $table->string('name');
                $table->string('designation')->nullable();
                $table->string('email')->nullable();
                $table->string('phone', 50)->nullable();
                $table->string('password')->nullable();
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('external_teams')) {
            Schema::create('external_teams', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->unsignedBigInteger('external_organization_id')->index();
                $table->string('name');
                $table->unsignedBigInteger('team_leader_contact_id')->nullable()->index();
                $table->text('description')->nullable();
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('external_team_members')) {
            Schema::create('external_team_members', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('external_team_id')->index();
                $table->unsignedBigInteger('external_contact_id')->index();
                $table->string('role', 100)->default('member');
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->timestamps();

                $table->unique(['external_team_id', 'external_contact_id'], 'ext_team_member_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('external_team_members');
        Schema::dropIfExists('external_teams');
        Schema::dropIfExists('external_contacts');
        Schema::dropIfExists('external_organizations');
    }
};
